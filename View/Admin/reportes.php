<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reporte Mensual - ParkinGO</title>
    <link rel="stylesheet" href="../CSS/reporte.css">

</head>

<body>
    <div class="header-actions">
        <a href="Dashboard.php" class="btn-regresar">
            <ion-icon name="chevron-back-circle-outline"></ion-icon>
            <span>Volver al inicio</span>
        </a>
    </div>

    <div class="reporte-container">
        <h2>Reporte Mensual de Ingresos</h2>

        <div class="reporte-header">
            <label for="mes">Mes:</label>
            <select id="mes">
                <option value="01">Enero</option>
                <option value="02">Febrero</option>
                <option value="03">Marzo</option>
                <option value="04">Abril</option>
                <option value="05">Mayo</option>
                <option value="06">Junio</option>
                <option value="07">Julio</option>
                <option value="08">Agosto</option>
                <option value="09">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>

            <label for="anio">Año:</label>
            <select id="anio">
                <!-- Se puede rellenar dinámicamente con JS -->
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
            </select>

            <button onclick="consultarReporte()">Consultar</button>
            <button onclick="window.print()">Imprimir</button>
            <button onclick="descargarPDF()">Descargar PDF</button>
        </div>

        <table id="tablaReporte">
            <thead>
                <tr>
                    <th>Espacio</th>
                    <th>Tipo</th>
                    <th>Ingreso</th>
                    <th>Salida</th>
                    <th>Duración</th>
                    <th>Pago</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aquí se agregan los datos dinámicamente -->
            </tbody>
        </table>

        <div class="total-recaudado" id="totalRecaudado">
            Total recaudado: $0 COP
        </div>
    </div>

    <script src="../JS/reportes.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

</body>

</html>