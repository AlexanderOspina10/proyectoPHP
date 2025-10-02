<?php
session_start();

// Conexión a la base de datos
$host = "localhost";
$user = "root";
$pass = "3135497455Jj";
$db   = "fashion_store";

$con = new mysqli($host, $user, $pass, $db);

if ($con->connect_errno) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '❌ Error de conexión']);
    exit;
}

$con->set_charset("utf8mb4");

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'agregar':
        $producto_id = intval($_POST['producto_id'] ?? 0);
        $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
        if ($producto_id <= 0) {
            echo json_encode(['success' => false, 'message' => '❌ Producto inválido']);
            break;
        }
        
        $stmt = $con->prepare("SELECT nombre, precio, stock, imagen FROM productos WHERE id = ?");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($producto = $result->fetch_assoc()) {
            if (intval($producto['stock']) >= $cantidad) {
                if (isset($_SESSION['carrito'][$producto_id])) {
                    $nueva_cantidad = $_SESSION['carrito'][$producto_id]['cantidad'] + $cantidad;
                    if ($nueva_cantidad <= intval($producto['stock'])) {
                        $_SESSION['carrito'][$producto_id]['cantidad'] = $nueva_cantidad;
                        echo json_encode([
                            'success' => true,
                            'message' => '✅ Producto agregado al carrito exitosamente',
                            'carrito_count' => contarItemsCarrito(),
                            'carrito_html' => generarHTMLCarrito()
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => '⚠️ No hay suficiente stock. Máximo disponible: ' . intval($producto['stock'])
                        ]);
                    }
                } else {
                    $_SESSION['carrito'][$producto_id] = [
                        'nombre' => $producto['nombre'],
                        'precio' => $producto['precio'],
                        'cantidad' => $cantidad,
                        'imagen' => $producto['imagen']
                    ];
                    echo json_encode([
                        'success' => true,
                        'message' => '✅ Producto agregado al carrito exitosamente',
                        'carrito_count' => contarItemsCarrito(),
                        'carrito_html' => generarHTMLCarrito()
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '❌ Stock insuficiente. Disponibles: ' . intval($producto['stock'])
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => '❌ Producto no encontrado']);
        }
        if ($stmt) $stmt->close();
        break;

    case 'actualizar':
        $alertas_stock = [];
        
        if (!empty($_POST['qty']) && is_array($_POST['qty'])) {
            foreach ($_POST['qty'] as $pid => $q) {
                $pid = intval($pid);
                $q = max(0, intval($q));

                if ($q === 0) {
                    if (isset($_SESSION['carrito'][$pid])) {
                        $nombre = htmlspecialchars($_SESSION['carrito'][$pid]['nombre']);
                        unset($_SESSION['carrito'][$pid]);
                        $alertas_stock[] = "🗑️ Producto <strong>{$nombre}</strong> removido";
                    }
                    continue;
                }

                $stmt = $con->prepare("SELECT nombre, stock FROM productos WHERE id = ?");
                $stmt->bind_param("i", $pid);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $stmt->close();

                $stock = $row ? intval($row['stock']) : 0;
                $nombre_producto = $row ? htmlspecialchars($row['nombre']) : "Producto ID: {$pid}";

                if (!isset($_SESSION['carrito'][$pid])) {
                    continue;
                }

                if ($q <= $stock) {
                    $_SESSION['carrito'][$pid]['cantidad'] = $q;
                } else {
                    if ($stock > 0) {
                        $_SESSION['carrito'][$pid]['cantidad'] = $stock;
                        $alertas_stock[] = "⚠️ <strong>{$nombre_producto}</strong> ajustado a stock máximo: <strong>{$stock}</strong>";
                    } else {
                        unset($_SESSION['carrito'][$pid]);
                        $alertas_stock[] = "❌ <strong>{$nombre_producto}</strong> eliminado (sin stock)";
                    }
                }
            }
        }

        $mensaje = !empty($alertas_stock) ? implode("<br>", $alertas_stock) : "✅ Carrito actualizado";
        
        echo json_encode([
            'success' => true,
            'message' => $mensaje,
            'carrito_count' => contarItemsCarrito(),
            'carrito_html' => generarHTMLCarrito()
        ]);
        break;

    case 'eliminar':
        $producto_id = intval($_POST['producto_id'] ?? 0);
        if ($producto_id <= 0) {
            echo json_encode(['success' => false, 'message' => '❌ Producto inválido']);
            break;
        }

        if (isset($_SESSION['carrito'][$producto_id])) {
            $nombre = htmlspecialchars($_SESSION['carrito'][$producto_id]['nombre']);
            unset($_SESSION['carrito'][$producto_id]);
            echo json_encode([
                'success' => true,
                'message' => "🗑️ Producto <strong>{$nombre}</strong> eliminado",
                'carrito_count' => contarItemsCarrito(),
                'carrito_html' => generarHTMLCarrito()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => '❌ Producto no encontrado']);
        }
        break;

    case 'remover_seleccionados':
        if (!empty($_POST['seleccionar']) && is_array($_POST['seleccionar'])) {
            $removidos = 0;
            foreach ($_POST['seleccionar'] as $pid) {
                $pid = intval($pid);
                if (isset($_SESSION['carrito'][$pid])) {
                    unset($_SESSION['carrito'][$pid]);
                    $removidos++;
                }
            }
            $mensaje = "🗑️ {$removidos} elemento(s) removido(s)";
            echo json_encode([
                'success' => true,
                'message' => $mensaje,
                'carrito_count' => contarItemsCarrito(),
                'carrito_html' => generarHTMLCarrito()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => '⚠️ No se seleccionó ningún producto']);
        }
        break;

    case 'vaciar':
        $_SESSION['carrito'] = [];
        echo json_encode([
            'success' => true,
            'message' => '🗑️ Carrito completamente vaciado',
            'carrito_count' => 0,
            'carrito_html' => generarHTMLCarrito()
        ]);
        break;

    case 'fetch':
        echo json_encode([
            'success' => true,
            'carrito_count' => contarItemsCarrito(),
            'carrito_html' => generarHTMLCarrito()
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

$con->close();

// Funciones auxiliares
function contarItemsCarrito() {
    $count = 0;
    if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $count += (isset($item['cantidad']) ? intval($item['cantidad']) : 0);
        }
    }
    return $count;
}

function calcularTotalCarrito() {
    $total = 0;
    if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $precio = isset($item['precio']) ? floatval($item['precio']) : 0;
            $cantidad = isset($item['cantidad']) ? intval($item['cantidad']) : 0;
            $total += $precio * $cantidad;
        }
    }
    return $total;
}

