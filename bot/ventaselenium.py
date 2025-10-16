import os
import sys, json
import time
import mysql.connector
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.keys import Keys
from selenium.common.exceptions import TimeoutException

# ============================
# CONFIG
# ============================
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
DOWNLOAD_DIR = os.path.join(SCRIPT_DIR, "..", "descargas_sunat")
DOWNLOAD_DIR = os.path.abspath(DOWNLOAD_DIR)


# ============================
# FUNCIONES SELENIUM
# ============================
def cerrar_popups(driver):
    driver.switch_to.default_content()
    popup_cerrado = False

    # --- 1️⃣ Modal
    def cerrar_modal():
        modals = driver.find_elements(By.ID, "modalInformativoValidacionDatos")
        if modals and modals[0].is_displayed():
            btn = driver.find_element(By.ID, "btnFinalizarValidacionDatos")
            driver.execute_script("arguments[0].click();", btn)
            return True
        for frame in driver.find_elements(By.TAG_NAME, "iframe"):
            driver.switch_to.default_content()
            driver.switch_to.frame(frame)
            modals = driver.find_elements(By.ID, "modalInformativoValidacionDatos")
            if modals and modals[0].is_displayed():
                btn = driver.find_element(By.ID, "btnFinalizarValidacionDatos")
                driver.execute_script("arguments[0].click();", btn)
                return True
        driver.switch_to.default_content()
        return False

    popup_cerrado = cerrar_modal()

    # --- 2️⃣ Panel
    def cerrar_panel():
        driver.switch_to.default_content()
        try:
            panel = WebDriverWait(driver, 3).until(
                EC.presence_of_element_located((By.ID, "divPanelIU02"))
            )
            if panel.is_displayed():
                btn = driver.find_element(By.ID, "btnCerrar")
                driver.execute_script("arguments[0].click();", btn)
                WebDriverWait(driver, 5).until(
                    EC.invisibility_of_element_located((By.ID, "divPanelIU02"))
                )
                return True
        except TimeoutException:
            for frame in driver.find_elements(By.TAG_NAME, "iframe"):
                driver.switch_to.default_content()
                driver.switch_to.frame(frame)
                try:
                    panel = WebDriverWait(driver, 2).until(
                        EC.presence_of_element_located((By.ID, "divPanelIU02"))
                    )
                    if panel.is_displayed():
                        btn = driver.find_element(By.ID, "btnCerrar")
                        driver.execute_script("arguments[0].click();", btn)
                        WebDriverWait(driver, 5).until(
                            EC.invisibility_of_element_located((By.ID, "divPanelIU02"))
                        )
                        return True
                except TimeoutException:
                    continue
        driver.switch_to.default_content()
        return False

    panel_cerrado = cerrar_panel()
    return popup_cerrado or panel_cerrado


