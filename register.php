<?php
// register.php
require 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $rol_id = $_POST['rol_id'];
    $sistemas_acceso = json_encode($_POST['sistemas_acceso']);

    // Insertar el nuevo usuario en la base de datos
    $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password, rol_id, sistemas_acceso) VALUES (:usuario, :password, :rol_id, :sistemas_acceso)");
    $stmt->execute([
        'usuario' => $usuario,
        'password' => $password,
        'rol_id' => $rol_id,
        'sistemas_acceso' => $sistemas_acceso
    ]);

    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario - MISIOPHARMA</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="register-container">
        <h2>Registrar Usuario</h2>
        <form action="register.php" method="POST">
            <label for="usuario">Nombre de Usuario:</label>
            <input type="text" id="usuario" name="usuario" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <label for="rol_id">ID de Rol:</label>
            <input type="number" id="rol_id" name="rol_id" required>

            <label for="sistemas_acceso">Sistemas de Acceso (JSON):</label>
            <textarea id="sistemas_acceso" name="sistemas_acceso" required></textarea>

            <button type="submit">Registrar</button>
        </form>
    </div>
</body>
</html>