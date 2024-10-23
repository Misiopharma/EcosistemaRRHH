<?php
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empleado_id'])) {
    $empleado_id = $_POST['empleado_id'];
    $tipo_documento = $_POST['tipo_documento'];
    $archivo = $_FILES['archivo'];

    // Consulta para obtener el apellido del empleado
    $sql_empleado = "SELECT apellido FROM empleados WHERE id = :empleado_id";
    $stmt = $conn->prepare($sql_empleado);
    $stmt->bindParam(':empleado_id', $empleado_id);
    $stmt->execute();
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
    $apellido = $empleado['apellido'];

    // Verificar que el archivo ha sido subido sin errores
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
        // Crear la carpeta "uploads/legajos" si no existe
        $directorio_subida = '../uploads/legajos/';
        if (!is_dir($directorio_subida)) {
            mkdir($directorio_subida, 0777, true);
        }

        // Cambiar el nombre del archivo a formato: tipo_documento-apellido.pdf
        $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $nuevo_nombre_archivo = $tipo_documento . '-' . $apellido . '.' . $extension;

        // Ruta completa del archivo a subir
        $ruta_destino = $directorio_subida . $nuevo_nombre_archivo;
        $ruta_relativa = '/rrhh/uploads/legajos/' . $nuevo_nombre_archivo; // Ruta relativa para guardar en la base de datos

        // Mover el archivo subido a la carpeta de destino
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
            // Insertar los detalles del documento en la base de datos
            $sql_insertar_documento = "INSERT INTO documentos_legajo (empleado_id, tipo_documento, ruta_archivo, fecha_subida) 
                                       VALUES (:empleado_id, :tipo_documento, :ruta_archivo, NOW())";
            $stmt = $conn->prepare($sql_insertar_documento);
            $stmt->bindParam(':empleado_id', $empleado_id);
            $stmt->bindParam(':tipo_documento', $tipo_documento);
            $stmt->bindParam(':ruta_archivo', $ruta_relativa); // Usar la ruta relativa
            $stmt->execute();

            // Redirigir a la página del legajo
            header("Location: ../ver_legajo.php?id=$empleado_id");
        } else {
            echo "Error al mover el archivo a la carpeta de destino.";
        }
    } else {
        echo "Error al subir el archivo.";
    }
}
?>