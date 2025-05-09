<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    
    // Verificar si el correo existe
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        // Generar token
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Guardar token en la tabla
        $stmt = $conexion->prepare("INSERT INTO tokens_recuperacion (correo, token, fecha_expiracion) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expira);
        $stmt->execute();

        // Enlace de recuperación
        $link = "http://localhost/PA/View/reset_password.php?token=$token";

        // Enviar email con PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // cambia si usas otro
            $mail->SMTPAuth = true;
            $mail->Username = 'ospinoaddel@gmail.com'; // tu correo
            $mail->Password = 'mbac lsty qauw dhux'; // tu clave (o clave de aplicación)
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('ospinoaddel@gmail.com', 'ParkinGO');
            $mail->addAddress($email);
            $mail->Subject = 'Recuperación de contraseña';
            $mail->Body = "Haz clic aquí para recuperar tu contraseña: $link";

            $mail->send();
            echo "<script>alert('Revisa tu correo para recuperar la contraseña'); window.location.href='../View/index.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Error al enviar correo: {$mail->ErrorInfo}');</script>";
        }
    } else {
        echo "<script>alert('Correo no registrado'); window.location.href='../View/index.php';</script>";
    }
}
?>
