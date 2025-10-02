<?php
session_start();
require '../Conexion/conexion_new.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$uid = intval($_SESSION['usuario_id']);
$stmt = $con->prepare("SELECT id, nombre, correo, perfil FROM usuario WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Perfil - Fashion Store</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>
  <?php /* header */ ?>
  <div class="container" style="margin-top:100px">
    <h2>Mi perfil</h2>
    <p><strong>Nombre:</strong> <?= htmlspecialchars($user['nombre']); ?></p>
    <p><strong>Correo:</strong> <?= htmlspecialchars($user['correo']); ?></p>
    <p><strong>Perfil:</strong> <?= htmlspecialchars($user['perfil']); ?></p>

    <a href="../index.php" class="btn btn-light">Volver</a>
  </div>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

