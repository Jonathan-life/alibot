#!/usr/bin/env python3
# Bot de ejemplo (modo mock). En producción implementa Playwright/Selenium para login y descarga.
import sys, json, random, time
if len(sys.argv) < 4:
    print(json.dumps({"error":"Faltan parámetros"}))
    sys.exit(1)

ruc = sys.argv[1]
desde = sys.argv[2]
hasta = sys.argv[3]

# Simular trabajo
time.sleep(1)
cantidad = random.randint(5,200)
print(json.dumps({"cantidad":cantidad, "ruc": ruc, "desde": desde, "hasta": hasta}))
