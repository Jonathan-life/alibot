# Alibot - Prototipo API REST (PHP)

Estructura mínima para correr un prototipo local de **Alibot** (asistente contable).

## Requisitos
- PHP 7.4+
- MySQL / MariaDB
- Python 3 (para el bot demo)
- Servidor local (XAMPP, Laragon, o `php -S`)

## Instalación rápida
1. Copia este proyecto a tu carpeta de servidor (p.ej. `C:\xampp\htdocs\alibot-api` o `/var/www/html/alibot-api`).
2. Importa `sql/alibot.sql` en tu MySQL para crear base y tablas.
3. Ajusta credenciales en `db/conexion.php` o exporta variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
4. Asegúrate de que la carpeta `bot/` y `api/` tengan permisos de ejecución para el usuario web si vas a usar `procesar_solicitud.php` por CLI.
5. Abre el navegador en `http://localhost/alibot-api/public/index.html` (o ajusta según tu servidor).

## Notas importantes
- Los endpoints usan respuestas JSON y están pensados para usarse con un frontend dinámico.
- El bot `bot/sunat_bot.py` es un *mock* (simula conteo). Para producción implementa Playwright o Selenium y respeta términos de uso de las plataformas (SUNAT, SIRE, SUNAFIL).
- `api/procesar_solicitud.php` está pensado para ejecutarse en consola (CLI) o cron y llama al bot Python.
- No guardes credenciales en texto plano en producción; usa variables de entorno y almacenamiento seguro.

## Probar flujo
1. Abre `public/index.html` y cotiza.
2. Registra solicitud.
3. Desde terminal ejecuta: `php api/procesar_solicitud.php` para que el script invoque al bot y marque solicitudes como completadas.
