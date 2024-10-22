<?php
session_start();
include 'config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener el ID de la asistencia
$asistencia_id = isset($_GET['asistencia_id']) ? $_GET['asistencia_id'] : null;

if (!$asistencia_id) {
    die("Asistencia no especificada.");
}

// Obtener los detalles de la asistencia
$stmt = $pdo->prepare("SELECT * FROM asistencia WHERE id = ?");
$stmt->execute([$asistencia_id]);
$asistencia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asistencia) {
    die("Asistencia no encontrada.");
}

// Actualizar la asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hora_entrada = $_POST['hora_entrada'] ?: $asistencia['hora_entrada'];
    $hora_salida = $_POST['hora_salida'] ?: $asistencia['hora_salida'];

    $stmt = $pdo->prepare("UPDATE asistencia SET hora_entrada = ?, hora_salida = ? WHERE id = ?");
    $stmt->execute([$hora_entrada, $hora_salida, $asistencia_id]);

    header("Location: ver_detalles.php?empleado_id=" . $asistencia['empleado_id']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Asistencia</title>
    <link rel="stylesheet" href="css/ver_detalles.css">
</head>
<body>
    <div class="detalle-container">
        <h1>Modificar Asistencia</h1>
        <form action="modificar_asistencia.php?asistencia_id=<?php echo $asistencia_id; ?>" method="POST">
            <div class="form-group">
                <label for="hora_entrada">Hora de Entrada:</label>
                <input type="time" id="hora_entrada" name="hora_entrada" value="<?php echo $asistencia['hora_entrada']; ?>">
            </div>
            <div class="form-group">
                <label for="hora_salida">Hora de Salida:</label>
                <input type="time" id="hora_salida" name="hora_salida" value="<?php echo $asistencia['hora_salida']; ?>">
            </div>
            <button type="submit">Guardar Cambios</button>
        </form>
        <p><a href="ver_detalles.php?empleado_id=<?php echo $asistencia['empleado_id']; ?>">Cancelar</a></p>
    </div>
</body>
</html>