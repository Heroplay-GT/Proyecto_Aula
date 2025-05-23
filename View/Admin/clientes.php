<?php
$titulo = 'Clientes';
ob_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - ParkinGo</title>
    <link rel="stylesheet" href="../CSS/cliente.css">
</head>

<body>

    <div class="header-actions">
        <a href="Dashboard.php" class="btn-regresar">
            <ion-icon name="chevron-back-circle-outline"></ion-icon>
            <span>Volver al inicio</span>
        </a>
    </div>

    <div class="admin-container">
        <h1>Listado de Clientes</h1>
        <table border="1" id="tabla-clientes">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">Cargando clientes...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="../JS/cliente.js"></script>
</body>

</html>