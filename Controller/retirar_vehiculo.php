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
            // Calcular tiempo y valor
            $fecha_ingreso = new DateTime($vehiculo['fecha_ingreso']);
            $fecha_salida = new DateTime();
            $interval = $fecha_ingreso->diff($fecha_salida);
            $horas = $interval->h + ($interval->days * 24);
            $valor_pagado = $horas * $vehiculo['precio_hora'];

            // Iniciar transacción
            $conexion->begin_transaction();

            try {
                // Mover a v_retirados
                $insert = "INSERT INTO v_retirados 
                          (placa, tipo, modelo, espacio_id, contacto, usuarios_id, fecha_ingreso, fecha_salida, tiempo_estancia, valor_pagado)
                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
                $stmt = $conexion->prepare($insert);
                $stmt->bind_param(
                    "sssisisid",
                    $vehiculo['placa'],
                    $vehiculo['tipo'],
                    $vehiculo['modelo'],
                    $vehiculo['espacio_id'],
                    $vehiculo['contacto'],
                    $vehiculo['usuarios_id'],
                    $vehiculo['fecha_ingreso'],
                    $horas,
                    $valor_pagado
                );
                $stmt->execute();

                // Actualizar estado en v_ingresados
                $update = "UPDATE v_ingresados SET estado = 'Finalizado', fecha_salida = NOW() WHERE id = ?";
                $stmt = $conexion->prepare($update);
                $stmt->bind_param("i", $id);
                $stmt->execute();

                // Actualizar estado del espacio
                $updateEspacio = "UPDATE espacios SET estado = 'Disponible' WHERE id = ?";
                $stmt = $conexion->prepare($updateEspacio);
                $stmt->bind_param("i", $vehiculo['espacio_id']);
                $stmt->execute();

                // Actualizar reservas si existe
                $updateReserva = "UPDATE reservas SET estado = 'Finalizado', fecha_salida = NOW() 
                                 WHERE placa = ? AND estado = 'Activo'";
                $stmt = $conexion->prepare($updateReserva);
                $stmt->bind_param("s", $vehiculo['placa']);
                $stmt->execute();

                $conexion->commit();

                // Devolver datos para el recibo
                echo json_encode([
                    'success' => true,
                    'placa' => $vehiculo['placa'],
                    'tipo' => $vehiculo['tipo'],
                    'modelo' => $vehiculo['modelo'],
                    'espacio' => $vehiculo['espacio_id'],
                    'fecha_ingreso' => $vehiculo['fecha_ingreso'],
                    'fecha_salida' => date('Y-m-d H:i:s'),
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
