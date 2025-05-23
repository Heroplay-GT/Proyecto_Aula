<?php
require_once __DIR__ . '/../conexion.php';

class Cliente
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        if (!$conexion || $conexion->connect_error) {
            throw new Exception("Error de conexión a la base de datos");
        }
        $this->conexion = $conexion;
    }

    public function obtenerClientes()
    {
        $sql = "SELECT username, email FROM usuarios";
        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $this->conexion->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
