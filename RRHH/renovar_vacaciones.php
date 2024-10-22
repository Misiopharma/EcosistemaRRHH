<?php
require '../config/config.php';
require 'auth.php';

// Obtener todos los empleados y sus saldos de vacaciones
$sql = "SELECT e.id, e.nombre, e.apellido, s.vacaciones_restantes, s.vacaciones_acumuladas, s.vacaciones_adelantadas 
        FROM empleados e
        LEFT JOIN saldo_vacaciones s ON e.id = s.empleado_id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['renovar_vacaciones'])) {
    // Renovar vacaciones de cada empleado
    foreach ($empleados as $empleado) {
        $vacaciones_restantes = $empleado['vacaciones_restantes'];
        $vacaciones_acumuladas = $empleado['vacaciones_acumuladas'];
        $vacaciones_adelantadas = $empleado['vacaciones_adelantadas'];

        // Calcular las vacaciones acumuladas y adelantadas
        if ($vacaciones_restantes > 0) {
            $vacaciones_acumuladas += $vacaciones_restantes;
        }

        // Calcular las nuevas vacaciones restantes considerando días adelantados
        $vacaciones_restantes = 14 + $vacaciones_acumuladas - $vacaciones_adelantadas;
        $vacaciones_acumuladas = max(0, $vacaciones_acumuladas - $vacaciones_adelantadas);

        // Asegurarse de que los días restantes no sean negativos
        $vacaciones_restantes = max(0, $vacaciones_restantes);

        // Reiniciar los días adelantados
        $vacaciones_adelantadas = 0;

        // Actualizar saldo de vacaciones para cada empleado
        $sql_update = "UPDATE saldo_vacaciones 
                       SET vacaciones_restantes = :vacaciones_restantes, 
                           vacaciones_acumuladas = :vacaciones_acumuladas, 
                           vacaciones_adelantadas = :vacaciones_adelantadas
                       WHERE empleado_id = :empleado_id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':vacaciones_restantes', $vacaciones_restantes);
        $stmt_update->bindParam(':vacaciones_acumuladas', $vacaciones_acumuladas);
        $stmt_update->bindParam(':vacaciones_adelantadas', $vacaciones_adelantadas);
        $stmt_update->bindParam(':empleado_id', $empleado['id']);
        $stmt_update->execute();
    }

    // Redirigir a la página de renovación de vacaciones con un mensaje de éxito
    header("Location: renovar_vacaciones.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renovar Vacaciones - Recursos Humanos</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Recursos Humanos</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="dashboardprincipal.php"><i class="fas fa-home"></i> Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard_legajos.php"><i class="fas fa-folder"></i> Legajos</a>
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
                        <li><a class="dropdown-item" href="solicitudes_pendientes.php"><i class="fas fa-check-circle"></i> Autorización Vacaciones</a></li>
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
                        <li><a class="dropdown-item" href="crear_empleado.php"><i class="fas fa-user-plus"></i> Crear Empleado</a></li>
                        <li><a class="dropdown-item" href="renovar_vacaciones.php"><i class="fas fa-sync-alt"></i> Renovar Vacaciones</a></li>
                        <li><a class="dropdown-item" href="documentos_empresa.php"><i class="fas fa-file-alt"></i> Gestión Documentos</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-5">
    <h1>Renovar Vacaciones</h1>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" role="alert">
            Las vacaciones se han renovado exitosamente.
        </div>
    <?php endif; ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Vacaciones Restantes</th>
                <th>Vacaciones Acumuladas</th>
                <th>Vacaciones Adelantadas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empleados as $empleado): ?>
                <tr>
                    <td><?= htmlspecialchars($empleado['id']) ?></td>
                    <td><?= htmlspecialchars($empleado['nombre']) ?></td>
                    <td><?= htmlspecialchars($empleado['apellido']) ?></td>
                    <td><?= htmlspecialchars($empleado['vacaciones_restantes']) ?></td>
                    <td><?= htmlspecialchars($empleado['vacaciones_acumuladas']) ?></td>
                    <td><?= htmlspecialchars($empleado['vacaciones_adelantadas']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="fixed-bottom text-center mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModal">Renovar Vacaciones</button>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Renovación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas renovar las vacaciones de todos los empleados?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="renovar_vacaciones.php" method="POST">
                    <button type="submit" name="renovar_vacaciones" class="btn btn-primary">Renovar Vacaciones</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
