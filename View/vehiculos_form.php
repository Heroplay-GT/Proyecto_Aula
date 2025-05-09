<?php
$titulo = 'Ingresar Vehículo - Parking Go';
ob_start();

$success = isset($_GET['success']);
$error = isset($_GET['error']);
?>

<div class="wrapper active-popup">
    <div class="form-box login">
        <h2>Ingreso de Vehículo</h2>

        <?php if ($error): ?>
            <div class="error-message">Error al registrar el vehículo</div>
        <?php elseif ($success): ?>
            <div class="success-message">Vehículo registrado exitosamente</div>
        <?php endif; ?>

        <form method="POST" action="../Controller/VehiculoController.php">
            <div class="input-box">
                <span class="icon"><i class="fas fa-car"></i></span>
                <input type="text" name="placa" required>
                <label>Placa del vehículo</label>
            </div>

            <div class="input-box">
                <span class="icon"><i class="fas fa-car-side"></i></span>
                <select name="tipo" required>
                    <option value="">Selecciona tipo</option>
                    <option value="Carro">Carro</option>
                    <option value="Moto">Moto</option>
                    <option value="Bicicleta">Bicicleta</option>
                </select>
            </div>

            <button type="submit" class="btn">Registrar</button>
        </form>

        <div class="login-register">
            <p><a href="cliente.php">Volver al inicio</a></p>
        </div>
    </div>
</div>

<?php
$contenido = ob_get_clean();
include '../View/Templates/layout.php';
