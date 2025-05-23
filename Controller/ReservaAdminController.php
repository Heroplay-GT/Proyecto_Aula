<?php
session_start();
require_once '../conexion.php';
require_once '../Model/Reserva.php';
require_once '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Verificar sesión
if (!isset($_SESSION['username'])) {
    header("Location: /PROYECTO_AULA/View/Home.php");
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

// Obtener todos los espacios (AJAX)
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

// Obtener vehículos activos (AJAX)
if (isset($_GET['vehiculos_activos'])) {
    header('Content-Type: application/json');
    $result = $reserva->obtenerVehiculosActivos();
    $vehiculos = [];

    while ($row = $result->fetch_assoc()) {
        $vehiculos[] = $row;
    }

    echo json_encode($vehiculos);
    exit;
}

// Obtener espacios disponibles por tipo (AJAX)
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

// Procesar formulario de ingreso (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $placa = trim($_POST['placa'] ?? '');
    $tipo = trim($_POST['tipo_vehiculo'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $espacio_id = (int)($_POST['espacio'] ?? 0);
    $contacto = trim($_POST['contacto'] ?? '');

    $errores = [];

    if ($tipo !== 'Bicicleta' && empty($placa)) $errores[] = "placa";
    if (empty($tipo)) $errores[] = "tipo_vehiculo";
    if ($espacio_id <= 0) $errores[] = "espacio";
    if (empty($contacto)) $errores[] = "contacto";

    if (!empty($errores)) {
        echo "<h3 style='color:red;'>❌ Faltan campos obligatorios. Redirigiendo...</h3>";
        echo "<script>
            setTimeout(() => {
                window.location.href = 'Formulario.php';
            }, 2000);
        </script>";
        exit;
    }

    // Asignar placa a bicicletas automáticamente
    if ($tipo === 'Bicicleta' && empty($placa)) {
        $placa = 'BIC-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    if ($reserva->ingresarVehiculoDesdeAdmin($placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id)) {
        echo "<h3 style='color:green;'>✅ Vehículo ingresado correctamente. Redirigiendo...</h3>";
        echo "<script>
            setTimeout(() => {
                window.location.href = '../View/Admin/Insertar.php';
            }, 2000);
        </script>";
    } else {
        echo "<h3 style='color:red;'>❌ Ocurrió un error al registrar el vehículo. Redirigiendo...</h3>";
        echo "<script>
            setTimeout(() => {
                window.location.href = '../View/Admin/Insertar.php';
            }, 2000);
        </script>";
    }
    exit;
}
// Redirección por defecto
header("Location: ../View/Vehiculos/Formulario.php");
