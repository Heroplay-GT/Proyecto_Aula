<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../Model/cliente.php';

    $clienteModel = new Cliente();
    $action = $_GET['action'] ?? '';

    if ($action === 'listar') {
        $clientes = $clienteModel->obtenerClientes();
        echo json_encode([
            'success' => true,
            'data' => $clientes,
            'count' => count($clientes)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Acción no válida'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
