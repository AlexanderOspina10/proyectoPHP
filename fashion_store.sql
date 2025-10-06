CREATE DATABASE IF NOT EXISTS fashion_store;

USE fashion_store;

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
  usuario_id INT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL,
  celular VARCHAR(15) NOT NULL,
  direccion VARCHAR(200) NOT NULL,
  fecha_pedido DATE NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  num_prendas INT NOT NULL,
  estado ENUM('pendiente','confirmado','enviado','entregado','cancelado') DEFAULT 'pendiente',
  notas TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);


-- Tabla de detalles del pedido (productos dentro de cada pedido)
CREATE TABLE IF NOT EXISTS pedido_detalles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  producto_id INT NOT NULL,
  nombre_producto VARCHAR(100) NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  cantidad INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
);