
<?php
session_start();

include 'carrito_functions.php';
?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Fashion Store - Tienda de Moda</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/logo.png" rel="icon">
  <link href="assets/img/logo.png">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Satisfy:wght@400&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
  <link href="index.css" rel="stylesheet">
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

  <header id="header" class="header fixed-top">
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
        <a href="index.php" class="logo d-flex align-items-center">
          <h1 class="sitename">Fashion Store</h1>
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="#hero" class="active">Inicio</a></li>
            <li><a href="#Informacion">Información</a></li>
            <li><a href="#Catalogo">Catalogo</a></li>
            <li><a href="#Ofertas">Ofertas</a></li>
            <li><a href="#Equipodetrabajo">Equipo de trabajo</a></li>
            <li><a href="#Referencias">Referencias</a></li>
            <li><a href="#Contacto">Contacto</a></li>
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

<!-- Botón flotante del carrito -->
<button class="carrito-toggle" id="carritoToggle" onclick="toggleCarrito()">
    <i class="bi bi-cart-fill"></i>
    <?php if (contarItemsCarrito() > 0): ?>
    <span class="carrito-badge"><?php echo contarItemsCarrito(); ?></span>
    <?php endif; ?>
</button>

<!-- Panel del carrito -->
<div id="carritoPanel" class="carrito-float" style="display: none;">
    <div class="carrito-header">
        <h5 class="mb-0"><i class="bi bi-cart-check me-2"></i>Mi Carrito</h5>
        <button class="btn-close-carrito" onclick="toggleCarrito()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div id="carritoContent">
        <?php if (empty($_SESSION['carrito'])): ?>
            <div style="padding: 15px;">
                <p class="text-center text-muted py-3">
                    <i class="bi bi-cart-x" style="font-size: 3rem; color: #ced4da;"></i><br>
                    Tu carrito está vacío
                </p>
            </div>
        <?php else: ?>
            <form id="carritoForm" onsubmit="return false;">
                <div style="max-height:40vh; overflow-y:auto; padding-right:10px; margin-bottom:15px;">
                    <?php foreach ($_SESSION['carrito'] as $id => $item): 
                        $img = $item['imagen'];
                        $rutaFisica = __DIR__ . '/menuadmin/assets/img/productos/' . $img;
                        $rutaWeb = 'menuadmin/assets/img/productos/' . $img;
                        $thumb = ($img && is_file($rutaFisica)) ? $rutaWeb : 'assets/img/default-product.png';
                    ?>
                    <div class="carrito-item d-flex align-items-center gap-2">
                        <input type="checkbox" name="seleccionar[]" value="<?php echo intval($id); ?>" title="Seleccionar para quitar">
                        
                        <img src="<?php echo htmlspecialchars($thumb); ?>" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                        
                        <div style="flex:1;">
                            <strong><?php echo htmlspecialchars($item['nombre']); ?></strong><br>
                            <small class="text-muted">Precio: $<?php echo number_format($item['precio'],0,',','.'); ?></small>
                            
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <input type="number" name="qty[<?php echo intval($id); ?>]" value="<?php echo intval($item['cantidad']); ?>" min="0" class="form-control form-control-sm" style="width:70px;">
                                
                                <span class="text-success fw-bold" style="font-size:0.95rem;">$<?php echo number_format($item['precio'] * $item['cantidad'],0,',','.'); ?></span>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(<?php echo intval($id); ?>)" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-3 pt-3 border-top" style="padding: 0 15px 15px;">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total del Carrito:</strong>
                        <strong class="text-primary fs-5">$<?php echo number_format(calcularTotalCarrito(), 0, ',', '.'); ?></strong>
                    </div>

                    <div class="d-grid gap-2">
                        <div class="d-flex gap-2">
                            <button type="button" onclick="actualizarCarrito()" class="btn btn-primary btn-sm flex-fill">
                                <i class="bi bi-arrow-repeat me-1"></i>Actualizar Cantidades
                            </button>
                            <button type="button" onclick="removerSeleccionados()" class="btn btn-danger btn-sm flex-fill">
                                <i class="bi bi-trash-fill me-1"></i>Quitar Seleccionados
                            </button>
                        </div>
                        
                        <?php if (isset($_SESSION['id'])): ?>
                            <button type="submit" name="realizar_pedido" class="btn btn-success w-100 mt-2" onclick="realizarPedido()">
                                <i class="bi bi-check-circle me-1"></i>Finalizar Pedido
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-success w-100 mt-2 btn-pedido-disabled" disabled title="Debes iniciar sesión para realizar pedidos">
                                <i class="bi bi-exclamation-circle me-1"></i>Inicia sesión para pedir
                            </button>
                            <a href="#Iniciosesion" class="btn btn-outline-primary btn-sm w-100" onclick="toggleCarrito()">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Ir a iniciar sesión
                            </a>
                        <?php endif; ?>
                        
                        <button type="button" onclick="vaciarCarrito()" class="btn btn-sm btn-link text-danger">
                            Vaciar carrito por completo
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>


  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">
      <div id="hero-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-item active">
          <img src="assets/img/logo.png" alt="">
          <div class="carousel-container">
            <h2><span>Bienvenidos! a</span> Fashion Store</h2>
            <p>Abrimos las puertas a un espacio donde la moda cobra vida, donde cada prenda es más que tela y costuras: es una declaración de estilo, una extensión de tu personalidad y un reflejo de quién eres.</p>
            <div>
              <a href="#Catalogo" class="btn-get-started">Nuestro Catalogo</a>
              <a href="#Pide lo que mas te gusto" class="btn-get-started">Pide lo que mas te gusto</a>
            </div>
          </div>
        </div>

        <div class="carousel-item">
          <img src="assets/img/logo2.jpg.jpeg" alt="">
          <div class="carousel-container">
            <h2>Nuestra Historia</h2>
            <p>Nace de una pasión por la moda y por el deseo de ofrecer algo más que simples prendas de vestir. Queremos que cada cliente sienta que aquí puede encontrar algo único.</p>
            <div>
              <a href="#Catalogo" class="btn-get-started">Nuestro Catalogo</a>
              <a href="#Pide lo que mas te gusto" class="btn-get-started">Pide lo que mas te gusto</a>
            </div>
          </div>
        </div>

        <div class="carousel-item">
          <img src="assets/img/portada.jpeg" alt="">
          <div class="carousel-container">
            <h2>Vive la experiencia en Fashion Store</h2>
            <p>Hoy queremos invitarlos a descubrir cada rincón de nuestra tienda, a explorar nuestras colecciones y, sobre todo, a sentir la emoción de encontrar esa prenda perfecta que los haga destacar.</p>
            <div>
              <a href="#Catalogo" class="btn-get-started">Nuestro Catalogo</a>
              <a href="#Pide lo que mas te gusto" class="btn-get-started">Pide lo que mas te gusto</a>
            </div>
          </div>
        </div>

        <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
          <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
        </a>

        <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
          <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
        </a>

        <ol class="carousel-indicators"></ol>
      </div>
    </section>

    <!-- Inicio sesión -->
    <section id="Iniciosesion" class="sesion inicio de sesion">
      <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
          <div class="col-lg-6 text-center" data-aos="fade-up" data-aos-delay="100">
            <img src="assets/img/menu/iniciosesion.jpeg" alt="Foto de inicio" class="img-fluid d-block mx-auto" style="width: 250px; height: 250px; border-radius: 50%; object-fit: cover; box-shadow: 0 0 10px rgba(0,0,0,0.2);">
          </div>
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="panel panel-default">
              <div class="panel-heading text-center mb-3">
                <h4><span class="glyphicon glyphicon-lock"></span> Acceso al Sistema</h4>
              </div>
              <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" action="validar_acceso.php">
                  <div class="form-group">
                    <input type="email" name="correo" class="form-control" placeholder="Ingrese su Correo Electrónico" required>
                  </div>
                  <div class="form-group">
                    <input type="password" name="clave" class="form-control" placeholder="Ingrese su Clave" required>
                  </div>
                  <div class="form-group text-center">
                    <button type="submit" class="btn btn-purple btn-sm">Ingresar</button>
                    <button type="reset" class="btn btn-default btn-sm">Limpiar</button>
                  </div>
                  <div class="panel-footer text-center mt-3">
                    <p>¿No estás registrado? <a href="iniciosesion/registrarse.php">Regístrate aquí</a></p>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- About Section -->
    <section id="Informacion" class="sesion de información">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 col-md-6 content text-center" data-aos="fade-up" data-aos-delay="100">
            <h3>MISIÓN</h3>
            <p class="fst-italic">Ofrecer a los clientes una experiencia de compra en línea moderna y confiable, donde encuentren productos de moda con calidad y estilo, asegurando una interfaz intuitiva y amigable.</p>
          </div>
          <div class="col-lg-6 col-md-6 content text-center" data-aos="fade-up" data-aos-delay="200">
            <h3>VISIÓN</h3>
            <p class="fst-italic">Consolidar a Fashion Store como una tienda virtual reconocida en el mercado digital, destacándose por la innovación, el diseño y la satisfacción de sus clientes.</p>
          </div>
          <div class="col-lg-6 col-md-16 content text-center" data-aos="fade-up" data-aos-delay="300">
            <h3>OBJETIVO GENERAL</h3>
            <p class="fst-italic">Desarrollar un sistema de Informacion funcional para la tienda Fashion Store que permita mostrar los productos de manera atractiva y accesible, facilitando la experiencia de navegación del usuario.</p>
          </div>
          <div class="col-lg-6 col-md-16 content text-center" data-aos="fade-up" data-aos-delay="300">
            <h3>OBJETIVO ESPECIFICO</h3>
            <p class="fst-italic">Diseñar una interfaz visual clara y atractiva mediante el uso de HTML y CSS. Componer la estructura de los productos para facilitar la navegación del usuario. Construir la conexión con una base de datos MySQL para gestionar la información.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Us Section -->
    <section id="Por que nosostros?" class="Por que nosotros? section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Por que nosotros?</h2>
        <div><span>Por que elegirnos?</span> <span class="description-title">Nuestra tienda online</span></div>
      </div>

      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card-item">
              <span>01</span>
              <h4><a href="" class="stretched-link">"Confeccionado con amor, pensado para ti"</a></h4>
              <p>Ropa hecha para durar y destacar.</p>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card-item">
              <span>02</span>
              <h4><a href="" class="stretched-link">"Ropa con alma artística"</a></h4>
              <p>Cada prenda es una pieza única.</p>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card-item">
              <span>03</span>
              <h4><a href="" class="stretched-link">"Renueva tu estilo sin perder tu esencia."</a></h4>
              <p>Diseños originales, como tú.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Menu Section - CATÁLOGO DINÁMICO -->
   <section id="Catalogo" class="menu section">
    <div class="container section-title" data-aos="fade-up">
        <h2>Menu</h2>
        <div><span>Observa nuestras hermosas</span> <span class="description-title">prendas</span></div>
    </div>

    <div class="container isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">
        <div class="row" data-aos="fade-up" data-aos-delay="100">
            <div class="col-lg-12 d-flex justify-content-center">
                <ul class="menu-filters isotope-filters">
                    <li data-filter="*" class="filter-active">Todo</li>
                    <li data-filter=".filter-hombre">Hombre</li>
                    <li data-filter=".filter-mujer">Mujer</li>
                    <li data-filter=".filter-zapatos">Zapatos</li>
                    <li data-filter=".filter-accesorios">Accesorios</li>
                </ul>
            </div>
        </div>

        <div class="row isotope-container" data-aos-delay="200">
            <?php 
            // Obtener productos de la base de datos
            $sql = "SELECT * FROM productos ORDER BY categoria, nombre";
            $resultado = $con->query($sql);

            if ($resultado && $resultado->num_rows > 0):
                while($producto = $resultado->fetch_assoc()):
                    $categoria_class = 'filter-' . strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $producto['categoria']));
                    $imagenArchivo = $producto['imagen'];
                    $rutaFisica = __DIR__ . '/menuadmin/assets/img/productos/' . $imagenArchivo;
                    $rutaWeb = 'menuadmin/assets/img/productos/' . $imagenArchivo;
                    $imgSrc = ($imagenArchivo && is_file($rutaFisica)) ? $rutaWeb : 'assets/img/default-product.png';
                    $stockActual = intval($producto['stock']);
            ?>
            <div class="col-lg-6 menu-item isotope-item <?php echo $categoria_class; ?>">
                <div class="producto-card position-relative d-flex flex-column">
                    <div style="position:relative; border-radius:10px; overflow:hidden;">
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                             class="menu-img" 
                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                             style="height:320px; width:100%; object-fit:cover; display:block;">
                        <div style="position:absolute; left:10px; bottom:10px; background:rgba(0,0,0,0.6); color:#fff; padding:6px 10px; border-radius:8px; font-weight:700;">
                            $<?php echo number_format($producto['precio'],0,',','.'); ?>
                        </div>
                        
                        <!-- Indicador de stock bajo -->
                        <?php if ($stockActual > 0 && $stockActual <= 5): ?>
                        <div style="position:absolute; right:10px; top:10px; background:#ff9800; color:#fff; padding:4px 8px; border-radius:6px; font-size:12px; font-weight:600;">
                            ¡Últimas unidades!
                        </div>
                        <?php elseif ($stockActual <= 0): ?>
                        <div style="position:absolute; right:10px; top:10px; background:#f44336; color:#fff; padding:4px 8px; border-radius:6px; font-size:12px; font-weight:600;">
                            Agotado
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-3" style="flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                            <p class="text-muted small mb-2" style="min-height:44px;"><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge <?php echo ($stockActual > 5) ? 'bg-success' : (($stockActual > 0) ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php echo $stockActual; ?> disponibles
                                </span>
                            </div>
                            
                            <!-- Formulario con AJAX -->
                            <form method="POST" onsubmit="return agregarAlCarrito(this)" style="flex-grow: 1; margin-left: 10px;">
                                <input type="hidden" name="producto_id" value="<?php echo intval($producto['id']); ?>">
                                <div class="input-group">
                                    <input type="number" 
                                           name="cantidad" 
                                           value="1" 
                                           min="1" 
                                           max="<?php echo $stockActual; ?>" 
                                           class="form-control form-control-sm" 
                                           style="width:60px;" 
                                           <?php echo ($stockActual <= 0) ? 'disabled' : ''; ?>>
                                    <button type="submit" 
                                            name="agregar_carrito" 
                                            class="btn btn-add-cart" 
                                            <?php echo ($stockActual <= 0) ? 'disabled' : ''; ?>>
                                        <i class="bi bi-cart-plus me-1"></i> Agregar al Carrito
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endwhile;
            endif; 
            ?>
        </div>
    </div>
</section>


    <!-- Specials Section -->
    <section id="Ofertas" class="specials section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Ofertas</h2>
        <div><span>Nuestras</span> <span class="description-title">Ofertas</span></div>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row">
          <div class="col-lg-3">
            <ul class="nav nav-tabs flex-column">
              <li class="nav-item">
                <a class="nav-link active show" data-bs-toggle="tab" href="#Hastaagotarexistencias">Hasta agotar existencias</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#Tucumpleaños">Tu cumpleaños</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#Diadelamujer">Dia de la mujer</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#Descuentos">Descuentos</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#Obsequios">Obsequios</a>
              </li>
            </ul>
          </div>
          <div class="col-lg-9 mt-4 mt-lg-0">
            <div class="tab-content">
              <div class="tab-pane active show" id="Hastaagotarexistencias">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>¡Se va volando!</h3>
                    <p class="fst-italic">Stock bajito. Precios increíbles. ¡Corre ya</p>
                   <p class="fst-italic">Exclusivo y por tiempo corto. ¡No te lo pierdas!</p>
                    <p class="fst-italic"> Si parpadeas, lo pierdes 👀</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="assets/img/pinzas1.png" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="Tucumpleaños">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>Regalo de cumpleaños</h3>
                    <p class="fst-italic">Por ser fiel comprador te regalamos este detaller , 
                      hermoso , para que lo luzacas en la calle y tengas un bonito glamour.</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="assets/img/Manillera.png  " alt="" class="img-fluid">
                  </div>
                </div>
              </div>
               <div class="tab-pane" id="Diadelamujer">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>"Mujer: eres fuerza, belleza y cambio." </h3>
                   <p class="fst-italic">Para ti, que llenas el mundo de amor y color.</p>
                    <p class="fst-italic">Hoy celebramos lo que ya sabíamos: que eres única.</p>
                    <p class="fst-italic">Tu esencia no pasa de moda.</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="assets/img/Mujer4.png" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="Descuentos">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>Moda con rebaja, estilo sin límites!</h3>
                    <p class="fst-italic">Tu outfit favorito… ahora con descuentazo 👗</p>
                    <p class="fst-italic">Moda que enamora, precios que sorprenden 💖</p>
                    <p class="fst-italic">Algo bonito pero barato</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="assets/img/Mujer7.png" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="Obsequios">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>Nos encanta sorprenderte… ¡este es tu momento!</h3>
                    <p class="fst-italic">
                      Por varias compras te regalamos lo mejor que tenemos en nuestra pagina</p>
                    <p class="fst-italic"> Por cada compra especial, un regalo pensado para ti 💝</p>
                    <p class="fst-italic">“¡Sí! Te damos un detalle extra solo por elegirnos</p>
                    </p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="assets/img/Obsequio.png" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Specials Section -->

    

   <!-- Referencias Section -->
 <section id="Referencias" class="Referencias section">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <!-- Título principal de la galería -->
    <h2>Referencias</h2>
    <!-- Subtítulo con una breve descripción -->
    <div><span>Algunas fotos de</span> <span class="description-title">nuestro local</span></div>
  </div><!-- End Section Title -->

  <!-- Contenedor principal con animación al hacer scroll -->
  <div class="container-fluid" data-aos="fade-up" data-aos-delay="100">

    <!-- Contenedor de la galería que organiza las imágenes en forma de tabla usando flexbox -->
    <div class="gallery-grid" 
         style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">

      <!-- Cada imagen se coloca dentro de un div con tamaño fijo -->
      <!-- Imagen 1 -->
      <div class="gallery-item" 
           style="width: 250px; height: 180px; overflow: hidden;">
        <a href="assets/img/testimonials/Estilosderopa2.jpg" class="glightbox" data-gallery="images-gallery">
          <!-- La imagen se ajusta al tamaño del contenedor sin deformarse -->
          <img src="assets/img/testimonials/Estilosderopa2.jpg" alt="" 
               style="width: 100%; height: 100%; object-fit: cover;">
        </a>
      </div>

      <!-- Imagen 2 -->
      <div class="gallery-item" 
           style="width: 250px; height: 180px; overflow: hidden;">
        <a href="assets/img/testimonials/Esttilosderopa3.jpeg" class="glightbox" data-gallery="images-gallery">
          <img src="assets/img/testimonials/Esttilosderopa3.jpeg" alt="" 
               style="width: 100%; height: 100%; object-fit: cover;">
        </a>
      </div>

      <!-- Imagen 3 -->
      <div class="gallery-item" 
           style="width: 250px; height: 180px; overflow: hidden;">
        <a href="assets/img/testimonials/Estilosderopa4.jpg" class="glightbox" data-gallery="images-gallery">
          <img src="assets/img/testimonials/Estilosderopa4.jpg" alt="" 
               style="width: 100%; height: 100%; object-fit: cover;">
        </a>
      </div>

      <!-- Imagen 4 -->
      <div class="gallery-item" 
           style="width: 250px; height: 180px; overflow: hidden;">
        <a href="assets/img/testimonials/estilosderopa5.jpeg" class="glightbox" data-gallery="images-gallery">
          <img src="assets/img/testimonials/Estilosderopa6.jpg" alt="" 
               style="width: 100%; height: 100%; object-fit: cover;">
        </a>
      </div>

      <!-- Imagen 5 -->
      <div class="gallery-item" 
           style="width: 250px; height: 180px; overflow: hidden;">
        <a href="assets/img/testimonials/Estilosderopa7.jpeg" class="glightbox" data-gallery="images-gallery">
          <img src="assets/img/testimonials/Estilosderopa7.jpeg" alt="" 
               style="width: 100%; height: 100%; object-fit: cover;">
        </a>
      </div>

      <!-- Imagen 6 -->
      <div class="gallery-item" 
           style="width: 250px; height: 180px; overflow: hidden;">
        <a href="assets/img/local.png" class="glightbox" data-gallery="images-gallery">
          <img src="assets/img/local.png" alt="" 
               style="width: 100%; height: 100%; object-fit: cover;">
        </a>
      </div>         

      <!-- Imagen 7 -->
      <div class="gallery-item" 
           style="width: 250px; height: 180px; overflow: hidden;">
        <a href="assets/img/local2.png" class="glightbox" data-gallery="images-gallery">
          <img src="assets/img/local2.png" alt="" 
               style="width: 100%; height: 100%; object-fit: cover;">
        </a>
      </div>

            <!-- Imagen 8 -->
      <div class="gallery-item" 
           style="width: 250px; height: 180px; overflow: hidden;">
        <a href="assets/img/local3.png" class="glightbox" data-gallery="images-gallery">
          <img src="assets/img/local3.png" alt="" 
               style="width: 100%; height: 100%; object-fit: cover;">
        </a>
      </div>

            <!-- Imagen 9 -->
      <div class="gallery-item" 
           style="width: 250px; height: 180px; overflow: hidden;">
        <a href="assets/img/local4.png" class="glightbox" data-gallery="images-gallery">
          <img src="assets/img/local4.png" alt="" 
               style="width: 100%; height: 100%; object-fit: cover;">
        </a>
      </div>

    </div> <!-- Fin del grid de galería -->

  </div> <!-- Fin del contenedor principal -->

</section><!-- /Gallery Section -->


    <!-- Equipo de trabajo-->
    <section id="Equipodetrabajo" class="Equipo de trabajo section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Nosotros</h2>
        <div><span>Nuestro Equipo</span> <span class="description-title">de trabajo</span></div>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-5">

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="member">
              <div class="pic"><img src="assets/img/testimonials/Esteban3.jpg" class="img-fluid" alt="100" width="285"></div>
              <div class="member-info">
                <h4>Esteban Guapacha</h4>
                <span>BackEND</span>
                <div class="social">
                  <a href="https://www.instagram.com/esteban_14g?igsh=MW11YmRzZ3V5OXM1Mw=="><i class="bi bi-instagram"></i></a>
                   <a href="https://www.tiktok.com/@esteban__14g?_t=ZS-8w9nqOl3dns&_r=1"><i class="bi bi-tiktok"></i></a>  
                </div>
              </div>
            </div>
          </div><!-- End Team Member -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="member">
              <div class="pic"><img src="assets/img/testimonials/Michel3.jpg" class="img-fluid" alt="100" width="295"></div>
              <div class="member-info">
                <h4>Michel Jaramillo</h4>
                <span>FrontEND</span>
                <div class="social">
                  <a href=""><i class="bi bi-tik tok"></i></a>
                  <a href="https://www.instagram.com/michel___1902/profilecard"><i class="bi bi-instagram"></i></a>
                  <a href="https://www.tiktok.com/@michel_____19?_t=ZS-8w9nd9QRJWT&_r=1"><i class="bi bi-tiktok"></i></a>
                </div>
              </div>
            </div>
          </div><!-- End Team Member -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="member">
              <div class="pic"><img src="assets/img/Mariana.png" class="img-fluid" alt="200" width="285"></div>
              <div class="member-info">
                <h4>Mariana Maldonado</h4>
                <span>Base de datos</span>
                <div class="social">
                  <a href="https://www.instagram.com/mari_mld16/profilecard/?igsh=N3k5dHExYmR1eXFx"><i class="bi bi-instagram"></i></a>
                  <a href="https://www.tiktok.com/@mari_loaiza17?_t=ZS-8w9qUOObqIE&_r=1"><i class="bi bi-tiktok"></i></a>
                </div>
              </div>
            </div>
          </div><!-- End Team Member -->

        </div>

      </div>

    </section><!-- /Chefs Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section dark-background">

      <img src="assets/img/testimonials/tienda.jpg" class="testimonials-bg" alt="">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              }
            }
          </script>
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/Mariana.jpeg" class="testimonial-img" alt="">
                <h3>Mariana</h3>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                    class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Es una persona comprometida y le pone amor a las cosas que hcaes, es amable, responsable y
                    honesta. </span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/esteban2.jpeg" class="testimonial-img" alt="">
                <h3>Esteban</h3>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                    class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Es una persona apasionada en lo que hace , le gusta conocer gente y es muy sociable.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/michel2.jpeg" class="testimonial-img" alt="">
                <h3>Michel</h3>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                    class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>es una persona cariñosa y responsable hace todo con amor y es noble.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->
          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>

    </section><!-- /Testimonials Section -->

  <!-- Contact Section -->
