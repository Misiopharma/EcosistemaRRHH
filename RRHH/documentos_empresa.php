<?php
require '../config/config.php';

// Verificar si la carpeta de uploads/documentos_empresa existe, si no, crearla
$upload_dir = 'uploads/documentos_empresa/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Crear la carpeta con permisos de escritura
}

// Obtener la lista de documentos
$sql = "SELECT * FROM documentos_empresa";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para limpiar el nombre del documento (elimina caracteres especiales, espacios, etc.)
function limpiarNombreArchivo($nombre) {
    $nombre_limpio = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre); // Reemplazar caracteres especiales por "_"
    return strtolower($nombre_limpio); // Convertir a minúsculas
}

// Procesar la subida de un nuevo documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_documento'])) {
    $nombre_documento = $_POST['nombre_documento'];
    $archivo = $_FILES['archivo'];

    // Verificar si el archivo es válido
    if ($archivo['error'] === UPLOAD_ERR_OK && $archivo['type'] === 'application/pdf') {
        $nombre_archivo = limpiarNombreArchivo($nombre_documento) . "_" . time() . ".pdf"; // Crear un nombre de archivo único
        $ruta_archivo = $upload_dir . $nombre_archivo; // Guardar en la carpeta correcta

        // Mover el archivo a la carpeta de uploads
        if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
            // Insertar el nuevo documento en la base de datos
            $fecha_subida = date('Y-m-d');
            $sql_insert = "INSERT INTO documentos_empresa (nombre_documento, ruta_archivo, fecha_subida) 
                           VALUES (:nombre_documento, :ruta_archivo, :fecha_subida)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->bindParam(':nombre_documento', $nombre_documento);
            $stmt_insert->bindParam(':ruta_archivo', $ruta_archivo);
            $stmt_insert->bindParam(':fecha_subida', $fecha_subida);
            $stmt_insert->execute();

            // Redirigir a la misma página para actualizar la tabla
            header('Location: documentos_empresa.php');
            exit();
        } else {
            echo "Error al mover el archivo.";
        }
    } else {
        echo "El archivo no es válido o no es un PDF.";
    }
}

// Procesar la actualización de un documento existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_documento'])) {
    $documento_id = $_POST['documento_id'];
    $archivo = $_FILES['archivo_actualizado'];

    // Verificar si el archivo es válido
    if ($archivo['error'] === UPLOAD_ERR_OK && $archivo['type'] === 'application/pdf') {
        $nombre_documento = $_POST['nombre_documento_actualizado']; // Nombre del documento actualizado
        $nombre_archivo = limpiarNombreArchivo($nombre_documento) . "_" . time() . ".pdf"; // Crear un nombre de archivo único
        $ruta_archivo = $upload_dir . $nombre_archivo; // Guardar en la carpeta correcta

        // Mover el archivo a la carpeta de uploads
        if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
            // Actualizar el documento en la base de datos
            $fecha_actualizacion = date('Y-m-d');
            $sql_update = "UPDATE documentos_empresa 
                           SET ruta_archivo = :ruta_archivo, ultima_actualizacion = :ultima_actualizacion 
                           WHERE id = :id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindParam(':ruta_archivo', $ruta_archivo);
            $stmt_update->bindParam(':ultima_actualizacion', $fecha_actualizacion);
            $stmt_update->bindParam(':id', $documento_id);
            $stmt_update->execute();

            // Redirigir a la misma página para actualizar la tabla
            header('Location: documentos_empresa.php');
            exit();
        } else {
            echo "Error al mover el archivo.";
        }
    } else {
        echo "El archivo no es válido o no es un PDF.";
    }
}
?>  

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Documentos - Empresa</title>
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
        <div class="row">
            <div class="col-12">
                <h1 class="text-center">Gestión de Documentos de la Empresa</h1>
            </div>
        </div>

        <!-- Formulario para subir nuevo documento -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2>Subir nuevo documento</h2>
                    </div>
                    <div class="card-body">
                        <form action="documentos_empresa.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nombre_documento" class="form-label">Nombre del documento:</label>
                                <input type="text" class="form-control" name="nombre_documento" id="nombre_documento" required>
                            </div>
                            <div class="mb-3">
                                <label for="archivo" class="form-label">Archivo (PDF):</label>
                                <input type="file" class="form-control" name="archivo" id="archivo" accept="application/pdf" required>
                            </div>
                            <button type="submit" class="btn btn-primary" name="subir_documento">Subir Documento</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Lista de documentos existentes -->
        <div class="row">
            <div class="col-12">
                <h2>Documentos existentes</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre del Documento</th>
                            <th>Fecha de Subida</th>
                            <th>Última Actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $documento): ?>
                            <tr>
                                <td><?= htmlspecialchars($documento['nombre_documento']) ?></td>
                                <td><?= htmlspecialchars($documento['fecha_subida']) ?></td>
                                <td><?= htmlspecialchars($documento['ultima_actualizacion'] ?? 'No actualizado') ?></td>
                                <td>
                                    <a href="<?= htmlspecialchars($documento['ruta_archivo']) ?>" target="_blank" class="btn btn-info">Ver Documento</a>
                                    <button class="btn btn-warning" onclick="openModal(<?= $documento['id'] ?>, '<?= htmlspecialchars($documento['nombre_documento']) ?>')">Actualizar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
                            
    <!-- Modal para actualizar documento -->
    <div id="update-modal" class="modal fade" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Actualizar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="documentos_empresa.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="documento_id" id="documento_id_modal">
                        <div class="mb-3">
                            <label for="nombre_documento_actualizado" class="form-label">Nombre del documento actualizado:</label>
                            <input type="text" class="form-control" name="nombre_documento_actualizado" id="nombre_documento_actualizado" required>
                        </div>
                        <div class="mb-3">
                            <label for="archivo_actualizado" class="form-label">Archivo (PDF):</label>
                            <input type="file" class="form-control" name="archivo_actualizado" id="archivo_actualizado" accept="application/pdf" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="actualizar_documento">Actualizar Documento</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(documentoId, nombreDocumento) {
            document.getElementById('documento_id_modal').value = documentoId;
            document.getElementById('nombre_documento_actualizado').value = nombreDocumento;
            var modal = new bootstrap.Modal(document.getElementById('update-modal'));
            modal.show();
        }
    </script>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
