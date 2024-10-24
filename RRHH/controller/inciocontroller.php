<?php
class DashboardController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function getDashboardData() {
        $fechaHoy = date('Y-m-d');
        return [
            'totalEmpleados' => $this->model->getTotalEmpleados(),
            'presentesHoy' => $this->model->getPresentesHoy($fechaHoy),
            'ausentesHoy' => $this->model->getAusentesHoy($fechaHoy),
            'tardanzasHoy' => $this->model->getTardanzasHoy($fechaHoy),
            'vacacionesActuales' => $this->model->getVacacionesActuales(),
            'inasistenciasHoy' => $this->model->getInasistenciasHoy($fechaHoy),
        ];
    }
}
?>  