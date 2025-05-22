<?php
require_once '../conexion.php';

header('Content-Type: application/json');

if (isset($_GET['action']) && $_GET['action'] === 'eliminar_todo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexion->query("DELETE FROM reservas");
        $conexion->query("DELETE FROM espacios");
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error al eliminar: ' . $e->getMessage()]);
    }
    exit;
}

// Configuración actual (cantidad y precios)
if (isset($_GET['action']) && $_GET['action'] === 'config_actual') {
    $carros = $conexion->query("SELECT COUNT(*) AS total, MAX(precio_hora) AS precio FROM espacios WHERE tipo_vehiculo = 'Carro'")->fetch_assoc();
    $motos = $conexion->query("SELECT COUNT(*) AS total, MAX(precio_hora) AS precio FROM espacios WHERE tipo_vehiculo = 'Moto'")->fetch_assoc();
    $bicis = $conexion->query("SELECT COUNT(*) AS total, MAX(precio_hora) AS precio FROM espacios WHERE tipo_vehiculo = 'Bicicleta'")->fetch_assoc();

    echo json_encode([
        'carros' => $carros['total'],
        'precioCarro' => $carros['precio'],
        'motos' => $motos['total'],
        'precioMoto' => $motos['precio'],
        'bicicletas' => $bicis['total'],
        'precioBici' => $bicis['precio'],
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carros = intval($_POST['carros']);
    $motos = intval($_POST['motos']);
    $bicicletas = intval($_POST['bicicletas']);

    $precioCarro = floatval($_POST['precioCarro']);
    $precioMoto = floatval($_POST['precioMoto']);
    $precioBici = floatval($_POST['precioBici']);

    $conexion->begin_transaction();
    try {
        // 1. Eliminar todos los espacios existentes
        $conexion->query("DELETE FROM v_ingresados");
        $conexion->query("DELETE FROM reservas");
        $conexion->query("DELETE FROM espacios");

        $contador = 1;
        for ($i = 1; $i <= $carros; $i++) {
            $codigo = "A" . $i;
            $stmt = $conexion->prepare("INSERT INTO espacios (codigo, tipo_vehiculo, estado, precio_hora) VALUES (?, 'Carro', 'Disponible', ?)");
            $stmt->bind_param("sd", $codigo, $precioCarro);
            $stmt->execute();
        }
        for ($i = 1; $i <= $motos; $i++) {
            $codigo = "B" . $i;
            $stmt = $conexion->prepare("INSERT INTO espacios (codigo, tipo_vehiculo, estado, precio_hora) VALUES (?, 'Moto', 'Disponible', ?)");
            $stmt->bind_param("sd", $codigo, $precioMoto);
            $stmt->execute();
        }
        for ($i = 1; $i <= $bicicletas; $i++) {
            $codigo = "C" . $i;
            $stmt = $conexion->prepare("INSERT INTO espacios (codigo, tipo_vehiculo, estado, precio_hora) VALUES (?, 'Bicicleta', 'Disponible', ?)");
            $stmt->bind_param("sd", $codigo, $precioBici);
            $stmt->execute();
        }

        $conexion->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }

    exit;
}

// Obtener todos los espacios (para mostrar en tabla)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $espacios = $conexion->query("SELECT * FROM espacios ORDER BY codigo ASC");
    $datos = [];
    while ($row = $espacios->fetch_assoc()) {
        $datos[] = $row;
    }
    echo json_encode($datos);
    exit;
}
