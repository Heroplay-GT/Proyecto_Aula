<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkinGO - Reserva</title>
    <link rel="stylesheet" href="../CSS/reserva.css">
</head>

<body>
    <div class="vehiculo-wrapper">
        <h2>Reservar Espacio</h2>

        <form id="reservaForm" method="POST" action="../../Controller/VehiculoController.php" class="vehiculo-form">
            <div class="form-row">
                <div class="input-box">
                    <span class="icon"><ion-icon name="car"></ion-icon></span>
                    <input type="text" name="placa" required maxlength="10">
                    <label>Placa del vehículo</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="call"></ion-icon></span>
                    <input type="text" name="contacto" required>
                    <label>Número de contacto</label>
                </div>
            </div>

            <div class="form-row">
                <div class="input-box">
                    <span class="icon"></span>
                    <select name="tipo_vehiculo" id="tipoVehiculo" required>
                        <option value="" disabled selected hidden></option>
                        <option value="Carro">Carro</option>
                        <option value="Moto">Moto</option>
                        <option value="Bicicleta">Bicicleta</option>
                    </select>
                    <label>Tipo de vehículo</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="construct"></ion-icon></span>
                    <input type="text" name="modelo" required>
                    <label>Modelo del vehículo</label>
                </div>
            </div>

            <div class="form-row">
                <div class="input-box">
                    <span class="icon"><ion-icon name="cash"></ion-icon></span>
                    <input type="text" id="precio" name="precio" readonly>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="grid"></ion-icon></span>
                    <input type="text" name="espacio" required>
                    <label>Espacio a utilizar</label>
                </div>
            </div>

            <button type="button" id="verEspacios" class="btn">Ver Espacios</button>

            <div id="tablasEspacios" class="espacios-tabla">
                <!-- Aquí se mostrarán las tablas de espacios -->
            </div>

            <div id="error-message" class="error-message"></div>
            <div id="success-message" class="success-message"></div>

            <!-- Mostrar mensajes de error/éxito -->
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php
                    switch ($_GET['error']) {
                        case 'campos_vacios':
                            echo "Todos los campos son obligatorios";
                            break;
                        case 'bd':
                            echo "Error al guardar en la base de datos";
                            break;
                        default:
                            echo "Ocurrió un error";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    ¡Reserva realizada con éxito!
                </div>
            <?php endif; ?>

            <button type="submit" class="btn">Reservar</button>
        </form>
    </div>

    <script src="../JS/reserva.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>