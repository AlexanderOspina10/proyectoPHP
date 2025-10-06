<?php
session_start();

// Verificar sesión activa y perfil de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Configuración de base de datos
$host = 'localhost';
$dbname = 'fashion_store';
$username = 'root';
$password = '3135497455Jj';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesar acciones
$mensaje = '';
$tipo_mensaje = '';

// ============= CRUD PRODUCTOS =============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_producto') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $categoria = trim($_POST['categoria']);
    $stock = intval($_POST['stock']);
    
    $imagen = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen = 'producto_' . time() . '.' . $extension;
        $ruta_destino = 'assets/img/productos/' . $imagen;
        
        if (!is_dir('assets/img/productos/')) {
            mkdir('assets/img/productos/', 0777, true);
        }
        
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino);
    }
    
    $sql = "INSERT INTO productos (nombre, descripcion, precio, categoria, stock, imagen) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nombre, $descripcion, $precio, $categoria, $stock, $imagen])) {
        $mensaje = "Producto creado exitosamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al crear el producto";
        $tipo_mensaje = "danger";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_producto') {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $categoria = trim($_POST['categoria']);
    $stock = intval($_POST['stock']);
    
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    $imagen = $producto_actual['imagen'];
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        if ($imagen && file_exists('assets/img/productos/' . $imagen)) {
            unlink('assets/img/productos/' . $imagen);
        }
        
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen = 'producto_' . time() . '.' . $extension;
        $ruta_destino = 'assets/img/productos/' . $imagen;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino);
    }
    
    $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria = ?, stock = ?, imagen = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nombre, $descripcion, $precio, $categoria, $stock, $imagen, $id])) {
        $mensaje = "Producto actualizado exitosamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al actualizar el producto";
        $tipo_mensaje = "danger";
    }
}

if (isset($_GET['eliminar_producto'])) {
    $id = intval($_GET['eliminar_producto']);
    
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $sql = "DELETE FROM productos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id])) {
        if ($producto['imagen'] && file_exists('assets/img/productos/' . $producto['imagen'])) {
            unlink('assets/img/productos/' . $producto['imagen']);
        }
        $mensaje = "Producto eliminado exitosamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al eliminar el producto";
        $tipo_mensaje = "danger";
    }
}

// ============= CRUD USUARIOS =============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_usuario') {
    $correo = trim($_POST['correo']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $perfil = $_POST['perfil'];
    $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO usuario (correo, nombre, apellido, telefono, direccion, perfil, clave) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$correo, $nombre, $apellido, $telefono, $direccion, $perfil, $clave])) {
        $mensaje = "Usuario creado exitosamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al crear el usuario";
        $tipo_mensaje = "danger";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_usuario') {
    $id = intval($_POST['id']);
    $correo = trim($_POST['correo']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $perfil = $_POST['perfil'];
    
    if (!empty($_POST['clave'])) {
        $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
        $sql = "UPDATE usuario SET correo = ?, nombre = ?, apellido = ?, telefono = ?, direccion = ?, perfil = ?, clave = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$correo, $nombre, $apellido, $telefono, $direccion, $perfil, $clave, $id]);
    } else {
        $sql = "UPDATE usuario SET correo = ?, nombre = ?, apellido = ?, telefono = ?, direccion = ?, perfil = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$correo, $nombre, $apellido, $telefono, $direccion, $perfil, $id]);
    }
    
    if ($result) {
        $mensaje = "Usuario actualizado exitosamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al actualizar el usuario";
        $tipo_mensaje = "danger";
    }
}

if (isset($_GET['eliminar_usuario'])) {
    $id = intval($_GET['eliminar_usuario']);
    
    if ($id === $_SESSION['usuario_id']) {
        $mensaje = "No puedes eliminar tu propia cuenta";
        $tipo_mensaje = "warning";
    } else {
        $sql = "DELETE FROM usuario WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id])) {
            $mensaje = "Usuario eliminado exitosamente";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar el usuario";
            $tipo_mensaje = "danger";
        }
    }
}

