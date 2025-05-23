<?php
require_once '../conexion.php';

$placa = $_GET['placa'] ?? '';

$query = "SELECT vi.id, vi.placa, vi.tipo, vi.modelo, e.codigo as espacio, 
          DATE_FORMAT(vi.fecha_ingreso, '%Y-%m-%d %H:%i:%s') as fecha_ingreso
          FROM v_ingresados vi
          JOIN espacios e ON vi.espacio_id = e.id
          WHERE vi.estado = 'Activo'";

if (!empty($placa)) {
    $query .= " AND vi.placa LIKE ?";
    $stmt = $conexion->prepare($query);
    $placaParam = "%$placa%";
    $stmt->bind_param("s", $placaParam);
} else {
    $stmt = $conexion->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
$vehiculos = [];

while ($row = $result->fetch_assoc()) {
    $vehiculos[] = $row;
}

echo json_encode($vehiculos);
