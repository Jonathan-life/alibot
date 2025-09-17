import os
import glob
import xml.etree.ElementTree as ET
import mysql.connector
import logging
import zipfile
import re

# ==========================
# CONFIGURACIÓN
# ==========================
DOWNLOAD_DIR = os.path.abspath("descargas_sunat")
os.makedirs(DOWNLOAD_DIR, exist_ok=True)

LOG_FILE = "log.txt"
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

# ==========================
# CONEXIÓN A LA BD
# ==========================
def get_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="sistema_contable"
    )

# ==========================
# GUARDAR ARCHIVO BINARIO
# ==========================
def guardar_archivo_binario(id_factura, archivo, tipo):
    if id_factura is None:
        log(f"No se puede guardar {archivo} porque no hay ID de factura", "error")
        return

    ruta = os.path.join(DOWNLOAD_DIR, archivo)
    if not os.path.exists(ruta):
        log(f"No existe el archivo: {ruta}", "error")
        return

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
    os.remove(ruta)
    log(f"Eliminado de carpeta: {archivo}")

# ==========================
# PROCESAR XML → INSERTAR FACTURA
# ==========================
def procesar_xml(archivo, id_empresa):
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

        # Número de factura completo (E001-XXX)
        nro_cpe = root.find(".//cbc:ID", ns)
        nro_cpe = nro_cpe.text if nro_cpe is not None else ""

        # Datos del emisor
        emisor_ruc = root.find(".//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID", ns)
        emisor_ruc = emisor_ruc.text if emisor_ruc is not None else ""

        emisor_nombre = root.find(".//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName", ns)
        emisor_nombre = emisor_nombre.text if emisor_nombre is not None else ""

        # Datos de fecha y monto
        fecha_emision = root.find(".//cbc:IssueDate", ns)
        fecha_emision = fecha_emision.text if fecha_emision is not None else ""

        monto = root.find(".//cac:LegalMonetaryTotal/cbc:PayableAmount", ns)
        monto = float(monto.text) if monto is not None else 0.0

        # Guardar en BD
        conn = get_connection()
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO facturas (id_empresa, nro_cpe, emisor_ruc, emisor_nombre, fecha_emision, importe_total)
            VALUES (%s, %s, %s, %s, %s, %s)
        """, (id_empresa, nro_cpe, emisor_ruc, emisor_nombre, fecha_emision, monto))
        conn.commit()
        id_factura = cursor.lastrowid
        cursor.close()
        conn.close()

        log(f"Factura guardada en BD: {nro_cpe} - {emisor_nombre}")
        return id_factura, nro_cpe
    except Exception as e:
        log(f"Error procesando XML {archivo}: {e}", "error")
        return None, None

# ==========================
# PROCESAR ARCHIVOS DESCARGADOS
# ==========================
def procesar_archivos_descargados(id_empresa):
    # Extraer todos los ZIPs
    for zip_file in glob.glob(os.path.join(DOWNLOAD_DIR, "*.zip")):
        try:
            with zipfile.ZipFile(zip_file, 'r') as zip_ref:
                zip_ref.extractall(DOWNLOAD_DIR)
                log(f"Extraído {zip_file}")
            os.remove(zip_file)
        except Exception as e:
            log(f"No se pudo extraer {zip_file}: {e}", "error")

    # Listar XML y PDF
    xml_files = [os.path.basename(f) for f in glob.glob(os.path.join(DOWNLOAD_DIR, "*.XML"))]
    pdf_files = [os.path.basename(f) for f in glob.glob(os.path.join(DOWNLOAD_DIR, "*.PDF"))]

    # Mapear XML → ID factura
    xml_map = {}
    for xml in xml_files:
        id_factura, nro_cpe = procesar_xml(xml, id_empresa)
        if id_factura:
            guardar_archivo_binario(id_factura, xml, "XML")
            xml_map[nro_cpe.replace("-", "")] = id_factura  # Guardamos sin guion para fácil coincidencia

    # Asociar PDFs usando nro_cpe
    for pdf in pdf_files:
        nro_cpe_pdf = None
        match = re.search(r'E\d{1,}-?\d+', pdf)  # Captura E00169920607062111 o E001-699...
        if match:
            nro_cpe_pdf = match.group(0).replace("-", "")  # Normalizamos sin guion
        id_factura = xml_map.get(nro_cpe_pdf)
        guardar_archivo_binario(id_factura, pdf, "PDF")

# ==========================
# EJECUCIÓN PRINCIPAL
# ==========================
if __name__ == "__main__":
    id_empresa = 1  # Cambiar según tu empresa
    procesar_archivos_descargados(id_empresa)
    log("✅ Proceso finalizado")
