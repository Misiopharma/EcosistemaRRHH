<?php
session_start();

// Verificar si el usuario es administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {  
    header("Location: login.php");
    exit;
}

include 'config.php';

$mensaje = "";

// Lógica para crear un nuevo usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    $nombre = $_POST['nombre_usuario'];
    $password = $_POST['password_usuario'];
    $rol_id = $_POST['rol_usuario'];

    $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password, rol_id) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $password, $rol_id]);

    $mensaje = "Usuario creado exitosamente.";
}

// Lógica para crear un nuevo empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_empleado'])) {
    $nombre = $_POST['nombre_empleado'];
    $apellido = $_POST['apellido_empleado'];
    $puesto = $_POST['puesto_empleado'];

    $stmt = $pdo->prepare("INSERT INTO empleados (nombre, apellido, puesto) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $apellido, $puesto]);

    $mensaje = "Empleado creado exitosamente.";
}

// Lógica para justificar la tardanza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['justificar_tardanza'])) {
    $asistencia_id = $_POST['asistencia_id'];
    $justificacion_tardanza = $_POST['justificacion_tardanza'];

    try {
        $stmt = $pdo->prepare("UPDATE asistencia SET justificacion_tardanza = ?, estado = 'Presente (Tardanza: Justificada)' WHERE id = ?");
        $stmt->execute([$justificacion_tardanza, $asistencia_id]);

        $mensaje = "Tardanza justificada exitosamente.";
    } catch (Exception $e) {
        $mensaje = "Error al justificar la tardanza: " . $e->getMessage();
    }
}

// Fecha actual
$fecha_actual = date('Y-m-d');
$dia_semana = date('N', strtotime($fecha_actual)); // 1 = Lunes, 7 = Domingo

