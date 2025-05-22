<?php
require_once '../conexion.php';
require_once '../Model/Reserva.php';

if (!isset($_GET['id'])) {
    die("<h3 style='color:red;'> ID de vehículo no recibido.</h3>");
}

$id = intval($_GET['id']);
$reserva = new Reserva($conexion);

if ($reserva->retirarVehiculo($id, 0, 0)) {
    echo "<h3 style='color:green;'> Vehículo retirado con éxito.</h3>";
    echo "<script>
        setTimeout(() => {
            window.location.href = '../View/Admin/Insertar.html';
        }, 2000);
    </script>";
} else {
    echo "<h3 style='color:red;'> Error al retirar el vehículo.</h3>";
}
