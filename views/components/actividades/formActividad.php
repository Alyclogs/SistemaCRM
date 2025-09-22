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
?>

<div class="d-flex gap-3" style="min-height: 630px;">
    <div class="flex-grow-1">
        <form id="formActividad" method="POST">
            <div class="buttons-row buttons-actividad mb-3">
                <button class="btn-outline btn-actividad" data-type="llamada"><?php include('../../../assets/svg/call.svg') ?></button>
                <button class="btn-outline btn-actividad" data-type="videollamada"><?php include('../../../assets/svg/video.svg') ?></button>
                <button class="btn-outline btn-actividad" data-type="reunion"><?php include('../../../assets/svg/profile-2user.svg') ?></button>
            </div>
            <div class="mb-4">
                <div class="d-flex gap-4 align-items-center titulo-actividad">
                    <h5 class="text-large" id="tituloActividadLabel">Nueva actividad</h5>
                    <button type="button" class="btn-icon" id="btnEditarTituloActividad">
                        <?php include('../../../assets/svg/edit.svg') ?>
                    </button>
                </div>
                <input type="text" class="form-control titulo-actividad-editando" id="titleInput" name="nombre" style="display: none;">
            </div>
            <div class="horas-container d-flex flex-column gap-2">
                <h6>Configure las horas:</h6>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <?php include('../../../assets/svg/clock.svg') ?>
                        <input type="time" class="form-control w-auto" name="hora_inicio" id="horaInicioInput">
                    </div>
                    <span class="p-3">a</span>
                    <div class="d-flex align-items-center gap-2">
                        <?php include('../../../assets/svg/clock.svg') ?>
                        <input type="time" class="form-control w-auto" name="hora_fin" id="horaFinInput">
                    </div>
                </div>
            </div>
            <div class="notas-container w-100 d-flex flex-column gap-2 mb-4">
                <h6>Notas de la actividad:</h6>
                <div>
                    <div id="notaEditor"></div>
                </div>
            </div>
            <div class="cliente-container d-flex flex-column gap-2">
                <h6>Cliente:</h6>
                <div class="d-flex align-items-center gap-2">
                    <?php include('../../../assets/svg/profile.svg') ?>
                    <div class="busqueda-grupo disable-auto">
                        <input type="text" class="form-control" id="clienteInput">
                        <input type="hidden" name="idcliente">
                        <div class="resultados-busqueda" data-parent="clienteInput" style="top: 2.5rem;"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div style="width: 380px; height: 100%">
        <div id="miniCalendar"></div>
    </div>
</div>