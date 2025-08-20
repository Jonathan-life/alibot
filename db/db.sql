-- Crear base de datos
CREATE DATABASE alibot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alibot_db;

-- ==========================
-- 1. USUARIOS Y ROLES
-- ==========================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE, -- admin, contador, usuario
    descripcion VARCHAR(255)
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- con hash
    rol_id INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Control de sesiones
CREATE TABLE sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    ip VARCHAR(45),
    navegador VARCHAR(150),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expira_en TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ==========================
-- 2. EMPRESAS
-- ==========================
CREATE TABLE empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruc VARCHAR(11) NOT NULL UNIQUE,
    razon_social VARCHAR(150) NOT NULL,
    direccion VARCHAR(255),
    telefono VARCHAR(20),
    email_contacto VARCHAR(100),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Relación usuarios - empresas (ej: contador maneja varias)
CREATE TABLE usuarios_empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    empresa_id INT NOT NULL,
    rol_empresa ENUM('propietario','contador','empleado') DEFAULT 'empleado',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

-- ==========================
-- 3. COMPROBANTES / SOLICITUDES
-- ==========================
CREATE TABLE tipos_comprobante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE, -- 01=factura, 03=boleta, etc.
    descripcion VARCHAR(100) NOT NULL
);

CREATE TABLE comprobantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo_id INT NOT NULL,
    serie VARCHAR(10) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    descripcion TEXT,
    monto DECIMAL(12,2) NOT NULL,
    igv DECIMAL(12,2) DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    moneda ENUM('PEN','USD') DEFAULT 'PEN',
    estado ENUM('pendiente','enviado','aceptado','rechazado','baja') DEFAULT 'pendiente',
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (tipo_id) REFERENCES tipos_comprobante(id)
);

-- Archivos asociados a comprobantes
CREATE TABLE archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comprobante_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    tipo ENUM('pdf','xml','imagen','otro') DEFAULT 'pdf',
    subido_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comprobante_id) REFERENCES comprobantes(id) ON DELETE CASCADE
);

-- ==========================
-- 4. INTEGRACIÓN CON SUNAT
-- ==========================
CREATE TABLE sunat_consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comprobante_id INT,
    ruc_consultado VARCHAR(11),
    respuesta JSON,
    codigo_respuesta VARCHAR(20),
    mensaje VARCHAR(255),
    consultado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comprobante_id) REFERENCES comprobantes(id) ON DELETE SET NULL
);

-- ==========================
-- 5. AUDITORÍA
-- ==========================
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tabla VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    accion ENUM('INSERT','UPDATE','DELETE','LOGIN','LOGOUT') NOT NULL,
    valores_anteriores JSON,
    valores_nuevos JSON,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- ==========================
-- 6. TAREAS / RECORDATORIOS
-- ==========================
CREATE TABLE tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    usuario_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    fecha_vencimiento DATE NOT NULL,
    estado ENUM('pendiente','completado','atrasado') DEFAULT 'pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- ==========================
-- 7. DATOS INICIALES
-- ==========================
INSERT INTO roles (nombre, descripcion) VALUES
('admin','Acceso total'),
('contador','Gestión contable'),
('usuario','Acceso básico');

INSERT INTO usuarios (nombre,email,password,rol_id)
VALUES ('Admin','admin@alibot.com',MD5('123456'),1);

INSERT INTO tipos_comprobante (codigo, descripcion) VALUES
('01','Factura'),
('03','Boleta de venta'),
('07','Nota de crédito'),
('08','Nota de débito');
