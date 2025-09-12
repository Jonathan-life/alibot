from flask import Flask, render_template, request
import mysql.connector
import threading
import selenium_bot  # Nuestro módulo de Selenium

app = Flask(__name__)

# ============================
# FUNCIONES DB
# ============================
def listar_empresas():
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="sistema_contable"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT id_empresa, razon_social FROM empresas WHERE estado='ACTIVO'")
    empresas = cursor.fetchall()
    conn.close()
    return empresas

def obtener_credenciales_empresa(id_empresa):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="sistema_contable"
    )
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
    return render_template("index.html", empresas=empresas)

@app.route("/descargar", methods=["POST"])
def descargar():
    id_empresa = int(request.form["id_empresa"])
    fecha_inicio = request.form["fecha_inicio"]
    fecha_fin = request.form["fecha_fin"]

    empresa = obtener_credenciales_empresa(id_empresa)

    # Ejecutar Selenium en otro hilo
    threading.Thread(
        target=selenium_bot.descargar_documentos,
        args=(empresa, fecha_inicio, fecha_fin)
    ).start()

    return f"✅ Descarga iniciada para {empresa['razon_social']} desde {fecha_inicio} hasta {fecha_fin}."

if __name__ == "__main__":
    app.run(debug=True)
