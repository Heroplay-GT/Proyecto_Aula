<?php
require_once 'controllers/VehiculoController.php';

$action = $_GET['action'] ?? 'formulario';
$controller = new VehiculoController();

if ($action === 'registrar') {
    $controller->registrar();
} else {
    $controller->mostrarFormulario();
}
