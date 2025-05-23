<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkinGO</title>
    <link rel="stylesheet" href="../View/CSS/style.css">
</head>

<body>

    <header>
        <h2 class="logo">ParkinGO</h2>
        <nav class="navigation">
            <a href="#">Inicio</a>
            <a href="#">Servicios</a>
            <a href="#" class="contact-link">Contacto</a>

            <button class="btnLogin-popup">Iniciar sesión</button>
        </nav>
    </header>

    <!--Formulario de inicio de sesión-->
    <div class="wrapper">
        <span class="icon-close">
            <ion-icon name="close"></ion-icon>
        </span>
        <div class="form-box login">
            <h2>Iniciar sesión</h2>
            <form action="../Controller/login.php" method="POST">
                <div class="input-box">
                    <span class="icon"><ion-icon name="person"></ion-icon></span>
                    <input type="text" name="username" required>
                    <label>Nombre de usuario</label>
                </div>

                <div class="input-box">
                    <input type="password" name="password" id="loginPassword" required>
                    <label>Contraseña</label>
                    <span class="toggle-password" data-target="loginPassword">
                        <ion-icon name="eye-off-outline"></ion-icon>
                    </span>
                </div>
                <div class="remember-forgot">
                    <a href="Auth/recovery.php">¿Olvidaste tu contraseña?</a>
                </div>
                <button type="submit" class="btn">Iniciar sesión</button>
                <div class="login-register">
                    <p>¿No tienes una cuenta? <a href="#" class="register-link">Regístrate</a></p>
                </div>
            </form>
        </div>

        <!--Formulario de registro-->
        <div class="form-box register">
            <h2>Registro</h2>
            <form action="../Controller/registro.php" method="POST">
                <div class="input-box">
                    <span class="icon"><ion-icon name="person"></ion-icon></span>
                    <input type="text" name="username" required>
                    <label>Nombre de usuario</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="email" name="email" required>
                    <label>Correo electrónico</label>
                </div>

                <div class="input-box">
                    <input type="password" name="password" id="registerPassword" required>
                    <label>Contraseña</label>
                    <span class="toggle-password" data-target="registerPassword">
                        <ion-icon name="eye-off-outline"></ion-icon>
                    </span>
                </div>

                <div class="remember-forgot">
                    <label><input type="checkbox" name="agree"> Acepto los términos y condiciones</label>
                </div>
                <button type="submit" class="btn">Registrarse</button>
                <div class="login-register">
                    <p>¿Ya tienes una cuenta? <a href="#" class="login-link">Iniciar sesión</a></p>
                </div>
            </form>
        </div>

        <!-- contacto -->
        <div class="form-box contacto">
            <h2>Contáctame</h2>
            <p style="text-align: center; margin-bottom: 20px;">¿Tienes dudas? Escríbeme por WhatsApp</p>
            <div class="input-box" style="justify-content: center;">
                <span class="icon"><ion-icon name="logo-whatsapp"></ion-icon></span>
                <a class="btn" style="text-align: center;" target="_blank"
                    href="https://wa.me/573009048312">Enviar mensaje</a>
            </div>
        </div>

    </div>

    <script src="../View/JS/script.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>


    <footer>

        <p>© 2025 ParkinGo. Todos los derechos reservados.</p>

    </footer>
</body>

</html>