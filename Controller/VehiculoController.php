<?php
require_once 'models/VehiculoModel.php';

class VehiculoController {

    public function mostrarFormulario() {
        include '../View/Vehiculos/ingresar.php';
    }

    public function registrar() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $placa = trim($_POST['placa']);
            $tipo = trim($_POST['tipo']);

            if (empty($placa) || empty($tipo)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                $modelo = new VehiculoModel();
                if ($modelo->insertar($placa, $tipo)) {
                    $success = "Vehículo registrado exitosamente.";
                } else {
                    $error = "Error al registrar el vehículo. Puede que la placa ya exista.";
                }
            }
        }

        include 'views/formulario.php';
    }
}
