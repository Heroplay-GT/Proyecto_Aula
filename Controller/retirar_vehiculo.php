<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        // Obtener información del vehículo
        $query = "SELECT vi.*, e.precio_hora 
                  FROM v_ingresados vi 
                  JOIN espacios e ON vi.espacio_id = e.id 
                  WHERE vi.id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $vehiculo = $result->fetch_assoc();

        if ($vehiculo) {
            // Iniciar transacción
            $conexion->begin_transaction();

            try {
                // Actualizar estado en v_ingresados con fecha de salida
                $update = "UPDATE v_ingresados SET estado = 'Finalizado', fecha_salida = NOW() WHERE id = ?";
                $stmt = $conexion->prepare($update);
                $stmt->bind_param("i", $id);
                $stmt->execute();

                // Obtener fecha_salida ya guardada
                $querySalida = "SELECT fecha_salida FROM v_ingresados WHERE id = ?";
                $stmt = $conexion->prepare($querySalida);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $salidaRow = $result->fetch_assoc();
                $fecha_salida = new DateTime($salidaRow['fecha_salida']);
                $fecha_ingreso = new DateTime($vehiculo['fecha_ingreso']);

                // Calcular duración en horas
                $interval = $fecha_ingreso->diff($fecha_salida);
                $minutos_totales = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i + ($interval->s > 0 ? 1 : 0);
                $horas = ceil($minutos_totales / 60);

                $valor_pagado = $horas * $vehiculo['precio_hora'];

                // Mover a v_retirados
                $insert = "INSERT INTO v_retirados 
                          (placa, tipo, modelo, espacio_id, contacto, usuarios_id, fecha_ingreso, fecha_salida, tiempo_estancia, valor_pagado)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conexion->prepare($insert);
                $stmt->bind_param(
                    "sssisisisd",
                    $vehiculo['placa'],
                    $vehiculo['tipo'],
                    $vehiculo['modelo'],
                    $vehiculo['espacio_id'],
                    $vehiculo['contacto'],
                    $vehiculo['usuarios_id'],
                    $vehiculo['fecha_ingreso'],
                    $salidaRow['fecha_salida'],
                    $horas,
                    $valor_pagado
                );
                $stmt->execute();

                // Actualizar estado del espacio
                $updateEspacio = "UPDATE espacios SET estado = 'Disponible' WHERE id = ?";
                $stmt = $conexion->prepare($updateEspacio);
                $stmt->bind_param("i", $vehiculo['espacio_id']);
                $stmt->execute();

                // Finalizar reserva si existe
                $updateReserva = "UPDATE reservas SET estado = 'Finalizado', fecha_salida = ? 
                                 WHERE placa = ? AND estado = 'Activo'";
                $stmt = $conexion->prepare($updateReserva);
                $stmt->bind_param("ss", $salidaRow['fecha_salida'], $vehiculo['placa']);
                $stmt->execute();

                $conexion->commit();

                // Respuesta
                echo json_encode([
                    'success' => true,
                    'placa' => $vehiculo['placa'],
                    'tipo' => $vehiculo['tipo'],
                    'modelo' => $vehiculo['modelo'],
                    'espacio' => $vehiculo['espacio_id'],
                    'fecha_ingreso' => $vehiculo['fecha_ingreso'],
                    'fecha_salida' => $salidaRow['fecha_salida'],
                    'horas' => $horas,
                    'valor_pagado' => number_format($valor_pagado, 2),
                    'precio_hora' => number_format($vehiculo['precio_hora'], 2)
                ]);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Vehículo no encontrado']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
