<?php
session_start();

// Verificar que existe confirmación de pedido
if (!isset($_SESSION['pedido_exitoso'])) {
    header("Location: ../index.php");
    exit();
}

$pedido_info = $_SESSION['pedido_exitoso'];
unset($_SESSION['pedido_exitoso']); // Limpiar después de mostrar
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Pedido Confirmado - Fashion Store</title>
  
  <link href="../assets/img/logo.png" rel="icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/main.css" rel="stylesheet">
  
  <style>
    /* Estilos del header (por si se agrega en el futuro) */
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

    /* Estilos del body */
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    main {
      margin-top: 0;
      background-color: transparent;
    }
    
    .confirmacion-container {
      max-width: 600px;
      width: 100%;
    }
    
    .confirmacion-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      overflow: hidden;
      animation: zoomIn 0.6s ease;
    }
    
    @keyframes zoomIn {
      from {
        opacity: 0;
        transform: scale(0.8);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }
    
    .confirmacion-header {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      padding: 40px;
      text-align: center;
    }
    
    .confirmacion-icon {
      font-size: 5rem;
      margin-bottom: 20px;
      animation: checkmark 0.8s ease;
    }
    
    @keyframes checkmark {
      0% {
        transform: scale(0) rotate(0deg);
      }
      50% {
        transform: scale(1.2) rotate(180deg);
      }
      100% {
        transform: scale(1) rotate(360deg);
      }
    }
    
    .confirmacion-header h2 {
      margin: 0;
      font-size: 2rem;
      font-weight: 700;
    }
    
    .confirmacion-body {
      padding: 40px;
    }
    
    .info-pedido {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 25px;
    }
    
    .info-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #dee2e6;
    }
    
    .info-item:last-child {
      border-bottom: none;
    }
    
    .info-label {
      font-weight: 600;
      color: #495057;
    }
    
    .info-value {
      font-weight: 700;
      color: #667eea;
    }
    
    .btn-action {
      padding: 15px 30px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.1rem;
      border: none;
      cursor: pointer;
      transition: all 0.3s;
      width: 100%;
      margin-bottom: 10px;
    }
    
    .btn-primary-custom {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    
    .btn-primary-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }
    
    .btn-secondary-custom {
      background: white;
      color: #667eea;
      border: 2px solid #667eea;
    }
    
    .btn-secondary-custom:hover {
      background: #667eea;
      color: white;
    }
    
    .alert-info-custom {
      background: #e7f3ff;
      border-left: 4px solid #2196F3;
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<div class="confirmacion-container">
  <div class="confirmacion-card">
    <div class="confirmacion-header">
      <div class="confirmacion-icon">
        <i class="bi bi-check-circle-fill"></i>
      </div>
      <h2>¡Pedido Confirmado!</h2>
      <p class="mb-0">Tu orden ha sido procesada exitosamente</p>
    </div>

    <div class="confirmacion-body">
      <div class="alert-info-custom">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Importante:</strong> Recibirás un correo electrónico con los detalles de tu pedido.
      </div>

      <div class="info-pedido">
        <h5 class="mb-3 text-center">
          <i class="bi bi-receipt me-2"></i>Detalles del Pedido
        </h5>
        
        <div class="info-item">
          <span class="info-label">
            <i class="bi bi-hash me-1"></i>Número de Pedido:
          </span>
          <span class="info-value">#<?php echo str_pad($pedido_info['pedido_id'], 6, '0', STR_PAD_LEFT); ?></span>
        </div>
        
        <div class="info-item">
          <span class="info-label">
            <i class="bi bi-box me-1"></i>Total de Prendas:
          </span>
          <span class="info-value"><?php echo $pedido_info['num_prendas']; ?> unidades</span>
        </div>
        
        <div class="info-item">
          <span class="info-label">
            <i class="bi bi-currency-dollar me-1"></i>Total Pagado:
          </span>
          <span class="info-value" style="font-size: 1.3rem;">
            $<?php echo number_format($pedido_info['total'], 0, ',', '.'); ?>
          </span>
        </div>
        
        <div class="info-item">
          <span class="info-label">
            <i class="bi bi-calendar-check me-1"></i>Fecha de Entrega:
          </span>
          <span class="info-value">
            <?php 
              $fecha = new DateTime($pedido_info['fecha_entrega']);
              echo $fecha->format('d/m/Y'); 
            ?>
          </span>
        </div>
        
        <div class="info-item">
          <span class="info-label">
            <i class="bi bi-hourglass-split me-1"></i>Estado:
          </span>
          <span class="badge bg-warning text-dark" style="font-size: 1rem; padding: 8px 15px;">
            Pendiente
          </span>
        </div>
      </div>

      <div class="text-center mb-3">
        <p class="text-muted">
          <i class="bi bi-truck me-2"></i>
          Puedes hacer seguimiento de tu pedido en cualquier momento
        </p>
      </div>

      <div class="d-grid gap-2">
        <a href="ver_pedidos.php" class="btn btn-action btn-primary-custom text-decoration-none">
          <i class="bi bi-list-ul me-2"></i>Ver Mis Pedidos
        </a>
        <a href="../index.php#Catalogo" class="btn btn-action btn-secondary-custom text-decoration-none">
          <i class="bi bi-arrow-left me-2"></i>Seguir Comprando
        </a>
        <a href="../index.php" class="btn btn-link text-muted text-decoration-none">
          <i class="bi bi-house me-2"></i>Volver al Inicio
        </a>
      </div>
    </div>
  </div>
</div>

<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
// Confetti animation (opcional - efecto visual)
window.addEventListener('load', function() {
  // Animación de celebración simple con el icono
  const icon = document.querySelector('.confirmacion-icon');
  setTimeout(() => {
    icon.style.transform = 'scale(1.1)';
    setTimeout(() => {
      icon.style.transform = 'scale(1)';
    }, 200);
  }, 600);
});
</script>

</body>
</html>