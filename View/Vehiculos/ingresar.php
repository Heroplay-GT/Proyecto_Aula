<?php
$titulo = 'Ingresar Vehículo - Parking Go';
ob_start();
?>

<div class="wrapper active-popup">
    
    <div class="form-box login">
        <h2>Ingreso de Vehículo</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="/ingresarVehiculo" method="post">
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
            <p><a href="../cliente.php">Volver al inicio</a></p>
        </div>
    </div>
</div>

<?php
$contenido = ob_get_clean();
include '../Templates/layout.php';
