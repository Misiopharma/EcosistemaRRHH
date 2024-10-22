<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");  // Redirige al login si no ha iniciado sesión
    exit;
}

// Redirigir según el rol_id del usuario
switch ($_SESSION['rol_id']) {
    case 1:  // Admin
        header("Location: admin_dashboard.php");
        break;
    case 2:  // Visualizador
        header("Location: viewer_dashboard.php");
        break;
    case 3:
        header("location: dash_credencial.php");
        break;
    default:
        echo "Rol no reconocido.";
        break;
}
exit;
?>