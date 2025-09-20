<?php
require_once __DIR__ . '/../../../models/actividades/ActividadModel.php';

$idactividad = $_GET['id'] ?? null;
$agendaModel = new ActividadModel();
$actividad = null;

if ($idactividad) {
    $actividad = $agendaModel->obtenerActividad($idactividad);

    if (!$actividad) {
        echo '<div class="alert alert-danger">No se encontró la actividad</div>';
        exit;
    }
}
