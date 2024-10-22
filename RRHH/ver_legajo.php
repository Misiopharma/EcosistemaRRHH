<?php
// Incluir la configuración de la base de datos
require '../config/config.php';
require 'auth.php';

// Obtener el ID del empleado desde la URL
$empleado_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$empleado_id) {
    echo "Empleado no encontrado.";
    exit;
}

// Consulta para obtener los datos personales del empleado
$sql_empleado = "SELECT le.dni, le.fecha_alta, le.fecha_nacimiento, le.sexo, e.nombre, e.apellido
                 FROM empleados e
                 LEFT JOIN legajos_empleados le ON e.id = le.empleado_id
                 WHERE e.id = :empleado_id";
$stmt = $pdo->prepare($sql_empleado);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->execute();
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no se encuentra el legajo, mostrar el formulario para crear uno nuevo
if (!$empleado['dni']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_legajo'])) {
        // Insertar el nuevo legajo
        $dni = $_POST['dni'];
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $sexo = $_POST['sexo'];

        $sql_insertar_legajo = "INSERT INTO legajos_empleados (empleado_id, dni, fecha_nacimiento, sexo, fecha_alta) 
                                VALUES (:empleado_id, :dni, :fecha_nacimiento, :sexo, NOW())";
        $stmt = $pdo->prepare($sql_insertar_legajo);
        $stmt->bindParam(':empleado_id', $empleado_id);
        $stmt->bindParam(':dni', $dni);
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt->bindParam(':sexo', $sexo);
        $stmt->execute();

        header("Location: ver_legajo.php?id=$empleado_id");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Legajo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Crear Legajo para <?= htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']) ?></h1>
        
        <form action="ver_legajo.php?id=<?= $empleado_id ?>" method="POST" class="mt-4">
            <div class="mb-3">
                <label for="dni" class="form-label">DNI:</label>
                <input type="text" name="dni" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
                <input type="date" name="fecha_nacimiento" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="sexo" class="form-label">Sexo:</label>
                <select name="sexo" class="form-select" required>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            <button type="submit" name="crear_legajo" class="btn btn-primary">Crear Legajo</button>
        </form>

        <a href="dashboard_legajos.php" class="btn btn-secondary mt-3">Volver a la lista de empleados</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    exit;
}

// Si el legajo ya existe, mostrar los datos y formularios normales

// Consulta para obtener los documentos del legajo
$sql_documentos = "SELECT tipo_documento, ruta_archivo, fecha_subida 
                   FROM documentos_legajo 
                   WHERE empleado_id = :empleado_id";
$stmt = $pdo->prepare($sql_documentos);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->execute();
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener el historial de vacaciones aprobadas
$sql_vacaciones = "SELECT id, fecha_inicio, fecha_fin, dias_solicitados, comentarios, pdf_generado
                   FROM historial_vacaciones 
                   WHERE empleado_id = :empleado_id AND estado = 'Aceptada'";
$stmt = $pdo->prepare($sql_vacaciones);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->execute();
$vacaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el historial de licencias aceptadas
$sql_licencias = "SELECT * 
                FROM inasistencias 
                WHERE empleado_id = :empleado_id AND estado = 'Aprobada'";
$stmt_licencias = $pdo->prepare($sql_licencias);
$stmt_licencias->bindParam(':empleado_id', $empleado_id);
$stmt_licencias->execute();
$licencias = $stmt_licencias->fetchAll(PDO::FETCH_ASSOC);

// Obtener el mes actual
$mes_actual = date('Y-m');

// Consulta para obtener el historial de asistencia del empleado
$sql_asistencia = "SELECT hora_entrada, hora_salida 
                   FROM asistencia 
                   WHERE empleado_id = :empleado_id AND DATE_FORMAT(fecha, '%Y-%m') = :mes_actual";
$stmt = $pdo->prepare($sql_asistencia);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->bindParam(':mes_actual', $mes_actual);
$stmt->execute();
$historial_asistencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cálculo del acumulado de horas trabajadas
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

// Contar las ausencias del mes
$sql_ausencias = "SELECT COUNT(*) FROM asistencia WHERE empleado_id = :empleado_id AND DATE_FORMAT(fecha, '%Y-%m') = :mes_actual AND estado = 'Ausente'";
$stmt = $pdo->prepare($sql_ausencias);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->bindParam(':mes_actual', $mes_actual);
$stmt->execute();
$total_ausencias_mes = $stmt->fetchColumn();

