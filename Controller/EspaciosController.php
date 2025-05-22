<?php
require_once '../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Datos del formulario
$carros = intval($_POST['carros'] ?? 0);
$motos = intval($_POST['motos'] ?? 0);
$bicis = intval($_POST['bicicletas'] ?? 0);

$precioCarro = floatval($_POST['precioCarro'] ?? 0);
$precioMoto = floatval($_POST['precioMoto'] ?? 0);
$precioBici = floatval($_POST['precioBici'] ?? 0);

try {
    $conexion->begin_transaction();

    // Tipos
    $tipos = [
        'Carro' => ['letra' => 'A', 'cantidad' => $carros, 'precio' => $precioCarro],
        'Moto' => ['letra' => 'B', 'cantidad' => $motos, 'precio' => $precioMoto],
        'Bicicleta' => ['letra' => 'C', 'cantidad' => $bicis, 'precio' => $precioBici]
    ];

    foreach ($tipos as $tipo => $info) {
        // Ver cuántos hay ya
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM espacios WHERE tipo_vehiculo = ?");
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $existentes = intval($result['total']);

        // Insertar si faltan
        for ($i = $existentes + 1; $i <= $info['cantidad']; $i++) {
            $codigo = $info['letra'] . $i;
            $stmtInsert = $conexion->prepare("INSERT INTO espacios (codigo, tipo_vehiculo, estado, precio_hora) VALUES (?, ?, 'Disponible', ?)");
            $stmtInsert->bind_param("ssd", $codigo, $tipo, $info['precio']);
            $stmtInsert->execute();
        }

        // Actualizar precio para todos
        $stmtUpdate = $conexion->prepare("UPDATE espacios SET precio_hora = ? WHERE tipo_vehiculo = ?");
        $stmtUpdate->bind_param("ds", $info['precio'], $tipo);
        $stmtUpdate->execute();
    }

    $conexion->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['success' => false, 'error' => 'Error al guardar la configuración']);
}
