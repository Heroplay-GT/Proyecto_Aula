<?php
class Reserva
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function getConexion()
    {
        return $this->conexion;
    }

    // ========== ESPACIOS ==========
    public function obtenerTodosLosEspacios()
    {
        $sql = "SELECT id, codigo, precio_hora, tipo_vehiculo, estado FROM espacios ORDER BY codigo";
        $result = $this->conexion->query($sql);
        if (!$result) {
            error_log("Error en consulta: " . $this->conexion->error);
            return false;
        }
        return $result;
    }

    public function obtenerEspaciosDisponibles($tipo_vehiculo)
    {
        $sql = "SELECT id, codigo, precio_hora, tipo_vehiculo FROM espacios 
                WHERE tipo_vehiculo = ? AND estado = 'Disponible' 
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
        return $stmt->get_result();
    }

    // ========== RESERVAS ==========
    public function obtenerReservasPorUsuario($usuario_id)
    {
        $sql = "SELECT r.*, e.codigo AS espacio_codigo 
                FROM reservas r 
                LEFT JOIN espacios e ON r.espacio_id = e.id 
                WHERE r.usuarios_id = ? 
                ORDER BY r.fecha_reserva DESC";
        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->conexion->error);
            return false;
        }
        $stmt->bind_param("i", $usuario_id);
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta: " . $stmt->error);
            return false;
        }
        return $stmt->get_result();
    }

    public function obtenerReservaPorId($reserva_id)
    {
        $sql = "SELECT r.*, e.codigo AS espacio_codigo 
                FROM reservas r 
                JOIN espacios e ON r.espacio_id = e.id 
                WHERE r.id = ?";
        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->conexion->error);
            return false;
        }
        $stmt->bind_param("i", $reserva_id);
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta: " . $stmt->error);
            return false;
        }
        return $stmt->get_result();
    }

    public function cancelarReserva($reserva_id)
    {
        $this->conexion->begin_transaction();
        try {
            $stmt = $this->conexion->prepare("SELECT espacio_id FROM reservas WHERE id = ?");
            $stmt->bind_param("i", $reserva_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $reserva = $result->fetch_assoc();

            $stmt = $this->conexion->prepare("UPDATE reservas SET estado = 'Cancelada' WHERE id = ?");
            $stmt->bind_param("i", $reserva_id);
            $stmt->execute();

            $stmt = $this->conexion->prepare("UPDATE espacios SET estado = 'Disponible' WHERE id = ?");
            $stmt->bind_param("i", $reserva['espacio_id']);
            $stmt->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            error_log("Error cancelando reserva: " . $e->getMessage());
            return false;
        }
    }

    // ========== INGRESO DE VEHÍCULO ==========
    public function ingresarVehiculo($placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id)
    {
        $this->conexion->begin_transaction();
        try {
            $stmtCheck = $this->conexion->prepare("SELECT estado FROM espacios WHERE id = ?");
            $stmtCheck->bind_param("i", $espacio_id);
            $stmtCheck->execute();
            $resultadoCheck = $stmtCheck->get_result();
            if ($resultadoCheck->num_rows === 0) {
                throw new Exception("El espacio no existe");
            }
            $espacio = $resultadoCheck->fetch_assoc();
            if ($espacio['estado'] !== 'Disponible') {
                throw new Exception("El espacio no está disponible");
            }

            $sql = "INSERT INTO reservas 
                    (placa, tipo, modelo, espacio_id, contacto, usuarios_id, estado, fecha_reserva) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', NOW())";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sssisi", $placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id);
            if (!$stmt->execute()) {
                throw new Exception("Error insertando reserva: " . $stmt->error);
            }

            $reserva_id = $this->conexion->insert_id;
            $qrFilename = $this->generarCodigoQR($reserva_id);
            if (!$qrFilename) {
                throw new Exception("Error generando el código QR");
            }

            $stmtUpdate = $this->conexion->prepare("UPDATE reservas SET qr_code = ? WHERE id = ?");
            $stmtUpdate->bind_param("si", $qrFilename, $reserva_id);
            $stmtUpdate->execute();

            $stmtOcupar = $this->conexion->prepare("UPDATE espacios SET estado = 'Ocupado' WHERE id = ?");
            $stmtOcupar->bind_param("i", $espacio_id);
            $stmtOcupar->execute();

            $this->conexion->commit();
            return $reserva_id;
        } catch (Exception $e) {
            $this->conexion->rollback();
            error_log("Error en ingresarVehiculo: " . $e->getMessage());
            return false;
        }
    }

    // ========== SALIDA DE VEHÍCULO ==========
    public function obtenerVehiculosActivos()
    {
        return $this->conexion->query("SELECT * FROM V_Ingresados WHERE estado = 'Activo' ORDER BY fecha_ingreso DESC");
    }

    public function retirarVehiculo($vehiculo_id, $minutos, $valor)
    {
        $this->conexion->begin_transaction();
        try {
            $vehiculo = $this->conexion->query("SELECT * FROM V_Ingresados WHERE id = $vehiculo_id")->fetch_assoc();

            $stmt = $this->conexion->prepare("INSERT INTO V_Retirados (vehiculo_id, placa, tipo, tiempo_estancia, valor_pagado) 
                                              VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issid", $vehiculo_id, $vehiculo['placa'], $vehiculo['tipo'], $minutos, $valor);
            $stmt->execute();

            $this->conexion->query("UPDATE V_Ingresados SET estado = 'Finalizado', fecha_salida = NOW() WHERE id = $vehiculo_id");
            $this->conexion->query("UPDATE espacios SET estado = 'Disponible' WHERE id = {$vehiculo['espacio_id']}");

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            error_log("Error retirando vehículo: " . $e->getMessage());
            return false;
        }
    }

    // ========== QR GENERATOR ==========
    public function generarCodigoQR($reserva_id)
{
    try {
        // Verificar que la librería está disponible
        if (!class_exists('Endroid\QrCode\Builder\Builder')) {
            throw new Exception("La librería QR Code no está instalada correctamente");
        }

        // Obtener datos de la reserva
        $stmt = $this->conexion->prepare("
            SELECT r.id, r.placa, r.tipo, r.modelo, r.contacto, r.fecha_reserva, r.estado, e.codigo AS espacio_codigo
            FROM reservas r
            JOIN espacios e ON r.espacio_id = e.id
            WHERE r.id = ?
        ");
        $stmt->bind_param("i", $reserva_id);
        if (!$stmt->execute()) {
            throw new Exception("Error consultando reserva: " . $stmt->error);
        }

        $reserva = $stmt->get_result()->fetch_assoc();
        if (!$reserva) {
            throw new Exception("Reserva no encontrada");
        }

        // Construir el texto para el QR
        $qrData = "http://localhost/PROYECTO_AULA//Controller/activar_reserva.php?id=" . $reserva_id . "\n";
        $qrData .= "Placa: " . $reserva['placa'] . "\n";
        $qrData .= "Tipo: " . $reserva['tipo'] . "\n";
        $qrData .= "Modelo: " . $reserva['modelo'] . "\n";
        $qrData .= "Espacio: " . $reserva['espacio_codigo'] . "\n";
        $qrData .= "Contacto: " . $reserva['contacto'] . "\n";
        $qrData .= "Fecha: " . $reserva['fecha_reserva'] . "\n";
        $qrData .= "Estado: " . $reserva['estado'];

        // Configurar rutas
        $qrFilename = "qr_" . $reserva_id . ".png";
        $qrDir = __DIR__ . "/../Media/QRCodes";
        $qrPath = $qrDir . "/" . $qrFilename;

        if (!file_exists($qrDir)) {
            if (!mkdir($qrDir, 0777, true)) {
                throw new Exception("No se pudo crear el directorio QRCodes");
            }
        }

        // Generar QR
        $qrCode = \Endroid\QrCode\Builder\Builder::create()
            ->data($qrData)
            ->size(200)
            ->margin(10)
            ->build();

        $qrCode->saveToFile($qrPath);

        // Guardar nombre del archivo en la base de datos
        $stmtUpdate = $this->conexion->prepare("UPDATE reservas SET qr_code = ? WHERE id = ?");
        $stmtUpdate->bind_param("si", $qrFilename, $reserva_id);
        if (!$stmtUpdate->execute()) {
            throw new Exception("Error guardando QR: " . $stmtUpdate->error);
        }

        return $qrFilename;
    } catch (Exception $e) {
        error_log("Error generando QR con información: " . $e->getMessage());
        return false;
    }
}

}
