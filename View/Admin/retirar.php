<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Retiro de Vehículos</title>
    <link rel="stylesheet" href="../CSS/retirar.css" />
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
</head>

<body>
    <div class="retirar-container">
        <div class="header-actions">
            <a href="Dashboard.php" class="btn-regresar">
                <ion-icon name="arrow-back-outline"></ion-icon>
                Regresar
            </a>
        </div>

        <h1>Retiro de Vehículos</h1>

        <form id="formBuscarPlaca" method="POST" onsubmit="return false;">
            <div class="form-group">
                <label for="buscarPlaca">Buscar por Placa:</label>
                <input type="text" name="placa" id="buscarPlaca" placeholder="Ej: ABC123" autocomplete="off" />
            </div>
        </form>

        <table id="tablaVehiculos">
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Tipo</th>
                    <th>Modelo</th>
                    <th>Espacio</th>
                    <th>Fecha Ingreso</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <!-- Datos se cargan con JS -->
            </tbody>
        </table>
    </div>

    <!-- Modal para retiro -->
    <div id="modalRetiro" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="cerrarModal()" title="Cerrar">&times;</button>
            <h3>Confirmar Retiro</h3>
            <p id="infoVehiculo"></p>
            <p><strong>El tiempo y valor se calcularán automáticamente.</strong></p>
            <button id="confirmarRetiro" class="btn">Confirmar Retiro</button>
        </div>
    </div>

    <!-- Modal para mostrar recibo -->
    <div id="reciboModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="cerrarRecibo()" title="Cerrar">&times;</button>
            <h3>Recibo de Retiro</h3>
            <div id="reciboContenido"></div>
            <button class="btn" onclick="cerrarRecibo()">Cerrar</button>
        </div>
    </div>

    <script src="../JS/retirar.js"></script>
</body>

</html>