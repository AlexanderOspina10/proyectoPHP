<?php
session_start();

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php#Iniciosesion");
    exit();
}

// Verificar que hay datos de pedido en sesión
if (!isset($_SESSION['pedido']) || empty($_SESSION['pedido']['items'])) {
    $_SESSION['flash'] = "❌ No hay productos en el pedido.";
    header("Location: ../index.php#Catalogo");
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

// Obtener datos del usuario
$usuario_id = $_SESSION['id'];
$stmt = $con->prepare("SELECT nombre, apellido, correo, telefono, direccion FROM usuario WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

$items_pedido = $_SESSION['pedido']['items'];
$total_pedido = $_SESSION['pedido']['total'];
$num_prendas = array_sum(array_column($items_pedido, 'cantidad'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Finalizar Pedido - Fashion Store</title>
  
  <!-- Favicons -->
  <link href="../assets/img/logo.png" rel="icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/aos/aos.css" rel="stylesheet">

  <!-- Main CSS File -->
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
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding-top: 140px;
    }
    
    main {
      margin-top: 0;
      background-color: transparent;
    }
    
    main h1, main h2, main h3, main h4, main h5, main p, main div, main span, main label {
      color: inherit;
    }
    
    .pedido-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .pedido-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      overflow: hidden;
      animation: slideUp 0.6s ease;
    }
    
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .pedido-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px;
      text-align: center;
    }
    
    .pedido-header h2 {
      margin: 0;
      font-size: 2rem;
      font-weight: 700;
    }
    
    .pedido-body {
      padding: 30px;
    }
    
    .section-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 3px solid #667eea;
    }
    
    .producto-item {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 15px;
      transition: transform 0.2s;
    }
    
    .producto-item:hover {
      transform: translateX(5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .producto-img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 10px;
      border: 2px solid #dee2e6;
    }
    
    .producto-info {
      flex: 1;
    }
    
    .producto-nombre {
      font-weight: 600;
      font-size: 1.1rem;
      color: #333;
      margin-bottom: 5px;
    }
    
    .producto-detalles {
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .producto-precio {
      font-size: 1.2rem;
      font-weight: 700;
      color: #667eea;
    }
    
    .info-group {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
    }
    
    .info-group label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 8px;
      display: block;
    }
    
    .info-group input,
    .info-group textarea {
      width: 100%;
      padding: 12px;
      border: 2px solid #dee2e6;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }
    
    .info-group input:focus,
    .info-group textarea:focus {
      outline: none;
      border-color: #667eea;
    }
    
    .resumen-total {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 25px;
      border-radius: 12px;
      margin-top: 30px;
    }
    
    .resumen-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      font-size: 1.1rem;
    }
    
    .resumen-total-final {
      font-size: 1.8rem;
      font-weight: 700;
      padding-top: 15px;
      border-top: 2px solid rgba(255,255,255,0.3);
      margin-top: 15px;
    }
    
    .btn-confirmar {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      padding: 15px 40px;
      border: none;
      border-radius: 50px;
      font-size: 1.2rem;
      font-weight: 600;
      width: 100%;
      margin-top: 20px;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .btn-confirmar:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
    }
    
    .btn-cancelar {
      background: #6c757d;
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 50px;
      font-size: 1rem;
      font-weight: 500;
      width: 100%;
      margin-top: 10px;
      cursor: pointer;
      transition: background 0.2s;
    }
    
    .btn-cancelar:hover {
      background: #5a6268;
    }
    
    .alert-custom {
      padding: 15px 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      animation: slideDown 0.5s ease;
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
    </div>
  </div>
   <!-- snippet: perfil en header -->
  <div style="position: absolute; top: 12px; right: 20px; z-index: 1000;">
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
  <div class="pedido-container">
    <div class="pedido-card">
      <div class="pedido-header">
        <i class="bi bi-bag-check" style="font-size: 3rem; margin-bottom: 10px;"></i>
        <h2>Finalizar Pedido</h2>
        <p class="mb-0">Revisa los detalles de tu compra antes de confirmar</p>
      </div>

      <div class="pedido-body">
        <?php if (isset($_SESSION['error_pedido'])): ?>
          <div class="alert alert-danger alert-custom">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo $_SESSION['error_pedido']; unset($_SESSION['error_pedido']); ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="procesar_pedido.php" id="formPedido">
          <!-- Productos del pedido -->
          <div class="mb-4">
            <h3 class="section-title">
              <i class="bi bi-cart3 me-2"></i>Productos (<?php echo $num_prendas; ?> artículos)
            </h3>
            <?php foreach ($items_pedido as $id => $item): 
              $img = $item['imagen'] ?? '';
              $rutaWeb = '../menuadmin/assets/img/productos/' . $img;
              $thumb = $img ? $rutaWeb : '../assets/img/default-product.png';
            ?>
              <div class="producto-item">
                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="" class="producto-img">
                <div class="producto-info">
                  <div class="producto-nombre"><?php echo htmlspecialchars($item['nombre']); ?></div>
                  <div class="producto-detalles">
                    Cantidad: <strong><?php echo $item['cantidad']; ?></strong> × 
                    $<?php echo number_format($item['precio'], 0, ',', '.'); ?>
                  </div>
                </div>
                <div class="producto-precio">
                  $<?php echo number_format($item['precio'] * $item['cantidad'], 0, ',', '.'); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Información de entrega -->
          <div class="mb-4">
            <h3 class="section-title">
              <i class="bi bi-truck me-2"></i>Información de Entrega
            </h3>
            
            <div class="row">
              <div class="col-md-6">
                <div class="info-group">
                  <label for="nombre">
                    <i class="bi bi-person me-1"></i>Nombre Completo *
                  </label>
                  <input type="text" id="nombre" name="nombre" 
                         value="<?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>" 
                         required>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="info-group">
                  <label for="correo">
                    <i class="bi bi-envelope me-1"></i>Correo Electrónico *
                  </label>
                  <input type="email" id="correo" name="correo" 
                         value="<?php echo htmlspecialchars($usuario['correo']); ?>" 
                         required>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="info-group">
                  <label for="celular">
                    <i class="bi bi-phone me-1"></i>Celular *
                  </label>
                  <input type="tel" id="celular" name="celular" 
                         value="<?php echo htmlspecialchars($usuario['telefono']); ?>" 
                         required pattern="[0-9]{10}" 
                         placeholder="3001234567">
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="info-group">
                  <label for="fecha_entrega">
                    <i class="bi bi-calendar me-1"></i>Fecha de Entrega Deseada *
                  </label>
                  <input type="date" id="fecha_entrega" name="fecha_entrega" 
                         min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                         value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" 
                         required>
                </div>
              </div>
            </div>

            <div class="info-group">
              <label for="direccion">
                <i class="bi bi-geo-alt me-1"></i>Dirección de Entrega *
              </label>
              <textarea id="direccion" name="direccion" rows="3" required 
                        placeholder="Ej: Calle 10 #72-104, Barrio Belén Miravalle"><?php echo htmlspecialchars($usuario['direccion']); ?></textarea>
            </div>

            <div class="info-group">
              <label for="notas">
                <i class="bi bi-sticky me-1"></i>Notas Adicionales (Opcional)
              </label>
              <textarea id="notas" name="notas" rows="3" 
                        placeholder="¿Alguna instrucción especial para la entrega?"></textarea>
            </div>
          </div>

          <!-- Resumen del pedido -->
          <div class="resumen-total">
            <div class="resumen-item">
              <span><i class="bi bi-box me-2"></i>Número de Prendas:</span>
              <strong><?php echo $num_prendas; ?></strong>
            </div>
            <div class="resumen-item">
              <span><i class="bi bi-tag me-2"></i>Subtotal:</span>
              <strong>$<?php echo number_format($total_pedido, 0, ',', '.'); ?></strong>
            </div>
            <div class="resumen-item">
              <span><i class="bi bi-truck me-2"></i>Envío:</span>
              <strong>GRATIS</strong>
            </div>
            <div class="resumen-item resumen-total-final">
              <span>TOTAL:</span>
              <strong>$<?php echo number_format($total_pedido, 0, ',', '.'); ?></strong>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="mt-4">
            <button type="submit" class="btn-confirmar">
              <i class="bi bi-check-circle me-2"></i>Confirmar Pedido
            </button>
            <a href="../index.php#Catalogo" class="btn btn-cancelar text-decoration-none d-block text-center">
              <i class="bi bi-arrow-left me-2"></i>Volver al Catálogo
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>

<!-- Vendor JS Files -->
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/aos/aos.js"></script>

<script>
// Validación del formulario
document.getElementById('formPedido').addEventListener('submit', function(e) {
  const telefono = document.getElementById('celular').value;
  if (telefono.length !== 10 || !/^\d+$/.test(telefono)) {
    e.preventDefault();
    alert('Por favor ingresa un número de celular válido (10 dígitos)');
    return false;
  }
  
  // Confirmar antes de enviar
  if (!confirm('¿Estás seguro de confirmar este pedido?')) {
    e.preventDefault();
    return false;
  }
});

// Inicializar AOS
AOS.init({
  duration: 800,
  once: true
});
</script>

</body>
</html>
<?php $con->close(); ?>