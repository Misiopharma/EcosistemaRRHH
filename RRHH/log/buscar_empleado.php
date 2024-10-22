<?php
// Incluir la configuración de la base de datos
require '../../config/config.php';

// Recibir el término de búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Consulta para buscar empleados cuyo nombre o apellido coincida con el término de búsqueda
$sql = "SELECT e.id, e.nombre, e.apellido, s.vacaciones_restantes 
        FROM empleados e
        LEFT JOIN saldo_vacaciones s ON e.id = s.empleado_id
        WHERE e.nombre LIKE :busqueda OR e.apellido LIKE :busqueda";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':busqueda', '%' . $busqueda . '%');
$stmt->execute();

// Obtener los resultados y devolverlos en formato JSON
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($empleados);
?>
