<?php
$host = "localhost";
$user = "root";
$pass = "3135497455Jj";
$db   = "fashion_store";

$con = new mysqli($host, $user, $pass, $db);

if ($con->connect_errno) {
    die("Error en la conexión: " . $con->connect_error);
}

$con->set_charset("utf8mb4");
?>