<section id="Contacto" class="Contacto section">

  <!-- Título de la sección -->
  <div class="container section-title" data-aos="fade-up">
    <h2>Contacto</h2>
    <div><span>Nuestro</span> <span class="description-title">Contacto</span></div>
  </div><!-- Fin del título -->

  <!-- Contenedor principal de la sección -->
  <div class="container" data-aos="fade">

    <!-- Se agregó una fila con separación vertical (gy) y horizontal (gx-lg) -->
    <!-- Esto permite organizar el contenido en dos columnas, una para la info y otra para el formulario -->
    <div class="row gy-5 gx-lg-5">
      
      <!-- COLUMNA IZQUIERDA: Se movió la información de contacto dentro de esta columna col-lg-4 -->
      <!-- Antes estaba suelta fuera del layout y desorganizada -->
      <div class="col-lg-4">
        
        <!-- BLOQUE: Información de ubicación -->
        <!-- Se envolvió en .info-item con clases flex para alinear ícono y texto -->
        <div class="info-item d-flex mb-4">
          <i class="bi bi-geo-alt flex-shrink-0"></i> <!-- Ícono de ubicación con Bootstrap Icons -->
          <div>
            <h4>Locación:</h4>
            <p>Medellín, Belén Miravalle</p>
          </div>
        </div>

        <!-- BLOQUE: Información de correo electrónico -->
        <div class="info-item d-flex mb-4">
          <i class="bi bi-envelope flex-shrink-0"></i> <!-- Ícono de correo -->
          <div>
            <h4>Correo:</h4>
            <p>Fashion31store@gmail.com</p>
          </div>
        </div>

        <!-- BLOQUE: Información de celular -->
        <div class="info-item d-flex mb-4">
          <i class="bi bi-phone flex-shrink-0"></i> <!-- Ícono de teléfono -->
          <div>
            <h4>Número Celular:</h4>
            <p>+57 3113235370</p>
          </div>
        </div>

      </div><!-- Fin columna izquierda -->

      <!-- COLUMNA DERECHA: Se colocó el formulario dentro de una columna col-lg-8 -->
      <!-- Esto permite mostrarlo al lado de la info de contacto en pantallas grandes -->
      <div class="col-lg-8">
        <form action="forms/contact.php" method="post" role="form" class="php-email-form">
          
          <!-- Se mantuvo la fila con dos columnas para nombre y correo -->
          <div class="row">
            <div class="col-md-6 form-group">
              <input type="text" name="name" class="form-control" id="Nombre" placeholder="Nombre" required="">
            </div>
            <div class="col-md-6 form-group mt-3 mt-md-0">
              <input type="email" class="form-control" name="email" id="Correo" placeholder="Correo" required="">
            </div>
          </div>

          <!-- Campo: Asunto -->
          <div class="form-group mt-3">
            <input type="text" class="form-control" name="Asunto" id="Asunto" placeholder="Asunto" required="">
          </div>

          <!-- Campo: Mensaje -->
          <div class="form-group mt-3">
            <textarea class="form-control" name="Mensaje" placeholder="Mensaje" required=""></textarea>
          </div>

          <!-- Bloque para mostrar mensajes de carga, error o éxito -->
          <div class="my-3">
            <div class="Cargando">Cargando</div>
            <div class="error en mensaje"></div>
            <div class="mandar mensaje">¡Su mensaje ha sido enviado, muchas gracias!</div>
          </div>

          <!-- Botón para enviar el formulario -->
          <div class="text-center"><button type="submit">Mandar mensaje</button></div>

        </form>
      </div><!-- Fin columna formulario -->

    </div><!-- Fin fila principal -->

   </div><!-- Fin contenedor -->

  </section><!-- Fin sección contacto -->



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
          <a href="https://www.facebook.com/share/1BoTD7KUbg/" target="_blank"><img src="assets/img/Facebook.jpeg"
              width="50" height="50" alt="image de red social facebook"></a>
          <a href="https://twitter.com/?lang=es" target="_blank"><img src="assets/img/twitter.jpeg" width="50"
              height="50" alt="image de red social twitter"></a>
          <a href="https://www.instagram.com/fashionstore_mme?igsh=MTBoOXVxdWQ4Y2I0eg%3D%3D&utm_source=qr"
            target="_blank"><img src="assets/img/Instagram.jpeg" width="50" height="50"
              alt="image de red social instagram"></a>
          <a href="Fashion31store@gmail.com" target="_blank"><img src="assets/img/Gmail.jpeg" width="50" height="50"
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
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  
  <script src="assets/js/main.js"></script>

  <script>

        // Actualiza el contenido del panel si existe (aunque esté oculto)
        function updateCarritoPanelHtml(html) {
            const content = document.getElementById('carritoContent');
            if (content) content.innerHTML = html;
        }

        // Solicita al servidor el estado actual del carrito (usa la acción 'fetch')
        function fetchCarritoFromServer() {
            const fd = new FormData();
            fd.append('action', 'fetch');

            return fetch('carrito_ajax.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                try {
                    if (data && data.success) {
                        updateCarritoBadge(data.carrito_count);
                        updateCarritoPanelHtml(data.carrito_html);
                    }
                    return data;
                } catch (err) {
                    console.error('Error procesando fetchCarritoFromServer:', err);
                }
            })
            .catch(err => {
                console.error('Error al obtener carrito desde servidor:', err);
            });
        }

        // showNotification mejorada: infiere tipo y fuerza error si es eliminación
        function showNotification(message, type = 'info') {
            try {
                const msgStr = String(message || '');
                if (msgStr.includes('🗑️') || msgStr.includes('❌')) {
                    type = 'error';
                } else if (msgStr.includes('✅') && type === 'info') {
                    type = 'success';
                } else if (msgStr.includes('⚠️') && type === 'info') {
                    type = 'warning';
                }
            } catch (e) {
                // ignore
            }

            const existing = document.querySelector('.custom-notification');
            if (existing) existing.remove();

            const notification = document.createElement('div');
            notification.className = `custom-notification custom-notification-${type}`;

            let icon = 'bi-info-circle';
            if (type === 'success' || String(message).includes('✅')) icon = 'bi-check-circle-fill';
            else if (type === 'error' || String(message).includes('❌') || String(message).includes('🗑️')) icon = 'bi-x-circle-fill';
            else if (type === 'warning' || String(message).includes('⚠️')) icon = 'bi-exclamation-triangle-fill';
            else if (String(message).includes('🗑️')) icon = 'bi-trash-fill';

            const cleanMessage = String(message).replace(/✅|❌|⚠️|🗑️/g, '').trim();

            notification.innerHTML = `
                <div class="notification-content">
                    <i class="bi ${icon} notification-icon"></i>
                    <div class="notification-message">${cleanMessage}</div>
                    <button class="notification-close" onclick="hideNotification()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="notification-progress"></div>
            `;

            document.body.appendChild(notification);

            // Forzar progreso según tipo
            const progress = notification.querySelector('.notification-progress');
            if (progress) {
                if (type === 'error') {
                    progress.style.background = 'linear-gradient(90deg, #f44336, #da190b)';
                } else if (type === 'success') {
                    progress.style.background = 'linear-gradient(90deg, #4CAF50, #45a049)';
                } else if (type === 'warning') {
                    progress.style.background = 'linear-gradient(90deg, #ff9800, #e68900)';
                } else {
                    progress.style.background = 'linear-gradient(90deg, #2196F3, #0b7dda)';
                }
            }

            setTimeout(() => notification.classList.add('show'), 10);

            const timeout = (type === 'error') ? 6000 : 5000;
            setTimeout(() => hideNotification(), timeout);
        }

        function hideNotification() {
            const notification = document.querySelector('.custom-notification');
            if (notification) {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }
        }

        // Actualizar badge del carrito
        function updateCarritoBadge(count) {
            let badge = document.querySelector('.carrito-badge');
            const toggle = document.getElementById('carritoToggle');

            if (!toggle) return;

            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.classList.add('carrito-badge');
                    toggle.appendChild(badge);
                }
                badge.textContent = count;
                badge.style.transform = 'scale(1.3)';
                setTimeout(() => badge.style.transform = 'scale(1)', 200);
            } else if (badge) {
                badge.remove();
            }
        }

        // Toggle del panel del carrito
        function toggleCarrito() {
            const panel = document.getElementById('carritoPanel');
            if (!panel) return;

            const isVisible = panel.style.display === 'block';

            if (isVisible) {
                panel.classList.remove('carrito-visible');
                setTimeout(() => panel.style.display = 'none', 300);
            } else {
                panel.style.display = 'block';
                setTimeout(() => panel.classList.add('carrito-visible'), 10);
            }
        }

        // Agregar producto al carrito (usa el botón del formulario)
        function agregarAlCarrito(formElement) {
            const formData = new FormData(formElement);
            formData.append('action', 'agregar');

            const btn = formElement.querySelector('button[name="agregar_carrito"]');
            const btnText = btn ? btn.innerHTML : null;
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Agregando...';
            }

            fetch('carrito_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                try {
                    if (data && data.success) {
                        showNotification(data.message, 'success');
                        updateCarritoBadge(data.carrito_count);
                        updateCarritoPanelHtml(data.carrito_html);
                        try { localStorage.setItem('carrito_update', Date.now().toString()); } catch (e) {}
                        if (btn) {
                            btn.classList.add('btn-success');
                            btn.innerHTML = '<i class="bi bi-check2 me-1"></i>¡Agregado!';
                            setTimeout(() => {
                                btn.classList.remove('btn-success');
                                if (btnText) btn.innerHTML = btnText;
                            }, 1500);
                        }
                    } else {
                        showNotification(data?.message || '❌ Error desconocido', 'error');
                    }
                } catch (err) {
                    console.error('Error procesando respuesta agregarAlCarrito:', err);
                    showNotification('❌ Error al procesar la respuesta', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ Error al agregar el producto', 'error');
            })
            .finally(() => {
                if (btn) btn.disabled = false;
            });

            return false;
        }

        // Actualizar cantidades del carrito
        function actualizarCarrito() {
            const form = document.getElementById('carritoForm');
            if (!form) return;

            const formData = new FormData(form);
            formData.append('action', 'actualizar');

            fetch('carrito_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                try {
                    if (data && data.success) {
                        showNotification(data.message, 'success');
                        updateCarritoBadge(data.carrito_count);
                        updateCarritoPanelHtml(data.carrito_html);
                        try { localStorage.setItem('carrito_update', Date.now().toString()); } catch (e) {}
                    } else {
                        showNotification(data?.message || '❌ Error desconocido', 'error');
                    }
                } catch (err) {
                    console.error('Error procesando respuesta actualizarCarrito:', err);
                    showNotification('❌ Error al procesar la respuesta', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ Error al actualizar el carrito', 'error');
            });
        }

        // Eliminar producto individual (notificación en rojo)
        function eliminarProducto(productoId) {
            if (!confirm('¿Eliminar este producto del carrito?')) return;

            const formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('producto_id', productoId);

            fetch('carrito_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                try {
                    if (data && data.success) {
                        showNotification(data.message, 'error'); // rojo
                        updateCarritoBadge(data.carrito_count);
                        updateCarritoPanelHtml(data.carrito_html);
                        try { localStorage.setItem('carrito_update', Date.now().toString()); } catch (e) {}
                    } else {
                        showNotification(data?.message || '❌ Producto no encontrado', 'error');
                    }
                } catch (err) {
                    console.error('Error procesando respuesta eliminarProducto:', err);
                    showNotification('❌ Error al procesar la respuesta', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ Error al eliminar el producto', 'error');
            });
        }

        // Remover productos seleccionados (notificación en rojo)
        function removerSeleccionados() {
            const checkboxes = document.querySelectorAll('input[name="seleccionar[]"]:checked');
            if (checkboxes.length === 0) {
                showNotification('⚠️ Selecciona al menos un producto', 'warning');
                return;
            }

            if (!confirm(`¿Quitar ${checkboxes.length} producto(s) del carrito?`)) return;

            const form = document.getElementById('carritoForm');
            if (!form) return;

            const formData = new FormData(form);
            formData.append('action', 'remover_seleccionados');

            fetch('carrito_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                try {
                    if (data && data.success) {
                        showNotification(data.message, 'error'); // rojo
                        updateCarritoBadge(data.carrito_count);
                        updateCarritoPanelHtml(data.carrito_html);
                        try { localStorage.setItem('carrito_update', Date.now().toString()); } catch (e) {}
                    } else {
                        showNotification(data?.message || '❌ Error desconocido', 'error');
                    }
                } catch (err) {
                    console.error('Error procesando respuesta removerSeleccionados:', err);
                    showNotification('❌ Error al procesar la respuesta', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ Error al remover productos', 'error');
            });
        }

        // Vaciar carrito completo (notificación en rojo)
        function vaciarCarrito() {
            if (!confirm('¿Estás seguro de que quieres vaciar TODO el carrito?')) return;

            const formData = new FormData();
            formData.append('action', 'vaciar');

            fetch('carrito_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                try {
                    if (data && data.success) {
                        showNotification(data.message, 'error'); // rojo
                        updateCarritoBadge(data.carrito_count);
                        updateCarritoPanelHtml(data.carrito_html);
                        try { localStorage.setItem('carrito_update', Date.now().toString()); } catch (e) {}
                    } else {
                        showNotification(data?.message || '❌ Error desconocido', 'error');
                    }
                } catch (err) {
                    console.error('Error procesando respuesta vaciarCarrito:', err);
                    showNotification('❌ Error al procesar la respuesta', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ Error al vaciar el carrito', 'error');
            });
        }

        // Realizar pedido (redirige al formulario)
        function realizarPedido() {
            window.location.href = 'index.php?realizar_pedido=1';
        }

        // Sincronización entre pestañas: si otra pestaña escribe 'carrito_update', pedimos al servidor
        window.addEventListener('storage', (e) => {
            if (e.key === 'carrito_update') {
                fetchCarritoFromServer();
            }
        });

        // Al cargar la página sincronizamos badge/panel desde servidor
        document.addEventListener('DOMContentLoaded', () => {
            fetchCarritoFromServer();
        });

</script>
</body>
</html>