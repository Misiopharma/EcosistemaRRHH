<?php
include "../../config/config.php";
// Validar que los parámetros hayan sido recibidos
$empleado_id = isset($_GET['empleado_id']) ? $_GET['empleado_id'] : null;
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
$observaciones = isset($_GET['observaciones']) ? $_GET['observaciones'] : '';

// Verificar que los parámetros obligatorios estén presentes
if (!$empleado_id || !$fecha_inicio || !$fecha_fin) {
    echo "Faltan parámetros obligatorios. No se puede generar la solicitud.";
    exit();
}

// Obtener los datos del empleado
$sql = "SELECT e.nombre, e.apellido, e.puesto FROM empleados e WHERE e.id = :empleado_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->execute();
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

// Calcular los días de licencia solicitados
$dias_licencia = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / (60 * 60 * 24) + 1;

// Generar la fecha de la solicitud
$fecha_solicitud = date('d/m/Y');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Vacaciones</title>
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
            border-bottom: 1px solid #666; /* Separadores más oscuros */
        }
        table th {
            background-color: #333; /* Color más oscuro para los encabezados */
            color: white;
            text-align: left;
        }
        table td {
            color: #333; /* Color más fuerte para los datos */
        }
        .signature-section {
            margin-top: 80px; /* Bajar las firmas */
            display: flex;
            justify-content: space-around;
        }
        .signature-section div {
            width: 40%; /* Hacer las líneas más cortas */
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
                display: none !important; /* Aseguramos que no se muestre en impresión */
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
        <h2>Solicitud de Licencia por Vacaciones</h2>

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
                <th>Periodo de Licencia</th>
                <td>Desde el <?= date('d/m/Y', strtotime($fecha_inicio)) ?> hasta el <?= date('d/m/Y', strtotime($fecha_fin)) ?></td>
            </tr>
            <tr>
                <th>Total de Días de Licencia</th>
                <td><?= $dias_licencia ?> días</td>
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
