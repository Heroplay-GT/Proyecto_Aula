<?php
require_once __DIR__ . '/../vendor/autoload.php';

class Reserva
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
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

            // Verificar si ya existe una reserva activa o pendiente para esa placa
            $stmtCheckPlaca = $this->conexion->prepare("
                SELECT id FROM reservas 
                WHERE placa = ? AND estado IN ('Pendiente', 'Activo')
            ");
            $stmtCheckPlaca->bind_param("s", $placa);
            $stmtCheckPlaca->execute();
            $resultCheckPlaca = $stmtCheckPlaca->get_result();

            if ($resultCheckPlaca->num_rows > 0) {
                throw new Exception("Ya existe una reserva activa o pendiente con esta placa.");
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

    //============== Generar QR desde Admin =================
    public function generarQRparaAdmin($vehiculo_id)
    {
        try {
            $qrData = "http://localhost/Proyecto_Aula/Controller/retirar_vehiculo.php?id=" . $vehiculo_id;
            $qrFilename = "admin_qr_" . $vehiculo_id . ".png";
            $qrDir = __DIR__ . '/../Media/QRCodes';
            $qrPath = $qrDir . '/' . $qrFilename;

            if (!file_exists($qrDir)) {
                if (!mkdir($qrDir, 0777, true)) {
                    throw new Exception("No se pudo crear el directorio QRCodes");
                }
            }

            if (!class_exists('Endroid\QrCode\QrCode')) {
                throw new Exception("Librería QR no disponible");
            }

            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $qrCode = \Endroid\QrCode\QrCode::create($qrData)
                ->setSize(200)
                ->setMargin(10);
            $result = $writer->write($qrCode);
            $result->saveToFile($qrPath);

            // Guardar en base de datos
            $stmt = $this->conexion->prepare("UPDATE v_ingresados SET qr_code = ? WHERE id = ?");
            $stmt->bind_param("si", $qrFilename, $vehiculo_id);
            if (!$stmt->execute()) {
                throw new Exception("Error guardando QR: " . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error generando QR Admin: " . $e->getMessage());
            echo 'ID correcto: ' . $vehiculo_id;
            return false;
        }
    }

    //============ Ingresar Vehiculo Desde ADmin ============
    public function ingresarVehiculoDesdeAdmin($placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id)
    {
        $this->conexion->begin_transaction();
        try {
            // Validar espacio disponible
            $stmt = $this->conexion->prepare("SELECT estado FROM espacios WHERE id = ?");
            $stmt->bind_param("i", $espacio_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("El espacio no existe");
            }

            $espacio = $result->fetch_assoc();
            if ($espacio['estado'] !== 'Disponible') {
                throw new Exception("El espacio no está disponible");
            }

            // Verificar placa activa
            $stmtCheck = $this->conexion->prepare("
            SELECT id FROM v_ingresados WHERE placa = ? AND estado = 'Activo'
        ");
            $stmtCheck->bind_param("s", $placa);
            $stmtCheck->execute();
            $check = $stmtCheck->get_result();
            if ($check->num_rows > 0) {
                throw new Exception("La placa ya tiene un ingreso activo.");
            }

            // Insertar en v_ingresados
            $stmtInsert = $this->conexion->prepare("
            INSERT INTO v_ingresados (placa, tipo, modelo, espacio_id, contacto, usuarios_id, fecha_ingreso, estado)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Activo')
        ");
            $stmtInsert->bind_param("sssisi", $placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id);
            $stmtInsert->execute();

            $vehiculo_id = $this->conexion->insert_id;

            // Ocupar espacio
            $stmtOcupar = $this->conexion->prepare("UPDATE espacios SET estado = 'Ocupado' WHERE id = ?");
            $stmtOcupar->bind_param("i", $espacio_id);
            $stmtOcupar->execute();

            $this->conexion->commit();

            // Contenido del QR (link para retirar)
            $this->generarQRparaAdmin($vehiculo_id);

            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            error_log("Error ingreso admin: " . $e->getMessage());
            return false;
        }
    }

    public function retirarVehiculoDesdeIngresados($placa)
    {
        $this->conexion->begin_transaction();
        try {
            // Obtener los datos actuales del vehículo ingresado
            $stmt = $this->conexion->prepare("SELECT * FROM v_ingresados WHERE placa = ? AND estado = 'Activo'");
            $stmt->bind_param("s", $placa);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("Vehículo no encontrado o ya retirado.");
            }

            $vehiculo = $result->fetch_assoc();

            // Calcular minutos y valor
            $fechaIngreso = new DateTime($vehiculo['fecha_ingreso']);
            $fechaSalida = new DateTime(); // NOW
            $intervalo = $fechaIngreso->diff($fechaSalida);
            $minutos = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;
            $horasDecimal = round($minutos / 60, 2);
            $valor = ceil($horasDecimal * $vehiculo['precio_hora']); // Puedes ajustar redondeo

            // Insertar en v_retirados
            $stmt = $this->conexion->prepare("INSERT INTO v_retirados (placa, tipo, modelo, espacio_id, fecha_ingreso, fecha_salida, minutos, valor, precio_hora)
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "sssissiid",
                $vehiculo['placa'],
                $vehiculo['tipo'],
                $vehiculo['modelo'],
                $vehiculo['espacio_id'],
                $vehiculo['fecha_ingreso'],
                $fechaSalida->format("Y-m-d H:i:s"),
                $minutos,
                $valor,
                $vehiculo['precio_hora']
            );
            $stmt->execute();

            // Actualizar estado a Finalizado en v_ingresados
            $stmt = $this->conexion->prepare("UPDATE v_ingresados SET estado = 'Finalizado' WHERE placa = ?");
            $stmt->bind_param("s", $placa);
            $stmt->execute();

            // Liberar espacio
            $stmt = $this->conexion->prepare("UPDATE espacios SET estado = 'Disponible' WHERE id = ?");
            $stmt->bind_param("i", $vehiculo['espacio_id']);
            $stmt->execute();

            $this->conexion->commit();

            return [
                'placa' => $vehiculo['placa'],
                'tipo' => $vehiculo['tipo'],
                'modelo' => $vehiculo['modelo'],
                'fecha_ingreso' => $vehiculo['fecha_ingreso'],
                'fecha_salida' => $fechaSalida->format("Y-m-d H:i:s"),
                'minutos' => $minutos,
                'horas' => $horasDecimal,
                'precio_hora' => $vehiculo['precio_hora'],
                'valor' => $valor
            ];
        } catch (Exception $e) {
            $this->conexion->rollback();
            error_log("Error al retirar vehículo: " . $e->getMessage());
            return false;
        }
    }

    public function buscarVehiculosPorPlaca($placa)
    {
        $placaBusqueda = '%' . $placa . '%';

        $sql = "SELECT id, placa, tipo, modelo, espacio_id, fecha_ingreso
            FROM v_ingresados
            WHERE placa LIKE ? AND estado = 'Activo'";

        if ($stmt = $this->conexion->prepare($sql)) {
            $stmt->bind_param("s", $placaBusqueda);
            $stmt->execute();
            $result = $stmt->get_result();

            $vehiculos = [];
            while ($row = $result->fetch_assoc()) {
                $vehiculos[] = $row;
            }

            $stmt->close();
            return $vehiculos;
        } else {
            return false;
        }
    }

    public function obtenerVehiculoPorId($id)
    {
        $sql = "SELECT * FROM v_ingresados WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 0) {
            return false;
        }
        $vehiculo = $resultado->fetch_assoc();
        $stmt->close();
        return $vehiculo;
    }
}
