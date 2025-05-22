<?php
require_once '../conexion.php';
require_once '../Model/Reserva.php';

if (!isset($_GET['id'])) {
    die("ID de reserva no proporcionado.");
}

$reserva_id = intval($_GET['id']);
$reserva = new Reserva($conexion);

// Verificar que la reserva exista y esté pendiente
$datos = $reserva->obtenerReservaPorId($reserva_id);
if (!$datos || $datos->num_rows === 0) {
    die("Reserva no encontrada.");
}

$data = $datos->fetch_assoc();
if ($data['estado'] !== 'Pendiente') {
    echo "<h3 style='color:orange;'>Esta placa ya ha sido ingresada o cancelada.<br>Redirigiendo...</h3>";
    echo "<script>
        setTimeout(() => {
            window.location.href = '../View/Admin/Insertar.html';
        }, 2000);
    </script>";
    exit;
}

// Comenzar transacción
$conexion->begin_transaction();
try {
    // 1. Cambiar estado de la reserva
    $stmt1 = $conexion->prepare("UPDATE reservas SET estado = 'Activo', fecha_ingreso = NOW() WHERE id = ?");
    $stmt1->bind_param("i", $reserva_id);
    $stmt1->execute();

    // 2. Insertar en v_ingresados
    $stmt2 = $conexion->prepare("
        INSERT INTO v_ingresados 
        (placa, tipo, modelo, espacio_id, contacto, usuarios_id, fecha_ingreso, estado)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Activo')
    ");
    $stmt2->bind_param(
        "sssisi",
        $data['placa'],
        $data['tipo'],
        $data['modelo'],
        $data['espacio_id'],
        $data['contacto'],
        $data['usuarios_id']
    );
    $stmt2->execute();

    // 3. Obtener el ID recién insertado
    $vehiculo_id = $conexion->insert_id;

    // 4. Generar el QR de retiro
    $reserva->generarQRparaAdmin($vehiculo_id);

    $conexion->commit();

    echo "<h3 style='color:green;'>✅ Reserva activada y QR de retiro generado.</h3>";
} catch (Exception $e) {
    $conexion->rollback();
    echo "<h3 style='color:red;'>❌ Error al activar la reserva. Intenta de nuevo.</h3>";
}

echo "<script>
    setTimeout(() => {
        window.location.href = '../View/Admin/Insertar.html';
    }, 2000);
</script>";
