<?php
// Incluir la configuración de la base de datos
include '../config/config.php';
include 'config/auth.php';

// Consultas para obtener datos
$sqlEmpleados = "SELECT COUNT(*) AS total_empleados FROM empleados";
$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->execute();
$totalEmpleados = $stmtEmpleados->fetch(PDO::FETCH_ASSOC)['total_empleados'];

$hoy = date('Y-m-d');
$sqlPresentes = "SELECT COUNT(*) AS presentes_hoy FROM asistencia WHERE fecha = :hoy AND (estado = 'presente' OR estado = 'Presente (Tardanza)' OR estado = 'Presente (Tardanza: Justificada)')";
$stmtPresentes = $pdo->prepare($sqlPresentes);
$stmtPresentes->bindParam(':hoy', $hoy);
$stmtPresentes->execute();
$presentesHoy = $stmtPresentes->fetch(PDO::FETCH_ASSOC)['presentes_hoy'];

$sqlAusentes = "SELECT COUNT(*) AS ausentes_hoy FROM asistencia WHERE fecha = :hoy AND estado = 'ausente'";
$stmtAusentes = $pdo->prepare($sqlAusentes);
$stmtAusentes->bindParam(':hoy', $hoy);
$stmtAusentes->execute();
$ausentesHoy = $stmtAusentes->fetch(PDO::FETCH_ASSOC)['ausentes_hoy'];

$sqlTardanzas = "SELECT COUNT(*) AS tardanzas_hoy FROM asistencia WHERE fecha = :hoy AND (estado = 'Presente (Tardanza)' OR estado = 'Presente (Tardanza: Justificada)')";
$stmtTardanzas = $pdo->prepare($sqlTardanzas);
$stmtTardanzas->bindParam(':hoy', $hoy);
$stmtTardanzas->execute();
$tardanzasHoy = $stmtTardanzas->fetch(PDO::FETCH_ASSOC)['tardanzas_hoy'];

$sqlVacaciones = "SELECT COUNT(*) AS vacaciones_actuales FROM historial_vacaciones WHERE estado = 'aprobado' AND CURDATE() BETWEEN fecha_inicio AND fecha_fin";
$stmtVacaciones = $pdo->prepare($sqlVacaciones);
$stmtVacaciones->execute();
$vacacionesActuales = $stmtVacaciones->fetch(PDO::FETCH_ASSOC)['vacaciones_actuales'];

$sqlInasistencias = "SELECT COUNT(*) AS inasistencias_hoy FROM asistencia WHERE fecha = :hoy AND estado = 'ausente'";
$stmtInasistencias = $pdo->prepare($sqlInasistencias);
$stmtInasistencias->bindParam(':hoy', $hoy);
$stmtInasistencias->execute();
$inasistenciasHoy = $stmtInasistencias->fetch(PDO::FETCH_ASSOC)['inasistencias_hoy'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Principal - Recursos Humanos</title>
    <link rel="stylesheet" href="css/main.css">
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

    <div class="container mt-5 pt-5">

       <h2 class="text-center">Hola  <!-- <?= htmlspecialchars($usuario) ?>* -->,¿cómo te va hoy? :D</h2>

        <h1 class="text-center">Panel de Control - Recursos Humanos</h1>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Asistencia</div>
                    <div class="card-body">
                        <p>Total de empleados: <strong><?= $totalEmpleados ?></strong></p>
                        <p>Presentes hoy: <strong><?= $presentesHoy ?></strong></p>
                        <p>Ausentes hoy: <strong><?= $ausentesHoy ?></strong></p>
                        <p>Tardanzas hoy: <strong><?= $tardanzasHoy ?></strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Vacaciones</div>
                    <div class="card-body">
                        <p>Empleados en vacaciones: <strong><?= $vacacionesActuales ?></strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-header">Inasistencias</div>
                    <div class="card-body">
                        <p>Inasistencias hoy: <strong><?= $inasistenciasHoy ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>