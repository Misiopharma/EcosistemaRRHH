<?php
require '../config/config.php';
$empleado_id = $_GET['empleado_id'];
$fecha_inicio = $_GET['fecha_inicio'];
$fecha_fin = $_GET['fecha_fin'];
$observaciones = $_GET['observaciones'] ?? '';

// Calcular los días de inasistencia
$datetime1 = new DateTime($fecha_inicio);
$datetime2 = new DateTime($fecha_fin);
$interval = $datetime1->diff($datetime2);
$dias_inasistencia = $interval->days + 1;

// Obtener los datos del empleado
$sql = "SELECT nombre, apellido, puesto FROM empleados WHERE id = :empleado_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->execute();
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

// Generar la fecha de la solicitud
$fecha_solicitud = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Inasistencia</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 15px;
            border-bottom: 1px solid #666;
        }
        table th {
            background-color: #333;
            color: white;
            text-align: left;
        }
        table td {
            color: #333;
        }
        .signature-section {
            margin-top: 80px;
            display: flex;
            justify-content: space-around;
        }
        .signature-section div {
            width: 40%;
            text-align: center;
            padding-top: 80px;
        }
        .signature-section div span {
            display: block;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 20px;
        }
        @media print {
            .print-button {
                display: none !important;
            }
            body {
                margin: 0;
            }
        }
        .print-button {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }
        .print-button button {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .print-button button:hover {
            background-color: #444;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Solicitud de Inasistencia</h2>

        <!-- Tabla con detalles de la solicitud -->
        <table>
            <tr>
                <th>Fecha de Solicitud</th>
                <td><?= $fecha_solicitud ?></td>
            </tr>
            <tr>
                <th>Nombre del Empleado</th>
                <td><?= $empleado['nombre'] . " " . $empleado['apellido'] ?></td>
            </tr>
            <tr>
                <th>Cargo</th>
                <td><?= $empleado['puesto'] ?></td>
            </tr>
            <tr>
                <th>Periodo de Inasistencia</th>
                <td>Desde el <?= date('d/m/Y', strtotime($fecha_inicio)) ?> hasta el <?= date('d/m/Y', strtotime($fecha_fin)) ?></td>
            </tr>
            <tr>
                <th>Total de Días de Inasistencia</th>
                <td><?= $dias_inasistencia ?> días</td>
            </tr>
            <tr>
                <th>Observaciones</th>
                <td><?= !empty($observaciones) ? $observaciones : 'Ninguna' ?></td>
            </tr>
        </table>

        <!-- Sección de firmas -->
        <div class="signature-section">
            <div class="firma-empleado">
                <span>Firma del Empleado</span>
            </div>
            <div class="firma-autorizante">
                <span>Firma del Autorizante</span>
            </div>
        </div>

        <!-- Botón para imprimir -->
        <div class="print-button">
            <button onclick="window.print();">Imprimir Solicitud</button>
        </div>
        <!-- Botón para volver -->
        <div class="print-button">
            <button onclick="window.history.back();">Volver</button>
        </div>
    </div>
</body>
</html>

