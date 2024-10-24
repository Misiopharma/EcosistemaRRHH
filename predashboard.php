
<?php
require 'config/auth.php';
require 'config/config.php';

$sistemasAcceso = json_decode($user['sistemas_acceso'], true);

if (!is_array($sistemasAcceso)) {
    $sistemasAcceso = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Predashboard - MISIOPHARMA</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/predashboard.css?v=<?php echo time(); ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mb-4">
            <h2>Bienvenido, <?php echo htmlspecialchars($user['usuario']); ?></h2>
            <h3>Sistemas disponibles:</h3>
        </div>
        <div class="row">
            <?php foreach ($sistemasAcceso as $sistema) : ?>
                <div class="col-md-6 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <?php if ($sistema === 'asistencias') : ?>
                                <i class="fas fa-calendar-check fa-3x mb-3"></i>
                            <?php elseif ($sistema === 'recursos_humanos') : ?>
                                <i class="fas fa-users fa-3x mb-3"></i>
                            <?php endif; ?>
                            <h5 class="card-title"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($sistema))); ?></h5>
                            <a href="<?php echo ($sistema === 'recursos_humanos') ? '/RRHH/inicio.php' : htmlspecialchars($sistema) . '.php'; ?>" class="btn btn-primary">Ir al sistema</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>