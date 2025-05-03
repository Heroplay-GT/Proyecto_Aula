<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibir los datos del formulario
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validación de que los campos no estén vacíos
    if (!empty($username) && !empty($email) && !empty($password)) {
        
        // Cifrar la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Verificar si el nombre de usuario o el correo ya existen
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            echo "<script>alert('El nombre de usuario o email ya están registrados'); window.location.href='../View/index.php';</script>";
        } else {
            // Insertar un nuevo usuario
            $stmt = $conexion->prepare("INSERT INTO usuarios (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                echo "<script>alert('Registro exitoso'); window.location.href='../View/index.php';</script>";
            } else {
                echo "<script>alert('Error al registrar'); window.location.href='../View/index.php';</script>";
            }
        }
    } else {
        echo "<script>alert('Por favor completa todos los campos'); window.location.href='../View/index.php';</script>";
    }
}
?>