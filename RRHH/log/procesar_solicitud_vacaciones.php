<?php
include "../../config/config.php";
$empleado_id = $_POST['empleado_id'];
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];
$observaciones = $_POST['observaciones'] ?? '';

// Calcular los días solicitados
$dias_solicitados = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / (60 * 60 * 24) + 1;

// Insertar la solicitud en el historial de vacaciones con estado 'Pendiente'
$sql_insert = "INSERT INTO historial_vacaciones (empleado_id, fecha_inicio, fecha_fin, dias_solicitados, estado, fecha_solicitud, comentarios)
               VALUES (:empleado_id, :fecha_inicio, :fecha_fin, :dias_solicitados, 'Pendiente', CURDATE(), :observaciones)";

$stmt_insert = $pdo->prepare($sql_insert);
$stmt_insert->bindParam(':empleado_id', $empleado_id);
$stmt_insert->bindParam(':fecha_inicio', $fecha_inicio);
$stmt_insert->bindParam(':fecha_fin', $fecha_fin);
$stmt_insert->bindParam(':dias_solicitados', $dias_solicitados);
$stmt_insert->bindParam(':observaciones', $observaciones);
$stmt_insert->execute();

// Redirigir a la página para generar el PDF o mostrar la solicitud
header("Location: generar_solicitud_vacaciones.php?empleado_id=$empleado_id&fecha_inicio=$fecha_inicio&fecha_fin=$fecha_fin&observaciones=$observaciones");
exit();
?>
