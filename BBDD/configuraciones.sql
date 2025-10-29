-- Tabla para almacenar configuraciones del sistema
CREATE TABLE IF NOT EXISTS configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descripcion TEXT,
    tipo ENUM('texto', 'numero', 'decimal', 'booleano', 'fecha') DEFAULT 'texto',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    usuario_actualizacion VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla para preferencias de usuario
CREATE TABLE IF NOT EXISTS preferencias_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    moneda_preferida ENUM('BS', 'USD', 'AMBAS') DEFAULT 'BS',
    sonidos_notificacion TINYINT(1) DEFAULT 1,
    confirmacion_ventas TINYINT(1) DEFAULT 1,
    auto_imprimir TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario) REFERENCES usuarios(nombre) ON DELETE CASCADE
);

-- Insertar configuraciones iniciales
INSERT INTO configuraciones (clave, valor, descripcion, tipo) VALUES
('tasa_dolar', '36.00', 'Tasa de cambio del dólar estadounidense', 'decimal'),
('moneda_principal', 'BS', 'Moneda principal del sistema', 'texto'),
('nombre_empresa', 'Cafetín', 'Nombre de la empresa', 'texto'),
('direccion_empresa', '', 'Dirección de la empresa', 'texto'),
('telefono_empresa', '', 'Teléfono de la empresa', 'texto'),
('email_empresa', '', 'Email de la empresa', 'texto'),
('iva_porcentaje', '16.00', 'Porcentaje de IVA aplicable', 'decimal'),
('formato_fecha', 'Y-m-d', 'Formato de fecha del sistema', 'texto'),
('zona_horaria', 'America/Caracas', 'Zona horaria del sistema', 'texto'),
('idioma_sistema', 'es', 'Idioma del sistema', 'texto');

-- Tabla para historial de cambios de tasa
CREATE TABLE IF NOT EXISTS historial_tasa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tasa_anterior DECIMAL(10,2),
    tasa_nueva DECIMAL(10,2) NOT NULL,
    usuario VARCHAR(100) NOT NULL,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo TEXT
);