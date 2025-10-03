
-- TABLA EMPRESAS
-- ================================================
DROP TABLE IF EXISTS empresas;
CREATE TABLE empresas (
  id_empresa INT NOT NULL AUTO_INCREMENT,
  ruc VARCHAR(20) NOT NULL,
  razon_social VARCHAR(255) NOT NULL,
  usuario_sol VARCHAR(50) NOT NULL,
  clave_sol VARCHAR(255) NOT NULL,
  api_client_id VARCHAR(255) DEFAULT NULL,
  api_client_secret VARCHAR(255) DEFAULT NULL,
  fecha_registro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (id_empresa),
  UNIQUE KEY ux_emp_ruc (ruc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- TABLA USUARIOS
-- ================================================
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  id_usuario INT NOT NULL AUTO_INCREMENT,
  id_empresa INT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','contador','cliente') DEFAULT 'contador',
  estado ENUM('activo','inactivo') DEFAULT 'activo',
  fecha_registro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY ux_usuario_correo (correo),
  CONSTRAINT fk_usuario_empresa FOREIGN KEY (id_empresa) 
    REFERENCES empresas(id_empresa) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- TABLA FACTURAS (Comprobantes)
-- ================================================

DROP TABLE IF EXISTS `facturas`;
CREATE TABLE IF NOT EXISTS `facturas` (
  `id_comprobante` int NOT NULL AUTO_INCREMENT,
  `id_empresa` int NOT NULL,
  `tipo_doc` enum('FACTURA','BOLETA','NC','ND','OTROS') COLLATE utf8mb4_general_ci NOT NULL,
  `serie` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `correlativo` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nro_cpe` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `ruc_emisor` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre_emisor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ruc_receptor` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre_receptor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `base_imponible` decimal(15,2) DEFAULT '0.00',
  `igv` decimal(15,2) DEFAULT '0.00',
  `importe_total` decimal(15,2) DEFAULT '0.00',
  `moneda` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'PEN',
  `origen` enum('VENTA','COMPRA') COLLATE utf8mb4_general_ci NOT NULL,
  `estado_sunat` enum('ACEPTADO','RECHAZADO','ANULADO','PENDIENTE') COLLATE utf8mb4_general_ci DEFAULT 'ACEPTADO',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_usuario_import` int DEFAULT NULL,
  PRIMARY KEY (`id_comprobante`),
  UNIQUE KEY ux_comp_empresa_doc (`id_empresa`,`tipo_doc`,`serie`0/orrelativo),
  KEY idx_comp_fecha (`id_empresa`,`fecha_emision`),
  KEY fk_factura_usuario (`id_usuario_import`)
) ENGINE=InnoDB ;

-- TABLA ARCHIVOS FACTURA
-- ================================================
DROP TABLE IF EXISTS archivos_factura;
CREATE TABLE archivos_factura (
  id_archivo INT NOT NULL AUTO_INCREMENT,
  id_factura INT NOT NULL,
  tipo ENUM('XML','PDF','ZIP') NOT NULL,
  nombre_archivo VARCHAR(255) NOT NULL,
  ruta VARCHAR(500) NOT NULL,
  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
  archivo_binario LONGBLOB,
  PRIMARY KEY (id_archivo),
  KEY idx_factura (id_factura),
  CONSTRAINT fk_archivo_factura FOREIGN KEY (id_factura) 
    REFERENCES facturas(id_comprobante) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- TABLA DETALLE DE FACTURAS
-- ================================================
DROP TABLE IF EXISTS comprobante_lineas;
CREATE TABLE comprobante_lineas (
  id_linea INT NOT NULL AUTO_INCREMENT,
  id_comprobante INT NOT NULL,
  item INT NOT NULL,
  codigo_producto VARCHAR(100),
  descripcion_producto VARCHAR(500),
  cantidad DECIMAL(18,4) DEFAULT 0,
  unidad VARCHAR(50),
  precio_unitario DECIMAL(15,4) DEFAULT 0,
  subtotal DECIMAL(15,2) DEFAULT 0,
  igv_linea DECIMAL(15,2) DEFAULT 0,
  importe_linea DECIMAL(15,2) DEFAULT 0,
  PRIMARY KEY (id_linea),
  KEY fk_linea_comprobante (id_comprobante),
  CONSTRAINT fk_linea_factura FOREIGN KEY (id_comprobante) 
    REFERENCES facturas(id_comprobante) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- TABLA LIBROS ELECTRÓNICOS
-- ================================================
DROP TABLE IF EXISTS libros_electronicos;
CREATE TABLE libros_electronicos (
  id_libro INT NOT NULL AUTO_INCREMENT,
  id_empresa INT NOT NULL,
  tipo_libro ENUM('VENTAS','COMPRAS','HONORARIOS','DIARIO','MAYOR') NOT NULL,
  periodo VARCHAR(7) NOT NULL, -- formato YYYY-MM
  archivo_nombre VARCHAR(255),
  archivo_ruta VARCHAR(500),
  estado_validacion ENUM('VALIDO','OBSERVADO','ERROR') DEFAULT 'VALIDO',
  fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  observaciones TEXT,
  PRIMARY KEY (id_libro),
  KEY idx_libro_empresa_periodo (id_empresa, tipo_libro, periodo),
  CONSTRAINT fk_libro_empresa FOREIGN KEY (id_empresa) 
    REFERENCES empresas(id_empresa) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- TABLA RESUMEN FINANCIERO
-- ================================================
DROP TABLE IF EXISTS resumen_financiero;
CREATE TABLE resumen_financiero (
  id_resumen INT NOT NULL AUTO_INCREMENT,
  id_empresa INT NOT NULL,
  periodo VARCHAR(7) NOT NULL, -- ejemplo '2025-09'
  ingresos DECIMAL(18,2) DEFAULT 0,
  egresos DECIMAL(18,2) DEFAULT 0,
  igv_ventas DECIMAL(18,2) DEFAULT 0,
  igv_compras DECIMAL(18,2) DEFAULT 0,
  fecha_calculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_resumen),
  UNIQUE KEY ux_resumen_empresa_periodo (id_empresa, periodo),
  CONSTRAINT fk_resumen_empresa FOREIGN KEY (id_empresa) 
    REFERENCES empresas(id_empresa) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- TABLA TEMPORAL PARA PLE
-- ================================================
DROP TABLE IF EXISTS ple_temporal;
CREATE TABLE ple_temporal (
  id INT NOT NULL AUTO_INCREMENT,
  id_empresa INT,
  tipo_libro VARCHAR(50),
  periodo VARCHAR(7),
  fila_texto TEXT,
  fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_ple_empresa FOREIGN KEY (id_empresa) 
    REFERENCES empresas(id_empresa) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
