<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>
    
</head>
<body>
    <div class="wrapper">
        <h2>Recuperar Contraseña</h2>
        <form action="../Controller/send_recovery.php" method="POST">
            <div class="input-box">
                <label>Ingresa tu correo:</label>
                <input type="email" name="email" required>
            </div>
            <button type="submit" class="btn">Enviar enlace</button>
        </form>
    </div>
</body>
</html>
