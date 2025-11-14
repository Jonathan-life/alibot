import os
import glob
import xml.etree.ElementTree as ET
import mysql.connector
import logging
import zipfile
import re
import sys
import fitz  # PyMuPDF
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

def log(msg, tipo="info"):
    try:
        print(msg)
    except UnicodeEncodeError:
        msg_limpio = msg.encode("ascii", errors="ignore").decode()
        print(msg_limpio)

# ==========================
# CONEXIÓN A MYSQL
# ==========================
def get_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="sistema_contable"
    )

# ==========================
# GUARDAR PDF / ZIP EN BLOB
# ==========================
def guardar_archivo_binario(id_factura, archivo, tipo):
    if not id_factura:
        log(f"No se puede guardar {archivo}, id_factura es NULL", "error")
        return

    ruta = os.path.join(DOWNLOAD_DIR, archivo)
    if not os.path.exists(ruta):
        log(f"No existe el archivo: {ruta}", "error")
        return

    try:
        conn = get_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT COUNT(*) FROM archivos_factura
            WHERE id_factura = %s AND nombre_archivo = %s
        """, (id_factura, archivo))

        if cursor.fetchone()[0] > 0:
            log(f"Archivo duplicado NO guardado: {archivo}")
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
        log(f"Archivo guardado: {archivo}")

    except Exception as e:
        log(f"Error guardando archivo {archivo}: {e}", "error")

# ==========================
# PROCESAMIENTO XML
# ==========================
def procesar_xml(archivo, id_empresa, ruc_empresa, fecha_inicio=None, fecha_fin=None):
    ruta = os.path.join(DOWNLOAD_DIR, archivo)
    if not os.path.exists(ruta):
        log(f"No existe XML: {ruta}", "error")
        return None, None

    try:
        tree = ET.parse(ruta)
        root = tree.getroot()

        ns = {
            "cbc": "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
            "cac": "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
        }

        # SERIE - CORRELATIVO
        nro_cpe = root.findtext(".//cbc:ID", default="", namespaces=ns)
        serie, correlativo = ("", "")
        if "-" in nro_cpe:
            serie, correlativo = nro_cpe.split("-", 1)

        # FECHAS
        fecha_emision = root.findtext(".//cbc:IssueDate", default=None, namespaces=ns)
        fecha_vencimiento = root.findtext(".//cbc:DueDate", default=None, namespaces=ns)

        if fecha_inicio and fecha_fin and fecha_emision:
            f = datetime.strptime(fecha_emision, "%Y-%m-%d")
            if not (fecha_inicio <= f <= fecha_fin):
                log(f"⚠ {nro_cpe} fuera del rango")
                return None, None

        # EMISOR - RECEPTOR
        ruc_emisor = root.findtext(".//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID", default="", namespaces=ns)
        nombre_emisor = root.findtext(".//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName", default="", namespaces=ns)

        ruc_receptor = root.findtext(".//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID", default="", namespaces=ns)
        nombre_receptor = root.findtext(".//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName", default="", namespaces=ns)

        descripcion = root.findtext(".//cac:InvoiceLine/cac:Item/cbc:Description", default="", namespaces=ns)

        # ==========================
        # BASES SEGÚN SUNAT
        # ==========================
        base_gravadas = base_exoneradas = base_inafectas = base_exportacion = Decimal("0.00")

        for t in root.findall(".//cac:TaxSubtotal", ns):
            amount = Decimal(t.findtext("cbc:TaxableAmount", default="0.00", namespaces=ns))
            reason = t.findtext(".//cbc:TaxExemptionReasonCode", default="", namespaces=ns)
            tax_id = t.findtext(".//cac:TaxCategory/cac:TaxScheme/cbc:ID", default="", namespaces=ns)

            if reason == "10" or tax_id == "1000":
                base_gravadas += amount
            elif reason == "20" or tax_id == "9998":
                base_inafectas += amount
            elif reason == "30" or tax_id == "9997":
                base_exoneradas += amount
            elif reason == "40" or tax_id == "9995":
                base_exportacion += amount

        # Base imponible = suma de las 4 bases
        base_imponible = base_gravadas + base_inafectas + base_exoneradas + base_exportacion

        # IGV solo sobre bases gravadas
        igv = Decimal(root.findtext(".//cac:TaxTotal/cbc:TaxAmount", default="0.00", namespaces=ns))

        # Importe total
        importe_total = base_imponible + igv

        # Moneda
        moneda = root.findtext(".//cbc:DocumentCurrencyCode", default="PEN", namespaces=ns)

        # Tipo de documento
        tipo_doc_map = {"01": "FACTURA", "03": "BOLETA", "07": "NC", "08": "ND"}
        tipo_doc = tipo_doc_map.get(root.findtext(".//cbc:InvoiceTypeCode", default="01", namespaces=ns), "OTROS")

        # Origen
        origen = "COMPRA" if ruc_emisor != ruc_empresa else "VENTA"

        # Valores para BD
        valores = (
            id_empresa, tipo_doc, serie, correlativo, nro_cpe,
            fecha_emision, fecha_vencimiento,
            ruc_emisor, nombre_emisor, ruc_receptor, nombre_receptor,
            descripcion, base_imponible, igv, importe_total, moneda,
            origen, "ACEPTADO",
            base_gravadas, base_exoneradas, base_inafectas, base_exportacion,
            None
        )

        # INSERTAR EN BD
        conn = get_connection()
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO facturas (
                id_empresa, tipo_doc, serie, correlativo, nro_cpe,
                fecha_emision, fecha_vencimiento,
                ruc_emisor, nombre_emisor, ruc_receptor, nombre_receptor,
                descripcion, base_imponible, igv, importe_total, moneda,
                origen, estado_sunat,
                base_gravadas, base_exoneradas, base_inafectas, base_exportacion,
                id_usuario_import
            ) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
        """, valores)
        conn.commit()
        id_factura = cursor.lastrowid
        cursor.close()
        conn.close()

        log(f"Guardada factura {nro_cpe}")
        return id_factura, nro_cpe

    except Exception as e:
        log(f"ERROR XML {archivo}: {e}", "error")
        return None, None

