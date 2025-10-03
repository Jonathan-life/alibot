import os
import glob
import xml.etree.ElementTree as ET
import mysql.connector
import logging
import zipfile
import re
import sys
import fitz  # PyMuPDF (pip install pymupdf)
import json
from datetime import datetime

# ==========================
# CONFIGURACIÓN
# ==========================
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DOWNLOAD_DIR = os.path.join(BASE_DIR, "descargas_sunat")
os.makedirs(DOWNLOAD_DIR, exist_ok=True)

LOG_FILE = os.path.abspath("log.txt")
logging.basicConfig(
    filename=LOG_FILE,
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    encoding="utf-8"
)

def log(msg, level="info"):
    print(msg)
    if level == "error":
        logging.error(msg)
    else:
        logging.info(msg)

def get_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="sistema_contable"
    )

def guardar_archivo_binario(id_factura, archivo, tipo):
    if id_factura is None:
        log(f" No se puede guardar {archivo} porque no hay ID de factura", "error")
        return

    ruta = os.path.join(DOWNLOAD_DIR, archivo)
    if not os.path.exists(ruta):
        log(f" No existe el archivo: {ruta}", "error")
        return

    try:
        conn = get_connection()
        cursor = conn.cursor()

        # Verificar si ya existe
        cursor.execute("""
            SELECT COUNT(*) FROM archivos_factura
            WHERE id_factura = %s AND nombre_archivo = %s
        """, (id_factura, archivo))
        existe = cursor.fetchone()[0]

        if existe > 0:
            log(f"⚠️ Ya existe en BD: {archivo} (ID factura {id_factura}), no se guarda duplicado")
            cursor.close()
            conn.close()
            return

        with open(ruta, "rb") as f:
            contenido = f.read()

        cursor.execute("""
            INSERT INTO archivos_factura (id_factura, tipo, nombre_archivo, ruta, archivo_binario)
            VALUES (%s, %s, %s, %s, %s)
        """, (id_factura, tipo.upper(), archivo, ruta, contenido))
        conn.commit()
        cursor.close()
        conn.close()

        log(f"Guardado en BD (BLOB): {archivo} ({tipo.upper()})")
    except Exception as e:
        log(f" Error al guardar archivo {archivo} en BD: {e}", "error")


