<?php
require_once '../conexion.php';

header('Content-Type: application/json');

try {
    $tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';

    // Validar tipo de vehículo
    $tiposPermitidos = ['Carro', 'Moto', 'Bicicleta'];
    if (!empty($tipo) && !in_array($tipo, $tiposPermitidos)) {
        throw new Exception("Tipo de vehículo no válido");
    }

    $sql = "SELECT id, codigo, tipo_vehiculo, estado, precio_hora 
            FROM espacios 
            WHERE estado = 'Disponible'";

    if (!empty($tipo)) {
        $sql .= " AND tipo_vehiculo = ?";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $conexion->error);
        }
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql .= " ORDER BY tipo_vehiculo, codigo";
        $result = $conexion->query($sql);
        if (!$result) {
            throw new Exception("Error en la consulta: " . $conexion->error);
        }
    }

    $espacios = [];
    while ($row = $result->fetch_assoc()) {
        $espacios[] = $row;
    }

    echo json_encode($espacios);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conexion->close();
}
