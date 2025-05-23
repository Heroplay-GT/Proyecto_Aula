<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibir los datos del formulario
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Preparar la consulta para buscar el usuario
    $sql = "SELECT * FROM usuarios WHERE username = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Verificar si el usuario existe
    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        
        // Verificar la contraseña
        if (password_verify($password, $fila['password'])) {
            $_SESSION['username'] = $fila['username'];

            // Redirigir según el rol
            if ($fila['username'] === 'admin') {
                echo "<script>alert('Bienvenido administrador'); window.location.href='../View/Admin/Dashboard.php';</script>";
            } else {
                echo "<script>alert('Inicio de sesión exitoso'); window.location.href='../View/Clientes/Perfil.php';</script>";
            }
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='../View/Home.php';</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado'); window.location.href='../View/Home.php';</script>";
    }

    if (password_verify($password, $fila['password'])) {
        $_SESSION['username'] = $fila['username'];
        $_SESSION['id'] = $fila['id']; // <- AGREGA ESTA LÍNEA
    
        // Redirigir según el rol
        if ($fila['username'] === 'admin') {
            echo "<script>alert('Bienvenido administrador'); window.location.href='../View/Admin/Dashboard.php';</script>";
        } else {
            echo "<script>alert('Inicio de sesión exitoso'); window.location.href='../View/Clientes/Perfil.php';</script>";
        }
    }
    
}
?>