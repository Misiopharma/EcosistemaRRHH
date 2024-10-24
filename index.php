<?php
session_start();
require 'config/config.php';
require 'models/usermodel.php';

// Verificar si el usuario ya ha iniciado sesión
if (isset($_COOKIE['session_token'])) {
    $userModel = new UserModel($pdo);
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE session_token = :token");
    $stmt->execute(['token' => $_COOKIE['session_token']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Usuario autenticado, redirigir al predashboard
        header("Location: predashboard.php");
        exit();
    }
}

// Si no ha iniciado sesión, redirigir al logincontroller
header("Location: controller/logincontroller.php");
exit();


