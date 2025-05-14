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
        <form id="reservaForm" method="POST" action="../Controller/ReservarController.php" class="vehiculo-form">
            
            <div class="form-row">
                <div class="input-box">
                    <span class="icon"><ion-icon name="person"></ion-icon></span>
                    <input type="text" name="nombre" required>
                    <label>Nombre completo</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="call"></ion-icon></span>
                    <input type="text" name="numero" required>
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
                    <input type="text" name="espacio"  required>
                    <label>Espacio a utilizar</label>
                </div>
            </div>
            
            <button type="button" id="verEspacios" class="btn">Ver Espacios</button>
            
            <div id="tablasEspacios" class="espacios-tabla">
                <!-- Mensaje de error aparecerá aquí -->
                <div class="error-message">Error al cargar espacios.</div>
            </div>
            
            <button type="submit" class="btn">Reservar</button>
        </form>
    </div>
    
    <script src="../JS/reserva.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>