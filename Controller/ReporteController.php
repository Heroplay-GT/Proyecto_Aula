<?php
session_start(); // Asegúrate de que esté
require_once '../conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inicio = $_POST['fecha_inicio'] ?? '';
    $fin = $_POST['fecha_fin'] ?? '';

    if (!$inicio || !$fin) {
        echo json_encode(['success' => false, 'error' => 'Fechas incompletas']);
        exit;
    }

    // Consulta desde la tabla v_retirados
    $stmt = $conexion->prepare("
        SELECT espacio, tipo_vehiculo, fecha_ingreso, fecha_salida, duracion, valor_pagado 
        FROM v_retirados 
        WHERE fecha_salida BETWEEN ? AND ?
        ORDER BY fecha_salida ASC
    ");

    $stmt->bind_param("ss", $inicio, $fin);
    $stmt->execute();
    $result = $stmt->get_result();

    $datos = [];
    while ($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $datos]);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
