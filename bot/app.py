import os
from flask import Flask, render_template, request
import mysql.connector
import threading
import selenium_bot  # tu módulo Selenium

# 📌 Ruta absoluta a la carpeta "reportes"
BASE_DIR = os.path.dirname(os.path.abspath(__file__))  # carpeta /bot
TEMPLATES_DIR = os.path.join(BASE_DIR, "..", "public", "reportes")

app = Flask(__name__, template_folder=TEMPLATES_DIR)

# ============================
# FUNCIÓN PARA CONEXIÓN DB
# ============================
def get_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="sistema_contable"
    )

# ============================
# FUNCIONES DB
# ============================
def listar_empresas():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT id_empresa, razon_social, estado FROM empresas")
    empresas = cursor.fetchall()
    conn.close()
    print("📌 Empresas encontradas:", empresas)  # DEBUG
    return empresas


def obtener_credenciales_empresa(id_empresa):
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        "SELECT ruc, usuario_sol, clave_sol, razon_social FROM empresas WHERE id_empresa=%s AND estado='ACTIVO'",
        (id_empresa,)
    )
    resultado = cursor.fetchone()
    conn.close()
    if resultado:
        return resultado
    else:
        raise Exception("Empresa no encontrada")

# ============================
# RUTAS
# ============================
@app.route("/")
def index():
    empresas = listar_empresas()
    return render_template("venta_sire.html", empresas=empresas)

@app.route("/descargar", methods=["POST"])
def descargar():
    id_empresa = int(request.form["id_empresa"])
    fecha_inicio = request.form["fecha_inicio"]
    fecha_fin = request.form["fecha_fin"]

    empresa = obtener_credenciales_empresa(id_empresa)

    # Ejecutar Selenium en un hilo separado
    hilo = threading.Thread(
        target=selenium_bot.descargar_documentos,
        args=(empresa, fecha_inicio, fecha_fin),
        daemon=True
    )
    hilo.start()

    return f"✅ Descarga iniciada para {empresa['razon_social']} desde {fecha_inicio} hasta {fecha_fin}."

if __name__ == "__main__":
    app.run(debug=True)
