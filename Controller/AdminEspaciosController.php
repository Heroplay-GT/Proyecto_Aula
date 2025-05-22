<?php
require_once '../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carros = intval($_POST['carros'] ?? 0);
    $motos = intval($_POST['motos'] ?? 0);
    $bicicletas = intval($_POST['bicicletas'] ?? 0);

    $precioCarro = floatval($_POST['precioCarro'] ?? 0);
    $precioMoto = floatval($_POST['precioMoto'] ?? 0);
    $precioBici = floatval($_POST['precioBici'] ?? 0);

    $conexion->begin_transaction();
    try {
        // Eliminar espacios anteriores
        $conexion->query("DELETE FROM espacios");

        // Generar espacios de carros
        for ($i = 1; $i <= $carros; $i++) {
            $codigo = 'A' . $i;
            $stmt = $conexion->prepare("INSERT INTO espacios (codigo, tipo_vehiculo, estado, precio_hora) VALUES (?, 'Carro', 'Disponible', ?)");
            $stmt->bind_param("sd", $codigo, $precioCarro);
            $stmt->execute();
        }

        // Generar espacios de motos
        for ($i = 1; $i <= $motos; $i++) {
            $codigo = 'B' . $i;
            $stmt = $conexion->prepare("INSERT INTO espacios (codigo, tipo_vehiculo, estado, precio_hora) VALUES (?, 'Moto', 'Disponible', ?)");
            $stmt->bind_param("sd", $codigo, $precioMoto);
            $stmt->execute();
        }

        // Generar espacios de bicicletas
        for ($i = 1; $i <= $bicicletas; $i++) {
            $codigo = 'C' . $i;
            $stmt = $conexion->prepare("INSERT INTO espacios (codigo, tipo_vehiculo, estado, precio_hora) VALUES (?, 'Bicicleta', 'Disponible', ?)");
            $stmt->bind_param("sd", $codigo, $precioBici);
            $stmt->execute();
        }

        $conexion->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
    }
    exit;
}

// Obtener espacios existentes
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conexion->query("SELECT * FROM espacios ORDER BY tipo_vehiculo, codigo");
    $espacios = [];
    while ($row = $result->fetch_assoc()) {
        $espacios[] = $row;
    }
    echo json_encode($espacios);
    exit;
}
