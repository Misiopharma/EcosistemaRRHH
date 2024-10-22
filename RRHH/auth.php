<?php
// auth.php
require '../config/config.php';

session_start();

// Establecer el tiempo de inactividad permitido en segundos
$tiempoInactividadPermitido = 200;

// Verificar si la última actividad está registrada en la sesión
if (isset($_SESSION['ultima_actividad'])) {
    $tiempoInactivo = time() - $_SESSION['ultima_actividad'];
    if ($tiempoInactivo > $tiempoInactividadPermitido) {
        // Destruir la sesión y redirigir al usuario a la página de inicio de sesión
        session_unset();
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
}

// Actualizar la última actividad a la hora actual
$_SESSION['ultima_actividad'] = time();

// Verificar si la cookie existe y coincide con la base de datos
if (!isset($_COOKIE['session_token'])) {
    header("Location: ..(login.php");
    exit();
}

$sessionToken = $_COOKIE['session_token'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE session_token = :session_token");
$stmt->execute(['session_token' => $sessionToken]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: ../login.php");
    exit();
}
?>