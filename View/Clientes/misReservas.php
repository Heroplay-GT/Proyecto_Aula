<?php
// Verificar si hay reservas
$tieneReservas = !empty($reservas) && is_array($reservas);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkinGO - Mis Reservas</title>
    <link rel="stylesheet" href="../CSS/reservaciones.css">
</head>

<body>
    <div class="header-actions">
        <a href="Perfil.php" class="btn-regresar">
            <ion-icon name="chevron-back-circle-outline"></ion-icon>
            <span>Volver al perfil</span>
        </a>
    </div>

    <div class="vehiculo-wrapper">
        <h2>Mis Reservas</h2>

        <?php if ($tieneReservas): ?>
            <div class="reservas-container">
                <?php foreach ($reservas as $reserva): ?>
                    <div class="reserva-card <?php echo strtolower($reserva['estado']); ?>">
                        <h3>Reserva #<?php echo $reserva['id']; ?></h3>
                        <p><strong>Vehículo:</strong> <?php echo $reserva['tipo']; ?> - <?php echo $reserva['modelo']; ?></p>
                        <p><strong>Placa:</strong> <?php echo $reserva['placa']; ?></p>
                        <p><strong>Espacio:</strong> <?php echo $reserva['espacio_codigo']; ?></p>
                        <p><strong>Estado:</strong> <?php echo $reserva['estado']; ?></p>
                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])); ?></p>

                        <?php if (!empty($reserva['qr_code']) && $reserva['estado'] == 'Pendiente'): ?>
                            <div class="qr-container">
                                <img src="../../Media/QRCodes/<?php echo $reserva['qr_code']; ?>" alt="Código QR Reserva">
                                <p>Escanea este código al ingresar al parqueadero</p>
                            </div>
                        <?php endif; ?>
                    </div> <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-reservas">No tienes reservas registradas.</p>
        <?php endif; ?>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>