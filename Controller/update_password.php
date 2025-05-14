<?php
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $conexion->prepare("SELECT correo, fecha_expiracion FROM tokens_recuperacion WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (strtotime($row['fecha_expiracion']) > time()) {
            $email = $row['correo'];

            $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $new_password, $email);
            $stmt->execute();

            // Eliminar el token usado
            $stmt = $conexion->prepare("DELETE FROM tokens_recuperacion WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();

            echo "<script>alert('Contraseña actualizada correctamente'); window.location.href='../View/Home.php';</script>";
        } else {
            echo "<script>alert('El token ha expirado'); window.location.href='../View/Home.php';</script>";
        }
    } else {
        echo "<script>alert('Token inválido'); window.location.href='../Home/index.php';</script>";
    }
}
?>
