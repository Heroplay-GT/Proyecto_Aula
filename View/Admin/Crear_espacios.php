<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkinGO - Configuración de Espacios</title>
    <link rel="stylesheet" href="../CSS/espacios.css">
</head>
<body>
    <div class="admin-container">
        <h1>Configuración de Espacios</h1>
        
        <div class="current-config">
            <h3>Configuración Actual</h3>
            <div id="configActual"></div>
        </div>
        
        <form id="configForm">
            <h3>Nueva Configuración</h3>
            
            <div class="form-group">
                <label for="carros">Espacios para Carros:</label>
                <input type="number" id="carros" name="carros" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="motos">Espacios para Motos:</label>
                <input type="number" id="motos" name="motos" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="bicicletas">Espacios para Bicicletas:</label>
                <input type="number" id="bicicletas" name="bicicletas" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="precioCarro">Precio por hora (Carros):</label>
                <input type="number" id="precioCarro" name="precioCarro" min="0" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="precioMoto">Precio por hora (Motos):</label>
                <input type="number" id="precioMoto" name="precioMoto" min="0" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="precioBici">Precio por hora (Bicicletas):</label>
                <input type="number" id="precioBici" name="precioBici" min="0" step="0.01" required>
            </div>
            
            <button type="submit" class="btn-save">Guardar Configuración</button>
        </form>
        
        <div class="espacios-list">
            <h3>Espacios Creados</h3>
            <table id="tablaEspacios">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Precio/Hora</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    
    <script src="../js/admin-espacios.js"></script>
</body>
</html>