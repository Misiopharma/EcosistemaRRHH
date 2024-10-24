<?php
require '../config/config.php';
require 'config/auth.php';

// Función para obtener empleados con o sin búsqueda
function obtenerEmpleados($busqueda = '') {
    global $pdo;
    if (!empty($busqueda)) {
        $sql = "SELECT id, nombre, apellido FROM empleados WHERE nombre LIKE :busqueda OR apellido LIKE :busqueda";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busqueda', '%' . $busqueda . '%');
    } else {
        $sql = "SELECT id, nombre, apellido FROM empleados";
        $stmt = $pdo->prepare($sql);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener las solicitudes de inasistencia pendientes
function obtenerSolicitudesInasistencias() {
    global $pdo;
    $sql = "SELECT si.id, e.nombre, e.apellido, si.fecha_inicio, si.fecha_fin, si.dias, si.estado 
            FROM inasistencias si
            JOIN empleados e ON si.empleado_id = e.id
            WHERE si.estado = 'Pendiente'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Procesar la solicitud de inasistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_inasistencia'])) {
    $empleado_id = $_POST['empleado_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $observaciones = $_POST['observaciones'];

    // Calcular días de inasistencia
    $datetime1 = new DateTime($fecha_inicio);
    $datetime2 = new DateTime($fecha_fin);
    $interval = $datetime1->diff($datetime2);
    $dias_inasistencia = $interval->days + 1;

    // Insertar la solicitud de inasistencia en la base de datos
    $sql_inasistencia = "INSERT INTO inasistencias (empleado_id, fecha_inicio, fecha_fin, dias, observaciones, estado) 
                         VALUES (:empleado_id, :fecha_inicio, :fecha_fin, :dias, :observaciones, 'Pendiente')";
    $stmt_inasistencia = $conn->prepare($sql_inasistencia);
    $stmt_inasistencia->bindParam(':empleado_id', $empleado_id);
    $stmt_inasistencia->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt_inasistencia->bindParam(':fecha_fin', $fecha_fin);
    $stmt_inasistencia->bindParam(':dias', $dias_inasistencia);
    $stmt_inasistencia->bindParam(':observaciones', $observaciones);
    $stmt_inasistencia->execute();

    // Redirigir a la página para generar el archivo imprimible
    header("Location: log/generar_solicitud_inasistencias.php?empleado_id=$empleado_id&fecha_inicio=$fecha_inicio&fecha_fin=$fecha_fin&observaciones=$observaciones");
    exit();
}

// Procesar las solicitudes de inasistencias para ser mostradas
$empleados = obtenerEmpleados();
$solicitudes_inasistencias = obtenerSolicitudesInasistencias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Inasistencias - Recursos Humanos</title>

    <link rel="stylesheet" href="css/style_inasistencias.css"> <!-- Enlace al nuevo archivo CSS -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Recursos Humanos</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="inicio.php"><i class="fas fa-home"></i> Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="legajos.php"><i class="fas fa-folder"></i> Legajos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="politicas.php"><i class="fas fa-gavel"></i> Políticas</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="vacacionesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-briefcase"></i> Vacaciones
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="vacacionesDropdown">
                            <li><a class="dropdown-item" href="vacaciones.php"><i class="fas fa-list"></i> Lista de Empleados</a></li>
                            <li><a class="dropdown-item" href="vacaciones/pendientes.php"><i class="fas fa-check-circle"></i> Autorización Vacaciones</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inasistencias.php"><i class="fas fa-user-times"></i> Inasistencias</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="configuracionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cogs"></i> Configuración
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="configuracionDropdown">
                            <li><a class="dropdown-item" href="configuracion/crear_empleado.php"><i class="fas fa-user-plus"></i> Crear Empleado</a></li>
                            <li><a class="dropdown-item" href="configuracion/renovar_vacaciones.php"><i class="fas fa-sync-alt"></i> Renovar Vacaciones</a></li>
                            <li><a class="dropdown-item" href="configuracion/documentos_empresa.php"><i class="fas fa-file-alt"></i> Gestión Documentos</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../controller/logoutcontroller.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
                    </li>
                </ul>   
            </div>
        </div>
    </nav>



<div class="container main-content mt-5 pt-5">
    <!-- Solicitar Inasistencia -->
    <section class="section-header mb-5">
        <h2>Solicitar Inasistencia</h2>
        <div class="mb-3">
            <label for="buscar_empleado" class="form-label">Buscar Empleado:</label>
            <input type="text" id="buscar_empleado" class="form-control" placeholder="Buscar empleado..." autocomplete="off">
            <div id="resultados_busqueda" class="list-group mt-2"></div>
        </div>

        <!-- Formulario de inasistencia -->
        <form id="form-inasistencia" action="inasistencias.php" method="POST">
            <input type="hidden" name="empleado_id" id="empleado_id">
            <div class="mb-3">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio:</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="fecha_fin" class="form-label">Fecha de Fin:</label>
                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones:</label>
                <textarea name="observaciones" id="observaciones" class="form-control"></textarea>
            </div>
            <button type="submit" name="solicitar_inasistencia" class="btn btn-primary">Solicitar Inasistencia</button>
        </form>
    </section>

    <!-- Solicitudes Pendientes -->
    <section class="section-pendientes">
        <div class="solicitudes-header mb-3">
            <h2>Solicitudes Pendientes</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Fecha de Inicio</th>
                        <th>Fecha de Fin</th>
                        <th>Días</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes_inasistencias as $solicitud): ?>
                        <tr>
                            <td><?= htmlspecialchars($solicitud['nombre']) . " " . htmlspecialchars($solicitud['apellido']) ?></td>
                            <td><?= date('d/m/Y', strtotime($solicitud['fecha_inicio'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($solicitud['fecha_fin'])) ?></td>
                            <td><?= $solicitud['dias'] ?></td>
                            <td><?= htmlspecialchars($solicitud['estado']) ?></td>
                            <td>
                                <form action="log/gestionar_inasistencias.php" method="POST" class="d-inline">
                                    <input type="hidden" name="inasistencia_id" value="<?= $solicitud['id'] ?>">
                                    <input type="hidden" name="accion" value="aceptar">
                                    <button type="submit" class="btn btn-success btn-sm">Aprobar</button>
                                </form>
                                <form action="log/gestionar_inasistencias.php" method="POST" class="d-inline">
                                    <input type="hidden" name="inasistencia_id" value="<?= $solicitud['id'] ?>">
                                    <input type="hidden" name="accion" value="rechazar">
                                    <button type="submit" class="btn btn-danger btn-sm">Rechazar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

<!-- Script de búsqueda instantánea -->
<script>
    $(document).ready(function() {
        // Búsqueda instantánea
        $('#buscar_empleado').on('input', function() {
            var query = $(this).val();
            if (query != '') {
                $.ajax({
                    url: 'buscar_empleado_inasistencia.php',
                    method: 'POST',
                    data: {query: query},
                    success: function(data) {
                        $('#resultados_busqueda').fadeIn();
                        $('#resultados_busqueda').html(data);
                    }
                });
            } else {
                $('#resultados_busqueda').fadeOut();
            }
        });

        // Mostrar el formulario al seleccionar un empleado
        $(document).on('click', '.empleado-result', function() {
            var empleado_id = $(this).data('id');
            var empleado_nombre = $(this).text();

            $('#buscar_empleado').val(empleado_nombre);
            $('#empleado_id').val(empleado_id);
            $('#resultados_busqueda').fadeOut();
            $('#form-inasistencia').fadeIn();
        });
    });
</script>

</body>
</html>
