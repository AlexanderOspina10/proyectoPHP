<?php
session_start();

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php#Iniciosesion");
    exit();
}

// Verificar que hay datos de pedido en sesión
if (!isset($_SESSION['pedido']) || empty($_SESSION['pedido']['items'])) {
    $_SESSION['error_pedido'] = "❌ No hay productos en el pedido.";
    header("Location: ../index.php#Catalogo");
    exit();
}

// Verificar que la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: formulario_pedido.php");
    exit();
}

// Conexión a la base de datos
$host = "localhost";
$user = "root";
$pass = "3135497455Jj";
$db   = "fashion_store";

$con = new mysqli($host, $user, $pass, $db);

if ($con->connect_errno) {
    $_SESSION['error_pedido'] = "❌ Error de conexión a la base de datos.";
    header("Location: formulario_pedido.php");
    exit();
}

$con->set_charset("utf8mb4");

// Recoger datos del formulario
$usuario_id = $_SESSION['id'];
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$celular = trim($_POST['celular'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$fecha_entrega = $_POST['fecha_entrega'] ?? '';
$notas = trim($_POST['notas'] ?? '');

// Validaciones
if (empty($nombre) || empty($correo) || empty($celular) || empty($direccion) || empty($fecha_entrega)) {
    $_SESSION['error_pedido'] = "❌ Por favor completa todos los campos obligatorios.";
    header("Location: formulario_pedido.php");
    exit();
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_pedido'] = "❌ El correo electrónico no es válido.";
    header("Location: formulario_pedido.php");
    exit();
}

if (!preg_match('/^\d{10}$/', $celular)) {
    $_SESSION['error_pedido'] = "❌ El número de celular debe tener 10 dígitos.";
    header("Location: formulario_pedido.php");
    exit();
}

$items_pedido = $_SESSION['pedido']['items'];
$total_pedido = $_SESSION['pedido']['total'];
$num_prendas = array_sum(array_column($items_pedido, 'cantidad'));

// Iniciar transacción
$con->begin_transaction();

try {
    // 1. Verificar stock de todos los productos antes de procesar
    $productos_sin_stock = [];
    foreach ($items_pedido as $producto_id => $item) {
        $stmt = $con->prepare("SELECT nombre, stock FROM productos WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $producto = $result->fetch_assoc();
        $stmt->close();
        
        if (!$producto) {
            throw new Exception("Producto ID {$producto_id} no encontrado.");
        }
        
        if ($producto['stock'] < $item['cantidad']) {
            $productos_sin_stock[] = $producto['nombre'] . " (disponibles: {$producto['stock']}, solicitados: {$item['cantidad']})";
        }
    }
    
    // Si hay productos sin stock suficiente, abortar
    if (!empty($productos_sin_stock)) {
        throw new Exception("Stock insuficiente para: " . implode(", ", $productos_sin_stock));
    }
    
    // 2. Insertar el pedido principal
    $stmt = $con->prepare("INSERT INTO pedidos (usuario_id, nombre, correo, celular, direccion, fecha_pedido, total, num_prendas, estado, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)");
    $stmt->bind_param("isssssdis", $usuario_id, $nombre, $correo, $celular, $direccion, $fecha_entrega, $total_pedido, $num_prendas, $notas);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al insertar el pedido: " . $stmt->error);
    }
    
    $pedido_id = $con->insert_id;
    $stmt->close();
    
    // 3. Insertar los detalles del pedido y actualizar stock
    $stmt_detalle = $con->prepare("INSERT INTO pedido_detalles (pedido_id, producto_id, nombre_producto, precio_unitario, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_actualizar_stock = $con->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
    
    foreach ($items_pedido as $producto_id => $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        
        // Insertar detalle del pedido
        $stmt_detalle->bind_param("iisdid", $pedido_id, $producto_id, $item['nombre'], $item['precio'], $item['cantidad'], $subtotal);
        if (!$stmt_detalle->execute()) {
            throw new Exception("Error al insertar detalle del pedido: " . $stmt_detalle->error);
        }
        
        // Actualizar stock del producto
        $stmt_actualizar_stock->bind_param("ii", $item['cantidad'], $producto_id);
        if (!$stmt_actualizar_stock->execute()) {
            throw new Exception("Error al actualizar stock del producto ID {$producto_id}: " . $stmt_actualizar_stock->error);
        }
    }
    
    $stmt_detalle->close();
    $stmt_actualizar_stock->close();
    
    // 4. Confirmar transacción
    $con->commit();
    
    // 5. Limpiar carrito y datos de pedido
    unset($_SESSION['carrito']);
    unset($_SESSION['pedido']);
    
    // 6. Redirigir a página de confirmación
    $_SESSION['pedido_exitoso'] = [
        'pedido_id' => $pedido_id,
        'total' => $total_pedido,
        'num_prendas' => $num_prendas,
        'fecha_entrega' => $fecha_entrega
    ];
    
    header("Location: confirmacion_pedido.php");
    exit();
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $con->rollback();
    $_SESSION['error_pedido'] = "❌ " . $e->getMessage();
    header("Location: formulario_pedido.php");
    exit();
}

$con->close();
?>