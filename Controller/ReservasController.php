<?php
session_start();
require_once '../conexion.php';
require_once '../Model/Reserva.php';

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
$usuario = $result->fetch_assoc();
$usuario_id = $usuario['id'];

$reserva = new Reserva($conexion);

// Obtener reservas del usuario
$reservasResult = $reserva->obtenerReservasPorUsuario($usuario_id);

// Procesar reservas y generar QRs si es necesario
$reservas = [];
while ($reserva_data = $reservasResult->fetch_assoc()) {
    if ($reserva_data['estado'] == 'Pendiente' && empty($reserva_data['qr_code'])) {
        $reserva->generarCodigoQR($reserva_data['id']);
        // Volver a obtener los datos actualizados
        $reserva_data = $reserva->obtenerReservaPorId($reserva_data['id'])->fetch_assoc();
    }
    $reservas[] = $reserva_data;
}

// Pasar a la vista
require_once '../View/Clientes/misReservas.php';