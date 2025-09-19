import os
import glob
import xml.etree.ElementTree as ET
import mysql.connector
import logging
import zipfile
import re
import sys
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
        with open(ruta, "rb") as f:
            contenido = f.read()

        conn = get_connection()
        cursor = conn.cursor()
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
    """
    Procesa el archivo XML, extrae datos y guarda la factura en la BD.
    Retorna id_factura y nro_cpe para asociar PDFs después.
    """
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

        nro_cpe = root.find(".//cbc:ID", ns)
        nro_cpe = nro_cpe.text.strip() if nro_cpe is not None else ""

        emisor_ruc = root.find(".//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID", ns)
        emisor_ruc = emisor_ruc.text.strip() if emisor_ruc is not None else ""

        emisor_nombre = root.find(".//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName", ns)
        emisor_nombre = emisor_nombre.text.strip() if emisor_nombre is not None else ""

        fecha_emision = root.find(".//cbc:IssueDate", ns)
        fecha_emision = fecha_emision.text.strip() if fecha_emision is not None else ""

        if fecha_inicio and fecha_fin:
            fecha_emision_dt = datetime.strptime(fecha_emision, "%Y-%m-%d")
            if fecha_emision_dt < fecha_inicio or fecha_emision_dt > fecha_fin:
                log(f"Factura {nro_cpe} fecha {fecha_emision} fuera del rango {fecha_inicio.strftime('%Y-%m-%d')} - {fecha_fin.strftime('%Y-%m-%d')}")
                return None, None

        monto = root.find(".//cac:LegalMonetaryTotal/cbc:PayableAmount", ns)
        monto = float(monto.text.strip()) if monto is not None else 0.0

        log(f"Procesando factura: {nro_cpe} - Fecha: {fecha_emision} - Monto: {monto}")

        conn = get_connection()
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO facturas (id_empresa, nro_cpe, emisor_ruc, emisor_nombre, fecha_emision, importe_total, estado, fecha_registro)
            VALUES (%s, %s, %s, %s, %s, %s, 'VIGENTE', NOW())
        """, (id_empresa, nro_cpe, emisor_ruc, emisor_nombre, fecha_emision, monto))
        conn.commit()
        id_factura = cursor.lastrowid
        cursor.close()
        conn.close()

        log(f" Factura guardada: {nro_cpe} - {emisor_nombre}")
        return id_factura, nro_cpe
    except Exception as e:
        log(f" Error procesando XML {archivo}: {e}", "error")
        return None, None

def procesar_archivos_descargados(id_empresa, fecha_inicio=None, fecha_fin=None):
    """
    Extrae ZIPs, procesa XML y PDF para guardar en BD y elimina archivos luego de procesar.
    """
    zip_files = glob.glob(os.path.join(DOWNLOAD_DIR, "*.zip"))
    log(f"ZIPs encontrados: {len(zip_files)}")

    xml_files = []

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

                if id_factura:
                    log(f" Guardando ZIP en BD vinculado a factura ID {id_factura}")
                    guardar_archivo_binario(id_factura, zip_name, "ZIP")
                    xml_files.append(xml_path)
                else:
                    log(f" No se pudo procesar factura para XML {xml_in_zip[0]}", "error")

        except Exception as e:
            log(f" Error extrayendo {zip_name}: {e}", "error")

    pdf_files = glob.glob(os.path.join(DOWNLOAD_DIR, "*.pdf")) + glob.glob(os.path.join(DOWNLOAD_DIR, "*.PDF"))
    log(f"Archivos PDF encontrados: {len(pdf_files)}")

    if len(pdf_files) == 0:
        log(" No hay archivos PDF para procesar")

    xml_map = {}

    # Abrir conexión y cursor buffered para evitar errores Unread result found
    try:
        conn = get_connection()
        cursor = conn.cursor(buffered=True)
        log(f"Iniciando lectura y mapeo de XML para {len(xml_files)} archivos")
        for xml_path in xml_files:
            xml_name = os.path.basename(xml_path)
            try:
                log(f" Leyendo XML: {xml_name}")
                tree = ET.parse(xml_path)
                root = tree.getroot()
                ns = {
                    "cbc": "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
                    "cac": "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                }
                nro_cpe_elem = root.find(".//cbc:ID", ns)
                nro_cpe = nro_cpe_elem.text.strip() if nro_cpe_elem is not None else ""
                if not nro_cpe:
                    log(f" No se encontró nro_cpe en XML {xml_name}", "error")
                    continue

                log(f" Buscando factura en BD para nro_cpe: {nro_cpe}")
                cursor.execute("SELECT id_factura FROM facturas WHERE nro_cpe = %s", (nro_cpe,))
                row = cursor.fetchone()
                if row:
                    id_factura = row[0]
                    key = nro_cpe.replace("-", "").upper()
                    xml_map[key] = id_factura
                    log(f" Mapeado nro_cpe {key} a factura ID {id_factura}")
                else:
                    log(f" No se encontró factura para nro_cpe {nro_cpe}", "error")

            except Exception as e:
                log(f" Error leyendo XML {xml_name}: {e}", "error")

    except Exception as e:
        log(f" Error con la conexión o cursor de BD: {e}", "error")
    finally:
        try:
            cursor.close()
            conn.close()
        except Exception:
            pass

    # Procesar PDFs
    for pdf_path in pdf_files:
        pdf_name = os.path.basename(pdf_path).upper()
        pdf_base = os.path.splitext(pdf_name)[0]

        match = re.search(r'(E\d{3})-?(\d+)', pdf_base)
        if match:
            serie = match.group(1)
            correlativo = match.group(2)
            nro_cpe_pdf = f"{serie}{correlativo}"  # sin guion para mapear

            id_factura = xml_map.get(nro_cpe_pdf)
            if id_factura:
                log(f" Asociando PDF {pdf_name} a factura ID {id_factura}")
                guardar_archivo_binario(id_factura, pdf_name, "PDF")
            else:
                posibles_keys = [k for k in xml_map.keys() if k.startswith(serie) and correlativo in k]
                if posibles_keys:
                    id_factura = xml_map[posibles_keys[0]]
                    log(f" Asociando PDF {pdf_name} a factura ID {id_factura} por coincidencia aproximada")
                    guardar_archivo_binario(id_factura, pdf_name, "PDF")
                else:
                    log(f" PDF {pdf_name} no asociado a factura", "error")
        else:
            log(f"No se pudo asociar PDF: {pdf_name}", "error")

    # Eliminar XML y PDF procesados
    for file_path in xml_files + pdf_files:
        try:
            os.remove(file_path)
            log(f" Eliminado de carpeta: {os.path.basename(file_path)}")
        except Exception as e:
            log(f"Error eliminando archivo {file_path}: {e}", "error")

    # Eliminar archivos XML y PDF procesados
    for file_path in xml_files + pdf_files:
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
