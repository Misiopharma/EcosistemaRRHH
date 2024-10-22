<?php
session_start();

include 'config.php';  // Incluye el archivo de conexión a la base de datos

// Manejo de la autenticación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['nombre'];
    $password = $_POST['password'];

    $query = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password']) {  // Comparación directa en texto plano
        // Almacenar datos del usuario en la sesión
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['rol_id'] = $user['rol_id'];  // Asegúrate de que rol_id se almacena correctamente

        header("Location: redirect.php");
        exit;
    } else {
        $error = "Nombre de usuario o contraseña incorrectos";
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/login.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="login-container">
        <img src="/img/login.png" alt="Foto de perfil">
        <h3>Sistema de asistencia</h3>
        <h2>Iniciar Sesión</h2>
        <?php if (isset($error)): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <label for="nombre">Nombre de Usuario:</label>
            <input type="text" id="nombre" name="nombre" placeholder="Usuario" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" placeholder="Contraseña" required>

            <button type="submit">Ingresar</button>
        </form>

    </div>

</body>
</html>