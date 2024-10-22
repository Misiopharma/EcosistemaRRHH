<?php
require 'config/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtener los datos del formulario de login
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verificar el usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
    $stmt->execute(['usuario' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Generar un token de sesión único
        $sessionToken = bin2hex(random_bytes(32));

        // Establecer el token en una cookie segura
        setcookie("session_token", $sessionToken, time() + 3600, "/", "", true, true);

        // Almacenar el token en la base de datos
        $stmt = $pdo->prepare("UPDATE usuarios SET session_token = :session_token WHERE id = :id");
        $stmt->execute(['session_token' => $sessionToken, 'id' => $user['id']]);

        // Redirigir al predashboard
        header("Location: predashboard.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/login.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5">
                    <div class="card-body">
                        <div class="text-center">
                            <h3>Sistema de asistencia</h3>
                            <h2>Iniciar Sesión</h2>
                        </div>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        <form action="login.php" method="POST">
                            <div class="form-group">
                                <label for="username">Nombre de Usuario:</label>
                                <input type="text" id="username" name="username" class="form-control" placeholder="Usuario" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Contraseña:</label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>