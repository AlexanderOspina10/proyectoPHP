<?php
session_start();

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Finalizar proceso de pedido</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="../assets/img/logo.png" rel="icon">
  <link href="../assets/img/logo.png">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Satisfy:wght@400&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="../assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="../assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="../assets/css/main.css" rel="stylesheet">
  <link href="formulariopedido.css" rel="stylesheet">  
  
</head>
<body class="index-page">
            <!-- snippet: perfil en header -->
        <div style="position: absolute; top: 12px; right: 20px; z-index: 1000;">
        <?php if (isset($_SESSION['id'])): ?>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" id="perfilMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <div style="background:#6f42c1; color:#fff; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; margin-right:8px;">
                        <?= strtoupper(htmlspecialchars(substr($_SESSION['usuario_nombre'], 0, 1))); ?>
                    </div>
                    <span style="font-weight:600; color:#333;"><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="perfilMenu">
                    <li><a class="dropdown-item" href="menuusuario/perfil.php"><i class="bi bi-person"></i> Ver perfil</a></li>
                    <li><a class="dropdown-item" href="pedidos/ver_pedidos.php"><i class="bi bi-list-ul"></i> Mis pedidos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        <?php else: ?>
            <a href="#Iniciosesion" class="btn btn-success">Iniciar sesión</a>
        <?php endif; ?>
        </div>

        <header id="header" class="header fixed-top scrolled">
            <div class="topbar d-flex align-items-center">
            <div class="container d-flex justify-content-end justify-content-md-between">
                <div class="contact-info d-flex align-items-center">
                <i class="bi bi-phone d-flex align-items-center d-none d-lg-block"><span>+57 3113235370</span></i>
                <i class="bi bi-clock ms-4 d-none d-lg-flex align-items-center"><span>Lunes-Sabado 8:00 AM - 21:00 PM</span></i>
                </div>
            </div>
            </div>

            <div class="branding d-flex align-items-cente">
            <div class="container position-relative d-flex align-items-center justify-content-between">
                <a href="../index.php" class="logo d-flex align-items-center">
                <h1 class="sitename">Fashion Store</h1>
                </a>

                <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="../index.php" class="active">Inicio</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
                </nav>
            </div>
            </div>
        </header>
        <div id="notificationToast" class="custom-toast" style="display: none;">
            <div class="toast-header">
                <strong class="me-auto">Fashion Store</strong>
                <button type="button" class="btn-close" onclick="hideNotification()"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                </div>
        </div>

        <main class="main">
            <div style="height: 1000px; background-color: #f8f8f8; padding-top: 200px;">
                <h1>Contenido de la Página</h1>
                <p>Si añades mucho contenido aquí, el footer debería bajar.</p>
            </div>
        </main>


        <footer id="footer" class="footer dark-background">
            <div class="container">
            <div class="row gy-3">
                <div class="col-lg-3 col-md-6 d-flex">
                <i class="bi bi-geo-alt icon"></i>
                <div class="address">
                    <h4>Direccion</h4>
                    <p>Cl. 10 ##72-104</p>
                    <p>Medellin-Belen miravalle</p>
                    <p></p>
                </div>

                </div>

                <div class="col-lg-3 col-md-6 d-flex">
                <i class="bi bi-telephone icon"></i>
                <div>
                    <h4>Contacto </h4>
                    <p>
                    <strong>Celular:</strong> <span>+57 3005712936</span><br>
                    <strong>Correo:</strong><span>Fashion31store@gmail.com</span><br>
                    </p>
                </div>
                </div>

                <div class="col-lg-3 col-md-6 d-flex">
                <i class="bi bi-clock icon"></i>
                <div>
                    <h4>Horarios</h4>
                    <p>
                    <strong>De lunes a sabado:</strong> <span>8AM - 9PM</span><br>
                    <strong>Domingo</strong>: <span>Cerrado</span>
                    </p>
                </div>
                </div>

                <div class="col-lg-3 col-md-6">
                <div #contacto></div>
                <p>Síguenos en nuestras redes sociales:</p>
                <a href="https://www.facebook.com/share/1BoTD7KUbg/" target="_blank"><img src="../assets/img/Facebook.jpeg"
                    width="50" height="50" alt="image de red social facebook"></a>
                <a href="https://twitter.com/?lang=es" target="_blank"><img src="../assets/img/twitter.jpeg" width="50"
                    height="50" alt="image de red social twitter"></a>
                <a href="https://www.instagram.com/fashionstore_mme?igsh=MTBoOXVxdWQ4Y2I0eg%3D%3D&utm_source=qr"
                    target="_blank"><img src="../assets/img/Instagram.jpeg" width="50" height="50"
                    alt="image de red social instagram"></a>
                <a href="Fashion31store@gmail.com" target="_blank"><img src="../assets/img/Gmail.jpeg" width="50" height="50"
                    alt="image de mensaje al administrador"></a>
                </p>
                </div>

            </div>
            </div>
        </footer>

        <!-- Scroll Top -->
        <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

        <!-- Preloader -->
        <div id="preloader"></div>

        <!-- Vendor JS Files -->
        <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="../assets/vendor/aos/aos.js"></script>
        <script src="../assets/vendor/glightbox/js/glightbox.min.js"></script>
        <script src="../assets/vendor/swiper/swiper-bundle.min.js"></script>
        <script src="../assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
        <script src="../assets/vendor/php-email-form/validate.js"></script>

        <script src="../assets/js/main.js"></script>
        
        <script>

        </script>
</body>
</html>