# ==========================
# PROCESAR PDF (SERIE-CORRELATIVO)
# ==========================
def procesar_pdf(pdf_path):
    try:
        doc = fitz.open(pdf_path)
        texto = "".join(page.get_text("text") for page in doc)
        doc.close()

        match = re.search(r'([A-Z]\d{3})-?(\d+)', texto)
        if match:
            return f"{match.group(1)}{match.group(2)}"
        return None
    except:
        return None

# ==========================
# PROCESAR ARCHIVOS
# ==========================
def procesar_archivos_descargados(id_empresa, ruc_empresa, fecha_inicio=None, fecha_fin=None):
    zip_files = glob.glob(os.path.join(DOWNLOAD_DIR, "*.zip"))
    xml_map = {}
    log(f"ZIP encontrados: {len(zip_files)}")

    for zip_file in zip_files:
        zip_name = os.path.basename(zip_file)
        try:
            with zipfile.ZipFile(zip_file, 'r') as z:
                z.extractall(DOWNLOAD_DIR)
                xmls = [f for f in z.namelist() if f.lower().endswith(".xml")]
                if not xmls:
                    log(f"ZIP sin XML: {zip_name}")
                    continue

                xml_name = xmls[0]
                id_factura, nro_cpe = procesar_xml(xml_name, id_empresa, ruc_empresa, fecha_inicio, fecha_fin)

                if id_factura:
                    clave = nro_cpe.replace("-", "").upper()
                    xml_map[clave] = (id_factura, zip_name)
                    guardar_archivo_binario(id_factura, zip_name, "ZIP")

        except Exception as e:
            log(f"Error ZIP {zip_name}: {e}", "error")

    # PDFs
    pdfs = glob.glob(os.path.join(DOWNLOAD_DIR, "*.pdf"))
    for pdf_path in pdfs:
        pdf_name = os.path.basename(pdf_path).upper()
        nro = procesar_pdf(pdf_path)
        if not nro:
            m = re.search(r'([A-Z]\d{3})(\d+)', pdf_name)
            if m:
                nro = f"{m.group(1)}{m.group(2)}"
        if nro and nro in xml_map:
            id_factura, zipname = xml_map[nro]
            guardar_archivo_binario(id_factura, pdf_name, "PDF")
        else:
            log(f"PDF NO ASOCIADO: {pdf_name}")

    # ELIMINAR ARCHIVOS
    for file in zip_files + pdfs + glob.glob(os.path.join(DOWNLOAD_DIR, "*.xml")):
        try:
            os.remove(file)
        except:
            pass

# ==========================
# EJECUCIÓN PRINCIPAL
# ==========================
if __name__ == "__main__":
    if len(sys.argv) < 2:
        log("Debes pasar JSON de configuración", "error")
        sys.exit(1)

    json_path = sys.argv[1]
    if not os.path.exists(json_path):
        log("No existe JSON", "error")
        sys.exit(1)

    data = json.load(open(json_path, "r", encoding="utf-8"))

    empresa = data.get("empresa", {})
    id_empresa = empresa.get("id_empresa")
    ruc_empresa = str(empresa.get("ruc", "")).strip()

    if not id_empresa or not ruc_empresa:
        log("JSON incompleto", "error")
        sys.exit(1)

    fecha_inicio = fecha_fin = None
    try:
        if data.get("fecha_inicio") and data.get("fecha_fin"):
            fecha_inicio = datetime.strptime(data["fecha_inicio"], "%d/%m/%Y")
            fecha_fin = datetime.strptime(data["fecha_fin"], "%d/%m/%Y")
    except:
        pass

    procesar_archivos_descargados(id_empresa, ruc_empresa, fecha_inicio, fecha_fin)
    log("PROCESO FINALIZADO")
