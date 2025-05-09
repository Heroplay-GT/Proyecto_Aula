<?php
$titulo = 'Parking Go';
ob_start();
?>

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
                <a class="btn" href="../View/vehiculos_form.php">Vehículo</a>
                <a class="btn" href="facturacion.php">Factura</a>
                <a class="btn" href="index.php">Salir</a>
            </div>
        </div>
    </div>

    <?php
    $contenido = ob_get_clean();

    include '/xampp/htdocs/Proyecto_Aula/View/Templates/layout.php';
