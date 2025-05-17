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

    public function obtenerTodosLosEspacios()
    {
        $sql = "SELECT e.id, e.codigo, e.precio_hora, e.tipo_vehiculo, e.estado
            FROM espacios e
            ORDER BY e.codigo";

        $result = $this->conexion->query($sql);
        if (!$result) {
            error_log("Error en consulta: " . $this->conexion->error);
            return false;
        }

        return $result;
    }

    public function obtenerEspaciosDisponibles($tipo_vehiculo)
    {
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

        return $stmt->get_result();
    }

    // Registrar nuevo vehículo ingresado
    public function ingresarVehiculo($placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id)
    {
        $this->conexion->begin_transaction();
        try {
            // 1. Verificar que el espacio esté disponible
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

            // 2. Insertar en reservas (primero sin QR)
            $sql = "INSERT INTO reservas 
                (placa, tipo, modelo, espacio_id, contacto, usuarios_id, estado, fecha_reserva) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', NOW())";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sssisi", $placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id);

            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando consulta: " . $stmt->error);
            }

            // Obtener ID de la reserva recién creada
            $reserva_id = $this->conexion->insert_id;

            // 3. Generar QR con ID real
            $qrFilename = "qr_" . $reserva_id . ".png";
            $this->generarCodigoQR($reserva_id); // Genera el QR con el ID

            // 4. Actualizar la reserva con el nombre del QR
            $stmtUpdate = $this->conexion->prepare("UPDATE reservas SET qr_code = ? WHERE id = ?");
            $stmtUpdate->bind_param("si", $qrFilename, $reserva_id);
            if (!$stmtUpdate->execute()) {
                throw new Exception("Error actualizando QR en reserva: " . $stmtUpdate->error);
            }

            // 5. Ocupar el espacio
            $stmtOcupar = $this->conexion->prepare("UPDATE espacios SET estado = 'Ocupado' WHERE id = ?");
            $stmtOcupar->bind_param("i", $espacio_id);
            if (!$stmtOcupar->execute()) {
                throw new Exception("Error ocupando espacio: " . $stmtOcupar->error);
            }

            $this->conexion->commit();
            return $reserva_id;
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

    public function generarCodigoQR($reserva_id)
    {
        try {
            // Verificar que la librería está disponible
            if (!class_exists('Endroid\QrCode\QrCode')) {
                throw new Exception("La librería QR Code no está instalada correctamente");
            }

            // Configurar rutas y datos
            $qrData = "RESERVA-" . $reserva_id . "-" . md5(uniqid());
            $qrFilename = "qr_" . $reserva_id . ".png";
            $qrDir = __DIR__ . "/../Media/QRCodes";
            $qrPath = $qrDir . "/" . $qrFilename;

            // Crear directorio si no existe
            if (!file_exists($qrDir)) {
                if (!mkdir($qrDir, 0777, true)) {
                    throw new Exception("No se pudo crear el directorio QRCodes. Verifica permisos.");
                }
            }

            // Configuración del QR - Versión 5.x
            $qrCode = \Endroid\QrCode\QrCode::create($qrData)
                ->setSize(300)
                ->setMargin(10)
                ->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::High);

            // Guardar el QR
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);
            $result->saveToFile($qrPath);

            // Guardar en base de datos
            $stmt = $this->conexion->prepare("UPDATE reservas SET qr_code = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Error preparando consulta: " . $this->conexion->error);
            }

            $stmt->bind_param("si", $qrFilename, $reserva_id);
            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando consulta: " . $stmt->error);
            }

            return $qrFilename;
        } catch (Exception $e) {
            error_log("Error generando QR: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerReservasPorUsuario($usuario_id)
    {
        $sql = "SELECT r.*, e.codigo as espacio_codigo 
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
        $sql = "SELECT r.*, e.codigo as espacio_codigo 
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
            // 1. Obtener espacio_id de la reserva
            $stmt = $this->conexion->prepare("SELECT espacio_id FROM reservas WHERE id = ?");
            $stmt->bind_param("i", $reserva_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $reserva = $result->fetch_assoc();

            // 2. Actualizar estado de la reserva
            $stmt = $this->conexion->prepare("UPDATE reservas SET estado = 'Cancelada' WHERE id = ?");
            $stmt->bind_param("i", $reserva_id);
            $stmt->execute();

            // 3. Liberar espacio
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
}
