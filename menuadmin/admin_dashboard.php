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

// Procesar acciones CRUD
$mensaje = '';
$tipo_mensaje = '';

// CREAR producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
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

// ACTUALIZAR producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
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

// ELIMINAR producto
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $sql = "DELETE FROM productos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id])) {
        if ($producto['imagen'] && file_exists('/menuadmin/assets/img/productos/' . $producto['imagen'])) {
            unlink('/menuadmin/assets/img/productos/' . $producto['imagen']);
        }
        $mensaje = "Producto eliminado exitosamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al eliminar el producto";
        $tipo_mensaje = "danger";
    }
}

// LEER productos
$sql = "SELECT * FROM productos ORDER BY id DESC";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener producto para editar
$producto_editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Fashion Store</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
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
        
        .admin-header .user-info {
            font-size: 0.95rem;
            opacity: 0.95;
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
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 35px rgba(0,0,0,0.12);
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
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
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
            transition: transform 0.3s;
        }
        
        .producto-img:hover {
            transform: scale(1.1);
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
        
        .btn-warning:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
        }
        
        .btn-danger:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        }
        
        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #6c757d;
            font-size: 1.1rem;
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
        
        .stats-card p {
            color: #6c757d;
            margin: 0;
            font-size: 0.9rem;
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
                    <p class="user-info mb-0 mt-1">
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
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                    <h3><?php echo count($productos); ?></h3>
                    <p>Total Productos</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h3><?php echo count(array_filter($productos, fn($p) => $p['stock'] > 0)); ?></h3>
                    <p>Con Stock</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                    <h3><?php echo count(array_filter($productos, fn($p) => $p['stock'] == 0)); ?></h3>
                    <p>Sin Stock</p>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $tipo_mensaje === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Formulario Crear/Editar -->
        <div class="card">
            <div class="card-header text-white">
                <h4>
                    <i class="bi bi-<?php echo $producto_editar ? 'pencil-square' : 'plus-circle-fill'; ?> me-2"></i>
                    <?php echo $producto_editar ? 'Editar Producto' : 'Crear Nuevo Producto'; ?>
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="<?php echo $producto_editar ? 'actualizar' : 'crear'; ?>">
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
                        <a href="admin_dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Productos -->
        <div class="card">
            <div class="card-header text-white">
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
                                    <a href="?editar=<?php echo $producto['id']; ?>" 
                                    class="btn btn-sm btn-warning btn-action" 
                                    title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="?eliminar=<?php echo $producto['id']; ?>" 
                                    class="btn btn-sm btn-danger btn-action" 
                                    title="Eliminar"
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
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>No hay productos registrados</p>
                    <small class="text-muted">Comienza agregando tu primer producto usando el formulario de arriba</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>