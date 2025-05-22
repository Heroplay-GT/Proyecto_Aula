<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Cliente</title>
    <link rel="stylesheet" href="../CSS/reserva.css">
    <link rel="stylesheet" href="../CSS/configuracion.css">
</head>

<body>
    <div class="header-actions">
        <a href="Perfil.php" class="btn-regresar">
            <ion-icon name="chevron-back-circle-outline"></ion-icon>
            <span>Volver al perfil</span>
        </a>
    </div>
    
    <div class="vehiculo-wrapper">
        <h2>Configuración de tu cuenta</h2>

        <button class="btn" onclick="mostrarModal('modalDatos')">Actualizar Datos Personales</button>
        <button class="btn" onclick="mostrarModal('modalCorreo')">Actualizar Correo</button>
        <button class="btn" onclick="mostrarModal('modalContrasena')">Cambiar Contraseña</button>
        <button class="btn btn-cancelar" onclick="mostrarModal('modalEliminar')">Eliminar Cuenta</button>
    </div>

    <!-- MODAL DATOS -->
    <div class="modal" id="modalDatos">
        <div class="modal-content">
            <span class="close-modal" onclick="cerrarModal('modalDatos')">&times;</span>
            <h3>Actualizar Datos</h3>
            <form id="formActualizarDatos">
                <div class="input-box">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required value="">
                </div>

                <div class="input-box">
                    <label for="password">Contraseña actual:</label>
                    <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña actual" required>
                </div>

                <button type="submit" class="btn">Actualizar datos</button>
            </form>

        </div>
    </div>

    <!-- MODAL CORREO -->
    <div class="modal" id="modalCorreo">
        <div class="modal-content">
            <span class="close-modal" onclick="cerrarModal('modalCorreo')">&times;</span>
            <h3>Actualizar Correo</h3>
            <form id="formActualizarCorreo">
                <div class="input-box">
                    <label>Nuevo correo electrónico</label>

                    <input type="email" name="correo" required>
                </div>
                <button type="submit" class="btn">Guardar</button>
            </form>
        </div>
    </div>

    <!-- MODAL CONTRASEÑA -->
    <div class="modal" id="modalContrasena">
        <div class="modal-content">
            <span class="close-modal" onclick="cerrarModal('modalContrasena')">&times;</span>
            <h3>Cambiar Contraseña</h3>
            <form id="formCambiarContrasena">
                <div class="input-box">
                    <label>Contraseña actual</label>
                    <input type="password" name="actual" required>
                </div>
                <div class="input-box">
                    <label>Nueva contraseña</label>
                    <input type="password" name="nueva" required>
                </div>
                <div class="input-box">
                    <label>Confirmar contraseña</label>
                    <input type="password" name="confirmar" required>
                </div>
                <button type="submit" class="btn">Cambiar</button>
            </form>
        </div>
    </div>

    <!-- MODAL ELIMINAR -->
    <div class="modal" id="modalEliminar">
        <div class="modal-content">
            <span class="close-modal" onclick="cerrarModal('modalEliminar')">&times;</span>
            <h3>¿Eliminar tu cuenta?</h3>
            <p>¡Esta acción no se puede deshacer!</p>
            <button class="btn btn-cancelar" onclick="confirmarEliminacion()">Eliminar definitivamente</button>
        </div>
    </div>

    <script src="../JS/configuracion.js"></script>
</body>

</html>