def descargar_documentos(empresa, fecha_inicio, fecha_fin):
    RUC = empresa['ruc']
    USUARIO = empresa['usuario_sol']
    CLAVE = empresa['clave_sol']
    ID_EMPRESA = empresa['id_empresa']

    chrome_options = Options()
    chrome_options.add_experimental_option("prefs", {
        "download.default_directory": DOWNLOAD_DIR,
        "download.prompt_for_download": False,
        "plugins.always_open_pdf_externally": True,
        "profile.default_content_setting_values.automatic_downloads": 1,
        "download.directory_upgrade": True
    })

    driver = webdriver.Chrome(options=chrome_options)
    wait = WebDriverWait(driver, 30)

    try:
        # LOGIN
        driver.get("https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm?exe=11.5.3.1.2")
        wait.until(EC.presence_of_element_located((By.ID, "txtRuc"))).send_keys(RUC)
        driver.find_element(By.ID, "txtUsuario").send_keys(USUARIO)
        driver.find_element(By.ID, "txtContrasena").send_keys(CLAVE)
        driver.find_element(By.ID, "btnAceptar").click()

        cerrar_popups(driver)

        # Buscar iframe
        iframes = driver.find_elements(By.TAG_NAME, "iframe")
        iframe_index = None
        for i, iframe in enumerate(iframes):
            driver.switch_to.default_content()
            driver.switch_to.frame(iframe)
            try:
                driver.find_element(By.ID, "criterio.fec_desde")
                iframe_index = i
                break
            except:
                continue

        if iframe_index is None:
            raise Exception("No se encontró iframe con campos de fecha")

        driver.switch_to.default_content()
        driver.switch_to.frame(iframes[iframe_index])

        # Fechas
        inicio = wait.until(EC.element_to_be_clickable((By.ID, "criterio.fec_desde")))
        inicio.clear()
        inicio.send_keys(fecha_inicio)
        inicio.send_keys(Keys.TAB)

        fin = wait.until(EC.element_to_be_clickable((By.ID, "criterio.fec_hasta")))
        fin.clear()
        fin.send_keys(fecha_fin)
        fin.send_keys(Keys.TAB)

        # Tipo
        # Tipo de comprobante: cambiar a FE Emitidas
        tipo = wait.until(EC.presence_of_element_located((By.ID, "criterio.tipoConsulta")))
        tipo.clear()
        tipo.send_keys("FE Emitidas")
        time.sleep(1)

        # Aceptar
        aceptar_label = wait.until(EC.element_to_be_clickable((By.ID, "criterio.btnContinuar_label")))
        padre_aceptar = aceptar_label.find_element(By.XPATH, "..")
        padre_aceptar.click()
        time.sleep(2)

        # Esperar tabla
        wait.until(EC.presence_of_element_located((By.ID, "listadoFacturas")))
        wait.until(EC.presence_of_element_located((By.ID, "dojox_grid__View_1")))

        # Descargar
        divs_con_tablas = driver.find_elements(By.XPATH, '//*[@id="dojox_grid__View_1"]/div/div/div')
        descargados_xml = set()
        descargados_pdf = set()
        filas_vistas = set()

        for div in divs_con_tablas:
            ultima_altura = -1
            while True:
                tablas = div.find_elements(By.TAG_NAME, 'table')
                for tabla in tablas:
                    filas = tabla.find_elements(By.XPATH, './/tbody/tr')
                    for fila in filas:
                        fila_id = fila.text.strip()
                        if fila_id in filas_vistas:
                            continue
                        filas_vistas.add(fila_id)

                        # XML
                        try:
                            enlace_xml = fila.find_element(By.XPATH, './td[8]/a')
                            onclick_xml = enlace_xml.get_attribute('onclick')
                            if onclick_xml and onclick_xml not in descargados_xml:
                                driver.execute_script(onclick_xml)
                                descargados_xml.add(onclick_xml)
                                time.sleep(2)
                        except:
                            pass

                        # PDF
                        try:
                            enlace_pdf = fila.find_element(By.XPATH, './td[9]/a')
                            onclick_pdf = enlace_pdf.get_attribute('onclick')
                            if onclick_pdf and onclick_pdf not in descargados_pdf:
                                driver.execute_script(onclick_pdf)
                                descargados_pdf.add(onclick_pdf)
                                time.sleep(2)
                        except:
                            pass

                altura_actual = driver.execute_script("return arguments[0].scrollTop", div)
                driver.execute_script("arguments[0].scrollTop = arguments[0].scrollTop + arguments[0].offsetHeight", div)
                time.sleep(1.5)
                if altura_actual == ultima_altura:
                    break
                ultima_altura = altura_actual



    finally:
        time.sleep(5)
        driver.quit()

# ========================
# 🚀 Entrada desde PHP
# ========================
if __name__ == "__main__":
    if len(sys.argv) > 1:
        json_file = sys.argv[1]
        with open(json_file, "r", encoding="utf-8") as f:
            data = json.load(f)
        empresa = data["empresa"]
        fecha_inicio = data["fecha_inicio"]
        fecha_fin = data["fecha_fin"]

        descargar_documentos(empresa, fecha_inicio, fecha_fin)
    else:
        print(" No se recibió archivo JSON")
