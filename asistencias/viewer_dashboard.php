<?php
session_start();
include 'config.php';


// Obtener lista de empleados y su estado de asistencia del día actual
$fecha_actual = date('Y-m-d');
$empleados_asistencia = $pdo->prepare("SELECT e.id, e.nombre, e.apellido, a.hora_entrada, a.hora_salida, a.estado, a.justificacion 
                                        FROM empleados e 
                                        LEFT JOIN asistencia a ON e.id = a.empleado_id AND a.fecha = ?");
$empleados_asistencia->execute([$fecha_actual]);
$lista_asistencia = $empleados_asistencia->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Visualizador</title>
    <link rel="stylesheet" href="css/viewer.css">
</head>

<body>
    <div class="dashboard-container">
        <h1>Dashboard de Asistencia Diaria</h1>

        <!-- Visualizador de Asistencia -->
        <!-- Visualizador de Asistencia -->
        <div class="monitor">
            <h2>Monitor de Asistencia</h2>
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Estado</th>
                        <th>Hora de Entrada</th>
                        <th>Hora de Salida</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista_asistencia as $asistencia): ?>
                        <tr>
                            <td><a href="ver_detalles_viewer.php?empleado_id=<?php echo $asistencia['id']; ?>"><?php echo $asistencia['nombre'] . ' ' . $asistencia['apellido']; ?></a></td>
                            <td>
                                <?php
                                if ($asistencia['estado'] === 'Ausente') {
                                    if (!empty($asistencia['justificacion'])) {
                                        echo "Ausente (Motivo: " . htmlspecialchars($asistencia['justificacion']) . ")";
                                    } else {
                                        echo "Ausente (Sin justificación)";
                                    }
                                } elseif ($asistencia['estado'] === 'Presente (Tardanza)') {
                                    echo "Presente (Tardanza)";
                                } elseif ($asistencia['estado'] === 'Presente (Tardanza: Justificada)') {
                                    echo "Presente (Tardanza: Justificada)";
                                } else {
                                    echo "Presente";
                                }
                                ?>
                            </td>

                            <td><?php echo $asistencia['hora_entrada'] ? $asistencia['hora_entrada'] : 'N/A'; ?></td>
                            <td><?php echo $asistencia['hora_salida'] ? $asistencia['hora_salida'] : 'En horario laboral'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


        <p><a href="logout.php" class="logout">Cerrar Sesión</a></p>
    </div>

</body>

</html>
