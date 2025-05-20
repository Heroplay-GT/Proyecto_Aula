<?php
session_start();
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Model/Reserva.php';


// Verificar sesión
if (!isset($_SESSION['username'])) {
    echo "<h3 style='color:green;'>Por favor, ingrese sesion.
            <br>Redirigiendo...</h3>";
    echo "<script>
            setTimeout(() => {
                window.location.href = '../Home.php';
            }, 2000);
        </script>";
    exit;
}

// Obtener ID del usuario
$username = $_SESSION['username'];
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$usuario_id = $usuario['id'];

$reserva = new Reserva($conexion);

// 🔹 Manejar acción: detalle
if (isset($_GET['action']) && $_GET['action'] == 'detalle' && isset($_GET['id'])) {
    header('Content-Type: application/json');

    $reservaData = $reserva->obtenerReservaPorId($_GET['id'])->fetch_assoc();

    if ($reservaData && $reservaData['usuarios_id'] == $usuario_id) {
        echo json_encode($reservaData);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado o reserva no encontrada']);
    }
    exit;
}

// 🔹 Manejar acción: cancelar
if (isset($_GET['action']) && $_GET['action'] == 'cancelar' && isset($_GET['id'])) {
    $reservaData = $reserva->obtenerReservaPorId($_GET['id'])->fetch_assoc();

    if ($reservaData && $reservaData['usuarios_id'] == $usuario_id && $reservaData['estado'] == 'Pendiente') {
        $reserva->cancelarReserva($_GET['id']);
    }

    header("Location: ../View/Clientes/misReservas.php");
    exit;
}

// 🔹 Si no es acción AJAX, cargar reservas y vista
$reservasResult = $reserva->obtenerReservasPorUsuario($usuario_id);
$reservas = [];

while ($reserva_data = $reservasResult->fetch_assoc()) {
    if ($reserva_data['estado'] == 'Pendiente' && empty($reserva_data['qr_code'])) {
        $reserva->generarCodigoQR($reserva_data['id']);
        $reserva_data = $reserva->obtenerReservaPorId($reserva_data['id'])->fetch_assoc();
    }
    $reservas[] = $reserva_data;
}

// Cargar vista
require_once __DIR__ . '/../View/Clientes/misReservas.php';
