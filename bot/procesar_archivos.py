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
from decimal import Decimal
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

        cursor.execute("""
            SELECT COUNT(*) FROM archivos_factura
            WHERE id_factura = %s AND nombre_archivo = %s
        """, (id_factura, archivo))
        existe = cursor.fetchone()[0]

        if existe > 0:
            log(f" Ya existe en BD: {archivo} (ID factura {id_factura}), no se guarda duplicado")
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

        log(f" Guardado en BD (BLOB): {archivo} ({tipo.upper()})")
    except Exception as e:
        log(f" Error al guardar archivo {archivo} en BD: {e}", "error")

def procesar_xml(archivo, id_empresa, ruc_empresa, fecha_inicio=None, fecha_fin=None):
    ruta = os.path.join(DOWNLOAD_DIR, archivo)
    if not os.path.exists(ruta):
        log(f"No existe el archivo XML: {ruta}", "error")
        return None, None

    try:
        tree = ET.parse(ruta)
        root = tree.getroot()
        ns = {
            "cbc": "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
            "cac": "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
        }

        # === Serie y correlativo ===
        nro_cpe_node = root.find(".//cbc:ID", ns)
        nro_cpe = nro_cpe_node.text.strip() if nro_cpe_node is not None else ""
        serie, correlativo = ("", "")
        if "-" in nro_cpe:
            serie, correlativo = nro_cpe.split("-", 1)

        # === Fechas ===
        fecha_emision_node = root.find(".//cbc:IssueDate", ns)
        fecha_emision = fecha_emision_node.text.strip() if fecha_emision_node is not None else None

        fecha_vencimiento_node = root.find(".//cbc:DueDate", ns)
        fecha_vencimiento = fecha_vencimiento_node.text.strip() if fecha_vencimiento_node is not None else None

        # Filtro por rango de fechas
        if fecha_inicio and fecha_fin and fecha_emision:
            fecha_emision_dt = datetime.strptime(fecha_emision, "%Y-%m-%d")
            if not (fecha_inicio <= fecha_emision_dt <= fecha_fin):
                log(f"Factura {nro_cpe} ({fecha_emision}) fuera del rango {fecha_inicio.date()} - {fecha_fin.date()}")
                return None, None

        # === Emisor y receptor ===
        ruc_emisor = root.findtext(".//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID", default="", namespaces=ns)
        nombre_emisor = root.findtext(".//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName", default="", namespaces=ns)
        ruc_receptor = root.findtext(".//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID", default="", namespaces=ns)
        nombre_receptor = root.findtext(".//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName", default="", namespaces=ns)

        # === Descripción ===
        descripcion = root.findtext(".//cac:InvoiceLine/cac:Item/cbc:Description", default="", namespaces=ns)

        # === Montos ===
        base_imponible = Decimal(root.findtext(".//cac:LegalMonetaryTotal/cbc:LineExtensionAmount", default="0.00", namespaces=ns))
        igv = Decimal(root.findtext(".//cac:TaxTotal/cbc:TaxAmount", default="0.00", namespaces=ns))
        importe_total = Decimal(root.findtext(".//cac:LegalMonetaryTotal/cbc:PayableAmount", default="0.00", namespaces=ns))
        moneda = root.findtext(".//cbc:DocumentCurrencyCode", default="PEN", namespaces=ns)

        # === Subtotales por tipo ===
        base_gravadas = base_exoneradas = base_inafectas = base_exportacion = Decimal("0.00")
        for tax_subtotal in root.findall(".//cac:TaxSubtotal", ns):
            tax_amount = Decimal(tax_subtotal.findtext("cbc:TaxableAmount", default="0.00", namespaces=ns))
            reason = tax_subtotal.findtext(".//cbc:TaxExemptionReasonCode", default="", namespaces=ns)
            tax_code = tax_subtotal.findtext(".//cac:TaxCategory/cac:TaxScheme/cbc:ID", default="", namespaces=ns)
            if reason == "10" or tax_code == "1000": base_gravadas += tax_amount
            elif reason == "20" or tax_code == "9998": base_inafectas += tax_amount
            elif reason == "30" or tax_code == "9997": base_exoneradas += tax_amount
            elif reason == "40" or tax_code == "9995": base_exportacion += tax_amount

        # === Tipo documento y origen ===
        tipo_doc_code = root.findtext(".//cbc:InvoiceTypeCode", default="01", namespaces=ns)
        tipo_doc = {"01":"FACTURA","03":"BOLETA","07":"NC","08":"ND"}.get(tipo_doc_code, "OTROS")
        origen = "COMPRA" if ruc_emisor != ruc_empresa else "VENTA"
        estado_sunat = "ACEPTADO"

        valores = (
            id_empresa, tipo_doc, serie, correlativo, nro_cpe,
            fecha_emision, fecha_vencimiento, ruc_emisor, nombre_emisor,
            ruc_receptor, nombre_receptor, descripcion,
            base_imponible, igv, importe_total, moneda,
            origen, estado_sunat,
            base_gravadas, base_exoneradas, base_inafectas, base_exportacion,
            None
        )

        log(f"Valores para INSERT XML {archivo}: {valores}")

        conn = get_connection()
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO facturas (
                id_empresa, tipo_doc, serie, correlativo, nro_cpe,
                fecha_emision, fecha_vencimiento, ruc_emisor, nombre_emisor,
                ruc_receptor, nombre_receptor, descripcion, base_imponible,
                igv, importe_total, moneda, origen, estado_sunat,
                base_gravadas, base_exoneradas, base_inafectas, base_exportacion,
                id_usuario_import
            ) VALUES (
                %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s,
                %s, %s, %s
            )
        """, valores)

        conn.commit()
        id_factura = cursor.lastrowid
        cursor.close()
        conn.close()

        log(f"Factura guardada: {nro_cpe} - {nombre_emisor} ({origen})")
        return id_factura, nro_cpe

    except Exception as e:
        log(f"ERROR SQL procesando XML {archivo}: {e}", "error")
        return None, None


def procesar_pdf(pdf_path):
    try:
        doc = fitz.open(pdf_path)
        texto = ""
        for pagina in doc:
            texto += pagina.get_text("text")
        doc.close()

        match = re.search(r'([A-Z]\d{3})-?(\d+)', texto)
        if match:
            serie = match.group(1)
            correlativo = match.group(2)
            return f"{serie}{correlativo}"
        return None
    except Exception as e:
        log(f" Error leyendo PDF {os.path.basename(pdf_path)}: {e}", "error")
        return None

def procesar_archivos_descargados(id_empresa, ruc_empresa, fecha_inicio=None, fecha_fin=None):
    zip_files = glob.glob(os.path.join(DOWNLOAD_DIR, "*.zip"))
    log(f" ZIPs encontrados: {len(zip_files)}")

    xml_files = []
    xml_map = {}

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
                id_factura, nro_cpe = procesar_xml(xml_in_zip[0], id_empresa, ruc_empresa, fecha_inicio, fecha_fin)

                if id_factura and nro_cpe:
                    guardar_archivo_binario(id_factura, zip_name, "ZIP")
                    xml_files.append(xml_path)
                    key = nro_cpe.replace("-", "").upper()
                    xml_map[key] = (id_factura, zip_name)
                else:
                    log(f" No se pudo procesar XML {xml_in_zip[0]}", "error")

        except Exception as e:
            log(f" Error extrayendo {zip_name}: {e}", "error")

    # === PDFs ===
    pdf_files = glob.glob(os.path.join(DOWNLOAD_DIR, "*.pdf")) + glob.glob(os.path.join(DOWNLOAD_DIR, "*.PDF"))
    log(f" PDFs encontrados: {len(pdf_files)}")

    for pdf_path in pdf_files:
        pdf_name = os.path.basename(pdf_path).upper()
        nro_cpe_pdf = procesar_pdf(pdf_path)
        if not nro_cpe_pdf:
            match = re.search(r'([A-Z]\d{3})-?(\d+)', pdf_name)
            if match:
                nro_cpe_pdf = f"{match.group(1)}{match.group(2)}"

        if nro_cpe_pdf and nro_cpe_pdf in xml_map:
            id_factura, _ = xml_map[nro_cpe_pdf]
            guardar_archivo_binario(id_factura, pdf_name, "PDF")
        else:
            log(f" PDF {pdf_name} no asociado a ninguna factura", "error")

    # === Eliminar procesados ===
    for file_path in xml_files + pdf_files + zip_files:
        try:
            os.remove(file_path)
            log(f" Eliminado: {os.path.basename(file_path)}")
        except Exception as e:
            log(f" Error eliminando {file_path}: {e}", "error")

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
    ruc_empresa = str(empresa.get("ruc", "")).strip()

    if not id_empresa or not ruc_empresa:
        log(" El JSON debe incluir 'id_empresa' y 'ruc' de la empresa", "error")
        sys.exit(1)

    fecha_inicio = fecha_fin = None
    try:
        if "fecha_inicio" in data and "fecha_fin" in data:
            fecha_inicio = datetime.strptime(data["fecha_inicio"], "%d/%m/%Y")
            fecha_fin = datetime.strptime(data["fecha_fin"], "%d/%m/%Y")
            log(f" Filtro de fechas: {fecha_inicio.date()} - {fecha_fin.date()}")
    except Exception as e:
        log(f" Error parseando fechas: {e}")

    log(f" Iniciando procesamiento para empresa ID {id_empresa} (RUC {ruc_empresa})")
    procesar_archivos_descargados(id_empresa, ruc_empresa, fecha_inicio, fecha_fin)
    log(" Proceso finalizado correctamente")
