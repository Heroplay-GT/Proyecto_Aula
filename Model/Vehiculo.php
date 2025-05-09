<?php
class Vehiculo {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function insertar($placa, $tipo, $usuario_id) {
        $sql = "INSERT INTO vehiculos (placa, tipo, usuario_id) VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ssi", $placa, $tipo, $usuario_id);
        return $stmt->execute();
    }
}