// Guardar los datos personales si se envían desde el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_datos'])) {
    $dni = $_POST['dni'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $sexo = $_POST['sexo'];

    // Actualizar los datos en la base de datos
    $sql_actualizar = "UPDATE legajos_empleados SET dni = :dni, fecha_nacimiento = :fecha_nacimiento, sexo = :sexo 
                       WHERE empleado_id = :empleado_id";
    $stmt = $pdo->prepare($sql_actualizar);
    $stmt->bindParam(':dni', $dni);
    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':empleado_id', $empleado_id);
    $stmt->execute();

    // Redirigir después de la actualización
    header("Location: ver_legajo.php?id=$empleado_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legajo de <?= htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/legajo_moderno.css">
</head>
<body>
    <div class="container mt-5">
        <!-- Título principal -->
        <h1 class="text-center">Legajo de <?= htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']) ?></h1>

        <!-- Sección de Datos Personales -->
        <section id="datos-personales" class="section-block mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Datos Personales</h2>
                <button id="edit-datos" class="btn btn-warning">Editar</button>
            </div>
            <ul class="list-group">
                <li class="list-group-item"><strong>Nombre:</strong> <?= htmlspecialchars($empleado['nombre']) ?></li>
                <li class="list-group-item"><strong>Apellido:</strong> <?= htmlspecialchars($empleado['apellido']) ?></li>
                <li class="list-group-item"><strong>DNI:</strong> <span id="dni"><?= htmlspecialchars($empleado['dni']) ?></span></li>
                <li class="list-group-item"><strong>Fecha de Nacimiento:</strong> <span id="fecha-nacimiento"><?= htmlspecialchars($empleado['fecha_nacimiento']) ?></span></li>
                <li class="list-group-item"><strong>Sexo:</strong> <span id="sexo"><?= htmlspecialchars($empleado['sexo']) ?></span></li>
            </ul>

            <form id="form-datos" action="ver_legajo.php?id=<?= $empleado_id ?>" method="POST" class="mt-3" style="display:none;">
                <div class="mb-3">
                    <label for="dni" class="form-label">DNI:</label>
                    <input type="text" name="dni" class="form-control" value="<?= htmlspecialchars($empleado['dni']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" value="<?= htmlspecialchars($empleado['fecha_nacimiento']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="sexo" class="form-label">Sexo:</label>
                    <select name="sexo" class="form-select" required>
                        <option value="M" <?= ($empleado['sexo'] == 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($empleado['sexo'] == 'Femenino') ? 'selected' : '' ?>>Femenino</option>
                        <option value="Otro" <?= ($empleado['sexo'] == 'Otro') ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>

                <button type="submit" name="actualizar_datos" class="btn btn-success">Guardar Cambios</button>
            </form>
        </section>

        <!-- Sección de Documentos del Legajo -->
        <section id="documentos-legajo" class="section-block mt-4">
            <h2>Documentos del Legajo</h2>
            <ul class="list-group">
                <?php if (empty($documentos)): ?>
                    <li class="list-group-item">No se han subido documentos.</li>
                <?php else: ?>
                    <?php foreach ($documentos as $documento): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong><?= htmlspecialchars($documento['tipo_documento']) ?>:</strong> <a href="<?= htmlspecialchars($documento['ruta_archivo']) ?>" target="_blank">Ver Documento (Subido el <?= htmlspecialchars($documento['fecha_subida']) ?>)</a></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <!-- Botón para mostrar el formulario de subir documentos -->
            <button id="show-upload-form" class="btn btn-primary mt-3">Subir Documento</button>
            
            <!-- Formulario para subir documentos (oculto inicialmente) -->
            <form id="upload-form" action="log/subir_documento.php" method="POST" enctype="multipart/form-data" class="mt-3" style="display: none;">
                <input type="hidden" name="empleado_id" value="<?= $empleado_id ?>">
                <div class="mb-3">
                    <label for="tipo_documento" class="form-label">Tipo de Documento:</label>
                    <select name="tipo_documento" id="tipo_documento" class="form-select" required>
                        <option value="Fotocopia de DNI">Fotocopia de DNI</option>
                        <option value="Constancia de CUIL">Constancia de CUIL</option>
                        <option value="Certificado de domicilio (expedido por la policía)">Certificado de domicilio (expedido por la policía)</option>
                        <option value="Constancia de alumno regular o título académico">Constancia de alumno regular o título académico</option>
                        <option value="Fotocopia de DNI (grupo familiar)">Fotocopia de DNI (grupo familiar)</option>
                        <option value="Partida de nacimiento hijos">Partida de nacimiento hijos</option>
                        <option value="Acta de matrimonio o certificado de convivencia">Acta de matrimonio o certificado de convivencia</option>
                        <option value="Curriculum Vitae">Curriculum Vitae</option>
                        <option value="Declaración Jurada de Domicilio">Declaración Jurada de Domicilio</option>
                        <option value="Alta AFIP">Alta AFIP</option>
                        <option value="Formulario 2.61 ANSES">Formulario 2.61 ANSES</option>
                        <option value="Análisis Médicos (medicina del trabajo)">Análisis Médicos (medicina del trabajo)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="archivo" class="form-label">Archivo (PDF):</label>
                    <input type="file" name="archivo" id="archivo" class="form-control" accept="application/pdf" required>
                </div>
                <button type="submit" class="btn btn-success">Subir Documento</button>
            </form>
        </section>

        <!-- Sección de Vacaciones -->
        <section id="historial-vacaciones" class="section-block mt-4">
            <h2>Historial de Vacaciones Aceptadas</h2>
            <ul class="list-group">
                <?php if (empty($vacaciones)): ?>
                    <li class="list-group-item">No se encontraron vacaciones aceptadas.</li>
                <?php else: ?>
                    <?php foreach ($vacaciones as $vacacion): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>Desde:</strong> <?= htmlspecialchars($vacacion['fecha_inicio']) ?> <strong>Hasta:</strong> <?= htmlspecialchars($vacacion['fecha_fin']) ?> (Días: <?= htmlspecialchars($vacacion['dias_solicitados']) ?>) <em><?= htmlspecialchars($vacacion['comentarios']) ?></em></span>
                            <?php if (!empty($vacacion['pdf_generado'])): ?>
                                <a href="uploads/solicitudes/<?= htmlspecialchars($vacacion['pdf_generado']) ?>" target="_blank" class="btn btn-pdf">Ver PDF</a>
                            <?php else: ?>
                                <span>PDF no disponible</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>

        <!-- Sección de Licencias -->
        <section id="historial-licencias" class="section-block mt-4">
            <h2>Historial de Licencias Aceptadas</h2>
            <ul class="list-group">
                <?php if (empty($licencias)): ?>
                    <li class="list-group-item">No se encontraron licencias aceptadas.</li>
                <?php else: ?>
                    <?php foreach ($licencias as $licencia): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>Desde:</strong> <?= htmlspecialchars($licencia['fecha_inicio']) ?> <strong>Hasta:</strong> <?= htmlspecialchars($licencia['fecha_fin']) ?> (Días: <?= htmlspecialchars($licencia['dias']) ?>) <em><?= htmlspecialchars($licencia['observaciones']) ?></em></span>
                            <?php if (!empty($licencia['pdf_generado'])): ?>
                                <a href="uploads/inasistencias/<?= htmlspecialchars($licencia['pdf_generado']) ?>" target="_blank" class="btn btn-pdf">Ver PDF</a>
                            <?php else: ?>
                                <span>PDF no disponible</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>

        <!-- Sección de Asistencia -->
        <section id="asistencia" class="section-block mt-4">
            <h2>Asistencia</h2>
            <ul class="list-group">
                <li class="list-group-item"><strong>Inasistencias este mes:</strong> <?= htmlspecialchars($total_ausencias_mes) ?></li>
                <li class="list-group-item"><strong>Horas trabajadas:</strong> <?= number_format($acumulado_horas_mes, 2) ?> horas</li>
            </ul>
        </section>

        <!-- Botón para regresar -->
        <a href="dashboard_legajos.php" class="btn btn-secondary mt-3">Volver a la lista de empleados</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/Ocultar el formulario de edición
        document.getElementById('edit-datos').addEventListener('click', function() {
            var form = document.getElementById('form-datos');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });

        // Mostrar/Ocultar el formulario de subir documentos
        document.getElementById('show-upload-form').addEventListener('click', function() {
            var form = document.getElementById('upload-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    </script>
</body>
</html>


