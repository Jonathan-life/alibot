import os
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.keys import Keys
from selenium.common.exceptions import TimeoutException
import time, os

# ============================
# CONFIGURACIÓN
# ============================
RUC, USUARIO, CLAVE = "20494384273", "08258794", "MinESE2023"
FECHA_INICIO, FECHA_FIN = "01/06/2024", "25/06/2024"
DOWNLOAD_DIR = os.path.abspath("descargas_sunat")
os.makedirs(DOWNLOAD_DIR, exist_ok=True)

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

# ============================
# FUNCIONES
# ============================
def cerrar_popups(driver):
    """Cierra primero el modal 'Informativo' y luego el panel 'divPanelIU02' si aparecen"""
    try:
        modal_cerrado = False

        # === Paso 1: Buscar modal en default_content ===
        driver.switch_to.default_content()
        try:
            modal = WebDriverWait(driver, 5).until(
                EC.presence_of_element_located((By.ID, "modalInformativoValidacionDatos"))
            )

            if modal.is_displayed():
                print("✔ Modal detectado en página principal")

                btn_finalizar = WebDriverWait(driver, 5).until(
                    EC.element_to_be_clickable((By.ID, "btnFinalizarValidacionDatos"))
                )
                driver.execute_script("arguments[0].click();", btn_finalizar)
                print("✔ Clic en 'Finalizar' del modal")

                WebDriverWait(driver, 10).until(
                    EC.invisibility_of_element_located((By.ID, "modalInformativoValidacionDatos"))
                )
                print("✔ Modal cerrado correctamente (default_content)")
                modal_cerrado = True

        except TimeoutException:
            print("⚠ Modal no está en default_content, revisando iframes...")

            # === Paso 1b: Buscar modal en iframes ===
            iframes = driver.find_elements(By.TAG_NAME, "iframe")
            for i, frame in enumerate(iframes):
                driver.switch_to.default_content()
                driver.switch_to.frame(frame)
                print(f"→ Revisando iframe {i}...")

                try:
                    modal = WebDriverWait(driver, 5).until(
                        EC.presence_of_element_located((By.ID, "modalInformativoValidacionDatos"))
                    )

                    if modal.is_displayed():
                        print(f"✔ Modal detectado en iframe {i}")

                        btn_finalizar = WebDriverWait(driver, 5).until(
                            EC.element_to_be_clickable((By.ID, "btnFinalizarValidacionDatos"))
                        )
                        driver.execute_script("arguments[0].click();", btn_finalizar)
                        print("✔ Clic en 'Finalizar' del modal")

                        WebDriverWait(driver, 10).until(
                            EC.invisibility_of_element_located((By.ID, "modalInformativoValidacionDatos"))
                        )
                        print("✔ Modal cerrado correctamente (iframe)")
                        modal_cerrado = True
                        break

                except TimeoutException:
                    continue

        driver.switch_to.default_content()

        # === Paso 2: Cerrar panel divPanelIU02 ===
        if modal_cerrado:
            try:
                print("🔎 Buscando panel 'divPanelIU02'...")

                try:
                    panel = WebDriverWait(driver, 10).until(
                        EC.presence_of_element_located((By.ID, "divPanelIU02"))
                    )
                    panel_location = "default_content"
                except TimeoutException:
                    print("⚠ Panel no está en default_content, buscando en iframes...")
                    panel, panel_location = None, None
                    iframes = driver.find_elements(By.TAG_NAME, "iframe")
                    for i, frame in enumerate(iframes):
                        driver.switch_to.default_content()
                        driver.switch_to.frame(frame)
                        print(f"→ Revisando iframe {i} para el panel...")

                        try:
                            panel = WebDriverWait(driver, 5).until(
                                EC.presence_of_element_located((By.ID, "divPanelIU02"))
                            )
                            panel_location = f"iframe {i}"
                            break
                        except TimeoutException:
                            continue

                if panel and panel.is_displayed():
                    print(f"✔ Panel 'divPanelIU02' visible en {panel_location}")

                    btn_cerrar = WebDriverWait(driver, 5).until(
                        EC.element_to_be_clickable((By.ID, "btnCerrar"))
                    )
                    driver.execute_script("arguments[0].click();", btn_cerrar)
                    print("✔ Clic en 'Continuar sin confirmar'")

                    WebDriverWait(driver, 10).until(
                        EC.invisibility_of_element_located((By.ID, "divPanelIU02"))
                    )
                    print("✔ Panel cerrado correctamente")
                    driver.switch_to.default_content()
                    return True

                print("❌ El panel 'divPanelIU02' no apareció en ningún lugar")
                driver.switch_to.default_content()
                return modal_cerrado

            except Exception as e:
                driver.switch_to.default_content()
                print(f"⚠ Error cerrando panel 'divPanelIU02': {e}")
                return modal_cerrado

        return modal_cerrado

    except Exception as e:
        driver.switch_to.default_content()
        print(f"⚠ Error cerrando popups: {e}")
        return False

