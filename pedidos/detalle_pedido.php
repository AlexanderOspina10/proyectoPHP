<?php
session_start();

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php#Iniciosesion");
    exit();
}

// Verificar que se pasó un ID de pedido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ver_pedidos.php");
    exit();
}

// Conexión a la base de datos
$host = "localhost";
$user = "root";
$pass = "3135497455Jj";
$db   = "fashion_store";

$con = new mysqli($host, $user, $pass, $db);

if ($con->connect_errno) {
    die("Error en la conexión: " . $con->connect_error);
}

$con->set_charset("utf8mb4");

$pedido_id = intval($_GET['id']);
$usuario_id = $_SESSION['id'];

// Obtener información del pedido (solo si pertenece al usuario)
$stmt = $con->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $pedido_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();
$stmt->close();

if (!$pedido) {
    $_SESSION['flash'] = "❌ Pedido no encontrado.";
    header("Location: ver_pedidos.php");
    exit();
}

// Obtener detalles de los productos
$stmt = $con->prepare("SELECT * FROM pedido_detalles WHERE pedido_id = ?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();
$detalles = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Detalle del Pedido #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?> - Fashion Store</title>
  
  <link href="../assets/img/logo.png" rel="icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/main.css" rel="stylesheet">
  
  <style>
    /* Estilos del header */
    .header, 
    .header .topbar {
      background-color: black !important; 
      --default-color: #ffffff !important;
      --heading-color: #ffffff !important;
      --nav-color: #ffffff !important; 
      --contrast-color: #ffffff !important;
    }

    .header .logo h1, 
    .header .navmenu a,
    .header .topbar .contact-info i span {
      color: white !important;
    }

    .header .navmenu li:hover>a,
    .header .navmenu .active {
      color: #cccccc !important; 
    }

    .header button.dropdown-toggle span {
      color: white !important;
    }

    .header .branding .container {
      justify-content: center !important; 
    }

    .header .branding .container .logo {
      margin-right: 30px; 
    }

    /* Estilos del body y main */
    body {
      background: #f5f7fa;
      padding-top: 140px;
      min-height: 100vh;
    }
    
    main {
      margin-top: 0;
      background-color: transparent;
    }
    
    main h1, main h2, main h3, main h4, main h5, main h6, main p, main div, main span, main label {
      color: inherit;
    }
    
    .detalle-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 30px 20px;
    }
    
    .detalle-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      overflow: hidden;
      margin-bottom: 30px;
    }
    
    .detalle-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px;
    }
    
    .detalle-header h2 {
      margin: 0 0 10px 0;
      font-size: 2rem;
      font-weight: 700;
    }
    
    .estado-badge-large {
      display: inline-block;
      padding: 10px 20px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1rem;
      margin-top: 10px;
    }
    
    .detalle-body {
      padding: 30px;
    }
    
    .section-box {
      background: #f8f9fa;
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 25px;
    }
    
    .section-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: #333;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 3px solid #667eea;
    }
    
    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #dee2e6;
    }
    
    .info-row:last-child {
      border-bottom: none;
    }
    
    .info-label {
      font-weight: 600;
      color: #6c757d;
    }
    
    .info-value {
      font-weight: 600;
      color: #333;
    }
    
    .producto-detalle-item {
      background: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 15px;
      border: 2px solid #e9ecef;
      transition: all 0.3s;
    }
    
    .producto-detalle-item:hover {
      border-color: #667eea;
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
    }
    
    .producto-detalle-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }
    
    .producto-nombre-detalle {
      font-size: 1.2rem;
      font-weight: 700;
      color: #333;
    }
    
    .producto-precio-detalle {
      font-size: 1.3rem;
      font-weight: 700;
      color: #667eea;
    }
    
    .producto-info-detalle {
      color: #6c757d;
      font-size: 0.95rem;
    }
    
    .resumen-total-box {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 15px;
      padding: 25px;
      margin-top: 25px;
    }
    
    .resumen-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      font-size: 1.1rem;
    }
    
    .resumen-total {
      font-size: 2rem;
      font-weight: 700;
      padding-top: 15px;
      border-top: 2px solid rgba(255,255,255,0.3);
      margin-top: 15px;
    }
    
    .timeline {
      position: relative;
      padding-left: 30px;
    }
    
    .timeline::before {
      content: '';
      position: absolute;
      left: 8px;
      top: 0;
      bottom: 0;
      width: 3px;
      background: #dee2e6;
    }
    
    .timeline-item {
      position: relative;
      margin-bottom: 20px;
    }
    
    .timeline-item::before {
      content: '';
      position: absolute;
      left: -26px;
      top: 5px;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      background: white;
      border: 3px solid #667eea;
      z-index: 1;
    }
    
    .timeline-item.active::before {
      background: #667eea;
    }
    
    .timeline-content {
      background: white;
      padding: 15px;
      border-radius: 10px;
      border: 2px solid #e9ecef;
    }
    
    .timeline-title {
      font-weight: 700;
      color: #333;
      margin-bottom: 5px;
    }
    
    .timeline-date {
      font-size: 0.85rem;
      color: #6c757d;
    }
    
    .btn-back {
      background: #6c757d;
      color: white;
      padding: 12px 30px;
      border-radius: 50px;
      border: none;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s;
    }
    
    .btn-back:hover {
      background: #5a6268;
      color: white;
      transform: translateY(-2px);
    }
    
    .btn-print {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      padding: 12px 30px;
      border-radius: 50px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .btn-print:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    
    @media print {
      body {
        padding-top: 0;
      }
      header, footer, .btn-back, .btn-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

<header id="header" class="header fixed-top">
  <div class="topbar d-flex align-items-center">
    <div class="container d-flex justify-content-end justify-content-md-between">
      <div class="contact-info d-flex align-items-center">
        <i class="bi bi-phone d-flex align-items-center d-none d-lg-block"><span>+57 3113235370</span></i>
        <i class="bi bi-clock ms-4 d-none d-lg-flex align-items-center"><span>Lunes-Sábado 8:00 AM - 9:00 PM</span></i>
      </div>
    </div>
  </div>

  <div class="branding d-flex align-items-center">
    <div class="container position-relative d-flex align-items-center justify-content-between">
      <a href="../index.php" class="logo d-flex align-items-center">
        <h1 class="sitename">Fashion Store</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="../index.php">Inicio</a></li>
          <li><a href="../index.php#Catalogo">Catálogo</a></li>
          <li><a href="ver_pedidos.php">Mis Pedidos</a></li>
        </ul>
      </nav>
    </div>
  </div>
</header>

<main class="main">
  <div class="detalle-container">
    <div class="detalle-card">
      <div class="detalle-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h2>
              <i class="bi bi-receipt-cutoff me-2"></i>
              Pedido #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?>
            </h2>
            <p class="mb-0">
              <i class="bi bi-calendar3 me-1"></i>
              Realizado el <?php echo date('d/m/Y \a \l\a\s H:i', strtotime($pedido['creado_en'])); ?>
            </p>
          </div>
          <div class="mt-3 mt-md-0">
            <?php
            $estado_color = [
              'pendiente' => '#ffc107',
              'confirmado' => '#17a2b8',
              'enviado' => '#007bff',
              'entregado' => '#28a745',
              'cancelado' => '#dc3545'
            ];
            $color = $estado_color[$pedido['estado']] ?? '#6c757d';
            ?>
            <span class="estado-badge-large" style="background: <?php echo $color; ?>;">
              <i class="bi bi-circle-fill me-1" style="font-size: 0.7rem;"></i>
              <?php echo ucfirst($pedido['estado']); ?>
            </span>
          </div>
        </div>
      </div>

      <div class="detalle-body">
        <!-- Información del cliente -->
        <div class="section-box">
          <h3 class="section-title">
            <i class="bi bi-person-circle me-2"></i>Información del Cliente
          </h3>
          <div class="info-row">
            <span class="info-label"><i class="bi bi-person me-2"></i>Nombre:</span>
            <span class="info-value"><?php echo htmlspecialchars($pedido['nombre']); ?></span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="bi bi-envelope me-2"></i>Correo:</span>
            <span class="info-value"><?php echo htmlspecialchars($pedido['correo']); ?></span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="bi bi-phone me-2"></i>Celular:</span>
            <span class="info-value"><?php echo htmlspecialchars($pedido['celular']); ?></span>
          </div>
        </div>

        <!-- Información de entrega -->
        <div class="section-box">
          <h3 class="section-title">
            <i class="bi bi-truck me-2"></i>Información de Entrega
          </h3>
          <div class="info-row">
            <span class="info-label"><i class="bi bi-geo-alt me-2"></i>Dirección:</span>
            <span class="info-value"><?php echo htmlspecialchars($pedido['direccion']); ?></span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="bi bi-calendar-check me-2"></i>Fecha de Entrega:</span>
            <span class="info-value"><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></span>
          </div>
          <?php if (!empty($pedido['notas'])): ?>
          <div class="info-row">
            <span class="info-label"><i class="bi bi-sticky me-2"></i>Notas:</span>
            <span class="info-value"><?php echo htmlspecialchars($pedido['notas']); ?></span>
          </div>
          <?php endif; ?>
        </div>

        <!-- Productos del pedido -->
        <div class="section-box">
          <h3 class="section-title">
            <i class="bi bi-bag-check me-2"></i>Productos (<?php echo count($detalles); ?>)
          </h3>
          <?php foreach ($detalles as $detalle): ?>
          <div class="producto-detalle-item">
            <div class="producto-detalle-header">
              <div class="producto-nombre-detalle">
                <i class="bi bi-box-seam me-2"></i>
                <?php echo htmlspecialchars($detalle['nombre_producto']); ?>
              </div>
              <div class="producto-precio-detalle">
                $<?php echo number_format($detalle['subtotal'], 0, ',', '.'); ?>
              </div>
            </div>
            <div class="producto-info-detalle">
              <span class="me-3">
                <i class="bi bi-tag me-1"></i>
                Precio unitario: <strong>$<?php echo number_format($detalle['precio_unitario'], 0, ',', '.'); ?></strong>
              </span>
              <span>
                <i class="bi bi-x-circle me-1"></i>
                Cantidad: <strong><?php echo $detalle['cantidad']; ?></strong>
              </span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Resumen de totales -->
        <div class="resumen-total-box">
          <div class="resumen-row">
            <span><i class="bi bi-box me-2"></i>Total de Prendas:</span>
            <strong><?php echo $pedido['num_prendas']; ?> unidades</strong>
          </div>
          <div class="resumen-row">
            <span><i class="bi bi-tag me-2"></i>Subtotal:</span>
            <strong>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></strong>
          </div>
          <div class="resumen-row">
            <span><i class="bi bi-truck me-2"></i>Envío:</span>
            <strong>GRATIS</strong>
          </div>
          <div class="resumen-row resumen-total">
            <span>TOTAL PAGADO:</span>
            <strong>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></strong>
          </div>
        </div>

        <!-- Estado del pedido (Timeline) -->
        <div class="section-box">
          <h3 class="section-title">
            <i class="bi bi-clock-history me-2"></i>Estado del Pedido
          </h3>
          <div class="timeline">
            <div class="timeline-item <?php echo in_array($pedido['estado'], ['pendiente', 'confirmado', 'enviado', 'entregado']) ? 'active' : ''; ?>">
              <div class="timeline-content">
                <div class="timeline-title">Pedido Recibido</div>
                <div class="timeline-date"><?php echo date('d/m/Y H:i', strtotime($pedido['creado_en'])); ?></div>
              </div>
            </div>
            <div class="timeline-item <?php echo in_array($pedido['estado'], ['confirmado', 'enviado', 'entregado']) ? 'active' : ''; ?>">
              <div class="timeline-content">
                <div class="timeline-title">Pedido Confirmado</div>
                <div class="timeline-date">
                  <?php echo in_array($pedido['estado'], ['confirmado', 'enviado', 'entregado']) ? 'Confirmado' : 'Pendiente'; ?>
                </div>
              </div>
            </div>
            <div class="timeline-item <?php echo in_array($pedido['estado'], ['enviado', 'entregado']) ? 'active' : ''; ?>">
              <div class="timeline-content">
                <div class="timeline-title">En Camino</div>
                <div class="timeline-date">
                  <?php echo in_array($pedido['estado'], ['enviado', 'entregado']) ? 'En tránsito' : 'Pendiente'; ?>
                </div>
              </div>
            </div>
            <div class="timeline-item <?php echo $pedido['estado'] === 'entregado' ? 'active' : ''; ?>">
              <div class="timeline-content">
                <div class="timeline-title">Entregado</div>
                <div class="timeline-date">
                  <?php echo $pedido['estado'] === 'entregado' ? 'Completado' : 'Pendiente'; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex justify-content-between flex-wrap gap-3 mt-4">
          <a href="ver_pedidos.php" class="btn-back">
            <i class="bi bi-arrow-left me-2"></i>Volver a Mis Pedidos
          </a>
          <button onclick="window.print()" class="btn-print">
            <i class="bi bi-printer me-2"></i>Imprimir Pedido
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php $con->close(); ?>