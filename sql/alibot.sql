-- Base de datos para Alibot (MySQL)
CREATE DATABASE IF NOT EXISTS alibot CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE alibot;

CREATE TABLE IF NOT EXISTS empresas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ruc VARCHAR(20) NOT NULL UNIQUE,
  razon_social VARCHAR(255),
  correo VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS servicios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(100),
  tarifa DECIMAL(10,4) DEFAULT 0.20,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS solicitudes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT NOT NULL,
  servicio_id INT NOT NULL,
  fecha_desde DATE,
  fecha_hasta DATE,
  cantidad_doc INT DEFAULT 0,
  precio DECIMAL(12,2) DEFAULT 0.00,
  estado ENUM('pendiente','procesando','completado','cancelado') DEFAULT 'pendiente',
  resultado_json TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  FOREIGN KEY (servicio_id) REFERENCES servicios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar servicios iniciales
INSERT IGNORE INTO servicios (clave,nombre,tarifa) VALUES
('buzon_sunat','Buzón electrónico SUNAT',0.20),
('compras_sire','Compras SIRE',0.15),
('ventas_sire','Ventas SIRE',0.15),
('casilla_sunafil','Casilla electrónica SUNAFIL',0.25);
