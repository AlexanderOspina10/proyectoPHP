<?php
session_start();
require '../Conexion/conexion_new.php';
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
$uid = intval($_SESSION['usuario_id']);
$stmt = $con->prepare("SELECT id, nombre, correo, perfil, telefono, direccion FROM usuario WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Para el snippet del header, aseguramos que la sesión tenga estos datos
// En un caso real, esto se manejaría en el login. Aquí lo simulamos si faltan:
if (!isset($_SESSION['usuario_nombre']) && $user) {
    $_SESSION['usuario_nombre'] = $user['nombre'];
    $_SESSION['id'] = $user['id']; // Usando 'id' para el check en el header snippet
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Mi Perfil - Fashion Store</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../assets/img/logo.png" rel="icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="/FashionStore/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="/FashionStore/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="/FashionStore/assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="/FashionStore/assets/css/main.css" rel="stylesheet">
  <style>
    /* VARIABLES DE COLOR (A partir de tu gradiente) */
    :root {
      --color-primary-light: #667eea;
      --color-primary-dark: #764ba2;
      --color-accent: #6f42c1; /* Morado del avatar en header */
    }
    
    /* Mantenemos el fondo de la página */
    body {
      font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-primary-dark) 100%);
      min-height: 100vh;
      padding-top: 140px;
      color: #212529;
    }
    
    /* Ajustes al Header para uniformidad con el ejemplo de pedidos */
    .header, 
    .header .topbar {
      /* Puedes eliminar esto si ya está en main.css o si quieres el header en negro como el ejemplo de pedidos. 
      Si quieres el header transparente o blanco, déjalo como estaba.
      Para que coincida con el ejemplo de 'Finalizar Pedido', deberías usar el CSS de ese ejemplo: */
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
    .header button.dropdown-toggle span {
      color: white !important;
    }
    .header .branding .container {
      justify-content: space-between !important; /* Volver a la original para perfil */
    }
    
    /* Card de perfil */
    .perfil-container {
      max-width: 980px;
      margin: 0 auto;
      padding: 20px;
    }
    .perfil-card {
      background: white;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,0.28);
      display: flex;
      gap: 0;
      animation: cardUp .6s ease; /* Ajuste la duración para suavidad */
    }
    @keyframes cardUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Columna lateral */
    .perfil-aside {
      width: 320px;
      padding: 30px;
      /* Mismo gradiente principal */
      background: linear-gradient(180deg, var(--color-primary-light), var(--color-primary-dark));
      color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: start;
      gap: 20px; /* Más espaciado */
      text-align: center;
    }
    .perfil-avatar {
      width: 120px; /* Ligeramente más grande */
      height: 120px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 50px; /* Tamaño de fuente ajustado */
      font-weight: 700;
      background: rgba(255,255,255,0.15); /* Fondo más visible */
      border: 4px solid rgba(255,255,255,0.25); /* Borde más pronunciado */
      color: #fff;
      text-transform: uppercase;
      margin-bottom: 5px;
    }
    .perfil-nombre {
      font-size: 1.5rem; /* Más grande */
      font-weight: 700;
      margin: 0;
    }
    .perfil-rol {
      font-size: 1rem;
      opacity: 0.85; /* Menos opaco */
    }
    .perfil-actions {
      width: 100%;
      margin-top: 20px; /* Más margen superior */
      display: flex;
      gap: 15px; /* Más espacio entre botones */
      flex-direction: column;
    }
    
    /* Estilos de botones: Ajustados para uniformidad */
    .btn-perfil {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 18px;
      border-radius: 50px;
      font-weight: 600;
      border: none;
      transition: all 0.2s ease;
      text-decoration: none;
    }
    
    /* Botón Principal (Editar) - Color sólido o gradiente más suave */
    .btn-primary-perfil {
      background: #ffffff;
      color: var(--color-accent); /* Morado fuerte */
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .btn-primary-perfil:hover { 
      transform: translateY(-2px); 
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    
    /* Botón Secundario (Cambiar Contraseña, etc.) - Contorno */
    .btn-outline-perfil {
      background: transparent;
      border: 2px solid rgba(255,255,255,0.4);
      color: white;
    }
    .btn-outline-perfil:hover {
      background: rgba(255,255,255,0.1);
      border-color: white;
    }
    
    /* Botón de Logout (en el aside, si se mantiene) */
    .btn-logout-aside {
      color: #ffdddd; /* Rojo suave en fondo oscuro */
      border: 2px solid #ffdddd;
      background: transparent;
    }
    .btn-logout-aside:hover {
      background: rgba(255,0,0,0.2);
      border-color: #ffffff;
      color: #ffffff;
    }
    
    /* Columna principal */
    .perfil-main {
      flex: 1;
      padding: 35px; /* Más padding */
    }
    .perfil-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start; /* Alineación al inicio */
      gap: 15px;
      margin-bottom: 20px; /* Más margen inferior */
    }
    .perfil-header h2 {
      margin: 0;
      font-size: 1.8rem; /* Más grande */
      font-weight: 700;
      color: #343a40;
    }
    .perfil-sub {
      color: #6c757d;
      font-size: 1rem;
      margin-top: 6px;
    }
    .datos-grid {
      margin-top: 25px; /* Más margen superior */
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px; /* Más espacio entre datos */
    }
    .dato {
      background: #f8f9fa;
      border-radius: 15px; /* Más redondeado */
      padding: 16px;
      border: 1px solid #e9ecef;
    }
    .dato label {
      display:block;
      font-size: 0.9rem;
      color: var(--color-primary-dark); /* Color del gradiente */
      font-weight: 600;
      margin-bottom: 4px; /* Menos margen */
    }
    .dato .value {
      font-size: 1.1rem;
      color: #212529;
      word-break: break-word;
      font-weight: 500;
    }
    .full-width {
      grid-column: 1 / -1;
    }
    .acciones-row {
      display: flex;
      gap: 15px; /* Más espacio */
      margin-top: 30px; /* Más margen superior */
    }
    
    /* Botones de Acción - Uniformidad con botones del aside */
    /* Botón de Volver (en el header) */
    .btn-back-main {
      background: #f1f1f1;
      border: 1px solid #ddd;
      color: #495057;
      padding: 8px 15px;
      border-radius: 12px;
      font-weight: 600;
      transition: background 0.2s;
    }
    .btn-back-main:hover {
        background: #e9ecef;
    }
    
    /* Botón en Main (Editar) - Color de acento */
    .btn-edit-main {
      background: var(--color-accent);
      color: white;
      box-shadow: 0 5px 15px rgba(111,66,193,0.3);
    }
    .btn-edit-main:hover {
      background: #764ba2;
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(111,66,193,0.4);
    }
    
    /* Botón en Main (Pedidos) - Contorno primario */
    .btn-outline-main {
      background: transparent;
      border: 2px solid var(--color-primary-dark);
      color: var(--color-primary-dark);
    }
    .btn-outline-main:hover {
      background: var(--color-primary-dark);
      color: white;
    }
    
    /* Botón en Main (Logout) - Contorno rojo */
    .btn-logout-main {
      background: transparent;
      border: 2px solid #e74c3c;
      color: #e74c3c;
    }
    .btn-logout-main:hover {
      background: #e74c3c;
      color: white;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .perfil-card { flex-direction: column; }
      
      .perfil-aside { 
        width: 100%; 
        flex-direction: row; 
        padding: 20px; 
        justify-content: flex-start; 
        align-items: center; 
        gap: 15px;
      }
      .perfil-avatar { 
        width: 64px; 
        height: 64px; 
        font-size: 28px; 
        margin-bottom: 0;
      }
      .perfil-aside .perfil-text { 
        text-align: left; 
        flex-grow: 1; /* Permite que el texto ocupe espacio */
      }
      .perfil-aside .perfil-text p:first-child {
        font-size: 1.2rem;
      }
      .perfil-aside .perfil-text p:last-child {
        font-size: 0.9rem;
      }
      .perfil-actions {
        display: none; /* Ocultar botones de acción secundarios en aside para móviles */
      }
      
      .perfil-main { padding: 25px; }
      .perfil-header { flex-direction: column; align-items: stretch; }
      .perfil-header div:last-child { margin-top: 15px; } /* Espacio entre título y botón Volver */
      .datos-grid { grid-template-columns: 1fr; }
      .acciones-row { flex-direction: column; gap: 10px; }
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
</header>
<main class="main">
  <div class="perfil-container" data-aos="fade-up">
    <div class="perfil-card">
      <aside class="perfil-aside">
        <div class="perfil-avatar">
          <?= strtoupper(htmlspecialchars(substr($user['nombre'], 0, 1))); ?>
        </div>
        <div class="perfil-text"> <p class="perfil-nombre"><?= htmlspecialchars($user['nombre']); ?></p>
          <p class="perfil-rol"><?= htmlspecialchars($user['perfil']); ?></p>
        </div>
        <div class="perfil-actions" style="width:100%;">
          <a href="editar_perfil.php" class="btn-perfil btn-primary-perfil w-100">
            <i class="bi bi-pencil-square me-1"></i> Editar perfil
          </a>
          <a href="/FashionStore/logout.php" class="btn-perfil btn-logout-aside w-100 d-lg-none">
             <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
          </a>
        </div>
      </aside>
      <section class="perfil-main">
        <div class="perfil-header">
          <div>
            <h2>Mi Perfil</h2>
            <div class="perfil-sub">Actualiza tus datos personales y preferencias de cuenta.</div>
          </div>
          <div style="text-align:right;">
            <a href="../index.php" class="btn-back-main btn-perfil"> 
                <i class="bi bi-arrow-left me-1"></i> Volver a la Tienda
            </a>
          </div>
        </div>
        <div class="datos-grid">
          <div class="dato">
            <label><i class="bi bi-person me-1"></i>Nombre</label>
            <div class="value"><?= htmlspecialchars($user['nombre']); ?></div>
          </div>
          <div class="dato">
            <label><i class="bi bi-envelope me-1"></i>Correo Electrónico</label>
            <div class="value"><?= htmlspecialchars($user['correo']); ?></div>
          </div>
          <div class="dato">
            <label><i class="bi bi-phone me-1"></i>Teléfono</label>
            <div class="value"><?= htmlspecialchars($user['telefono'] ?? '—'); ?></div>
          </div>
          <div class="dato">
            <label><i class="bi bi-briefcase me-1"></i>Perfil de Usuario</label>
            <div class="value"><?= htmlspecialchars($user['perfil']); ?></div>
          </div>
          <div class="dato full-width">
            <label><i class="bi bi-geo-alt me-1"></i>Dirección</label>
            <div class="value"><?= nl2br(htmlspecialchars($user['direccion'] ?? '—')); ?></div>
          </div>
        </div>
        <div class="acciones-row">
          <a href="editar_perfil.php" class="btn-perfil btn-edit-main"><i class="bi bi-pencil-square me-1"></i> Editar Datos</a>
          <a href="/FashionStore/pedidos/ver_pedidos.php" class="btn-perfil btn-outline-main"><i class="bi bi-list-ul me-1"></i> Mis Pedidos</a>
          <a href="/FashionStore/logout.php" class="btn-perfil btn-logout-main"><i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión</a>
        </div>
      </section>
    </div>
  </div>
</main>
<script src="/FashionStore/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/FashionStore/assets/vendor/aos/aos.js"></script>
<script>
  AOS.init({ duration: 700, once: true });
</script>
</body>
</html>
<?php $con->close(); ?>