<?php
include '../config.php';

$empleado_id = $_GET['empleado_id'];

// Consulta para obtener las vacaciones disponibles del empleado
$sql = "SELECT vacaciones_restantes, vacaciones_acumuladas FROM saldo_vacaciones WHERE empleado_id = :empleado_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->execute();

$saldo = $stmt->fetch(PDO::FETCH_ASSOC);

$response = array(
    'vacaciones_restantes' => $saldo['vacaciones_restantes'],
    'vacaciones_acumuladas' => $saldo['vacaciones_acumuladas']
);

echo json_encode($response);
?>
