<?php
require '../config/config.php';

if (isset($_POST['query'])) {
    $query = $_POST['query'];

    // Búsqueda de empleados
    $sql = "SELECT id, nombre, apellido FROM empleados 
            WHERE nombre LIKE :query OR apellido LIKE :query LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':query', "%$query%");
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($resultados) {
        foreach ($resultados as $empleado) {
            echo "<div class='empleado-result' data-id='" . $empleado['id'] . "'>" . htmlspecialchars($empleado['nombre']) . " " . htmlspecialchars($empleado['apellido']) . "</div>";
        }
    } else {
        echo "<div>No se encontraron empleados</div>";
    }
}
?>
