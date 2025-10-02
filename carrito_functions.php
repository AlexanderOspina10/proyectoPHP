<?php
// Este archivo debe ser incluido al inicio de index.php
// Reemplaza la sección de funciones del carrito existente


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

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

/* -------------------
   REALIZAR PEDIDO (prepara y redirige al formulario)
   Solo se mantiene esta funcionalidad por GET para compatibilidad
   ------------------- */
if (isset($_GET['realizar_pedido'])) {
    // VERIFICAR SI EL USUARIO HA INICIADO SESIÓN
    if (!isset($_SESSION['id'])) {
        $_SESSION['flash'] = "❌ Debes iniciar sesión para realizar un pedido.";
        header("Location: index.php#Iniciosesion");
        exit();
    }
    
    if (empty($_SESSION['carrito'])) {
        $_SESSION['flash'] = "❌ El carrito está vacío. Agrega productos para continuar.";
        header("Location: index.php#Catalogo");
        exit();
    }
    
    // Preparamos los datos del pedido en sesión
    $_SESSION['pedido'] = [
        'items' => $_SESSION['carrito'],
        'total' => calcularTotalCarrito()
    ];
    header("Location: pedidos/formulario_pedido.php");
    exit();
}

/* Funciones auxiliares */
function calcularTotalCarrito() {
    $total = 0;
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
    }
    return $total;
}

function contarItemsCarrito() {
    $count = 0;
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $count += $item['cantidad'];
        }
    }
    return $count;
}
?>