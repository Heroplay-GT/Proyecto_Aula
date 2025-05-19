<?php
session_start();
require_once '../conexion.php';
require_once '../Model/Reserva.php';
require_once '../vendor/autoload.php'; // para usar Endroid\QrCode
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Verificar sesión
if (!isset($_SESSION['username'])) {
    header("Location: ../View/Home.php");
    exit;
}

// Obtener ID del usuario
$username = $_SESSION['username'];
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../View/Home.php");
    exit;
}

$usuario = $result->fetch_assoc();
$usuario_id = $usuario['id'];

$reserva = new Reserva($conexion);

// AJAX para obtener todos los espacios (modal)
if (isset($_GET['todos_los_espacios'])) {
    header('Content-Type: application/json');

    try {
        $espacios = $reserva->obtenerTodosLosEspacios();

        if ($espacios === false) {
            throw new Exception('Error al consultar espacios');
        }

        $resultados = [];
        while ($fila = $espacios->fetch_assoc()) {
            $resultados[] = [
                'id' => $fila['id'],
                'codigo' => $fila['codigo'],
                'precio_hora' => $fila['precio_hora'],
                'tipo_vehiculo' => $fila['tipo_vehiculo'],
                'estado' => $fila['estado']
            ];
        }

        echo json_encode(['success' => true, 'data' => $resultados]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// AJAX para obtener espacios disponibles por tipo
if (isset($_GET['tipo_vehiculo'])) {
    header('Content-Type: application/json');

    try {
        $tipo = $_GET['tipo_vehiculo'];
        $espacios = $reserva->obtenerEspaciosDisponibles($tipo);

        if ($espacios === false) {
            throw new Exception('Error al consultar espacios');
        }

        $resultados = [];
        while ($fila = $espacios->fetch_assoc()) {
            $resultados[] = [
                'id' => $fila['id'],
                'codigo' => $fila['codigo'],
                'precio_hora' => $fila['precio_hora'],
                'tipo_vehiculo' => $fila['tipo_vehiculo']
            ];
        }

        echo json_encode(['success' => true, 'data' => $resultados]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Retirar vehículo (acción administrativa)
if (isset($_GET['action']) && $_GET['action'] === 'retirar' && isset($_GET['id'])) {
    if ($reserva->retirarVehiculo(
        $_GET['id'],
        $_GET['minutos'] ?? 0,
        $_GET['valor'] ?? 0
    )) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No se pudo registrar el retiro']);
    }
    exit;
}


// --- Procesar formulario de ingreso ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar datos
    $placa = trim($_POST['placa'] ?? '');
    $tipo = trim($_POST['tipo_vehiculo'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $espacio_id = (int)($_POST['espacio'] ?? 0);
    $contacto = trim($_POST['contacto'] ?? '');

    // Validaciones básicas
    $errores = [];

    if ($tipo !== 'Bicicleta' && empty($placa)) {
        $errores[] = "placa";
    }

    if (empty($tipo)) $errores[] = "tipo_vehiculo";
    if ($espacio_id <= 0) $errores[] = "espacio";
    if (empty($contacto)) $errores[] = "contacto";

    if (!empty($errores)) {
        header("Location: ../View/Vehiculos/Formulario.php?error=" . implode(",", $errores));
        exit;
    }

    // Procesar bicicletas sin placa
    if ($tipo === 'Bicicleta' && empty($placa)) {
        $placa = 'BIC-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    // Registrar reserva
    if ($reserva->ingresarVehiculo($placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id)) {
        // Obtener el ID de la reserva recién creada
        $reserva_id = $conexion->insert_id;
        
        header("Location: ../View/Clientes/Perfil.php?success=reserva_creada&reserva_id=" . $reserva_id);
    } else {
        header("Location: ../View/Vehiculos/Formulario.php?error=reserva_error");
    }
    exit;
}

// Redirección por defecto
header("Location: ../View/Vehiculos/Formulario.php");
