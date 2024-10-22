<?php
// Incluir la configuración de la base de datos
require '../config/config.php';
require 'auth.php';


// Consulta para obtener los empleados y su saldo de vacaciones
$sql = "SELECT e.id, e.nombre, e.apellido, s.vacaciones_restantes, s.vacaciones_acumuladas, s.vacaciones_adelantadas 
        FROM empleados e
        JOIN saldo_vacaciones s ON e.id = s.empleado_id";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerente - Vista de Empleados</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Vista General de Empleados y Vacaciones</h1>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Vacaciones Restantes</th>
                <th>Vacaciones Acumuladas</th>
                <th>Vacaciones Adelantadas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empleados as $empleado): ?>
                <tr>
                    <td><?= $empleado['id'] ?></td>
                    <td><?= $empleado['nombre'] ?></td>
                    <td><?= $empleado['apellido'] ?></td>
                    <td><?= $empleado['vacaciones_restantes'] ?></td>
                    <td><?= $empleado['vacaciones_acumuladas'] ?></td>
                    <td><?= $empleado['vacaciones_adelantadas'] ?></td>
                    <td>
                        <a href="detalle_vacaciones.php?id=<?= $empleado['id'] ?>">Ver Detalle</a>
                        <!-- Aquí puedes agregar otras acciones como renovar vacaciones -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
