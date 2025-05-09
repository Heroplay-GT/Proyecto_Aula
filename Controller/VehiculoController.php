<?php
session_start();
include '../conexion.php';
include '../Model/Vehiculo.php';

// Asegurarse de que el usuario esté logueado
if (!isset($_SESSION['username'])) {
    header("Location: ../View/index.php");
    exit;
}

// Obtener ID del usuario
$username = $_SESSION['username'];
$sql = "SELECT id FROM usuarios WHERE username = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$usuario_id = $usuario['id'];

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $placa = trim($_POST['placa']);
    $tipo = trim($_POST['tipo']);

    $vehiculo = new Vehiculo($conexion);
    if ($vehiculo->insertar($placa, $tipo, $usuario_id)) {
        header("Location: ../View/vehiculos_form.php?success=1");
        exit;
    } else {
        header("Location: ../View/vehiculos_form.php?error=1");
        exit;
    }
}
