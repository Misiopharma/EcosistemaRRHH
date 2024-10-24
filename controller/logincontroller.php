<?php
require '../config/config.php';
require '../models/usermodel.php';

$error = '';
$userModel = new UserModel($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verificar las credenciales del usuario
    $user = $userModel->getUserByUsername($username);

    if ($user && password_verify($password, $user['password'])) {
        // Generar un token de sesión
        $sessionToken = bin2hex(random_bytes(32));

        // Establecer el token en una cookie segura
        setcookie("session_token", $sessionToken, time() + 3600, "/", "", true, true);

        // Almacenar el token en la base de datos
        $userModel->updateUserSessionToken($user['id'], $sessionToken);

        // Redirigir al predashboard
        header("Location: ../predashboard.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}

// Incluir la vista
require '../view/loginview.php';
?>