function generarHTMLCarrito() {
    if (empty($_SESSION['carrito'])) {
        return '<p class="text-center text-muted py-3">
                    <i class="bi bi-cart-x" style="font-size: 3rem; color: #ced4da;"></i><br>
                    Tu carrito está vacío
                </p>';
    }
    
    $html = '<form id="carritoForm" onsubmit="return false;">
                <div style="max-height:40vh; overflow-y:auto; padding-right:10px; margin-bottom:15px;">';
    
    foreach ($_SESSION['carrito'] as $id => $item) {
        $img = $item['imagen'] ?? '';
        $rutaWeb = 'menuadmin/assets/img/productos/' . $img;
        $thumb = $img ? $rutaWeb : 'assets/img/default-product.png';
        $nombre = htmlspecialchars($item['nombre'] ?? 'Producto');
        $precio = isset($item['precio']) ? number_format($item['precio'], 0, ',', '.') : '0';
        $cantidad = intval($item['cantidad'] ?? 1);
        $subtotal = number_format((isset($item['precio']) ? $item['precio'] * $cantidad : 0), 0, ',', '.');
        
        $html .= '<div class="carrito-item d-flex align-items-center gap-2">
                    <input type="checkbox" name="seleccionar[]" value="' . intval($id) . '" title="Seleccionar para quitar">
                    <img src="' . htmlspecialchars($thumb) . '" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                    <div style="flex:1;">
                        <strong>' . $nombre . '</strong><br>
                        <small class="text-muted">Precio: $' . $precio . '</small>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <input type="number" name="qty[' . intval($id) . ']" value="' . $cantidad . '" min="0" class="form-control form-control-sm" style="width:70px;">
                            <span class="text-success fw-bold" style="font-size:0.95rem;">$' . $subtotal . '</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(' . intval($id) . ')" title="Eliminar">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>';
    }
    
    $total = calcularTotalCarrito();
    $sesion_activa = isset($_SESSION['id']);
    
    $html .= '</div>
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total del Carrito:</strong>
                        <strong class="text-primary fs-5">$' . number_format($total, 0, ',', '.') . '</strong>
                    </div>
                    <div class="d-grid gap-2">
                        <div class="d-flex gap-2">
                            <button type="button" onclick="actualizarCarrito()" class="btn btn-primary btn-sm flex-fill">
                                <i class="bi bi-arrow-repeat me-1"></i>Actualizar Cantidades
                            </button>
                            <button type="button" onclick="removerSeleccionados()" class="btn btn-danger btn-sm flex-fill">
                                <i class="bi bi-trash-fill me-1"></i>Quitar Seleccionados
                            </button>
                        </div>';
    
    if ($sesion_activa) {
        $html .= '<button type="submit" name="realizar_pedido" class="btn btn-success w-100 mt-2" onclick="realizarPedido()">
                    <i class="bi bi-check-circle me-1"></i>Finalizar Pedido
                  </button>';
    } else {
        $html .= '<button type="button" class="btn btn-success w-100 mt-2 btn-pedido-disabled" disabled title="Debes iniciar sesión">
                    <i class="bi bi-exclamation-circle me-1"></i>Inicia sesión para pedir
                  </button>
                  <a href="#Iniciosesion" class="btn btn-outline-primary btn-sm w-100" onclick="toggleCarrito()">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Ir a iniciar sesión
                  </a>';
    }
    
    $html .= '      <button type="button" onclick="vaciarCarrito()" class="btn btn-sm btn-link text-danger">
                        Vaciar carrito por completo
                    </button>
                </div>
            </div>
        </form>';
    
    return $html;
}
?>
