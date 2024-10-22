<?php
include 'config.php';

$mensaje = '';

// Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $puesto = $_POST['puesto'];

    // Insertar el nuevo empleado en la base de datos
    $sql_insert = "INSERT INTO empleados (nombre, apellido, puesto) VALUES (:nombre, :apellido, :puesto)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bindParam(':nombre', $nombre);
    $stmt_insert->bindParam(':apellido', $apellido);
    $stmt_insert->bindParam(':puesto', $puesto);
    $stmt_insert->execute();

    // Establecer el mensaje de confirmación
    $mensaje = 'Empleado creado exitosamente.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Crear Nuevo Empleado</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
</head>
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        text-align: center;
        position: relative;
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .modal-content .close-btn { 
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .close-btn:hover,
    .close-btn:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-content p {
        font-size: 18px;
        margin: 20px 0;
    }

    .modal-content .valid-icon {
        font-size: 50px;
        color: green;
        margin-bottom: 20px;
    }
</style>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2>Crear Nuevo Empleado</h2>
                    </div>
                    <div class="card-body">
                        <form action="crear_empleado.php" method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido:</label>
                                <input type="text" id="apellido" name="apellido" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="puesto" class="form-label">Puesto:</label>
                                <input type="text" id="puesto" name="puesto" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success">Crear Empleado</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div id="modal" class="modal">
            <div class="modal-content">
                <span class="close-btn" id="close-btn">&times;</span>
                <i class="fas fa-check-circle valid-icon"></i>
                <p><?= htmlspecialchars($mensaje) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Mostrar la ventana modal si hay un mensaje
        <?php if ($mensaje): ?>
            document.getElementById('modal').style.display = 'block';
        <?php endif; ?>

        // Cerrar la ventana modal
        document.getElementById('close-btn').onclick = function() {
            document.getElementById('modal').style.display = 'none';
        }
    </script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

