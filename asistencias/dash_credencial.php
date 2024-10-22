<?php
session_start();

// Verificar si el usuario es operario
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: login.php");
    exit;
}

include 'config.php';

$mensaje = "";
$mostrarModal = false; // Variable para controlar la visualización del modal

// Lógica para registrar la hora de entrada en tiempo real con tardanza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_entrada'])) {
    $empleado_id = $_POST['empleado_id'];
    $fecha = date('Y-m-d');  
    $hora_entrada = date('H:i:s');  
    $hora_limite = '07:30:00'; // Hora límite para considerar tardanza

    // Verificar si ya existe un registro de asistencia para ese día
    $stmt = $pdo->prepare("SELECT id, estado FROM asistencia WHERE empleado_id = ? AND fecha = ?");
    $stmt->execute([$empleado_id, $fecha]);
    $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si el empleado es el ID 4, no se considera tardanza
    if ($empleado_id == 4) {
        $estado = 'Presente';
    } else {
        $estado = $hora_entrada > $hora_limite ? 'Presente (Tardanza)' : 'Presente';
    }



    if ($asistencia) {
        $stmt = $pdo->prepare("UPDATE asistencia SET hora_entrada = ?, estado = ? WHERE id = ?");
        $stmt->execute([$hora_entrada, $estado, $asistencia['id']]);

        // Si es tardanza y no es el empleado ID 4, mostrar modal para justificar
        if ($estado === 'Presente (Tardanza)' && $empleado_id != 4) {
            $_SESSION['asistencia_id'] = $asistencia['id'];
            $mostrarModal = true;
        }
    } else {
        $estado = $hora_entrada > $hora_limite ? 'Presente (Tardanza)' : 'Presente';
        $stmt = $pdo->prepare("INSERT INTO asistencia (empleado_id, fecha, hora_entrada, estado) VALUES (?, ?, ?, ?)");
        $stmt->execute([$empleado_id, $fecha, $hora_entrada, $estado]);

        if ($estado === 'Presente (Tardanza)') {
            $_SESSION['asistencia_id'] = $pdo->lastInsertId();
            $mostrarModal = true;
        }
    }

    $mensaje = "Hora de entrada registrada exitosamente.";
}

// Lógica para justificar la tardanza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['justificar_tardanza'])) {
    $asistencia_id = $_SESSION['asistencia_id'];
    $justificacion_tardanza = $_POST['justificacion_tardanza'];

    $stmt = $pdo->prepare("UPDATE asistencia SET justificacion_tardanza = ?, estado = 'Presente (Tardanza: Justificada)' WHERE id = ?");
    $stmt->execute([$justificacion_tardanza, $asistencia_id]);

    $mensaje = "Tardanza justificada exitosamente.";
    $mostrarModal = false; // Cerrar modal después de la justificación
}

// Lógica para registrar la hora de salida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_salida'])) {
    $empleado_id = $_POST['empleado_id'];
    $fecha = date('Y-m-d');  
    $hora_salida = date('H:i:s');  

    $stmt = $pdo->prepare("SELECT id FROM asistencia WHERE empleado_id = ? AND fecha = ?");
    $stmt->execute([$empleado_id, $fecha]);
    $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($asistencia) {
        $stmt = $pdo->prepare("UPDATE asistencia SET hora_salida = ? WHERE id = ?");
        $stmt->execute([$hora_salida, $asistencia['id']]);
        $mensaje = "Hora de salida registrada exitosamente.";
    } else {
        $mensaje = "Error: No se puede registrar la salida sin registrar la entrada primero.";
    }
}

// Obtener lista de empleados
$empleados = $pdo->query("SELECT id, nombre, apellido FROM empleados")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcación de Asistencia</title>
    <link rel="stylesheet" href="css/dash_credencial.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <h1>Asistencia</h1>
        </header>

        <div class="content-container">
            <div class="grid-container">
                <div class="grid-item" onclick="toggleForm('form-entrada')">
                    <div class="icon-container">
                        <img src="img/entrada_icon.png" alt="Entrada">
                    </div>
                    <p>Marcar Entrada</p>
                </div>
                <div class="grid-item" onclick="toggleForm('form-salida')">
                    <div class="icon-container">
                        <img src="img/salida_icon.png" alt="Salida">
                    </div>
                    <p>Marcar Salida</p>
                </div>
            </div>

            <!-- Formulario para registrar la entrada -->
            <div id="form-entrada" class="form-container">
                <h2>Registrar Entrada</h2>
                <form action="dash_credencial.php" method="POST">
                    <label for="empleado_id">Seleccionar Empleado:</label>
                    <select id="empleado_id" name="empleado_id" required>
                        <option value="">Seleccione un empleado</option>
                        <?php foreach ($empleados as $empleado): ?>
                            <option value="<?php echo $empleado['id']; ?>"><?php echo $empleado['nombre'] . ' ' . $empleado['apellido']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="registrar_entrada">Registrar Entrada</button>
                </form>
            </div>

            <!-- Formulario para registrar la salida -->
            <div id="form-salida" class="form-container">
                <h2>Registrar Salida</h2>
                <form action="dash_credencial.php" method="POST">
                    <label for="empleado_id">Seleccionar Empleado:</label>
                    <select id="empleado_id" name="empleado_id" required>
                        <option value="">Seleccione un empleado</option>
                        <?php foreach ($empleados as $empleado): ?>
                            <option value="<?php echo $empleado['id']; ?>"><?php echo $empleado['nombre'] . ' ' . $empleado['apellido']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="registrar_salida">Registrar Salida</button>
                </form>
            </div>

            <div id="mensaje" class="mensaje" style="display:none;"><?php echo $mensaje; ?></div>
        </div>

        <footer class="app-footer">
            <a href="logout.php" class="logout">Cerrar Sesión</a>
        </footer>
    </div>

    <!-- Modal para justificar tardanza -->
    <div id="justificarTardanzaModal" class="modal" style="display: <?php echo $mostrarModal ? 'block' : 'none'; ?>;">
        <div class="modal-content">
            <span class="close" onclick="closeJustificacionModal()">&times;</span>
            <h2>Justificar Tardanza</h2>
            <form action="dash_credencial.php" method="POST">
                <label for="justificacion_tardanza">Justificación:</label>
                <textarea id="justificacion_tardanza" name="justificacion_tardanza" rows="4" required></textarea>
                <button type="submit" name="justificar_tardanza">Justificar Tardanza</button>
            </form>
        </div>
    </div>

    <script>
        function toggleForm(formId) {
            var forms = document.querySelectorAll('.form-container');
            forms.forEach(function(form) {
                form.style.display = 'none';
            });
            document.getElementById(formId).style.display = 'block';
        }

        function closeJustificacionModal() {
            document.getElementById('justificarTardanzaModal').style.display = 'none';
        }

        <?php if (!empty($mensaje)): ?>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById('mensaje').style.display = 'block';
            setTimeout(function() {
                document.getElementById('mensaje').style.display = 'none';
            }, 3000);
        });
        <?php endif; ?>
    </script>
</body>
</html>
