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

if ($result->num_rows === 0) {
    header("Location: ../View/Home.php");
    exit;
}

$usuario = $result->fetch_assoc();
$usuario_id = $usuario['id'];

$reserva = new Reserva($conexion);

// AJAX para obtener espacios
if (isset($_GET['tipo_vehiculo'])) {
    header('Content-Type: application/json');

    try {
        $tipo = $_GET['tipo_vehiculo'];

        // Validar el tipo de vehículo
        $tiposPermitidos = ['Carro', 'Moto', 'Bicicleta'];
        if (!in_array($tipo, $tiposPermitidos)) {
            throw new Exception('Tipo de vehículo no válido');
        }

        $espacios = $reserva->obtenerEspaciosDisponibles($tipo);

        if ($espacios === false) {
            throw new Exception('Error en la consulta de espacios');
        }

        $resultados = [];
        while ($fila = $espacios->fetch_assoc()) {
            // Verificar que todos los campos necesarios estén presentes
            if (!isset($fila['id'], $fila['codigo'], $fila['precio_hora'], $fila['tipo_vehiculo'])) {
                throw new Exception('Datos incompletos en la respuesta');
            }

            $resultados[] = [
                'id' => (int)$fila['id'],
                'codigo' => htmlspecialchars($fila['codigo']),
                'precio_hora' => (float)$fila['precio_hora'],
                'tipo_vehiculo' => htmlspecialchars($fila['tipo_vehiculo']),
                'estado' => isset($fila['estado']) ? htmlspecialchars($fila['estado']) : 'Disponible'
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $resultados,
            'count' => count($resultados) // Para debug
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'debug' => [
                'tipo_recibido' => $_GET['tipo_vehiculo'] ?? 'No recibido',
                'hora' => date('Y-m-d H:i:s')
            ]
        ]);
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
    $espacio_id = trim($_POST['espacio'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');

    // Validaciones básicas
    $errores = [];
    if (empty($placa)) $errores[] = "placa";
    if (empty($tipo)) $errores[] = "tipo_vehiculo";
    if (empty($espacio_id)) $errores[] = "espacio";
    if (empty($contacto)) $errores[] = "contacto";

    if (!empty($errores)) {
        header("Location: ../View/Vehiculos/Formulario.php?error=" . implode(",", $errores));
        exit;
    }

    // Registrar ingreso
    if ($reserva->ingresarVehiculo($placa, $tipo, $modelo, $espacio_id, $contacto, $usuario_id)) {
        header("Location: ../View/Clientes/Perfil.php?success=ingreso_ok");
    } else {
        header("Location: ../View/Vehiculos/formulario.php?error=db_error");
    }
    exit;
}

// Redirección por defecto
header("Location: ../View/Vehiculos/Formulario.php");
