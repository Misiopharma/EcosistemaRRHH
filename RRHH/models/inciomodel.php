<?php
class DashboardModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getTotalEmpleados() {
        $sql = "SELECT COUNT(*) AS total_empleados FROM empleados";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_empleados'];
    }

    public function getPresentesHoy($fecha) {
        $sql = "SELECT COUNT(*) AS presentes_hoy FROM asistencia WHERE fecha = :fecha AND (estado = 'presente' OR estado = 'Presente (Tardanza)' OR estado = 'Presente (Tardanza: Justificada)')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['presentes_hoy'];
    }

    public function getAusentesHoy($fecha) {
        $sql = "SELECT COUNT(*) AS ausentes_hoy FROM asistencia WHERE fecha = :fecha AND estado = 'ausente'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['ausentes_hoy'];
    }

    public function getTardanzasHoy($fecha) {
        $sql = "SELECT COUNT(*) AS tardanzas_hoy FROM asistencia WHERE fecha = :fecha AND (estado = 'Presente (Tardanza)' OR estado = 'Presente (Tardanza: Justificada)')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['tardanzas_hoy'];
    }

    public function getVacacionesActuales() {
        $sql = "SELECT COUNT(*) AS vacaciones_actuales FROM historial_vacaciones WHERE estado = 'aprobado' AND CURDATE() BETWEEN fecha_inicio AND fecha_fin";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['vacaciones_actuales'];
    }

    public function getInasistenciasHoy($fecha) {
        $sql = "SELECT COUNT(*) AS inasistencias_hoy FROM asistencia WHERE fecha = :fecha AND estado = 'ausente'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['inasistencias_hoy'];
    }
}
?>