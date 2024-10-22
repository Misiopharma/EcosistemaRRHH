<?php
require '../config/config.php';
require 'auth.php';

$empleado_id = $_GET['id'];

// Obtener la información del empleado
$sql_empleado = "SELECT e.nombre, e.apellido, s.vacaciones_restantes, s.vacaciones_acumuladas, s.vacaciones_adelantadas
                 FROM empleados e
                 JOIN saldo_vacaciones s ON e.id = s.empleado_id
                 WHERE e.id = :empleado_id";
$stmt_empleado = $pdo->prepare($sql_empleado);
$stmt_empleado->bindParam(':empleado_id', $empleado_id);
$stmt_empleado->execute();
$empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

// Obtener el historial de vacaciones del empleado
$sql_historial = "SELECT fecha_inicio, fecha_fin, dias_solicitados, estado, dias_solicitados, fecha_solicitud, pdf_generado
                  FROM historial_vacaciones
                  WHERE empleado_id = :empleado_id";
$stmt_historial = $pdo->prepare($sql_historial);
$stmt_historial->bindParam(':empleado_id', $empleado_id);
$stmt_historial->execute();
$historial_vacaciones = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Vacaciones - <?= $empleado['nombre'] . " " . $empleado['apellido'] ?></title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <style>
        .container {
            margin-top: 50px;
        }
        .btn-pdf {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
            padding: 5px 10px;
            text-decoration: none;
        }
        .btn-back {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 10px 20px;
            text-decoration: none;
        }
        .btn-back i {
            margin-right: 5px;
        }

        .table thead th {
            background-color: #343a40;
            color: #fff;
            text-align: center;
        }

        .table tbody td {
            text-align: center;
            vertical-align: middle;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table-hover tbody tr:hover {
            background-color: #ddd;
        }

        .table .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .table .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Detalles de Vacaciones de <?= $empleado['nombre'] . " " . $empleado['apellido'] ?></h1>

        <section class="my-4">
            <h2>Información del Saldo de Vacaciones</h2>
            <ul class="list-group">
                <li class="list-group-item"><strong>Días Restantes:</strong> <?= $empleado['vacaciones_restantes'] ?></li>
                <li class="list-group-item"><strong>Días Acumulados:</strong> <?= $empleado['vacaciones_acumuladas'] ?></li>
                <li class="list-group-item"><strong>Días Adelantados:</strong> <?= $empleado['vacaciones_adelantadas'] ?></li>
            </ul>
        </section>

        <section class="my-4">
            <h2>Historial de Vacaciones</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha de Solicitud</th>
                            <th>Período de Vacaciones</th>
                            <th>Días Solicitados</th>
                            <th>Estado</th>
                            <th>Regreso Programado</th>
                            <th>Ver Solicitud PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial_vacaciones as $vacacion): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($vacacion['fecha_solicitud'])) ?></td>
                                <td>Desde <?= date('d/m/Y', strtotime($vacacion['fecha_inicio'])) ?> hasta <?= date('d/m/Y', strtotime($vacacion['fecha_fin'])) ?></td>
                                <td><?= $vacacion['dias_solicitados'] ?></td>
                                <td><?= $vacacion['estado'] ?></td>
                                <td><?= date('d/m/Y', strtotime($vacacion['fecha_fin'] . ' +1 day')) ?></td>
                                <td>
                                    <?php if (!empty($vacacion['pdf_generado'])): ?>
                                        <a href="uploads/solicitudes/<?= $vacacion['pdf_generado'] ?>" target="_blank" class="btn btn-primary btn-sm">Ver PDF</a>
                                    <?php else: ?>
                                        <span>No disponible</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="text-center">
            <a href="vacaciones.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Principal</a>
        </div>
    </div>
            <!-- jQuery and Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
