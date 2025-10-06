<?php
include("../Conexion/conexion_new.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo    = trim($_POST['correo']);
    $nombre    = trim($_POST['nombre']);
    $apellido  = trim($_POST['apellido']);
    $telefono  = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $rol       = trim($_POST['rol']); 
    $clave     = trim($_POST['clave']);

    // ✅ Validar campos vacíos
    if (empty($correo) || empty($nombre) || empty($apellido) || empty($telefono) || empty($direccion) || empty($rol) || empty($clave)) {
        $mensaje = "Por favor, complete todos los campos.";
    } else {
        // ✅ Validar si el correo ya existe
        $check_sql = "SELECT id FROM usuario WHERE correo = ?";
        $check_stmt = $con->prepare($check_sql);
        $check_stmt->bind_param("s", $correo);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $mensaje = "Este correo ya está registrado. Intente iniciar sesión.";
        } else {
            // 🔐 Encriptar la contraseña antes de guardar
            $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

            // ✅ Insertar usuario nuevo
            $sql = "INSERT INTO usuario (correo, nombre, apellido, telefono, direccion, perfil, clave) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $con->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssssss", $correo, $nombre, $apellido, $telefono, $direccion, $rol, $clave_hash);

                if ($stmt->execute()) {
                    // ✅ Mostrar confirmación en la misma vista
                    $mensaje = "✅ Registro exitoso. Ahora puede iniciar sesión.";
                } else {
                    $mensaje = "Error al registrar el usuario. Intente de nuevo.";
                }
                $stmt->close();
            } else {
                $mensaje = "Error en la preparación de la consulta.";
            }
        }
        $check_stmt->close();
    }

    $con->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrarse</title>
    <link rel="stylesheet" href="estiloregistro.css">
</head>
<body>
    <div class="form-container">
        <h2>Formulario de Registro</h2>

        <?php if (!empty($mensaje)): ?>
            <div style="padding:10px; background-color:#edf2ff; border-radius:8px; margin-bottom:15px; color:#333;">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Correo:</label>
            <input type="email" name="correo" required>

            <label>Nombre:</label>
            <input type="text" name="nombre" required>

            <label>Apellido:</label>
            <input type="text" name="apellido" required>

            <label>Teléfono:</label>
            <input type="text" name="telefono" required>

            <label>Dirección:</label>
            <input type="text" name="direccion" required>

            <label>Clave:</label>
            <input type="password" name="clave" required>

            <label>Perfil:</label>
            <select name="rol" required>
                <option value="Usuario">Usuario</option>
                <option value="Admin">Administrador</option>
            </select>

            <button type="submit">Registrarse</button>

            <!-- 🔁 Redirige al index para iniciar sesión -->
            <button type="button" class="cancelar" onclick="location.href='../index.php#Iniciosesion'">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>
