<?php
session_start();
require_once '../conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$username = $_SESSION['username'];
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
    exit;
}

$usuario = $result->fetch_assoc();
$usuario_id = $usuario['id'];

$action = $_POST['action'] ?? null;

switch ($action) {
    case 'actualizar_perfil':
        $nombre = trim($_POST['nombre'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($nombre) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios.']);
            exit;
        }

        if (!password_verify($password, $usuario['password'])) {
            echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta.']);
            exit;
        }

        $stmt = $conexion->prepare("UPDATE usuarios SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $nombre, $usuario_id);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit;

    case 'actualizar_correo':
        $correo = trim($_POST['correo'] ?? '');
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Correo inválido.']);
            exit;
        }

        $stmt = $conexion->prepare("UPDATE usuarios SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $correo, $usuario_id);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit;

    case 'actualizar_contrasena':
        $actual = $_POST['actual'] ?? '';
        $nueva = $_POST['nueva'] ?? '';
        $confirmar = $_POST['confirmar'] ?? '';

        if ($nueva !== $confirmar) {
            echo json_encode(['success' => false, 'error' => 'Las contraseñas no coinciden.']);
            exit;
        }

        if (!password_verify($actual, $usuario['password'])) {
            echo json_encode(['success' => false, 'error' => 'Contraseña actual incorrecta.']);
            exit;
        }

        $nueva_hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $nueva_hash, $usuario_id);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit;

    case 'eliminar_cuenta':
        $clave = $_POST['pass_confirmar'] ?? '';

        if (!password_verify($clave, $usuario['password'])) {
            echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
            exit;
        }

        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        if ($stmt->execute()) {
            session_destroy();
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo eliminar la cuenta']);
        }
        exit;

    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        exit;
}
