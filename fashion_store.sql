
-- Tabla de usuarios (incluye rol)
CREATE TABLE IF NOT EXISTS usuario (
  id INT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  correo VARCHAR(100) NOT NULL,
  nombre VARCHAR(25) NOT NULL,
  apellido VARCHAR(45) NOT NULL,
  telefono VARCHAR(45) NOT NULL,
  direccion VARCHAR(50) NOT NULL,
  perfil ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
  clave VARCHAR(255) NOT NULL
);

-- Tabla de productos (con imagen)
CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  precio DECIMAL(10,2) NOT NULL,
  categoria VARCHAR(50),
  stock INT DEFAULT 0,
  imagen VARCHAR(255)
);

-- Tabla de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL,
  celular VARCHAR(15) NOT NULL,
  fecha DATE NOT NULL,
  num_prendas VARCHAR(11) NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);