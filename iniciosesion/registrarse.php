<?php
include("../Conexion/conexion_new.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo    = $_POST['correo'];
    $nombre    = $_POST['nombre'];
    $apellido  = $_POST['apellido'];
    $telefono  = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $rol       = $_POST['rol']; 
    $clave     = $_POST['clave'];

    $sql = "INSERT INTO usuario (correo, nombre, apellido, telefono, direccion, perfil, clave) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);

    if (!$stmt) {
        die("Error en prepare: " . $con->error);
    }

    $stmt->bind_param("sssssss", $correo, $nombre, $apellido, $telefono, $direccion, $rol, $clave);

    if ($stmt->execute()) {
        // ✅ Redirigir a otra página cuando el registro sea exitoso
        header("Location: registro_exitoso.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
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
        <form method="POST" action="">
            <label>Correo:</label><br>
            <input type="text" name="correo" required><br><br>

            <label>Nombre:</label><br>
            <input type="text" name="nombre" required><br><br>

            <label>Apellido:</label><br>
            <input type="text" name="apellido" required><br><br>

            <label>Teléfono:</label><br>
            <input type="text" name="telefono" required><br><br>

            <label>Dirección:</label><br>
            <input type="text" name="direccion" required><br><br>

            <label>Clave:</label><br>
            <input type="password" name="clave" required><br><br>

            <label>Perfil:</label><br>
            <select name="rol" required>
                <option value="Usuario">Usuario</option>
                <option value="Admin">Administrador</option>
            </select><br><br>

            <button type="submit">Registrarse</button>
            <br>
            <button class="cancelar" onclick="location.href='../index.php'">Volver</button>
        </form>
    </div>
</body>
</html>



