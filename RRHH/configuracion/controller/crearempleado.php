<?php
require '../../../config/config.php';
require '../../config/auth.php';

$mensaje = '';

// Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $puesto = $_POST['puesto'];

    // Insertar el nuevo empleado en la base de datos
    $sql_insert = "INSERT INTO empleados (nombre, apellido, puesto) VALUES (:nombre, :apellido, :puesto)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->bindParam(':nombre', $nombre);
    $stmt_insert->bindParam(':apellido', $apellido);
    $stmt_insert->bindParam(':puesto', $puesto);
    $stmt_insert->execute();

    // Establecer el mensaje de confirmación
    $mensaje = 'Empleado creado exitosamente.';
}

// Incluir la vista
require '../crear_empleado.php';
?>