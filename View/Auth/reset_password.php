<?php
$token = $_GET['token'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="../CSS/style2.css">
    
</head>
<body>
    <div class="wrapper">
        <h2>Nueva Contraseña</h2>
        <form action="../../Controller/update_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="input-box">
                <label>Nueva Contraseña:</label>
                <input type="password" name="new_password" required>
            </div>
            <button type="submit" class="btn">Actualizar</button>
        </form>
    </div>
</body>
</html>
