<?php
// Incluir la configuración de la base de datos
require '../config/config.php';
require 'config/auth.php';

// Función para obtener empleados con o sin búsqueda
function obtenerEmpleados($busqueda = '') {
    global $pdo;

    // Si hay una búsqueda, agregamos un filtro por nombre o apellido
    if (!empty($busqueda)) {
        $sql = "SELECT e.id, e.nombre, e.apellido, s.vacaciones_restantes, s.vacaciones_acumuladas 
                FROM empleados e
                LEFT JOIN saldo_vacaciones s ON e.id = s.empleado_id
                WHERE e.nombre LIKE :busqueda OR e.apellido LIKE :busqueda";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busqueda', '%' . $busqueda . '%');
    } else {
        // Si no hay búsqueda, traemos todos los empleados
        $sql = "SELECT e.id, e.nombre, e.apellido, s.vacaciones_restantes, s.vacaciones_acumuladas 
                FROM empleados e
                LEFT JOIN saldo_vacaciones s ON e.id = s.empleado_id";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Procesar búsqueda si está presente
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$empleados = obtenerEmpleados($busqueda);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Vacaciones - Recursos Humanos</title>

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
        <div class="container-fluid">
            <!-- Contenido principal -->
            <main class="col-12 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Lista de Empleados</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button class="btn btn-primary" id="btn-solicitud"><i class="material-icons">add_circle</i> Solicitar Vacaciones</button>
                            </div>
                        </div>
                    </div>

                    <!-- Campo de búsqueda -->
                    <form method="GET" action="vacaciones.php">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Buscar empleado..." aria-label="Buscar empleado" aria-describedby="button-addon2" id="busqueda_empleado" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>">
                            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Buscar</button>
                        </div>
                    </form>



                    <!-- Tabla de empleados -->
                    <div class="table-responsive">
                        <table class="table table-custom table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Vacaciones Restantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($empleados)): ?>
                                    <?php foreach ($empleados as $empleado): ?>
                                        <tr>
                                            <td><?= $empleado['id'] ?></td>
                                            <td><?= $empleado['nombre'] ?></td>
                                            <td><?= $empleado['apellido'] ?></td>
                                            <td><?= $empleado['vacaciones_restantes'] + $empleado['vacaciones_acumuladas'] ?></td>
                                            <td>                               
                                                <a href="ver_detalles.php?id=<?= $empleado['id'] ?>" class="btn btn-detalles"><i class="fas fa-eye"></i> Ver Detalles</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5">No se encontraron empleados.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </main>
            </div>
        </div>
    </div>
    <!-- Modal para solicitud de vacaciones -->
    <div id="modal-solicitud" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Solicitar Vacaciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="log/procesar_solicitud_vacaciones.php" method="POST">
                        <div class="mb-3">
                            <label for="empleado_id" class="form-label">Empleado:</label>
                            <select name="empleado_id" id="empleado_id" class="form-select" required>
                                <?php foreach ($empleados as $empleado): ?>
                                    <option value="<?= $empleado['id'] ?>"><?= $empleado['nombre'] . " " . $empleado['apellido'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
                        <button type="submit" class="btn btn-primary"><span class="material-icons">send</span> Enviar Solicitud</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('modal-solicitud'));
            var btn = document.getElementById('btn-solicitud');

            // Abrir modal al hacer clic en el botón
            btn.onclick = function() {
                modal.show();
            }
        });
    </script>

    <script src="https://code.jquery.co m/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>