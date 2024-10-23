<?php
require '../config/config.php';
require '../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$inasistencia_id = $_POST['inasistencia_id'];
$accion = $_POST['accion']; // 'aceptar' o 'rechazar'

if ($accion === 'aceptar') {
    $estado = 'Aprobada';
} elseif ($accion === 'rechazar') {
    $estado = 'Rechazada';
} else {
    echo "Acción no válida.";
    exit();
}

// Actualizar el estado de la inasistencia
$sql = "UPDATE inasistencias SET estado = :estado WHERE id = :inasistencia_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':estado', $estado);
$stmt->bindParam(':inasistencia_id', $inasistencia_id);
$stmt->execute();

// Obtener los datos de la inasistencia y del empleado
$sql = "SELECT si.*, e.nombre, e.apellido, e.puesto 
        FROM inasistencias si
        JOIN empleados e ON si.empleado_id = e.id
        WHERE si.id = :inasistencia_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':inasistencia_id', $inasistencia_id);
$stmt->execute();
$inasistencia = $stmt->fetch(PDO::FETCH_ASSOC);

$fecha_solicitud = date('d/m/Y');
$fecha_inicio = $inasistencia['fecha_inicio'];
$fecha_fin = $inasistencia['fecha_fin'];
$dias_inasistencia = $inasistencia['dias'];
$observaciones = $inasistencia['observaciones'];
$empleado = [
    'nombre' => $inasistencia['nombre'],
    'apellido' => $inasistencia['apellido'],
    'puesto' => $inasistencia['puesto']
];

// Crear el contenido HTML para el PDF
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Inasistencia</title>
    <style>
        body {
            font-family: "Arial", sans-serif;
            margin: 20px;
            background-color: white;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        h2 {
            text-align: center;
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 80%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 15px;
            border-bottom: 1px solid #666;
        }
        table th {
            background-color: #fff;
            color: black;
            text-align: left;
        }
        table td {
            color: #333;
        }
        .watermark {
            position: absolute;
            top: 60%; /* Ajustado para bajar la marca de agua */
            left: 20%;
            font-size: 50px;
            color: rgba(150, 150, 150, 0.4);
            transform: rotate(-45deg);
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="watermark">' . $estado . '</div>
        <h2>Solicitud de Inasistencia</h2>
        <table>
            <tr>
                <th>Fecha de Solicitud</th>
                <td>' . $fecha_solicitud . '</td>
            </tr>
            <tr>
                <th>Nombre del Empleado</th>
                <td>' . $empleado['nombre'] . ' ' . $empleado['apellido'] . '</td>
            </tr>
            <tr>
                <th>Cargo</th>
                <td>' . $empleado['puesto'] . '</td>
            </tr>
            <tr>
                <th>Periodo de Inasistencia</th>
                <td>Desde el ' . date('d/m/Y', strtotime($fecha_inicio)) . ' hasta el ' . date('d/m/Y', strtotime($fecha_fin)) . '</td>
            </tr>
            <tr>
                <th>Total de Días de Inasistencia</th>
                <td>' . $dias_inasistencia . ' días</td>
            </tr>
            <tr>
                <th>Observaciones</th>
                <td>' . (!empty($observaciones) ? $observaciones : 'Ninguna') . '</td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>' . $estado . '</td>
            </tr>
        </table>
    </div>
</body>
</html>
';

// Crear una instancia de DomPDF con opciones
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Generar un nombre único para el archivo PDF
$file_counter = time();
$file_name = 'Solicitud_Inasistencia_' . $empleado['apellido'] . '_' . $file_counter . '.pdf';
$output_path = '../uploads/inasistencias/' . $file_name;

// Guardar el archivo PDF en la carpeta uploads/inasistencias
file_put_contents($output_path, $dompdf->output());

//actualizar el campo de PDF generado en la base de datos
$sql_update_pdf = "UPDATE inasistencias SET pdf_generado = :pdf_generado WHERE id = :id";
$stmt_update_pdf = $conn->prepare($sql_update_pdf);
$stmt_update_pdf->bindParam(':pdf_generado', $file_name);
$stmt_update_pdf->bindParam(':id', $inasistencia_id);
$stmt_update_pdf->execute();

// Redirigir de vuelta a la página de solicitudes pendientes
header('Location: ../inasistencias.php');
?>