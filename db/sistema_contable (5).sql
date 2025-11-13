-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 13-11-2025 a las 00:01:26
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_contable`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos_factura`
--

DROP TABLE IF EXISTS `archivos_factura`;
CREATE TABLE IF NOT EXISTS `archivos_factura` (
  `id_archivo` int NOT NULL AUTO_INCREMENT,
  `id_factura` int NOT NULL,
  `tipo` enum('XML','PDF','ZIP') COLLATE utf8mb4_general_ci NOT NULL,
  `nombre_archivo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ruta` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `archivo_binario` longblob,
  PRIMARY KEY (`id_archivo`),
  KEY `idx_factura` (`id_factura`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobante_lineas`
--

DROP TABLE IF EXISTS `comprobante_lineas`;
CREATE TABLE IF NOT EXISTS `comprobante_lineas` (
  `id_linea` int NOT NULL AUTO_INCREMENT,
  `id_comprobante` int NOT NULL,
  `item` int NOT NULL,
  `codigo_producto` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion_producto` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cantidad` decimal(18,4) DEFAULT '0.0000',
  `unidad` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `precio_unitario` decimal(15,4) DEFAULT '0.0000',
  `subtotal` decimal(15,2) DEFAULT '0.00',
  `igv_linea` decimal(15,2) DEFAULT '0.00',
  `importe_linea` decimal(15,2) DEFAULT '0.00',
  PRIMARY KEY (`id_linea`),
  KEY `fk_linea_comprobante` (`id_comprobante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deudas`
--

DROP TABLE IF EXISTS `deudas`;
CREATE TABLE IF NOT EXISTS `deudas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ruc` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `periodo_tributario` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `formulario` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero_orden` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tributo_multa` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `fecha_notificacion` date DEFAULT NULL,
  `fecha_pagos` date DEFAULT NULL,
  `fecha_calculos` date DEFAULT NULL,
  `etapa_basica` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `importe_deudas` decimal(15,2) DEFAULT NULL,
  `importe_tributaria` decimal(15,2) DEFAULT NULL,
  `interes_capitalizado` decimal(15,2) DEFAULT NULL,
  `interes_moratorio` decimal(15,2) DEFAULT NULL,
  `pagos` decimal(15,2) DEFAULT NULL,
  `saldo_total` decimal(15,2) DEFAULT NULL,
  `interes_diario` decimal(15,2) DEFAULT NULL,
  `interes_acumulado` decimal(15,2) DEFAULT NULL,
  `id_empresa` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_deudas_empresas` (`id_empresa`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `deudas`
--

INSERT INTO `deudas` (`id`, `ruc`, `periodo_tributario`, `formulario`, `numero_orden`, `tributo_multa`, `tipo`, `fecha_emision`, `fecha_notificacion`, `fecha_pagos`, `fecha_calculos`, `etapa_basica`, `importe_deudas`, `importe_tributaria`, `interes_capitalizado`, `interes_moratorio`, `pagos`, `saldo_total`, `interes_diario`, `interes_acumulado`, `id_empresa`) VALUES
(25, '20494384273', '2017-01', '1662', '000126', 'IGV', 'Tributo', '2017-07-27', '2017-07-31', '2025-11-03', '2025-11-05', 'Cobranza Coactiva', 373.00, 373.00, 0.00, 381.00, 0.00, 154.00, 0.00, 381.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

DROP TABLE IF EXISTS `empresas`;
CREATE TABLE IF NOT EXISTS `empresas` (
  `id_empresa` int NOT NULL AUTO_INCREMENT,
  `ruc` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `razon_social` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `usuario_sol` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `clave_sol` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `api_client_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `api_client_secret` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_empresa`),
  UNIQUE KEY `ux_emp_ruc` (`ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `ruc`, `razon_social`, `usuario_sol`, `clave_sol`, `api_client_id`, `api_client_secret`, `fecha_registro`, `estado`) VALUES
(1, '20494384273', 'MINERA SANTA ENMA S.A.C.', '08258794', 'MinESE2023', '79776d57-7f59-46ec-8137-7057ed35b9a6', 'NureXmMJWDzLdmCoe321Sw==', '2025-09-13 03:58:14', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

DROP TABLE IF EXISTS `facturas`;
CREATE TABLE IF NOT EXISTS `facturas` (
  `id_factura` int NOT NULL AUTO_INCREMENT,
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
  `base_gravadas` decimal(15,2) DEFAULT '0.00',
  `base_exoneradas` decimal(15,2) DEFAULT '0.00',
  `base_inafectas` decimal(15,2) DEFAULT '0.00',
  `base_exportacion` decimal(15,2) DEFAULT '0.00',
  PRIMARY KEY (`id_factura`),
  UNIQUE KEY `ux_comp_empresa_doc` (`id_empresa`,`tipo_doc`,`serie`,`correlativo`),
  KEY `idx_comp_fecha` (`id_empresa`,`fecha_emision`),
  KEY `fk_factura_usuario` (`id_usuario_import`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros_electronicos`
--

DROP TABLE IF EXISTS `libros_electronicos`;
CREATE TABLE IF NOT EXISTS `libros_electronicos` (
  `id_libro` int NOT NULL AUTO_INCREMENT,
  `id_empresa` int NOT NULL,
  `tipo_libro` enum('VENTAS','COMPRAS','HONORARIOS','DIARIO','MAYOR') COLLATE utf8mb4_general_ci NOT NULL,
  `periodo` varchar(7) COLLATE utf8mb4_general_ci NOT NULL,
  `archivo_nombre` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `archivo_ruta` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado_validacion` enum('VALIDO','OBSERVADO','ERROR') COLLATE utf8mb4_general_ci DEFAULT 'VALIDO',
  `fecha_generacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `observaciones` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id_libro`),
  KEY `idx_libro_empresa_periodo` (`id_empresa`,`tipo_libro`,`periodo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_deuda` int NOT NULL,
  `fecha_pago` date NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `observacion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_deuda` (`id_deuda`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ple_temporal`
--

DROP TABLE IF EXISTS `ple_temporal`;
CREATE TABLE IF NOT EXISTS `ple_temporal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_empresa` int DEFAULT NULL,
  `tipo_libro` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `periodo` varchar(7) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fila_texto` text COLLATE utf8mb4_general_ci,
  `fecha_generacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ple_empresa` (`id_empresa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resumen_financiero`
--

DROP TABLE IF EXISTS `resumen_financiero`;
CREATE TABLE IF NOT EXISTS `resumen_financiero` (
  `id_resumen` int NOT NULL AUTO_INCREMENT,
  `id_empresa` int NOT NULL,
  `periodo` varchar(7) COLLATE utf8mb4_general_ci NOT NULL,
  `ingresos` decimal(18,2) DEFAULT '0.00',
  `egresos` decimal(18,2) DEFAULT '0.00',
  `igv_ventas` decimal(18,2) DEFAULT '0.00',
  `igv_compras` decimal(18,2) DEFAULT '0.00',
  `fecha_calculo` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_resumen`),
  UNIQUE KEY `ux_resumen_empresa_periodo` (`id_empresa`,`periodo`),
  UNIQUE KEY `idx_empresa_periodo` (`id_empresa`,`periodo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resumen_financiero`
--

INSERT INTO `resumen_financiero` (`id_resumen`, `id_empresa`, `periodo`, `ingresos`, `egresos`, `igv_ventas`, `igv_compras`, `fecha_calculo`) VALUES
(1, 1, '2024-06', 0.00, 28122.24, 0.00, 4289.84, '2025-10-03 23:26:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `id_empresa` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rol` enum('admin','contador','cliente') COLLATE utf8mb4_general_ci DEFAULT 'contador',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `ux_usuario_correo` (`correo`),
  KEY `fk_usuario_empresa` (`id_empresa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivos_factura`
--
ALTER TABLE `archivos_factura`
  ADD CONSTRAINT `fk_archivo_factura` FOREIGN KEY (`id_factura`) REFERENCES `facturas` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `comprobante_lineas`
--
ALTER TABLE `comprobante_lineas`
  ADD CONSTRAINT `fk_linea_factura` FOREIGN KEY (`id_comprobante`) REFERENCES `facturas` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_factura_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_usuario` FOREIGN KEY (`id_usuario_import`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `libros_electronicos`
--
ALTER TABLE `libros_electronicos`
  ADD CONSTRAINT `fk_libro_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ple_temporal`
--
ALTER TABLE `ple_temporal`
  ADD CONSTRAINT `fk_ple_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `resumen_financiero`
--
ALTER TABLE `resumen_financiero`
  ADD CONSTRAINT `fk_resumen_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
