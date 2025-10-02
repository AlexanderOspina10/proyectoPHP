<?php
session_start();
include("Conexion/conexion_new.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo'] ?? '');
    $clave  = trim($_POST['clave'] ?? '');

    // Validar campos vacíos
    if (empty($correo) || empty($clave)) {
        echo "<script>alert('Por favor, rellene todos los campos.'); window.location.href='index.php#Iniciosesion';</script>";
        exit();
    }

    // IMPORTANTE: Usar prepared statement para prevenir SQL Injection
    $sql = "SELECT id, nombre, apellido, perfil, clave FROM usuario WHERE correo = ?";
    $stmt = $con->prepare($sql);
    
    if (!$stmt) {
        echo "<script>alert('Error en el sistema. Intente más tarde.'); window.location.href='index.php#Iniciosesion';</script>";
        exit();
    }
    
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        // Verificar si la contraseña está hasheada o en texto plano
        $password_valida = false;
        
        // Intentar verificar como hash (nuevo sistema)
        if (password_verify($clave, $usuario['clave'])) {
            $password_valida = true;
        } 
        // Si falla, verificar como texto plano (compatibilidad con sistema antiguo)
        elseif ($clave === $usuario['clave']) {
            $password_valida = true;
            
            // IMPORTANTE: Actualizar a hash para mayor seguridad
            $nueva_clave_hash = password_hash($clave, PASSWORD_DEFAULT);
            $update_sql = "UPDATE usuario SET clave = ? WHERE id = ?";
            $update_stmt = $con->prepare($update_sql);
            $update_stmt->bind_param("si", $nueva_clave_hash, $usuario['id']);
            $update_stmt->execute();
            $update_stmt->close();
        }

        if ($password_valida) {
            // Establecer variables de sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['id'] = $usuario['id']; // Para compatibilidad con el snippet del header
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_apellido'] = $usuario['apellido'] ?? '';
            $_SESSION['usuario_correo'] = $correo;
            $_SESSION['usuario_perfil'] = $usuario['perfil'];

            // Redirigir según el perfil
            if ($usuario['perfil'] === 'admin') {
                header("Location: menuadmin/admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            echo "<script>alert('Contraseña incorrecta.'); window.location.href='index.php#Iniciosesion';</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado.'); window.location.href='index.php#Iniciosesion';</script>";
    }
    
    $stmt->close();
} else {
    echo "Acceso no válido.";
}

$con->close();
?>