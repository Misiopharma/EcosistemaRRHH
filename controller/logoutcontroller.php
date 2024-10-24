<?php
session_start();

require_once '../config/config.php';
require_once '../models/usermodel.php';

// Crear una instancia del modelo de usuario
$userModel = new UserModel($pdo);

// Obtener el ID del usuario de la sesión
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Limpiar el token de sesión del usuario
    $userModel->clearUserSessionToken($userId);
}

// Destruir la sesión
session_unset();
session_destroy();

// Eliminar la cookie de sesión
if (isset($_COOKIE['session_token'])) {
    unset($_COOKIE['session_token']);
    setcookie('session_token', '', time() - 3600, '/'); // Expira la cookie
}

// Redirigir al usuario a la página de inicio
header("Location: ../index.php");
exit();
?>