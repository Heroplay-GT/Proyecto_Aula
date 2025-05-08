<?php

class VehiculoModel {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "parkingo");
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
    }

    public function insertar($placa, $tipo) {
        $stmt = $this->conn->prepare("INSERT INTO vehiculos (placa, tipo) VALUES (?, ?)");
        $stmt->bind_param("ss", $placa, $tipo);
        return $stmt->execute();
    }
}
