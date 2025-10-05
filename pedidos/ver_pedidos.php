<?php
session_start();

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php#Iniciosesion");
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

$usuario_id = $_SESSION['id'];

// Obtener pedidos del usuario
$stmt = $con->prepare("SELECT id, nombre, correo, celular, direccion, fecha_pedido, total, num_prendas, estado, notas, creado_en FROM pedidos WHERE usuario_id = ? ORDER BY creado_en DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$pedidos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Mis Pedidos - Fashion Store</title>
  
  <link href="../assets/img/logo.png" rel="icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/aos/aos.css" rel="stylesheet">
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
    
    main h1, main h2, main h3, main h4, main h5, main p, main div, main span, main label {
      color: inherit;
    }
    
    .pedidos-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 30px 20px;
    }
    
    .page-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 40px;
      border-radius: 20px;
      margin-bottom: 30px;
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .page-header h1 {
      margin: 0;
      font-size: 2.5rem;
      font-weight: 700;
    }
    
    .pedido-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      margin-bottom: 20px;
      overflow: hidden;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .pedido-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .pedido-header {
      background: linear-gradient(to right, #f8f9fa, #e9ecef);
      padding: 20px;
      border-bottom: 3px solid #667eea;
    }
    
    .pedido-numero {
      font-size: 1.3rem;
      font-weight: 700;
      color: #333;
    }
    
    .pedido-fecha {
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .pedido-body {
      padding: 25px;
    }
    
    .pedido-info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }
    
    .info-box {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 10px;
      border-left: 4px solid #667eea;
    }
    
    .info-box-label {
      font-size: 0.85rem;
      color: #6c757d;
      font-weight: 600;
      text-transform: uppercase;
      margin-bottom: 5px;
    }
    
    .info-box-value {
      font-size: 1.1rem;
      color: #333;
      font-weight: 600;
    }
    
    .estado-badge {
      padding: 8px 16px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.9rem;
      display: inline-block;
    }
    
    .estado-pendiente {
      background: #fff3cd;
      color: #856404;
    }
    
    .estado-confirmado {
      background: #d1ecf1;
      color: #0c5460;
    }
    
    .estado-enviado {
      background: #cce5ff;
      color: #004085;
    }
    
    .estado-entregado {
      background: #d4edda;
      color: #155724;
    }
    
    .estado-cancelado {
      background: #f8d7da;
      color: #721c24;
    }
    
    .btn-ver-detalles {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 10px 25px;
      border-radius: 50px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-ver-detalles:hover {
      transform: translateY(-2px);
      color: white;
    }
    
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    
    .empty-state-icon {
      font-size: 5rem;
      color: #dee2e6;
      margin-bottom: 20px;
    }
    
    .productos-preview {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 10px;
      margin-top: 15px;
    }
    
    .productos-preview-title {
      font-weight: 600;
      color: #495057;
      margin-bottom: 10px;
      font-size: 0.9rem;
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
        </ul>
      </nav>
      
 <!-- snippet: perfil en header -->
  <div style="position: absolute; top: 5px; right: 20px; z-index: 1000;">
  <?php if (isset($_SESSION['id'])): ?>
      <div class="dropdown">
          <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" id="perfilMenu" data-bs-toggle="dropdown" aria-expanded="false">
              <div style="background:#6f42c1; color:#fff; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; margin-right:8px;">
                  <?= strtoupper(htmlspecialchars(substr($_SESSION['usuario_nombre'], 0, 1))); ?>
              </div>
              <span style="font-weight:600; color:black !important;"><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="perfilMenu">
              <li><a class="dropdown-item" href="/FashionStore/menuusuario/perfil.php"><i class="bi bi-person"></i> Ver perfil</a></li>
              <li><a class="dropdown-item" href="/FashionStore/pedidos/ver_pedidos.php"><i class="bi bi-list-ul"></i> Mis pedidos</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/FashionStore/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
          </ul>
      </div>
  <?php else: ?>
      <a href="#Iniciosesion" class="btn btn-success">Iniciar sesión</a>
  <?php endif; ?>
  </div>
</header>

<main class="main">
  <div class="pedidos-container">
    <div class="page-header" data-aos="fade-down">
      <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
          <h1><i class="bi bi-bag-check me-3"></i>Mis Pedidos</h1>
          <p class="mb-0">Historial completo de tus compras</p>
        </div>
        <a href="../index.php#Catalogo" class="btn btn-light btn-lg mt-3 mt-md-0">
          <i class="bi bi-plus-circle me-2"></i>Realizar Nuevo Pedido
        </a>
      </div>
    </div>

    <?php if (empty($pedidos)): ?>
      <div class="empty-state" data-aos="fade-up">
        <div class="empty-state-icon">
          <i class="bi bi-inbox"></i>
        </div>
        <h3>No tienes pedidos aún</h3>
        <p class="text-muted">¡Explora nuestro catálogo y realiza tu primera compra!</p>
        <a href="../index.php#Catalogo" class="btn-ver-detalles mt-3">
          <i class="bi bi-shop me-2"></i>Ir al Catálogo
        </a>
      </div>
    <?php else: ?>
      <?php foreach ($pedidos as $index => $pedido): 
        // Obtener detalles de productos del pedido
        $stmt_detalles = $con->prepare("SELECT nombre_producto, cantidad, precio_unitario FROM pedido_detalles WHERE pedido_id = ? LIMIT 3");
        $stmt_detalles->bind_param("i", $pedido['id']);
        $stmt_detalles->execute();
        $result_detalles = $stmt_detalles->get_result();
        $detalles = $result_detalles->fetch_all(MYSQLI_ASSOC);
        $stmt_detalles->close();
        
        $estado_class = 'estado-' . $pedido['estado'];
        $estado_texto = ucfirst($pedido['estado']);
      ?>
      <div class="pedido-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
        <div class="pedido-header">
          <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
              <div class="pedido-numero">
                <i class="bi bi-receipt me-2"></i>
                Pedido #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?>
              </div>
              <div class="pedido-fecha">
                <i class="bi bi-calendar3 me-1"></i>
                Realizado el <?php echo date('d/m/Y', strtotime($pedido['creado_en'])); ?>
              </div>
            </div>
            <span class="estado-badge <?php echo $estado_class; ?> mt-2 mt-md-0">
              <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i>
              <?php echo $estado_texto; ?>
            </span>
          </div>
        </div>

        <div class="pedido-body">
          <div class="pedido-info-grid">
            <div class="info-box">
              <div class="info-box-label">
                <i class="bi bi-box me-1"></i>Prendas
              </div>
              <div class="info-box-value"><?php echo $pedido['num_prendas']; ?> unidades</div>
            </div>

            <div class="info-box">
              <div class="info-box-label">
                <i class="bi bi-currency-dollar me-1"></i>Total
              </div>
              <div class="info-box-value text-success">
                $<?php echo number_format($pedido['total'], 0, ',', '.'); ?>
              </div>
            </div>

            <div class="info-box">
              <div class="info-box-label">
                <i class="bi bi-truck me-1"></i>Entrega
              </div>
              <div class="info-box-value">
                <?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?>
              </div>
            </div>

            <div class="info-box">
              <div class="info-box-label">
                <i class="bi bi-geo-alt me-1"></i>Dirección
              </div>
              <div class="info-box-value" style="font-size: 0.9rem;">
                <?php echo htmlspecialchars(substr($pedido['direccion'], 0, 30)) . (strlen($pedido['direccion']) > 30 ? '...' : ''); ?>
              </div>
            </div>
          </div>

          <?php if (!empty($detalles)): ?>
          <div class="productos-preview">
            <div class="productos-preview-title">
              <i class="bi bi-bag me-1"></i>Productos en este pedido:
            </div>
            <?php foreach ($detalles as $detalle): ?>
              <div class="d-flex justify-content-between align-items-center py-1">
                <span><?php echo htmlspecialchars($detalle['nombre_producto']); ?></span>
                <span class="text-muted">
                  <?php echo $detalle['cantidad']; ?>x $<?php echo number_format($detalle['precio_unitario'], 0, ',', '.'); ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <div class="text-end mt-3">
            <a href="detalle_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn-ver-detalles">
              <i class="bi bi-eye me-2"></i>Ver Detalles Completos
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<footer id="footer" class="footer dark-background mt-5">
  <div class="container">
    <div class="row gy-3">
      <div class="col-lg-3 col-md-6 d-flex">
        <i class="bi bi-geo-alt icon"></i>
        <div class="address">
          <h4>Dirección</h4>
          <p>Cl. 10 #72-104</p>
          <p>Medellín - Belén Miravalle</p>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 d-flex">
        <i class="bi bi-telephone icon"></i>
        <div>
          <h4>Contacto</h4>
          <p>
            <strong>Celular:</strong> <span>+57 3005712936</span><br>
            <strong>Correo:</strong> <span>Fashion31store@gmail.com</span>
          </p>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 d-flex">
        <i class="bi bi-clock icon"></i>
        <div>
          <h4>Horarios</h4>
          <p>
            <strong>Lunes a Sábado:</strong> <span>8AM - 9PM</span><br>
            <strong>Domingo:</strong> <span>Cerrado</span>
          </p>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <h4>Síguenos</h4>
        <div class="social-links d-flex">
          <a href="https://www.facebook.com/share/1BoTD7KUbg/" target="_blank"><i class="bi bi-facebook"></i></a>
          <a href="https://twitter.com/?lang=es" target="_blank"><i class="bi bi-twitter"></i></a>
          <a href="https://www.instagram.com/fashionstore_mme" target="_blank"><i class="bi bi-instagram"></i></a>
        </div>
      </div>
    </div>
  </div>
</footer>

<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
  <i class="bi bi-arrow-up-short"></i>
</a>

<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/aos/aos.js"></script>

<script>
AOS.init({
  duration: 800,
  once: true
});
</script>

</body>
</html>
<?php $con->close(); ?>