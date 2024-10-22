<?php
session_start();
include 'config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener el ID del empleado
$empleado_id = isset($_GET['empleado_id']) ? $_GET['empleado_id'] : null;

if (!$empleado_id) {
    die("Empleado no especificado.");
}

// Obtener detalles del empleado
$stmt = $pdo->prepare("SELECT * FROM empleados WHERE id = ?");
$stmt->execute([$empleado_id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    die("Empleado no encontrado.");
}

// Filtro de mes
$mes_filtro = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');

// Obtener historial de asistencia del empleado para el mes seleccionado
$stmt = $pdo->prepare("SELECT * FROM asistencia WHERE empleado_id = ? AND fecha LIKE ? ORDER BY fecha DESC");
$stmt->execute([$empleado_id, "$mes_filtro%"]);
$historial_asistencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar inasistencias del mes seleccionado
$stmt = $pdo->prepare("SELECT COUNT(*) FROM asistencia WHERE empleado_id = ? AND fecha LIKE ? AND estado = 'Ausente'");
$stmt->execute([$empleado_id, "$mes_filtro%"]);
$total_ausencias_mes = $stmt->fetchColumn();

// Acumular horas trabajadas en el mes seleccionado
$acumulado_horas_mes = 0;
foreach ($historial_asistencia as $asistencia) {
    if ($asistencia['hora_entrada'] && $asistencia['hora_salida']) {
        $hora_entrada = new DateTime($asistencia['hora_entrada']);
        $hora_salida = new DateTime($asistencia['hora_salida']);
        $intervalo = $hora_entrada->diff($hora_salida);
        $horas_trabajadas = $intervalo->h + ($intervalo->i / 60);
        $acumulado_horas_mes += $horas_trabajadas;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Asistencia de <?php echo htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']); ?></title>
    <link rel="stylesheet" href="css/ver_detalles.css">
    <script>
        function toggleAusencias() {
            var x = document.getElementById("ausencias");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }

        function toggleTardanzas() {
            var x = document.getElementById("tardanzas");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <div class="detalle-container">
        <div class="header">
            <h1>Detalles de Asistencia</h1>
            <a href="admin_dashboard.php" class="back-button">X</a>
        </div>
        <h2>Empleado: <?php echo htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']); ?></h2>
        <p>Puesto: <?php echo htmlspecialchars($empleado['puesto']); ?></p>
        <p>Total de ausencias este mes: <?php echo $total_ausencias_mes; ?></p>
        <p>Acumulado de horas trabajadas este mes: <?php echo round($acumulado_horas_mes, 2); ?> horas</p>

        <form method="GET" action="ver_detalles.php">
            <input type="hidden" name="empleado_id" value="<?php echo $empleado_id; ?>">
            <label for="mes">Seleccionar mes:</label>
            <input type="month" id="mes" name="mes" value="<?php echo $mes_filtro; ?>">
            <button type="submit">Filtrar</button>
        </form>

        <h2>Historial de Asistencia</h2>
        <table>
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Hora de Entrada</th>
                    <th>Hora de Salida</th>
                    <th>Horas Trabajadas</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial_asistencia as $asistencia): ?>
                    <tr>
                        <td><?php echo traducirDia($asistencia['fecha']); ?></td>
                        <td><?php echo $asistencia['hora_entrada'] ? $asistencia['hora_entrada'] : 'N/A'; ?></td>
                        <td>
                            <?php 
                            if ($asistencia['hora_entrada']) {
                                echo $asistencia['hora_salida'] ? $asistencia['hora_salida'] : 'En horario laboral';
                            } else {
                                echo 'Ausente';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($asistencia['hora_entrada'] && $asistencia['hora_salida']) {
                                $hora_entrada = new DateTime($asistencia['hora_entrada']);
                                $hora_salida = new DateTime($asistencia['hora_salida']);
                                $intervalo = $hora_entrada->diff($hora_salida);
                                echo $intervalo->format('%h horas %i minutos');
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($asistencia['estado'] === 'Ausente' && $asistencia['justificacion']) {
                                echo "Ausente (Justificación: " . htmlspecialchars($asistencia['justificacion']) . ")";
                            } elseif ($asistencia['estado'] === 'Ausente') {
                                echo "Ausente (Sin justificación)";
                            } elseif ($asistencia['estado'] === 'Presente (Tardanza: Justificada)') {
                                echo "Presente (Tardanza: Justificada)";
                            } elseif ($asistencia['estado'] === 'Presente (Tardanza)') {
                                echo "Presente (Tardanza)";
                            } else {
                                echo $asistencia['estado'];
                            }
                            ?>
                        </td>
                        <td><a href="modificar_asistencia.php?asistencia_id=<?php echo $asistencia['id']; ?>">Modificar</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button onclick="toggleAusencias()">Mostrar Ausencias</button>
        <div id="ausencias" style="display:none;">
            <h2>Ausencias Justificadas y No Justificadas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT fecha, justificacion FROM asistencia WHERE empleado_id = ? AND estado = 'Ausente' ORDER BY fecha DESC");
                    $stmt->execute([$empleado_id]);
                    $lista_ausencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($lista_ausencias):
                        foreach ($lista_ausencias as $ausencia): ?>
                            <tr>
                                <td><?php echo traducirDia($ausencia['fecha']); ?></td>
                                <td><?php echo $ausencia['justificacion'] ? htmlspecialchars($ausencia['justificacion']) : 'Ausente (Sin justificación)'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No hay ausencias registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <button onclick="toggleTardanzas()">Mostrar Tardanzas</button>
        <div id="tardanzas" style="display:none;">
            <h2>Tardanzas Justificadas y No Justificadas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT fecha, justificacion_tardanza FROM asistencia WHERE empleado_id = ? AND estado LIKE 'Presente (Tardanza%' ORDER BY fecha DESC");
                    $stmt->execute([$empleado_id]);
                    $lista_tardanzas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($lista_tardanzas):
                        foreach ($lista_tardanzas as $tardanza): ?>
                            <tr>
                                <td><?php echo traducirDia($tardanza['fecha']); ?></td>
                                <td><?php echo $tardanza['justificacion_tardanza'] ? htmlspecialchars($tardanza['justificacion_tardanza']) : 'Tardanza (Sin justificación)'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No hay tardanzas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
// Función para traducir días a español y formatear la fecha
function traducirDia($fecha) {
    $dias = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo',
    ];
    $diaIngles = date('l', strtotime($fecha));
    $diaEspanol = $dias[$diaIngles] ?? $diaIngles;
    $fechaFormateada = date('d-m-Y', strtotime($fecha));
    return "$diaEspanol $fechaFormateada";
}