--  Tabla de empresas registradas
CREATE TABLE empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruc VARCHAR(11) NOT NULL UNIQUE,
    razon_social VARCHAR(255) NOT NULL,
    usuario_sol VARCHAR(50) NOT NULL,
    clave_sol VARCHAR(100) NOT NULL,
    api_client_id VARCHAR(255) NOT NULL,
    api_client_secret VARCHAR(255) NOT NULL,
    email_notificacion VARCHAR(255),
    estado ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tokens de SUNAT por empresa
CREATE TABLE sunat_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    access_token TEXT NOT NULL,
    token_type VARCHAR(20),
    expires_in INT, -- segundos de duración (3600 aprox.)
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

--  Ventas SIRE
CREATE TABLE ventas_sire (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    periodo VARCHAR(6) NOT NULL, -- Ej: 202508
    serie VARCHAR(10),
    numero VARCHAR(20),
    fecha_emision DATE,
    cliente_ruc VARCHAR(11),
    cliente_nombre VARCHAR(255),
    total NUMERIC(12,2),
    xml TEXT, -- opcional almacenar XML
    fecha_descarga TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

--  Compras SIRE
CREATE TABLE compras_sire (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    periodo VARCHAR(6) NOT NULL,
    proveedor_ruc VARCHAR(11),
    proveedor_nombre VARCHAR(255),
    tipo_comprobante VARCHAR(5),
    serie VARCHAR(10),
    numero VARCHAR(20),
    fecha_emision DATE,
    total NUMERIC(12,2),
    xml TEXT,
    fecha_descarga TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

--  Buzón electrónico SUNAT / SUNAFIL
CREATE TABLE buzones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    remitente VARCHAR(255),
    asunto VARCHAR(255),
    fecha_recepcion DATETIME,
    contenido TEXT,
    archivo_zip VARCHAR(255), -- ruta archivo descargado
    leido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

--  Detracciones
CREATE TABLE detracciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    tipo VARCHAR(50), -- compra, pago, cruce
    fecha DATE,
    proveedor_cliente VARCHAR(255),
    ruc_proveedor_cliente VARCHAR(11),
    monto NUMERIC(12,2),
    archivo_excel VARCHAR(255),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

--  Recibos por Honorarios
CREATE TABLE recibos_honorarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    serie VARCHAR(10),
    numero VARCHAR(20),
    fecha DATE,
    emisor_ruc VARCHAR(11),
    emisor_nombre VARCHAR(255),
    importe NUMERIC(12,2),
    xml TEXT,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

-- Notificaciones (por email/whatsapp)
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    tipo ENUM('email','whatsapp') NOT NULL,
    mensaje TEXT NOT NULL,
    estado ENUM('pendiente','enviado','error') DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_envio TIMESTAMP NULL,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);
