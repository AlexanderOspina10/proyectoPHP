<?php
session_start();

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

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Obtener el ID del pedido desde la URL
$pedido_id = $_GET['id'] ?? 0;

if (!$pedido_id) {
    header('Location: admin_dashboard.php?tab=pedidos');
    exit;
}

/**
 * Función para restaurar el stock cuando se cancela o elimina un pedido
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
            
            // Registrar la operación en un log
            error_log("Stock restaurado: Producto ID {$detalle['producto_id']} - Cantidad: {$detalle['cantidad']}");
        }
    }
    
    return $productos_afectados;
}

try {
    // Obtener información del pedido
    $stmt_pedido = $pdo->prepare("
        SELECT p.*, COUNT(pd.id) as num_prendas 
        FROM pedidos p 
        LEFT JOIN pedido_detalles pd ON p.id = pd.pedido_id 
        WHERE p.id = ? 
        GROUP BY p.id
    ");
    $stmt_pedido->execute([$pedido_id]);
    $pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        header('Location: admin_dashboard.php?tab=pedidos');
        exit;
    }

    // Obtener detalles del pedido
    $stmt_detalle = $pdo->prepare("SELECT * FROM pedido_detalles WHERE pedido_id = ?");
    $stmt_detalle->execute([$pedido_id]);
    $items_pedido = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

    // Procesar actualización del estado
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_estado_pedido') {
        $nuevo_estado = $_POST['estado'] ?? '';
        $estado_anterior = $pedido['estado'];
        $estados_permitidos = ['pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado'];
        
        // Validar que no se intente reactivar un pedido cancelado
        if ($estado_anterior === 'cancelado' && $nuevo_estado !== 'cancelado') {
            $_SESSION['mensaje'] = [
                'tipo' => 'warning',
                'texto' => 'No se puede reactivar un pedido cancelado. El cliente debe realizar un nuevo pedido.'
            ];
        }
        elseif (in_array($nuevo_estado, $estados_permitidos)) {
            // Iniciar transacción para asegurar la consistencia de datos
            $pdo->beginTransaction();
            
            try {
                // Actualizar estado del pedido
                $stmt_update = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
                $stmt_update->execute([$nuevo_estado, $pedido_id]);
                
                // Si el pedido se cancela y antes no estaba cancelado, restaurar stock DEFINITIVAMENTE
                if ($nuevo_estado === 'cancelado' && $estado_anterior !== 'cancelado') {
                    $productos_afectados = restaurarStock($pdo, $pedido_id);
                    $mensaje_stock = count($productos_afectados) . " producto(s) afectado(s)";
                    
                    $_SESSION['mensaje'] = [
                        'tipo' => 'info',
                        'texto' => 'Pedido cancelado y stock restaurado correctamente. ' . $mensaje_stock . '. El pedido no podrá ser reactivado.'
                    ];
                } else {
                    $_SESSION['mensaje'] = [
                        'tipo' => 'success',
                        'texto' => 'Estado del pedido actualizado correctamente'
                    ];
                }
                
                $pdo->commit();
                
                // Recargar los datos del pedido
                $stmt_pedido->execute([$pedido_id]);
                $pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['mensaje'] = [
                    'tipo' => 'danger',
                    'texto' => 'Error al actualizar el pedido: ' . $e->getMessage()
                ];
            }
        }
    }

} catch (PDOException $e) {
    error_log("Error al obtener pedido: " . $e->getMessage());
    $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'texto' => 'Error al cargar el pedido: ' . $e->getMessage()
    ];
}

$color_estado = [
    'pendiente' => 'warning',
    'confirmado' => 'info',
    'enviado' => 'primary',
    'entregado' => 'success',
    'cancelado' => 'danger'
];
$estado_actual = $pedido['estado'] ?? 'pendiente';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido #<?php echo $pedido_id; ?> - Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
        
        .card-header h4, .card-header h6 {
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
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(220, 53, 69, 0.3);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            color: white;
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
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 0;
        }
        
        .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb-item.active {
            color: #6c757d;
        }
        
        .page-title {
            color: #343a40;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .info-card .card-header {
            padding: 15px 20px;
        }
        
        .info-card .card-header h6 {
            font-size: 1.1rem;
            margin: 0;
        }
        
        .info-card .card-body {
            padding: 20px;
        }
        
        .info-card .card-body p {
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .estado-bloqueado {
            opacity: 0.6;
            pointer-events: none;
            background-color: #f8f9fa;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border: none;
        }
        
        .texto-advertencia {
            color: #dc3545;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Header similar al del panel admin -->
    <div class="admin-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2><i class="bi bi-gear-fill me-2"></i>Panel Administrativo</h2>
                </div>
                <div class="col-md-6 text-end">
                    <a href="admin_dashboard.php?tab=pedidos" class="btn btn-header">
                        <i class="bi bi-speedometer2 me-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-receipt me-2"></i>Editar Pedido #<?php echo $pedido_id; ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin_dashboard.php?tab=pedidos">Pedidos</a></li>
                        <li class="breadcrumb-item active">Editar Pedido #<?php echo $pedido_id; ?></li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="admin_dashboard.php?tab=pedidos" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver a Pedidos
                </a>
            </div>
        </div>

        <!-- Mensajes de alerta -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?php echo $_SESSION['mensaje']['tipo']; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $_SESSION['mensaje']['tipo'] === 'success' ? 'check-circle' : ($_SESSION['mensaje']['tipo'] === 'warning' ? 'exclamation-triangle' : 'info-circle'); ?>-fill me-2"></i>
                <?php echo $_SESSION['mensaje']['texto']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <!-- Información del Cliente y Pedido -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card info-card h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="fw-bold mb-0"><i class="bi bi-person-circle me-2"></i>Información del Cliente</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Nombre:</strong> <?php echo htmlspecialchars($pedido['nombre']); ?></p>
                        <p class="mb-2"><strong>Correo:</strong> <?php echo htmlspecialchars($pedido['correo']); ?></p>
                        <p class="mb-2"><strong>Celular:</strong> <?php echo htmlspecialchars($pedido['celular']); ?></p>
                        <p class="mb-0"><strong>Dirección:</strong> <?php echo htmlspecialchars($pedido['direccion']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card info-card h-100">
                    <div class="card-header bg-info text-white">
                        <h6 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2"></i>Información del Pedido</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                        <p class="mb-2"><strong>Total:</strong> <span class="text-success fw-bold">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></span></p>
                        <p class="mb-2"><strong>N° Prendas:</strong> <span class="badge bg-secondary"><?php echo $pedido['num_prendas']; ?></span></p>
                        <p class="mb-0">
                            <strong>Estado Actual:</strong> 
                            <span class="badge bg-<?php echo $color_estado[$estado_actual]; ?> fs-6">
                                <?php echo ucfirst($estado_actual); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notas del Pedido -->
        <?php if (!empty($pedido['notas'])): ?>
        <div class="alert alert-warning">
            <strong><i class="bi bi-chat-left-text me-2"></i>Notas del Cliente:</strong>
            <div class="mt-1"><?php echo nl2br(htmlspecialchars($pedido['notas'])); ?></div>
        </div>
        <?php endif; ?>

        <!-- Alerta si el pedido está cancelado -->
        <?php if ($estado_actual === 'cancelado'): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Pedido Cancelado</strong> - El stock ha sido restaurado y este pedido no puede ser reactivado. 
            El cliente debe realizar un nuevo pedido si desea adquirir los productos.
        </div>
        <?php endif; ?>

        <!-- Productos del Pedido -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-bag-check me-2"></i>Productos del Pedido</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="50%">Producto</th>
                                <th width="15%" class="text-center">Precio Unit.</th>
                                <th width="15%" class="text-center">Cantidad</th>
                                <th width="20%" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items_pedido as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['nombre_producto']); ?></strong>
                                </td>
                                <td class="text-center">$<?php echo number_format($item['precio_unitario'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?php echo $item['cantidad']; ?></span>
                                </td>
                                <td class="text-end">
                                    <strong>$<?php echo number_format($item['subtotal'], 0, ',', '.'); ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-success">
                                <td colspan="3" class="text-end fw-bold fs-6">TOTAL DEL PEDIDO:</td>
                                <td class="text-end fw-bold fs-5 text-success">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Formulario para cambiar estado (deshabilitado si está cancelado) -->
        <?php if ($estado_actual !== 'cancelado'): ?>
        <form method="POST" class="mt-4">
            <input type="hidden" name="accion" value="actualizar_estado_pedido">
            <input type="hidden" name="pedido_id" value="<?php echo $pedido_id; ?>">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="fw-bold mb-0"><i class="bi bi-arrow-repeat me-2"></i>Actualizar Estado del Pedido</h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Seleccionar nuevo estado:</label>
                            <select class="form-select form-select-lg" name="estado" required>
                                <option value="pendiente" <?php echo $estado_actual === 'pendiente' ? 'selected' : ''; ?>>🟡 Pendiente</option>
                                <option value="confirmado" <?php echo $estado_actual === 'confirmado' ? 'selected' : ''; ?>>🔵 Confirmado</option>
                                <option value="enviado" <?php echo $estado_actual === 'enviado' ? 'selected' : ''; ?>>🚚 Enviado</option>
                                <option value="entregado" <?php echo $estado_actual === 'entregado' ? 'selected' : ''; ?>>✅ Entregado</option>
                                <option value="cancelado" <?php echo $estado_actual === 'cancelado' ? 'selected' : ''; ?>>❌ Cancelado (DEFINITIVO)</option>
                            </select>
                            <div class="form-text texto-advertencia">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Al cancelar el pedido, el stock será restaurado automáticamente y no podrá ser reactivado.
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-check-circle me-2"></i>Actualizar Estado
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php else: ?>
        <div class="card estado-bloqueado">
            <div class="card-header bg-secondary text-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-lock me-2"></i>Estado del Pedido</h6>
            </div>
            <div class="card-body text-center py-5">
                <i class="bi bi-ban" style="font-size: 3rem; color: #6c757d;"></i>
                <h5 class="mt-3 text-muted">Pedido Cancelado</h5>
                <p class="text-muted">Este pedido ha sido cancelado y no puede ser modificado.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Botones de acción adicionales -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="admin_dashboard.php?tab=pedidos" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver a Pedidos
                    </a>
                    <div>
                        <a href="admin_dashboard.php?tab=pedidos&eliminar_pedido=<?php echo $pedido_id; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('¿Está seguro de eliminar este pedido?\n\n⚠️ Esta acción restaurará el stock y eliminará el pedido permanentemente.\n\nPedido #<?php echo $pedido_id; ?>');">
                            <i class="bi bi-trash me-2"></i>Eliminar Pedido
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>