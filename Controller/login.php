<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM usuarios WHERE username = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        if (password_verify($password, $fila['password'])) {
            $_SESSION['username'] = $fila['username'];

            if ($fila['username'] === '01xhero') {
                echo "<script>alert('Bienvenido administrador'); window.location.href='../View/dashboard.html';</script>";
            } else {
                echo "<script>alert('Inicio de sesión exitoso'); window.location.href='../View/cliente.html';</script>";
            }
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='../View/index.html';</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado'); window.location.href='../View/index.html';</script>";
    }
}
?>
