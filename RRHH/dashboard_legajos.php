<?php
// Incluir la configuración de la base de datos
require '../config/config.php';
require 'auth.php';

// Consulta para obtener todos los empleados
$sql = "SELECT e.id, e.nombre, e.apellido FROM empleados e";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legajos de Empleados - Recursos Humanos</title>
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

    <!-- Contenido principal -->
    <div class="container mt-5 pt-5">
        <h1 class="text-center">Legajos de Empleados</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $empleado): ?>
                    <tr>
                        <td><?= $empleado['id'] ?></td>
                        <td><?= $empleado['nombre'] ?></td>
                        <td><?= $empleado['apellido'] ?></td>
                        <td><a href="ver_legajo.php?id=<?= $empleado['id'] ?>" class="btn btn-primary">Ver Legajo</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>