// Revisar si es día no laboral (sábado o domingo)
if ($dia_semana == 6 || $dia_semana == 7) {
    foreach ($empleados as $empleado) {
        // Verificar si ya existe un registro de asistencia para ese día
        $stmt = $pdo->prepare("SELECT id FROM asistencia WHERE empleado_id = ? AND fecha = ?");
        $stmt->execute([$empleado['id'], $fecha_actual]);
        $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$asistencia) {
            // Si no hay registro, insertar como Día no laboral
            $stmt = $pdo->prepare("INSERT INTO asistencia (empleado_id, fecha, estado) VALUES (?, ?, 'Día no laboral')");
            $stmt->execute([$empleado['id'], $fecha_actual]);
        }
    }
} else {
    // Lógica para registrar la hora de entrada en días laborales
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_entrada'])) {
        $empleado_id = $_POST['empleado_id'];
        $hora_entrada = date('H:i:s');  
        $hora_limite = '07:30:00'; // Hora límite para considerar tardanza

        // Verificar si ya existe un registro de asistencia para ese día
        $stmt = $pdo->prepare("SELECT id, estado FROM asistencia WHERE empleado_id = ? AND fecha = ?");
        $stmt->execute([$empleado_id, $fecha_actual]);
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
        } else {
            $stmt = $pdo->prepare("INSERT INTO asistencia (empleado_id, fecha, hora_entrada, estado) VALUES (?, ?, ?, ?)");
            $stmt->execute([$empleado_id, $fecha_actual, $hora_entrada, $estado]);
        }

        // Si es tardanza y no es el empleado ID 4, mostrar modal para justificar
        if ($estado === 'Presente (Tardanza)' && $empleado_id != 4) {
            $_SESSION['mostrar_modal'] = true;
            $_SESSION['asistencia_id'] = $asistencia['id'] ?? $pdo->lastInsertId();
        }

        $mensaje = "Hora de entrada registrada exitosamente.";
    }

    // Lógica para registrar la hora de salida posteriormente
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_salida'])) {
        $empleado_id = $_POST['empleado_id'];
        $hora_salida = date('H:i:s');  

        $stmt = $pdo->prepare("SELECT id FROM asistencia WHERE empleado_id = ? AND fecha = ?");
        $stmt->execute([$empleado_id, $fecha_actual]);
        $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($asistencia) {
            $stmt = $pdo->prepare("UPDATE asistencia SET hora_salida = ? WHERE id = ?");
            $stmt->execute([$hora_salida, $asistencia['id']]);
            $mensaje = "Hora de salida registrada exitosamente.";
        } else {
            $mensaje = "Error: No se puede registrar la salida sin registrar la entrada primero.";
        }
    }

    // Lógica para notificar una inasistencia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notificar_inasistencia'])) {
        $empleado_id = $_POST['empleado_id'];
        $fecha = $_POST['fecha'];
        $justificacion = $_POST['justificacion'];

        $stmt = $pdo->prepare("SELECT id FROM asistencia WHERE empleado_id = ? AND fecha = ?");
        $stmt->execute([$empleado_id, $fecha]);
        $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($asistencia) {
            $stmt = $pdo->prepare("UPDATE asistencia SET estado = 'Ausente', justificacion = ? WHERE id = ?");
            $stmt->execute([$justificacion, $asistencia['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO asistencia (empleado_id, fecha, estado, justificacion) VALUES (?, ?, 'Ausente', ?)");
            $stmt->execute([$empleado_id, $fecha, $justificacion]);
        }

        $mensaje = "Inasistencia notificada exitosamente.";
    }
}

// Obtener lista de empleados y su estado de asistencia del día actual
$empleados_asistencia = $pdo->prepare("SELECT e.id, e.nombre, e.apellido, a.hora_entrada, a.hora_salida, a.estado, a.justificacion 
                                        FROM empleados e 
                                        LEFT JOIN asistencia a ON e.id = a.empleado_id AND a.fecha = ?");
$empleados_asistencia->execute([$fecha_actual]);
$lista_asistencia = $empleados_asistencia->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador</title>
    <link rel="stylesheet" href="css/admin_dash.css">
    <style>
        /* Estilos para el dashboard */
        .dashboard-container {
            width: 90%;
            margin: 0 auto;
            text-align: center;
        }

        .acciones button {
            margin: 10px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            color: #fff;
            background-color: #66bb6a;
            border: none;
            border-radius: 5px;
        }

        .acciones button:hover {
            background-color: #57a05c;
        }

        .form-container {
            display: none;
            margin-top: 20px;
            background-color: #2d2d2d;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container.active {
            display: block;
        }

        .monitor {
            margin-top: 30px;
            background-color: #333;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #444;
            border-radius: 5px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #666;
        }

        th {
            background-color: #66bb6a;
            color: #fff;
        }

        tr:nth-child(even) td {
            background-color: #555;
        }

        a {
            color: #66bb6a;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .mensaje {
            color: green;
            margin-top: 20px;
        }

        /* Estilos para la Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
        function toggleForm(formId) {
            document.querySelectorAll('.form-container').forEach(function(form) {
                form.classList.remove('active');
            });
            document.getElementById(formId).classList.add('active');
        }

        function openJustificarModal() {
            document.getElementById('justificarTardanzaModal').style.display = "block";
        }

        function closeJustificarModal() {
            document.getElementById('justificarTardanzaModal').style.display = "none";
        }

        // Mostrar la ventana modal automáticamente si es necesario
        <?php if (isset($_SESSION['mostrar_modal']) && $_SESSION['mostrar_modal']): ?>
            openJustificarModal();
            <?php unset($_SESSION['mostrar_modal']); ?>
        <?php endif; ?>
    </script>
</head>
<body>
    <div class="dashboard-container">
        <h1>Dashboard del Administrador</h1>

        <?php if ($mensaje): ?>
            <div class="mensaje">
                <p><?php echo $mensaje; ?></p>
            </div>
        <?php endif; ?>

        <!-- Botones para mostrar formularios -->
        <div class="acciones">
            <button onclick="toggleForm('crearEmpleadoForm')">Crear Empleado</button>
            <button onclick="toggleForm('crearUsuarioForm')">Crear Usuario para Login</button>
            <button onclick="toggleForm('registrarEntradaForm')">Registrar Entrada</button>
            <button onclick="toggleForm('registrarSalidaForm')">Registrar Salida</button>
            <button onclick="toggleForm('notificarInasistenciaForm')">Notificar Inasistencia</button>
        </div>

        <!-- Visualizador de Asistencia -->
        <div class="monitor">
            <h2>Monitor de Asistencia</h2>
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Estado</th>
                        <th>Hora de Entrada</th>
                        <th>Hora de Salida</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista_asistencia as $asistencia): ?>
                        <tr>
                            <td><a href="ver_detalles.php?empleado_id=<?php echo $asistencia['id']; ?>"><?php echo $asistencia['nombre'] . ' ' . $asistencia['apellido']; ?></a></td>
                            <td>
                                <?php
                                if ($asistencia['estado'] === 'Ausente') {
                                    if (!empty($asistencia['justificacion'])) {
                                        echo "Ausente (Motivo: " . htmlspecialchars($asistencia['justificacion']) . ")";
                                    } else {
                                         echo "Ausente (Sin justificación)";
                                    }
                                } elseif ($asistencia['estado'] === 'Presente (Tardanza)') {
                                    echo "Presente (Tardanza)";
                                } elseif ($asistencia['estado'] === 'Presente (Tardanza: Justificada)') {
                                    echo "Presente (Tardanza: Justificada)";
                                } elseif ($asistencia['estado'] === 'Día no laboral') {
                                    echo "Día no laboral";
                                } else {
                                    echo "Presente";
                                }
                                ?>
                            </td>

                            <td><?php echo $asistencia['hora_entrada'] ? $asistencia['hora_entrada'] : 'N/A'; ?></td>
                            <td><?php echo $asistencia['hora_salida'] ? $asistencia['hora_salida'] : 'En horario laboral'; ?></td>
                            <td>
                                <?php if ($asistencia['estado'] === 'Presente (Tardanza)' && empty($asistencia['justificacion_tardanza'])): ?>
                                    <button onclick="openJustificarModal()">Justificar Tardanza</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Formularios Desplegables -->
        <!-- Crear Empleado -->
        <div id="crearEmpleadoForm" class="form-container">
            <h2>Crear Empleado</h2>
            <form action="admin_dashboard.php" method="POST">
                <label for="nombre_empleado">Nombre:</label>
                <input type="text" id="nombre_empleado" name="nombre_empleado" required>

                <label for="apellido_empleado">Apellido:</label>
                <input type="text" id="apellido_empleado" name="apellido_empleado" required>

                <label for="puesto_empleado">Puesto:</label>
                <input type="text" id="puesto_empleado" name="puesto_empleado" required>

                <button type="submit" name="crear_empleado">Crear Empleado</button>
            </form>
        </div>

        <!-- Crear Usuario para Login -->
        <div id="crearUsuarioForm" class="form-container">
            <h2>Crear Usuario para Login</h2>
            <form action="admin_dashboard.php" method="POST">
                <label for="nombre_usuario">Nombre de Usuario:</label>
                <input type="text" id="nombre_usuario" name="nombre_usuario" required>

                <label for="password_usuario">Contraseña:</label>
                <input type="password" id="password_usuario" name="password_usuario" required>

                <label for="rol_usuario">Rol:</label>
                <select id="rol_usuario" name="rol_usuario" required>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?php echo $rol['id']; ?>"><?php echo $rol['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="crear_usuario">Crear Usuario</button>
            </form>
        </div>

        <!-- Registrar Entrada -->
        <div id="registrarEntradaForm" class="form-container">
            <h2>Registrar Entrada</h2>
            <form action="admin_dashboard.php" method="POST">
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

        <!-- Registrar Salida -->
        <div id="registrarSalidaForm" class="form-container">
            <h2>Registrar Salida</h2>
            <form action="admin_dashboard.php" method="POST">
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

        <!-- Formulario para Notificar Inasistencia -->
        <div id="notificarInasistenciaForm" class="form-container">
            <h2>Notificar Inasistencia</h2>
            <form action="admin_dashboard.php" method="POST">
                <label for="empleado_id">Seleccionar Empleado:</label>
                <select id="empleado_id" name="empleado_id" required>
                    <option value="">Seleccione un empleado</option>
                    <?php foreach ($empleados as $empleado): ?>
                        <option value="<?php echo $empleado['id']; ?>"><?php echo $empleado['nombre'] . ' ' . $empleado['apellido']; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>

                <label for="justificacion">Justificación:</label>
                <textarea id="justificacion" name="justificacion" rows="4" required></textarea>

                <button type="submit" name="notificar_inasistencia">Notificar Inasistencia</button>
            </form>
        </div>

        <!-- Modal para Justificar Tardanza -->
        <div id="justificarTardanzaModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeJustificarModal()">&times;</span>
                <h2>Justificar Tardanza</h2>
                <form action="admin_dashboard.php" method="POST">
                    <input type="hidden" name="asistencia_id" value="<?php echo isset($_SESSION['asistencia_id']) ? htmlspecialchars($_SESSION['asistencia_id']) : ''; ?>">
                    <label for="justificacion_tardanza">Justificación:</label>
                    <textarea id="justificacion_tardanza" name="justificacion_tardanza" rows="4" required></textarea>

                    <button type="submit" name="justificar_tardanza">Justificar Tardanza</button>
                </form>
            </div>
        </div>

        <!-- Botón para cerrar sesión -->
        <p><a href="logout.php" class="logout">Cerrar Sesión</a></p>
    </div>

</body>
</html>
