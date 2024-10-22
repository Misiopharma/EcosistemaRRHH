<?php
include 'config.php';

// Funcionalidad para renovar los días de vacaciones
if (isset($_POST['renovar_vacaciones'])) {
    // Obtener todos los empleados y sus saldos de vacaciones
    $sql = "SELECT empleado_id, vacaciones_restantes, vacaciones_acumuladas, vacaciones_adelantadas FROM saldo_vacaciones";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Renovar vacaciones de cada empleado
    foreach ($empleados as $empleado) {
        $dias_totales = 14 + $empleado['vacaciones_restantes'] - $empleado['vacaciones_adelantadas'];

        // Actualizar saldo de vacaciones para cada empleado
        $sql_update = "UPDATE saldo_vacaciones SET vacaciones_restantes = :vacaciones_restantes, vacaciones_adelantadas = 0 WHERE empleado_id = :empleado_id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':vacaciones_restantes', $dias_totales);
        $stmt_update->bindParam(':empleado_id', $empleado['empleado_id']);
        $stmt_update->execute();
    }
    echo "<script>alert('Vacaciones renovadas correctamente.');</script>";
}

// Funcionalidad para subir políticas de la empresa
if (isset($_POST['subir_politicas'])) {
    $nombre_archivo = $_FILES['archivo_politicas']['name'];
    $ruta_archivo = 'uploads/politicas/' . $nombre_archivo;

    // Mover archivo a la carpeta correspondiente
    if (move_uploaded_file($_FILES['archivo_politicas']['tmp_name'], $ruta_archivo)) {
        echo "<script>alert('Política subida correctamente.');</script>";
    } else {
        echo "<script>alert('Error al subir la política.');</script>";
    }
}

// Funcionalidad para configurar notificaciones
if (isset($_POST['guardar_notificaciones'])) {
    $notificar_aprobacion = isset($_POST['notificar_aprobacion']) ? 1 : 0;
    $notificar_rechazo = isset($_POST['notificar_rechazo']) ? 1 : 0;

    // Guardar las configuraciones de notificaciones
    $sql_update_notificaciones = "UPDATE configuracion_notificaciones SET notificar_aprobacion = :notificar_aprobacion, notificar_rechazo = :notificar_rechazo WHERE id = 1";
    $stmt_update_notificaciones = $conn->prepare($sql_update_notificaciones);
    $stmt_update_notificaciones->bindParam(':notificar_aprobacion', $notificar_aprobacion);
    $stmt_update_notificaciones->bindParam(':notificar_rechazo', $notificar_rechazo);
    $stmt_update_notificaciones->execute();

    echo "<script>alert('Configuración de notificaciones guardada.');</script>";
}

// Funcionalidad para exportar el historial de vacaciones
if (isset($_POST['exportar_datos'])) {
    $filename = "historial_vacaciones_" . date('Ymd') . ".csv";
    $output = fopen("php://output", "w");
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$filename");

    // Consultar el historial de vacaciones
    $sql_historial = "SELECT * FROM historial_vacaciones";
    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->execute();
    $historial = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);

    // Agregar encabezados al CSV
    fputcsv($output, array('ID', 'Empleado ID', 'Fecha Inicio', 'Fecha Fin', 'Días Solicitados', 'Estado', 'Fecha Solicitud', 'Días Adelantados', 'Comentarios'));

    // Escribir datos al CSV
    foreach ($historial as $fila) {
        fputcsv($output, $fila);
    }

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Recursos Humanos</title>
    <link rel="stylesheet" href="css/styles_configuracion.css">
</head>
<body>
    <div class="container">
        <h1>Configuración</h1>

        <!-- Botón para renovar vacaciones -->
        <section>
            <h2>Renovación de Vacaciones</h2>
            <form action="configuracion.php" method="POST">
                <button type="submit" name="renovar_vacaciones">Renovar Días de Vacaciones</button>
            </form>
        </section>

        <!-- Subir políticas de la empresa -->
        <section>
            <h2>Subir Políticas de la Empresa</h2>
            <form action="configuracion.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="archivo_politicas" required>
                <button type="submit" name="subir_politicas">Subir Documento</button>
            </form>
        </section>

        <!-- Configuración de notificaciones -->
        <section>
            <h2>Configuración de Notificaciones</h2>
            <form action="configuracion.php" method="POST">
                <label>
                    <input type="checkbox" name="notificar_aprobacion" checked> Notificar al empleado cuando se apruebe una solicitud
                </label>
                <br>
                <label>
                    <input type="checkbox" name="notificar_rechazo" checked> Notificar al empleado cuando se rechace una solicitud
                </label>
                <br>
                <button type="submit" name="guardar_notificaciones">Guardar Configuraciones</button>
            </form>
        </section>

        <!-- Exportar historial de vacaciones -->
        <section>
            <h2>Exportar Historial de Vacaciones</h2>
            <form action="configuracion.php" method="POST">
                <button type="submit" name="exportar_datos">Exportar Historial de Vacaciones (CSV)</button>
            </form>
        </section>
    </div>
</body>
</html>
