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
$sql = "SELECT id FROM usuarios WHERE username = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../View/Home.php");
    exit;
}

$usuario = $result->fetch_assoc();
$usuario_id = $usuario['id'];

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar y limpiar datos
    $placa = trim($_POST['placa'] ?? '');
    $tipo = trim($_POST['tipo_vehiculo'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $espacio = trim($_POST['espacio'] ?? '');
    $precio = trim($_POST['precio'] ?? 0);
    $contacto = trim($_POST['contacto'] ?? '');

    // Validaciones básicas
    if (empty($placa) || empty($tipo) || empty($modelo) || empty($espacio) || empty($contacto)) {
        header("Location: ../View/Vehiculos/formulario.php?error=campos_vacios");
        exit;
    }

    $reserva = new Reserva($conexion);

    if ($reserva->insertar($placa, $tipo, $modelo, $espacio, $precio, $usuario_id)) {
        header("Location: ../View/Vehiculos/formulario.php?success=1");
        exit;
    } else {
        header("Location: ../View/Vehiculos/formulario.php?error=bd");
        exit;
    }
}

header("Location: ../View/Vehiculos/Formulario.php");
exit;
