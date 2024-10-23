<?php
require '../config/config.php';
$empleado_id = $_POST['empleado_id'];

$sql = "SELECT vacaciones_restantes + vacaciones_acumuladas AS dias_restantes FROM saldo_vacaciones WHERE empleado_id = :empleado_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result);
?>
