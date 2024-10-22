<?php
session_start();

// Verificar si el usuario está autorizado
if (!isset($_SESSION['asistencia_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php';

$mensaje = "";

// Lógica para justificar la tardanza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['justificar_tardanza'])) {
    $asistencia_id = $_SESSION['asistencia_id'];
    $justificacion_tardanza = $_POST['justificacion_tardanza'];

    try {
        // Iniciar la transacción
        $pdo->beginTransaction();

        // Insertar la justificación en la tabla correspondiente
        $stmt = $pdo->prepare("UPDATE asistencia SET justificacion_tardanza = ?, estado = 'Presente (Tardanza: Justificada)' WHERE id = ?");
        $stmt->execute([$justificacion_tardanza, $asistencia_id]);

        // Finalizar la transacción
        $pdo->commit();

        // Eliminar la variable de sesión y redirigir al dashboard
        unset($_SESSION['asistencia_id']);
        $mensaje = "Tardanza justificada exitosamente. Redirigiendo al dashboard...";
        header("refresh:2;url=admin_dashboard.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensaje = "Error al justificar la tardanza: " . $e->getMessage();
    }
} else {
    $mensaje = "No se recibieron datos válidos.";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justificar Tardanza</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: red;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
        }

        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }

        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .mensaje {
            margin-top: 20px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Justificar Tardanza</h1>
        <?php if ($mensaje): ?>
            <div class="mensaje">
                <p><?php echo $mensaje; ?></p>
            </div>
        <?php endif; ?>

        <form action="justificar_tardanza.php" method="POST">
            <textarea name="justificacion_tardanza" placeholder="Escriba la justificación de la tardanza..." required></textarea>
            <button type="submit" name="justificar_tardanza">Enviar Justificación</button>
        </form>
    </div>
</body>
</html>