# ============================
# FLUJO PRINCIPAL
# ============================
try:
    # LOGIN
    driver.get("https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm?exe=11.5.3.1.2")

    wait.until(EC.presence_of_element_located((By.ID, "txtRuc"))).send_keys(RUC)
    driver.find_element(By.ID, "txtUsuario").send_keys(USUARIO)
    driver.find_element(By.ID, "txtContrasena").send_keys(CLAVE)
    driver.find_element(By.ID, "btnAceptar").click()

  
    # MANEJO DE POPUPS
    cerrar_popups(driver)
    # ============================
    # UBICAR IFRAME DE FECHAS
    # ============================
    iframes = driver.find_elements(By.TAG_NAME, "iframe")
    print(f"Hay {len(iframes)} iframes")
    iframe_index = None
    for i, iframe in enumerate(iframes):
        driver.switch_to.default_content()
        driver.switch_to.frame(iframe)
        try:
            driver.find_element(By.ID, "criterio.fec_desde")
            iframe_index = i
            print(f"Encontré iframe correcto en índice {i}")
            break
        except:
            print(f"No está en iframe {i}")

    if iframe_index is None:
        raise Exception("No se encontró iframe con los campos de fecha")

    driver.switch_to.default_content()
    driver.switch_to.frame(iframes[iframe_index])

    # ============================
    # LLENAR FECHAS
    # ============================
    inicio = wait.until(EC.element_to_be_clickable((By.ID, "criterio.fec_desde")))
    inicio.click()
    inicio.clear()
    inicio.send_keys(FECHA_INICIO)
    inicio.send_keys(Keys.TAB)

    fin = wait.until(EC.element_to_be_clickable((By.ID, "criterio.fec_hasta")))
    fin.click()
    fin.clear()
    fin.send_keys(FECHA_FIN)
    fin.send_keys(Keys.TAB)

    # ============================
    # SELECCIONAR FE RECIBIDAS
    # ============================
    tipo = wait.until(EC.presence_of_element_located((By.ID, "criterio.tipoConsulta")))
    tipo.clear()
    tipo.send_keys("FE Recibidas")
    time.sleep(1)

    # ============================
    # CLIC EN ACEPTAR
    # ============================
    aceptar_label = wait.until(EC.element_to_be_clickable((By.ID, "criterio.btnContinuar_label")))
    padre_aceptar = aceptar_label.find_element(By.XPATH, "..")
    padre_aceptar.click()
    print("Se hizo clic en Aceptar.")
    time.sleep(2)

    # ============================
    # ESPERAR CARGA DE LA TABLA
    # ============================
    try:
        wait.until(EC.presence_of_element_located((By.ID, "listadoFacturas")))
        wait.until(EC.presence_of_element_located((By.ID, "dojox_grid__View_1")))
        print("✔ Tabla 'listadoFacturas' y 'dojox_grid__View_1' encontrada.")
    except TimeoutException:
        raise Exception("❌ No se encontró la tabla 'listadoFacturas'.")

    # ============================
    # BUSCAR TODOS LOS DIVs CON TABLAS DENTRO DEL GRID
    # ============================
    divs_con_tablas = driver.find_elements(By.XPATH, '//*[@id="dojox_grid__View_1"]/div/div/div')

    descargados_xml = set()
    descargados_pdf = set()

    for idx, div in enumerate(divs_con_tablas, start=1):
        try:
            # Buscar todas las tablas dentro de este div (incluyendo subniveles)
            tablas = div.find_elements(By.TAG_NAME, 'table')
            for tabla in tablas:
                # Buscar todas las filas (tr) dentro de tbody
                filas = tabla.find_elements(By.XPATH, './/tbody/tr')
                for fila in filas:
                    # Intentar obtener enlace XML (columna 8)
                    try:
                        enlace_xml = fila.find_element(By.XPATH, './td[8]/a')
                        onclick_xml = enlace_xml.get_attribute('onclick')
                        if onclick_xml and onclick_xml not in descargados_xml:
                            print(f"🗎 Descargando XML: {onclick_xml}")
                            driver.execute_script(onclick_xml)
                            descargados_xml.add(onclick_xml)
                            time.sleep(2)
                    except Exception:
                        pass  # No hay enlace XML en esta fila

                    # Intentar obtener enlace PDF (columna 9)
                    try:
                        enlace_pdf = fila.find_element(By.XPATH, './td[9]/a')
                        onclick_pdf = enlace_pdf.get_attribute('onclick')
                        if onclick_pdf and onclick_pdf not in descargados_pdf:
                            print(f"📄 Descargando PDF: {onclick_pdf}")
                            driver.execute_script(onclick_pdf)
                            descargados_pdf.add(onclick_pdf)
                            time.sleep(2)
                    except Exception:
                        pass  # No hay enlace PDF en esta fila

        except Exception as e:
            print(f"⚠ Error en div[{idx}]: {e}")

    # ============================
    # LISTA DE ARCHIVOS DESCARGADOS
    # ============================
    archivos = os.listdir(DOWNLOAD_DIR)
    print(f"\n📂 Archivos descargados: {len(archivos)}")
    for archivo in archivos:
        print(" -", archivo)



finally:
    time.sleep(5)
    driver.quit()