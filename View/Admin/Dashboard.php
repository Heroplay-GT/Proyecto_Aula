<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - ParkinGO</title>
  <link rel="stylesheet" href="../CSS/dashboard.css" />
</head>

<body>
  <section>
    <!-- Menú lateral -->
    <div class="nav">
      <ul>
        <li>
          <a href="#">
            <span class="icono"><ion-icon name="aperture"></ion-icon></span>
            <span class="titulo">ParkinGO</span>
          </a>
        </li>
        <li>
          <a href="#">
            <span class="icono"><ion-icon name="person-outline"></ion-icon></span>
            <span class="titulo">Clientes</span>
          </a>
        </li>
        <li>
          <a href="reportes.php">
            <span class="icono"><ion-icon name="document-text-outline"></ion-icon></span>
            <span class="titulo">Reporte</span>
          </a>
        </li>
        <li>
          <a href="../Admin/Insertar.php">
            <span class="icono"><ion-icon name="add"></ion-icon></span>
            <span class="titulo">Ingreso</span>
          </a>
        </li>
        <li>
          <a href="retirar.php">
            <span class="icono"><ion-icon name="trash-outline"></ion-icon></span>
            <span class="titulo">Retirar</span>
          </a>
        </li>
        <li>
          <a href="Crear_espacios.php">
            <span class="icono"><ion-icon name="settings-outline"></ion-icon></span>
            <span class="titulo">Configuración Espacios</span>
          </a>
        </li>
        <li>
          <a href="../../Controller/logout.php">
            <span class="icono"><ion-icon name="log-out-outline"></ion-icon></span>
            <span class="titulo">Cerrar Sesión</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Contenedor principal -->
    <div class="container">
      <div class="topbar">
        <div class="toggle">
          <ion-icon name="menu"></ion-icon>
        </div>
        <div class="perfil-usuario">
          <img src="../../Media/perfil.jpg" alt="Perfil" />
        </div>
      </div>

      <div class="detalles">
        <h2>Vista del Parqueadero</h2>

        <div id="resumenTotales" class="resumen-totales"
          style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;"></div>

        <div class="filtro-tipo">
          <label for="filtroTipo">Filtrar por tipo:</label>
          <select id="filtroTipo">
            <option value="Todos">Todos</option>
            <option value="Carro">Carros</option>
            <option value="Moto">Motos</option>
            <option value="Bicicleta">Bicicletas</option>
          </select>
        </div>

        <div class="espacios-container" id="vistaParqueadero"></div>

        <!-- Modal de detalles de espacio -->
        <div id="modalEspacio" class="modal">
          <div class="modal-content">
            <span class="close-modal" onclick="cerrarModalEspacio()">&times;</span>
            <div id="modalEspacioContenido"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Iconos Ionicons -->
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script src="../JS/dashboard.js"></script>
</body>

</html>