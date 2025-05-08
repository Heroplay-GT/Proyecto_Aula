<?php
require_once '../model/Vehiculo.php';

class IngresoController {
    public function manejarIngreso() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $placa = trim($_POST['placa'] ?? '');
            $tipo = $_POST['tipo'] ?? '';

            $vehiculo = new Vehiculo($placa, $tipo);

            if (!$vehiculo->esValido()) {
                $error = "Todos los campos son obligatorios.";
                include './view/ingresar.php';
                return;
            }

            // Aquí se insertaría en la BD. Simulamos éxito:
            $exito = true;

            if ($exito) {
                $mensaje = "Vehículo ingresado correctamente.";
            } else {
                $error = "Error al ingresar el vehículo.";
            }

            include './view/ingresar.php';
        } else {
            include './view/ingresar.php';
        }
    }
}