def procesar_xml(archivo, id_empresa, fecha_inicio=None, fecha_fin=None):
    ruta = os.path.join(DOWNLOAD_DIR, archivo)
    if not os.path.exists(ruta):
        log(f" No existe el archivo XML: {ruta}", "error")
        return None, None

    try:
        tree = ET.parse(ruta)
        root = tree.getroot()
        ns = {
            "cbc": "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
            "cac": "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
        }

        # Serie y correlativo
        nro_cpe = root.find(".//cbc:ID", ns)
        nro_cpe = nro_cpe.text.strip() if nro_cpe is not None else ""
        serie, correlativo = ("", "")
        if "-" in nro_cpe:
            serie, correlativo = nro_cpe.split("-", 1)

        # Fechas
        fecha_emision = root.find(".//cbc:IssueDate", ns)
        fecha_emision = fecha_emision.text.strip() if fecha_emision is not None else ""

        fecha_vencimiento = root.find(".//cbc:DueDate", ns)
        fecha_vencimiento = fecha_vencimiento.text.strip() if fecha_vencimiento is not None else None

        if fecha_inicio and fecha_fin:
            fecha_emision_dt = datetime.strptime(fecha_emision, "%Y-%m-%d")
            if fecha_emision_dt < fecha_inicio or fecha_emision_dt > fecha_fin:
                log(f"Factura {nro_cpe} fecha {fecha_emision} fuera del rango {fecha_inicio.strftime('%Y-%m-%d')} - {fecha_fin.strftime('%Y-%m-%d')}")
                return None, None

        # Emisor
        ruc_emisor = root.find(".//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID", ns)
        ruc_emisor = ruc_emisor.text.strip() if ruc_emisor is not None else ""

        nombre_emisor = root.find(".//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName", ns)
        nombre_emisor = nombre_emisor.text.strip() if nombre_emisor is not None else ""

        # Receptor
        ruc_receptor = root.find(".//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID", ns)
        ruc_receptor = ruc_receptor.text.strip() if ruc_receptor is not None else ""

        nombre_receptor = root.find(".//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName", ns)
        nombre_receptor = nombre_receptor.text.strip() if nombre_receptor is not None else ""

        # Descripción (primera línea de detalle)
        descripcion = root.find(".//cac:InvoiceLine/cac:Item/cbc:Description", ns)
        descripcion = descripcion.text.strip() if descripcion is not None else ""

        # Montos
        base_imponible = root.find(".//cac:LegalMonetaryTotal/cbc:LineExtensionAmount", ns)
        base_imponible = float(base_imponible.text.strip()) if base_imponible is not None else 0.0

        igv = root.find(".//cac:TaxTotal/cbc:TaxAmount", ns)
        igv = float(igv.text.strip()) if igv is not None else 0.0

        importe_total = root.find(".//cac:LegalMonetaryTotal/cbc:PayableAmount", ns)
        importe_total = float(importe_total.text.strip()) if importe_total is not None else 0.0

        moneda = root.find(".//cbc:DocumentCurrencyCode", ns)
        moneda = moneda.text.strip() if moneda is not None else "PEN"

        # Tipo documento
        tipo_doc_code = root.find(".//cbc:InvoiceTypeCode", ns)
        tipo_doc_code = tipo_doc_code.text.strip() if tipo_doc_code is not None else "01"
        tipo_doc = {
            "01": "FACTURA",
            "03": "BOLETA",
            "07": "NC",
            "08": "ND"
        }.get(tipo_doc_code, "OTROS")

        # Origen -> depende de si el RUC emisor es el de la empresa
        origen = "COMPRA"
        if ruc_emisor == str(id_empresa):  # OJO: aquí deberías validar con el RUC real de tu empresa
            origen = "VENTA"

        estado_sunat = "ACEPTADO"

        # Guardar en BD
        conn = get_connection()
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO facturas (
                id_empresa, tipo_doc, serie, correlativo, nro_cpe,
                fecha_emision, fecha_vencimiento, ruc_emisor, nombre_emisor,
                ruc_receptor, nombre_receptor, descripcion, base_imponible,
                igv, importe_total, moneda, origen, estado_sunat, fecha_registro
            )
            VALUES (
                %s, %s, %s, %s, %s,
                %s, %s, %s, %s,
                %s, %s, %s, %s,
                %s, %s, %s, %s, %s, NOW()
            )
        """, (
            id_empresa, tipo_doc, serie, correlativo, nro_cpe,
            fecha_emision, fecha_vencimiento, ruc_emisor, nombre_emisor,
            ruc_receptor, nombre_receptor, descripcion, base_imponible,
            igv, importe_total, moneda, origen, estado_sunat
        ))
        conn.commit()
        id_factura = cursor.lastrowid
        cursor.close()
        conn.close()

        log(f" Factura guardada: {nro_cpe} - {nombre_emisor}")
        return id_factura, nro_cpe

    except Exception as e:
        log(f" Error procesando XML {archivo}: {e}", "error")
        return None, None

def procesar_pdf(pdf_path):
    """
    Extrae el nro_cpe del contenido del PDF.
    Retorna nro_cpe o None si no se encuentra.
    """
    try:
        doc = fitz.open(pdf_path)
        texto = ""
        for pagina in doc:
            texto += pagina.get_text("text")
        doc.close()

        # Buscar el patrón E###-correlativo (ejemplo: E001-12345)
        match = re.search(r'(E\d{3})-?(\d+)', texto)
        if match:
            serie = match.group(1)
            correlativo = match.group(2)
            return f"{serie}{correlativo}"
        return None
    except Exception as e:
        log(f" Error leyendo PDF {os.path.basename(pdf_path)}: {e}", "error")
        return None


def procesar_archivos_descargados(id_empresa, fecha_inicio=None, fecha_fin=None):
    """
    Extrae ZIPs, procesa XML y PDF para guardar en BD y elimina archivos luego de procesar.
    """
    zip_files = glob.glob(os.path.join(DOWNLOAD_DIR, "*.zip"))
    log(f"ZIPs encontrados: {len(zip_files)}")

    xml_files = []
    xml_map = {}  # nro_cpe normalizado → (id_factura, zip_name)

    for zip_file in zip_files:
        zip_name = os.path.basename(zip_file)
        try:
            with zipfile.ZipFile(zip_file, 'r') as zip_ref:
                zip_ref.extractall(DOWNLOAD_DIR)
                log(f" Extraído ZIP: {zip_name}")

                xml_in_zip = [f for f in zip_ref.namelist() if f.lower().endswith(".xml")]
                if not xml_in_zip:
                    log(f" No se encontró XML dentro de {zip_name}", "error")
                    continue

                xml_path = os.path.join(DOWNLOAD_DIR, xml_in_zip[0])
                log(f" Procesando XML dentro del ZIP: {xml_in_zip[0]}")

                id_factura, nro_cpe = procesar_xml(xml_in_zip[0], id_empresa, fecha_inicio, fecha_fin)

                if id_factura and nro_cpe:
                    log(f" Guardando ZIP en BD vinculado a factura ID {id_factura}")
                    guardar_archivo_binario(id_factura, zip_name, "ZIP")
                    xml_files.append(xml_path)

                    key = nro_cpe.replace("-", "").upper()
                    xml_map[key] = (id_factura, zip_name)
                    log(f" Mapeado nro_cpe {key} → factura ID {id_factura} desde {zip_name}")
                else:
                    log(f" No se pudo procesar factura para XML {xml_in_zip[0]}", "error")

        except Exception as e:
            log(f" Error extrayendo {zip_name}: {e}", "error")

    # --- Procesar PDFs ---
    pdf_files = glob.glob(os.path.join(DOWNLOAD_DIR, "*.pdf")) + glob.glob(os.path.join(DOWNLOAD_DIR, "*.PDF"))
    log(f"Archivos PDF encontrados: {len(pdf_files)}")

    if len(pdf_files) == 0:
        log(" No hay archivos PDF para procesar")

    for pdf_path in pdf_files:
        pdf_name = os.path.basename(pdf_path).upper()

        # 1. Intentar extraer nro_cpe desde el contenido del PDF
        nro_cpe_pdf = procesar_pdf(pdf_path)

        # 2. Si no se pudo, intentar desde el nombre del archivo
        if not nro_cpe_pdf:
            pdf_base = os.path.splitext(pdf_name)[0]
            match = re.search(r'(E\d{3})-?(\d+)', pdf_base)
            if match:
                serie = match.group(1)
                correlativo = match.group(2)
                nro_cpe_pdf = f"{serie}{correlativo}"

        if nro_cpe_pdf:
            if nro_cpe_pdf in xml_map:
                id_factura, zip_name = xml_map[nro_cpe_pdf]
                log(f" Asociando PDF {pdf_name} con factura nro_cpe {nro_cpe_pdf} (desde {zip_name}) -> factura ID {id_factura}")
                guardar_archivo_binario(id_factura, pdf_name, "PDF")
            else:
                posibles_keys = [k for k in xml_map.keys() if nro_cpe_pdf in k or k in nro_cpe_pdf]
                if posibles_keys:
                    id_factura, zip_name = xml_map[posibles_keys[0]]
                    log(f" Asociando PDF {pdf_name} con factura nro_cpe {nro_cpe_pdf} (aprox desde {zip_name}) -> factura ID {id_factura}")
                    guardar_archivo_binario(id_factura, pdf_name, "PDF")
                else:
                    log(f" PDF {pdf_name} no asociado a ninguna factura", "error")
        else:
            log(f"No se pudo extraer nro_cpe en PDF: {pdf_name}", "error")


    # --- Eliminar archivos procesados ---
    for file_path in xml_files + pdf_files + zip_files:
        try:
            os.remove(file_path)
            log(f" Eliminado de carpeta: {os.path.basename(file_path)}")
        except Exception as e:
            log(f"Error eliminando archivo {file_path}: {e}", "error")

if __name__ == "__main__":
    if len(sys.argv) < 2:
        log(" Debes proporcionar la ruta al archivo JSON como argumento", "error")
        sys.exit(1)

    json_path = sys.argv[1]
    if not os.path.exists(json_path):
        log(f" El archivo JSON no existe: {json_path}", "error")
        sys.exit(1)

    with open(json_path, "r", encoding="utf-8") as f:
        data = json.load(f)

    empresa = data.get("empresa", {})
    id_empresa = empresa.get("id_empresa")
    if not id_empresa:
        log(" No se encontró 'id_empresa' en JSON", "error")
        sys.exit(1)

    fecha_inicio = None
    fecha_fin = None
    if "fecha_inicio" in data and "fecha_fin" in data:
        try:
            fecha_inicio = datetime.strptime(data["fecha_inicio"], "%d/%m/%Y")
            fecha_fin = datetime.strptime(data["fecha_fin"], "%d/%m/%Y")
            log(f"Filtro fechas: {fecha_inicio.strftime('%Y-%m-%d')} - {fecha_fin.strftime('%Y-%m-%d')}")
        except Exception as e:
            log(f"⚠️ No se pudo parsear fechas: {e}", "error")
            fecha_inicio = None
            fecha_fin = None

    log(f" Iniciando procesamiento para empresa ID {id_empresa}")
    procesar_archivos_descargados(id_empresa, fecha_inicio, fecha_fin)
    log(" Proceso finalizado correctamente")