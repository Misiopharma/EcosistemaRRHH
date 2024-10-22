<?php
// Datos de conexión a la base de datos
$host = 'localhost';   // Host del servidor
$dbname = 'asistencia_empleados';  // Nombre de la base de datos
$username = 'root';  // Usuario de la base de datos
$password = '';  // Contraseña de la base de datos

// Intento de conexión
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configurar el modo de error de PDO a excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Configurar el juego de caracteres
    $conn->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit();
}
?>