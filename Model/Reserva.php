<?php
class Reserva {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function insertar($placa, $tipo, $modelo, $espacio, $precio, $usuario_id) {
        $sql = "INSERT INTO reservas (placa, tipo, modelo, espacio, precio, usuarios_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ssssdi", $placa, $tipo, $modelo, $espacio, $precio, $usuario_id);
        return $stmt->execute();
    }
}