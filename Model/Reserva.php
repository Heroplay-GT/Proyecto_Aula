<?php
class Reserva
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerEspaciosDisponibles($tipo_vehiculo)
    {
        // Validación adicional
        if (empty($tipo_vehiculo)) {
            error_log("Tipo de vehículo vacío");
            return false;
        }

        $sql = "SELECT id, codigo, precio_hora, tipo_vehiculo 
            FROM espacios 
            WHERE tipo_vehiculo = ? 
            AND estado = 'Disponible'
            ORDER BY codigo";

        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->conexion->error);
            return false;
        }

        $stmt->bind_param("s", $tipo_vehiculo);

        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta: " . $stmt->error);
            return false;
        }

        $resultado = $stmt->get_result();

        // Debug: Registrar cantidad de resultados
        error_log("Espacios encontrados para {$tipo_vehiculo}: " . $resultado->num_rows);

        return $resultado;
    }

    // Registrar nuevo vehículo ingresado
    public function ingresarVehiculo($placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id)
    {
        $this->conexion->begin_transaction();
        try {
            // 1. Verificar que el espacio esté disponible
            $check = $this->conexion->query("SELECT estado FROM espacios WHERE id = $espacio_id");
            if ($check->fetch_assoc()['estado'] !== 'Disponible') {
                throw new Exception("El espacio no está disponible");
            }

            // 2. Insertar en reservas (corregido espacio_id)
            $sql = "INSERT INTO reservas 
                (placa, tipo, modelo, espacio_id, contacto, usuarios_id, estado)
                VALUES (?, ?, ?, ?, ?, ?, 'Activo')";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sssisi", $placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id);
            $stmt->execute();

            // 3. Ocupar espacio
            $this->conexion->query("UPDATE espacios SET estado = 'Ocupado' WHERE id = $espacio_id");

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            error_log("Error en ingresarVehiculo: " . $e->getMessage());
            return false;
        }
    }
    // Obtener vehículos activos
    public function obtenerVehiculosActivos()
    {
        return $this->conexion->query(
            "SELECT * FROM V_Ingresados WHERE estado = 'Activo' ORDER BY fecha_ingreso DESC"
        );
    }

    // Registrar retiro
    public function retirarVehiculo($vehiculo_id, $minutos, $valor)
    {
        $this->conexion->begin_transaction();
        try {
            // 1. Obtener datos del vehículo
            $vehiculo = $this->conexion->query(
                "SELECT * FROM V_Ingresados WHERE id = $vehiculo_id"
            )->fetch_assoc();

            // 2. Insertar en V_Retirados
            $sql = "INSERT INTO V_Retirados 
                    (vehiculo_id, placa, tipo, tiempo_estancia, valor_pagado)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param(
                "issid",
                $vehiculo_id,
                $vehiculo['placa'],
                $vehiculo['tipo'],
                $minutos,
                $valor
            );
            $stmt->execute();

            // 3. Actualizar V_Ingresados
            $this->conexion->query(
                "UPDATE V_Ingresados SET 
                 estado = 'Finalizado',
                 fecha_salida = NOW()
                 WHERE id = $vehiculo_id"
            );

            // 4. Liberar espacio
            $this->conexion->query(
                "UPDATE espacios SET estado = 'Disponible' 
                 WHERE id = {$vehiculo['espacio_id']}"
            );

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            return false;
        }
    }
}
