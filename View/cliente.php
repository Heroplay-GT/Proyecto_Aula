<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Go</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <h2 class="logo">Parking Go</h2>
        <nav class="navigation">
            <a href="configuracion.php">Configuración</a>
            <a href="index.php">Salir</a>
        </nav>
    </header>

    <div class="wrapper active-popup">
        <div class="form-box login">
            <h2>Bienvenido</h2>
            <p class="welcome-message">Seleccione una opción para continuar</p>
            <div class="button-container">
                <a class="btn" href="vehiculos.php">Vehículo</a>
                <a class="btn" href="facturacion.php">Factura</a>
                <a class="btn" href="index.php">Salir</a>
            </div>
        </div>
    </div>
</body>

</html>