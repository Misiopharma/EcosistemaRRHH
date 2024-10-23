<?php
include "../../config/config.php";
require '../dompdf/autoload.inc.php'; // Asegúrate de que dompdf esté instalado y configurado correctamente

use Dompdf\Dompdf;
use Dompdf\Options;

$solicitud_id = $_GET['id'];
$accion = $_GET['accion'];

// Obtener la solicitud de vacaciones
$sql = "SELECT empleado_id, dias_solicitados, fecha_inicio, fecha_fin, comentarios FROM historial_vacaciones WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $solicitud_id);
$stmt->execute();
$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

$empleado_id = $solicitud['empleado_id'];
$dias_solicitados = $solicitud['dias_solicitados'];
$fecha_inicio = $solicitud['fecha_inicio'];
$fecha_fin = $solicitud['fecha_fin'];
$comentarios = $solicitud['comentarios'];

// Obtener los datos del empleado
$sql_empleado = "SELECT nombre, apellido, puesto FROM empleados WHERE id = :empleado_id";
$stmt_empleado = $pdo->prepare($sql_empleado);
$stmt_empleado->bindParam(':empleado_id', $empleado_id);
$stmt_empleado->execute();
$empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

// Verificar los días disponibles del empleado
$sql_saldo = "SELECT vacaciones_restantes, vacaciones_acumuladas, vacaciones_adelantadas FROM saldo_vacaciones WHERE empleado_id = :empleado_id";
$stmt_saldo = $pdo->prepare($sql_saldo);
$stmt_saldo->bindParam(':empleado_id', $empleado_id);
$stmt_saldo->execute();
$saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

$dias_disponibles = $saldo['vacaciones_restantes'] + $saldo['vacaciones_acumuladas'];
$dias_adelantados = $saldo['vacaciones_adelantadas'];

// Si se aprueba la solicitud
if ($accion == 'aprobar') {
    if ($dias_disponibles >= $dias_solicitados) {
        // Restar los días solicitados de los días disponibles
        $dias_restantes_actualizados = $dias_disponibles - $dias_solicitados;

        $sql_update_saldo = "UPDATE saldo_vacaciones 
                             SET vacaciones_restantes = :vacaciones_restantes, 
                                 vacaciones_acumuladas = 0 
                             WHERE empleado_id = :empleado_id";
        $stmt_update_saldo = $pdo->prepare($sql_update_saldo);
        $stmt_update_saldo->bindParam(':vacaciones_restantes', $dias_restantes_actualizados);
        $stmt_update_saldo->bindParam(':empleado_id', $empleado_id);
        $stmt_update_saldo->execute();
    } else {
        // Si no hay suficientes días, actualizar vacaciones adelantadas
        $exceso = $dias_solicitados - $dias_disponibles;
        $dias_adelantados_actualizados = $dias_adelantados + $exceso;

        $sql_update_saldo = "UPDATE saldo_vacaciones 
                             SET vacaciones_restantes = 0, 
                                 vacaciones_acumuladas = 0, 
                                 vacaciones_adelantadas = :vacaciones_adelantadas 
                             WHERE empleado_id = :empleado_id";
        $stmt_update_saldo = $pdo->prepare($sql_update_saldo);
        $stmt_update_saldo->bindParam(':vacaciones_adelantadas', $dias_adelantados_actualizados);
        $stmt_update_saldo->bindParam(':empleado_id', $empleado_id);
        $stmt_update_saldo->execute();
    }

    // Actualizar el estado de la solicitud a 'Aceptada'
    $sql_aprobar = "UPDATE historial_vacaciones SET estado = 'Aceptada' WHERE id = :id";
    $stmt_aprobar = $pdo->prepare($sql_aprobar);
    $stmt_aprobar->bindParam(':id', $solicitud_id);
    $stmt_aprobar->execute();

    $marca_agua = "ACEPTADA";

} elseif ($accion == 'rechazar') {
    // Actualizar el estado de la solicitud a 'Rechazada'
    $sql_rechazar = "UPDATE historial_vacaciones SET estado = 'Rechazada' WHERE id = :id";
    $stmt_rechazar = $pdo->prepare($sql_rechazar);
    $stmt_rechazar->bindParam(':id', $solicitud_id);
    $stmt_rechazar->execute();
    
    $marca_agua = "RECHAZADA";
} else {
    echo "Acción inválida.";
    exit();
}

// Iniciar DomPDF con opciones
$options = new Options();
$options->set('isRemoteEnabled', true); // Permitir carga de CSS/Imágenes remotas
$dompdf = new Dompdf($options);

// Estilo del documento y la marca de agua
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Vacaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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
        <div class="watermark">' . $marca_agua . '</div>
        <h2>Solicitud de Licencia por Vacaciones</h2>
        <table>
            <tr>
                <th>Fecha de Solicitud</th>
                <td>' . date('d/m/Y') . '</td>
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
                <th>Periodo de Licencia</th>
                <td>Desde el ' . date('d/m/Y', strtotime($fecha_inicio)) . ' hasta el ' . date('d/m/Y', strtotime($fecha_fin)) . '</td>
            </tr>
            <tr>
                <th>Total de Días de Licencia</th>
                <td>' . $dias_solicitados . ' días</td>
            </tr>
            <tr>
                <th>Observaciones</th>
                <td>' . (!empty($comentarios) ? $comentarios : 'Ninguna') . '</td>
            </tr>
        </table>
    </div>
</body>
</html>
';

// Cargar el contenido HTML en DomPDF
$dompdf->loadHtml($html);

// Ajustar tamaño de papel y orientación
$dompdf->setPaper('A4', 'portrait');

// Renderizar el PDF
$dompdf->render();

// Generar un nombre único para cada archivo basado en el apellido y un contador o timestamp
$file_counter = time(); // Usamos la marca de tiempo para garantizar un nombre único
$file_name = 'Solicitud_Vacaciones_' . $empleado['apellido'] . '_' . $file_counter . '.pdf';
$output_path = '../uploads/solicitudes/' . $file_name;

// Guardar el PDF en el servidor
file_put_contents($output_path, $dompdf->output());

// Actualizar el campo de PDF generado en la base de datos
$sql_update_pdf = "UPDATE historial_vacaciones SET pdf_generado = :pdf_generado WHERE id = :id";
$stmt_update_pdf = $pdo->prepare($sql_update_pdf);
$stmt_update_pdf->bindParam(':pdf_generado', $file_name);
$stmt_update_pdf->bindParam(':id', $solicitud_id);
$stmt_update_pdf->execute();

// Redirigir de vuelta a la página de solicitudes pendientes
header('Location: ../solicitudes_pendientes.php');
exit();
