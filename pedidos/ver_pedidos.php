<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Registrados</title>
    <link rel="stylesheet" href="estilos_pedidos.css">
</head>
<body>
<?php
include("../Conexion/conexion_new.php");
$result = $con->query("SELECT * FROM pedidos");
?>

<div class="form-container">
    <h2>Pedidos Registrados</h2>
    <a href="formulario_pedido.php" class="back-link">Registrar nuevo pedido</a>
</div>

<?php
if ($result->num_rows > 0) {
    echo "<div style='width:90%; margin: 20px auto;'>";
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Celular</th>
            <th>Fecha</th>
            <th>Número de Prendas</th>
            <th>Registrado en</th>
            <th>Acciones</th>
          </tr>";

    while ($fila = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$fila['id']}</td>
                <td>{$fila['nombre']}</td>
                <td>{$fila['correo']}</td>
                <td>{$fila['celular']}</td>
                <td>{$fila['fecha']}</td>
                <td>{$fila['num_prendas']}</td>
                <td>{$fila['creado_en']}</td>
                <td>
                    <form action='eliminar_pedido.php' method='POST' style='display:inline;'>
                        <input type='hidden' name='id' value='{$fila['id']}'>
                        <button type='submit' class='delete-button'
                                onclick='return confirm(\"¿Seguro que deseas eliminar este pedido?\");'>
                            Eliminar
                        </button>
                    </form>
                </td>
              </tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<div class='form-container'><p>No hay pedidos registrados aún.</p></div>";
}

$con->close();
?>
<br>
<button class="cancelar" onclick="location.href='../index.php'">Volver</button>
</body>
</html>