// ============= GESTIÓN PEDIDOS =============
if (isset($_GET['eliminar_pedido'])) {
    $pedido_id = $_GET['eliminar_pedido'];
    
    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // 1. Primero restaurar el stock
        $productos_afectados = restaurarStock($pdo, $pedido_id);
        
        // 2. Eliminar los detalles del pedido
        $stmt_eliminar_detalles = $pdo->prepare("DELETE FROM pedido_detalles WHERE pedido_id = ?");
        $stmt_eliminar_detalles->execute([$pedido_id]);
        
        // 3. Eliminar el pedido
        $stmt_eliminar_pedido = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt_eliminar_pedido->execute([$pedido_id]);
        
        $pdo->commit();
        
        $mensaje_stock = count($productos_afectados) . " producto(s) afectado(s)";
        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'texto' => 'Pedido eliminado y stock restaurado correctamente. ' . $mensaje_stock
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensaje'] = [
            'tipo' => 'danger',
            'texto' => 'Error al eliminar el pedido: ' . $e->getMessage()
        ];
    }
    
    header('Location: admin_dashboard.php?tab=pedidos');
    exit;
}

/**
 * Función para restaurar el stock (debe estar disponible en admin_dashboard.php también)
 */
function restaurarStock($pdo, $pedido_id) {
    // Obtener los detalles del pedido
    $stmt_detalles = $pdo->prepare("
        SELECT pd.producto_id, pd.cantidad, p.nombre 
        FROM pedido_detalles pd 
        LEFT JOIN productos p ON pd.producto_id = p.id 
        WHERE pd.pedido_id = ?
    ");
    $stmt_detalles->execute([$pedido_id]);
    $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
    
    $productos_afectados = [];
    
    foreach ($detalles as $detalle) {
        if ($detalle['producto_id']) {
            // Restaurar el stock sumando la cantidad
            $stmt_update_stock = $pdo->prepare("
                UPDATE productos 
                SET stock = stock + ? 
                WHERE id = ?
            ");
            $stmt_update_stock->execute([$detalle['cantidad'], $detalle['producto_id']]);
            
            $productos_afectados[] = [
                'id' => $detalle['producto_id'],
                'nombre' => $detalle['nombre'],
                'cantidad' => $detalle['cantidad']
            ];
        }
    }
    
    return $productos_afectados;
}

// Obtener datos
$productos = $pdo->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$usuarios = $pdo->query("SELECT * FROM usuario ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$pedidos = $pdo->query("SELECT p.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido 
                        FROM pedidos p 
                        LEFT JOIN usuario u ON p.usuario_id = u.id 
                        ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos para editar
$producto_editar = null;
$usuario_editar = null;
$pedido_detalle = null;

if (isset($_GET['editar_producto'])) {
    $id = intval($_GET['editar_producto']);
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_GET['editar_usuario'])) {
    $id = intval($_GET['editar_usuario']);
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE id = ?");
    $stmt->execute([$id]);
    $usuario_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_GET['ver_pedido'])) {
    $id = intval($_GET['ver_pedido']);
    $stmt = $pdo->prepare("SELECT p.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.correo as usuario_correo 
                          FROM pedidos p 
                          LEFT JOIN usuario u ON p.usuario_id = u.id 
                          WHERE p.id = ?");
    $stmt->execute([$id]);
    $pedido_detalle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM pedido_detalles WHERE pedido_id = ?");
    $stmt->execute([$id]);
    $pedido_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Determinar pestaña activa
$tab_activa = 'productos';
if (isset($_GET['tab'])) {
    $tab_activa = $_GET['tab'];
}
if (isset($_GET['editar_producto']) || isset($_GET['eliminar_producto'])) {
    $tab_activa = 'productos';
}
if (isset($_GET['editar_usuario']) || isset($_GET['eliminar_usuario'])) {
    $tab_activa = 'usuarios';
}
if (isset($_GET['ver_pedido']) || isset($_GET['eliminar_pedido'])) {
    $tab_activa = 'pedidos';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Fashion Store</title>
    
    <link href="/FashionStore/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/FashionStore/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 0;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            margin-bottom: 40px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .admin-header h2 {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
        }
        
        .btn-header {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }
        
        .btn-header:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
            color: white;
            transform: translateY(-2px);
        }
        
        .nav-tabs {
            border: none;
            margin-bottom: 30px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            background: white;
            color: #6c757d;
            font-weight: 600;
            padding: 15px 30px;
            margin-right: 10px;
            border-radius: 12px 12px 0 0;
            transition: all 0.3s;
        }
        
        .nav-tabs .nav-link:hover {
            color: #667eea;
            background: #f8f9fa;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 20px 25px;
        }
        
        .card-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.3rem;
            color: white;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        
        .table thead th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 15px;
        }
        
        .table tbody tr {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .table tbody tr:hover {
            transform: scale(1.01);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .table tbody td {
            border: none;
            padding: 20px 15px;
            vertical-align: middle;
        }
        
        .producto-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        }
        
        .badge {
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .btn-action {
            margin: 3px;
            border-radius: 8px;
            padding: 8px 12px;
            transition: all 0.3s;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            border: none;
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            color: white;
        }
        
        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        
        .stats-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin: 10px 0;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>
    <!-- Header Admin -->
    <div class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2><i class="bi bi-grid-fill me-2"></i>Panel de Administración</h2>
                    <p class="mb-0 mt-1" style="font-size: 0.95rem; opacity: 0.95;">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                    </p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="../index.php" class="btn btn-header">
                        <i class="bi bi-shop me-1"></i> Ver Tienda
                    </a>
                    <a href="../logout.php" class="btn btn-header">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Mensajes -->
        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $tipo_mensaje === 'success' ? 'check-circle-fill' : ($tipo_mensaje === 'warning' ? 'exclamation-triangle-fill' : 'exclamation-circle-fill'); ?> me-2"></i>
            <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Estadísticas Generales -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                    <h3><?php echo count($productos); ?></h3>
                    <p>Total Productos</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <i class="bi bi-people text-info" style="font-size: 2rem;"></i>
                    <h3><?php echo count($usuarios); ?></h3>
                    <p>Total Usuarios</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <i class="bi bi-cart-check text-success" style="font-size: 2rem;"></i>
                    <h3><?php echo count($pedidos); ?></h3>
                    <p>Total Pedidos</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <i class="bi bi-clock-history text-warning" style="font-size: 2rem;"></i>
                    <h3><?php echo count(array_filter($pedidos, fn($p) => $p['estado'] === 'pendiente')); ?></h3>
                    <p>Pedidos Pendientes</p>
                </div>
            </div>
        </div>

        <!-- Pestañas de Navegación -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo $tab_activa === 'productos' ? 'active' : ''; ?>" 
                   href="?tab=productos">
                    <i class="bi bi-box-seam me-2"></i>Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $tab_activa === 'usuarios' ? 'active' : ''; ?>" 
                   href="?tab=usuarios">
                    <i class="bi bi-people me-2"></i>Usuarios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $tab_activa === 'pedidos' ? 'active' : ''; ?>" 
                   href="?tab=pedidos">
                    <i class="bi bi-cart-check me-2"></i>Pedidos
                </a>
            </li>
        </ul>

        <!-- CONTENIDO PRODUCTOS -->
        <?php if ($tab_activa === 'productos'): ?>
        
        <!-- Formulario Crear/Editar Producto -->
        <div class="card">
            <div class="card-header">
                <h4>
                    <i class="bi bi-<?php echo $producto_editar ? 'pencil-square' : 'plus-circle-fill'; ?> me-2"></i>
                    <?php echo $producto_editar ? 'Editar Producto' : 'Crear Nuevo Producto'; ?>
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="<?php echo $producto_editar ? 'actualizar_producto' : 'crear_producto'; ?>">
                    <?php if ($producto_editar): ?>
                    <input type="hidden" name="id" value="<?php echo $producto_editar['id']; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-tag-fill me-1"></i>Nombre del Producto *</label>
                            <input type="text" class="form-control" name="nombre" 
                                   value="<?php echo $producto_editar ? htmlspecialchars($producto_editar['nombre']) : ''; ?>" 
                                   placeholder="Ej: Camisa Elegante" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-filter-circle me-1"></i>Categoría *</label>
                            <select class="form-select" name="categoria" required>
                                <option value="">Seleccione una categoría...</option>
                                <option value="hombre" <?php echo ($producto_editar && $producto_editar['categoria'] === 'hombre') ? 'selected' : ''; ?>>Hombre</option>
                                <option value="mujer" <?php echo ($producto_editar && $producto_editar['categoria'] === 'mujer') ? 'selected' : ''; ?>>Mujer</option>
                                <option value="zapatos" <?php echo ($producto_editar && $producto_editar['categoria'] === 'zapatos') ? 'selected' : ''; ?>>Zapatos</option>
                                <option value="accesorios" <?php echo ($producto_editar && $producto_editar['categoria'] === 'accesorios') ? 'selected' : ''; ?>>Accesorios</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-text-paragraph me-1"></i>Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="4" 
                                  placeholder="Describe las características del producto..."><?php echo $producto_editar ? htmlspecialchars($producto_editar['descripcion']) : ''; ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-currency-dollar me-1"></i>Precio (COP) *</label>
                            <input type="number" step="0.01" class="form-control" name="precio" 
                                   value="<?php echo $producto_editar ? $producto_editar['precio'] : ''; ?>" 
                                   placeholder="0.00" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-box me-1"></i>Stock *</label>
                            <input type="number" class="form-control" name="stock" 
                                   value="<?php echo $producto_editar ? $producto_editar['stock'] : '0'; ?>" 
                                   placeholder="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-image me-1"></i>Imagen</label>
                            <input type="file" class="form-control" name="imagen" accept="image/*">
                            <?php if ($producto_editar && $producto_editar['imagen']): ?>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                Imagen actual: <?php echo htmlspecialchars($producto_editar['imagen']); ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i>
                            <?php echo $producto_editar ? 'Actualizar Producto' : 'Guardar Producto'; ?>
                        </button>
                        <?php if ($producto_editar): ?>
                        <a href="?tab=productos" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Productos -->
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-list-check me-2"></i>Inventario de Productos</h4>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($productos)): ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><strong>#<?php echo $producto['id']; ?></strong></td>
                                <td>
                                    <?php if ($producto['imagen']): ?>
                                    <img src="assets/img/productos/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                         class="producto-img" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($producto['nombre']); ?></strong></td>
                                <td><span class="badge bg-info"><?php echo ucfirst($producto['categoria']); ?></span></td>
                                <td><strong>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <span class="badge <?php echo $producto['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $producto['stock']; ?> unid.
                                    </span>
                                </td>
                                <td>
                                    <a href="?tab=productos&editar_producto=<?php echo $producto['id']; ?>" 
                                       class="btn btn-sm btn-warning btn-action" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="?tab=productos&eliminar_producto=<?php echo $producto['id']; ?>" 
                                       class="btn btn-sm btn-danger btn-action" title="Eliminar"
                                       onclick="return confirm('¿Está seguro de eliminar este producto?\n\nProducto: <?php echo htmlspecialchars($producto['nombre']); ?>');">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                    <p class="text-muted mt-3">No hay productos registrados</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php endif; ?>

        <!-- CONTENIDO USUARIOS -->
        <?php if ($tab_activa === 'usuarios'): ?>
        
        <!-- Formulario Crear/Editar Usuario -->
        <div class="card">
            <div class="card-header">
                <h4>
                    <i class="bi bi-<?php echo $usuario_editar ? 'pencil-square' : 'person-plus-fill'; ?> me-2"></i>
                    <?php echo $usuario_editar ? 'Editar Usuario' : 'Crear Nuevo Usuario'; ?>
                </h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="<?php echo $usuario_editar ? 'actualizar_usuario' : 'crear_usuario'; ?>">
                    <?php if ($usuario_editar): ?>
                    <input type="hidden" name="id" value="<?php echo $usuario_editar['id']; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-envelope-fill me-1"></i>Correo Electrónico *</label>
                            <input type="email" class="form-control" name="correo" 
                                   value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['correo']) : ''; ?>" 
                                   placeholder="usuario@ejemplo.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-shield-fill me-1"></i>Perfil *</label>
                            <select class="form-select" name="perfil" required>
                                <option value="usuario" <?php echo ($usuario_editar && $usuario_editar['perfil'] === 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                                <option value="admin" <?php echo ($usuario_editar && $usuario_editar['perfil'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person-fill me-1"></i>Nombre *</label>
                            <input type="text" class="form-control" name="nombre" 
                                   value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['nombre']) : ''; ?>" 
                                   placeholder="Nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person-fill me-1"></i>Apellido *</label>
                            <input type="text" class="form-control" name="apellido" 
                                   value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['apellido']) : ''; ?>" 
                                   placeholder="Apellido" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-telephone-fill me-1"></i>Teléfono *</label>
                            <input type="tel" class="form-control" name="telefono" 
                                   value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['telefono']) : ''; ?>" 
                                   placeholder="3001234567" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-house-fill me-1"></i>Dirección *</label>
                            <input type="text" class="form-control" name="direccion" 
                                   value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['direccion']) : ''; ?>" 
                                   placeholder="Calle 123 #45-67" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label"><i class="bi bi-lock-fill me-1"></i>Contraseña <?php echo $usuario_editar ? '(dejar en blanco para mantener la actual)' : '*'; ?></label>
                            <input type="password" class="form-control" name="clave" 
                                   placeholder="Contraseña segura" <?php echo !$usuario_editar ? 'required' : ''; ?>>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i>
                            <?php echo $usuario_editar ? 'Actualizar Usuario' : 'Crear Usuario'; ?>
                        </button>
                        <?php if ($usuario_editar): ?>
                        <a href="?tab=usuarios" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Usuarios -->
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-people-fill me-2"></i>Lista de Usuarios</h4>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($usuarios)): ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th>Perfil</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><strong>#<?php echo $usuario['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['direccion']); ?></td>
                                <td>
                                    <span class="badge <?php echo $usuario['perfil'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                        <i class="bi bi-<?php echo $usuario['perfil'] === 'admin' ? 'shield-fill' : 'person-fill'; ?> me-1"></i>
                                        <?php echo ucfirst($usuario['perfil']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?tab=usuarios&editar_usuario=<?php echo $usuario['id']; ?>" 
                                       class="btn btn-sm btn-warning btn-action" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <?php if ($usuario['id'] !== $_SESSION['usuario_id']): ?>
                                    <a href="?tab=usuarios&eliminar_usuario=<?php echo $usuario['id']; ?>" 
                                       class="btn btn-sm btn-danger btn-action" title="Eliminar"
                                       onclick="return confirm('¿Está seguro de eliminar este usuario?\n\nUsuario: <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>');">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-people" style="font-size: 4rem; color: #dee2e6;"></i>
                    <p class="text-muted mt-3">No hay usuarios registrados</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php endif; ?>

   <!-- CONTENIDO PEDIDOS -->
<?php if ($tab_activa === 'pedidos'): ?>

<!-- Lista de Pedidos -->
<div class="card">
    <div class="card-header">
        <h4><i class="bi bi-cart-check-fill me-2"></i>Gestión de Pedidos</h4>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($pedidos)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="80">ID</th>
                        <th>Cliente</th>
                        <th width="120">Fecha</th>
                        <th width="120">Total</th>
                        <th width="100">Prendas</th>
                        <th width="130">Estado</th>
                        <th width="120" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                    <?php 
                    $color_estado = [
                        'pendiente' => 'warning',
                        'confirmado' => 'info',
                        'enviado' => 'primary',
                        'entregado' => 'success',
                        'cancelado' => 'danger'
                    ];
                    $estado_actual = $pedido['estado'] ?? 'pendiente';
                    ?>
                    <tr>
                        <td><strong>#<?php echo $pedido['id']; ?></strong></td>
                        <td>
                            <div class="d-flex flex-column">
                                <strong class="text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($pedido['nombre']); ?></strong>
                                <small class="text-muted text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($pedido['correo']); ?></small>
                            </div>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                        <td><strong>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></strong></td>
                        <td class="text-center"><span class="badge bg-secondary"><?php echo $pedido['num_prendas']; ?></span></td>
                        <td>
                            <span class="badge bg-<?php echo $color_estado[$estado_actual]; ?>">
                                <?php echo ucfirst($estado_actual); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <!-- Cambiamos el botón para redirigir a página de edición -->
                                <a href="editar_pedido.php?id=<?php echo $pedido['id']; ?>" 
                                   class="btn btn-outline-info btn-action" 
                                   title="Editar Pedido">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="?tab=pedidos&eliminar_pedido=<?php echo $pedido['id']; ?>" 
                                class="btn btn-outline-danger btn-action" 
                                title="Eliminar"
                                onclick="return confirm('¿Está seguro de eliminar este pedido?\n\nPedido #<?php echo $pedido['id']; ?>');">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 4rem; color: #dee2e6;"></i>
            <p class="text-muted mt-3">No hay pedidos registrados</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
   
</body>
</html>