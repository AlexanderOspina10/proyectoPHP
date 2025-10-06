<?php
session_start();
// Asegúrate de que la ruta a tu conexión y lógica de sesión sean correctas
require '../Conexion/conexion_new.php'; 

// 1. Verificar sesión y obtener ID de usuario
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php'); // Redirige al inicio si no hay sesión
    exit;
}
$uid = intval($_SESSION['usuario_id']);

// Inicializar variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// 2. Lógica para PROCESAR el formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear los datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($direccion)) {
        $mensaje = '❌ Todos los campos obligatorios deben ser llenados.';
        $tipo_mensaje = 'danger';
    } else if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = '❌ El formato del correo electrónico no es válido.';
        $tipo_mensaje = 'danger';
    } else {
        // Preparar la consulta SQL para actualizar
        $stmt_update = $con->prepare("UPDATE usuario SET nombre = ?, apellido = ?, correo = ?, telefono = ?, direccion = ? WHERE id = ?");
        
        if ($stmt_update) {
            $stmt_update->bind_param('sssssi', $nombre, $apellido, $correo, $telefono, $direccion, $uid);
            
            if ($stmt_update->execute()) {
                // Actualizar la sesión si es necesario
                $_SESSION['usuario_nombre'] = $nombre; 
                
                $mensaje = '✅ Tu perfil ha sido actualizado exitosamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = '❌ Error al actualizar el perfil: ' . $stmt_update->error;
                $tipo_mensaje = 'danger';
            }
            $stmt_update->close();
        } else {
            $mensaje = '❌ Error al preparar la consulta de actualización.';
            $tipo_mensaje = 'danger';
        }
    }
}

// 3. Lógica para OBTENER los datos actuales (GET o después de POST)
$stmt = $con->prepare("SELECT id, nombre, apellido, correo, perfil, telefono, direccion FROM usuario WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: perfil.php'); // Redirige si el usuario no existe
    exit;
}

// Para el snippet del header, aseguramos que la sesión tenga estos datos
if (!isset($_SESSION['usuario_nombre'])) {
    $_SESSION['usuario_nombre'] = $user['nombre'];
    $_SESSION['id'] = $user['id']; 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar Perfil - Fashion Store</title>
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
    /* Estilos copiados y adaptados de perfil.php y el ejemplo de pedidos */
    :root {
      --color-primary-light: #667eea;
      --color-primary-dark: #764ba2;
      --color-accent: #6f42c1; /* Morado del avatar en header */
    }

    body {
      font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-primary-dark) 100%);
      min-height: 100vh;
      padding-top: 140px;
      color: #212529;
    }
    
    /* Estilos del Header para uniformidad (fondo negro) */
    .header, .header .topbar {
      background-color: black !important; 
      --default-color: #ffffff !important;
      --heading-color: #ffffff !important;
      --nav-color: #ffffff !important; 
      --contrast-color: #ffffff !important;
    }
    .header .logo h1, .header .navmenu a, .header .topbar .contact-info i span {
      color: white !important;
    }
    .header button.dropdown-toggle span {
      color: white !important;
    }

    /* Contenedor y Tarjeta (similar a perfil.php) */
    .edit-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
    }
    .edit-card {
      background: white;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,0.28);
      animation: cardUp .6s ease;
      padding: 30px; /* Padding interno */
    }
    @keyframes cardUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Estilo del Formulario */
    .form-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      border-bottom: 2px solid #f1f1f1;
      padding-bottom: 15px;
    }
    .form-header h2 {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--color-primary-dark);
      margin: 0;
    }
    
    .info-group {
      margin-bottom: 20px;
    }
    
    .info-group label {
      font-weight: 600;
      color: var(--color-primary-light);
      margin-bottom: 8px;
      display: block;
      font-size: 0.95rem;
    }
    
    .info-group input,
    .info-group textarea {
      width: 100%;
      padding: 12px;
      border: 2px solid #dee2e6;
      border-radius: 12px;
      font-size: 1rem;
      transition: border-color 0.3s, box-shadow 0.3s;
    }
    
    .info-group input:focus,
    .info-group textarea:focus {
      outline: none;
      border-color: var(--color-accent);
      box-shadow: 0 0 0 4px rgba(111,66,193,0.1);
    }
    
    /* Botón de Guardar */
    .btn-save {
      background: var(--color-accent); /* Morado principal */
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 50px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .btn-save:hover {
      background: #764ba2;
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(111,66,193,0.4);
    }

    /* Botón de Cancelar */
    .btn-cancel {
      background: #f1f1f1;
      color: #6c757d;
      padding: 12px 30px;
      border: 1px solid #ddd;
      border-radius: 50px;
      font-size: 1.1rem;
      font-weight: 600;
      transition: background 0.2s;
      text-decoration: none;
    }
    .btn-cancel:hover {
        background: #e9ecef;
    }
    
    /* Mensajes de Alerta/Flash */
    .alert-custom {
      padding: 15px 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      animation: slideDown 0.5s ease;
    }
    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
    .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }

    /* Responsive */
    @media (max-width: 768px) {
        .form-header { flex-direction: column; align-items: flex-start; }
        .form-header a { margin-top: 10px; }
        .btn-save, .btn-cancel { width: 100%; margin-bottom: 10px; }
        .btn-cancel { margin-left: 0 !important; }
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
  <div class="edit-container" data-aos="fade-up">
    <div class="edit-card">
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-custom">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="form-header">
            <h2><i class="bi bi-pencil-square me-2"></i>Editar Información Personal</h2>
            <a href="perfil.php" class="btn btn-cancel">
                <i class="bi bi-arrow-left me-1"></i> Volver a Mi Perfil
            </a>
        </div>
        
        <form method="POST" action="editar_perfil.php">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-group">
                        <label for="nombre"><i class="bi bi-person me-1"></i>Nombre *</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?= htmlspecialchars($user['nombre']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-group">
                        <label for="apellido"><i class="bi bi-person me-1"></i>Apellido *</label>
                        <input type="text" id="apellido" name="apellido" 
                               value="<?= htmlspecialchars($user['apellido'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="info-group">
                        <label for="correo"><i class="bi bi-envelope me-1"></i>Correo Electrónico *</label>
                        <input type="email" id="correo" name="correo" 
                               value="<?= htmlspecialchars($user['correo']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-group">
                        <label for="telefono"><i class="bi bi-phone me-1"></i>Teléfono *</label>
                        <input type="tel" id="telefono" name="telefono" 
                               value="<?= htmlspecialchars($user['telefono'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <div class="info-group">
                <label for="direccion"><i class="bi bi-geo-alt me-1"></i>Dirección *</label>
                <textarea id="direccion" name="direccion" rows="3" required
                          placeholder="Tu dirección de entrega completa"><?= htmlspecialchars($user['direccion'] ?? ''); ?></textarea>
            </div>
            
            <div class="text-end pt-3">
                <button type="submit" class="btn-save">
                    <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                </button>
                <a href="perfil.php" class="btn-cancel ms-3 d-none d-lg-inline-block">
                    Cancelar
                </a>
            </div>
        </form>
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