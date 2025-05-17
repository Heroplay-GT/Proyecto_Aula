?<?php
$tieneReservas = !empty($reservas) && is_array($reservas);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkinGO - Mis Reservas</title>
    <link rel="stylesheet" href="../CSS/reserva.css">
    <link rel="stylesheet" href="../CSS/misReservas.css">
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
                    <div class="reserva-card estado-<?= strtolower($reserva['estado']) ?>"
                        onclick="mostrarDetalleReserva(<?= $reserva['id'] ?>)">
                        <h3>Reserva #<?= $reserva['id'] ?></h3>
                        <p><strong>Vehículo:</strong> <?= $reserva['tipo'] ?> - <?= $reserva['modelo'] ?></p>
                        <p><strong>Estado:</strong> <span class="estado"><?= $reserva['estado'] ?></span></p>
                        <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-reservas-container">
                <img src="../../Media/empty-state.png" alt="Sin reservas" class="empty-state-img">
                <p class="no-reservas-message">No tienes reservas registradas</p>
                <a href="../Vehiculos/Formulario.php" class="btn-crear-reserva">Crear nueva reserva</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para detalles de reserva -->
    <div id="reservaModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="cerrarModal()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <script src="../JS/misReservas.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>