<?php
require_once __DIR__ . '/../../../models/actividades/ActividadModel.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

<div class="d-flex gap-4" style="height: 590px;">
    <div class="flex-grow-1">
        <form id="formActividad" method="POST">
            <input type="hidden" name="idactividad" id="idactividad" value="<?= $actividad['idactividad'] ?? '' ?>">
            <div class="d-flex flex-column w-100">
                <div class="buttons-row buttons-actividad mb-3">
                    <button type="button" class="btn-outline btn-actividad" data-type="llamada"><?php include('../../../assets/svg/call.svg') ?></button>
                    <button type="button" class="btn-outline btn-actividad" data-type="videollamada"><?php include('../../../assets/svg/video.svg') ?></button>
                    <button type="button" class="btn-outline btn-actividad" data-type="reunion"><?php include('../../../assets/svg/profile-2user.svg') ?></button>
                </div>
                <div class="mb-4">
                    <div class="titulo-actividad">
                        <div class="d-flex gap-4 align-items-center">
                            <h5 class="text-large" id="tituloActividadLabel"><?= $actividad['nombre'] ?? "Nueva actividad" ?></h5>
                            <div class="svg-editar" style="display: none;">
                                <?php include('../../../assets/svg/edit.svg') ?>
                            </div>
                        </div>
                        <input type="text" class="form-control titulo-actividad-editando" id="titleInput" name="nombre" style="display: none;" value="<?= $actividad['nombre'] ?? "Nueva actividad" ?>">
                    </div>
                </div>
                <div class="horas-container d-flex gap-2 mb-3">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Duración de la actividad">
                        <?php include('../../../assets/svg/clock.svg') ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" class="form-control w-auto" name="fecha" id="fechaInput" value="<?= $actividad['fecha'] ?? '' ?>">
                        <input type="time" class="form-control w-auto" name="hora_inicio" id="horaInicioInput" value="<?= $actividad['hora_inicio'] ?? '' ?>" required>
                        <span class="px-3">a</span>
                        <input type="time" class="form-control w-auto" name="hora_fin" id="horaFinInput" value="<?= $actividad['hora_fin'] ?? '' ?>" required>
                    </div>
                </div>
                <div class="notas-container w-100 d-flex gap-2 mb-3" style="height: 180px;">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Notas de la actividad">
                        <?php include('../../../assets/svg/document-text-2.svg') ?>
                    </div>
                    <textarea id="notaInput" class="form-control w-100" name="nota" rows="3"><?= !empty($actividad['notas']) ? $actividad['notas'][0]['contenido'] : '' ?></textarea>
                </div>
                <div class="cliente-container d-flex gap-2 w-100 mb-3">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Asignada al cliente">
                        <?php include('../../../assets/svg/profile.svg') ?>
                    </div>
                    <div class="busqueda-grupo w-100">
                        <input type="text" class="form-control w-100" id="clienteInput" value="<?= $actividad['cliente'] ?? '' ?>" placeholder="Buscar cliente" required>
                        <input type="hidden" name="idcliente" value="<?= $actividad['idcliente'] ?? '' ?>">
                        <div class="resultados-busqueda disable-auto" data-parent="clienteInput" style="top: 2.5rem; min-width: 300px;"></div>
                    </div>
                </div>
                <div class="usuario-container d-flex gap-2 w-100">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Asignada al usuario">
                        <?php include('../../../assets/svg/user.svg') ?>
                    </div>
                    <div class="busqueda-grupo w-100">
                        <input type="text" class="form-control w-100" id="usuarioInput" value="<?= $actividad['usuario'] ?? $_SESSION['nombre'] ?>" placeholder="Buscar usuario" required>
                        <input type="hidden" name="idusuario" value="<?= $actividad['idusuario'] ?? $_SESSION['idusuario'] ?>">
                        <div class="resultados-busqueda disable-auto" data-parent="usuarioInput" style="top: 2.5rem; min-width: 300px;"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div style="width: 280px; height: 100%;">
        <div id="miniCalendar"></div>
    </div>
</div>

<script>
    (() => {
        const tituloActividad = document.querySelector('.titulo-actividad');
        const tituloActividadLabel = document.getElementById('tituloActividadLabel');
        const titleInput = tituloActividad.querySelector('#titleInput');
        const svgEditar = tituloActividad.querySelector('.svg-editar');

        tituloActividad.addEventListener('mouseover', function() {
            if (titleInput.style.display === 'none') {
                svgEditar.style.display = 'inline';
            }
        });

        tituloActividad.addEventListener('mouseout', function() {
            svgEditar.style.display = 'none';
        });

        tituloActividad.addEventListener('click', function() {
            titleInput.value = tituloActividadLabel.innerText;
            tituloActividadLabel.style.display = 'none';
            svgEditar.style.display = 'none';
            titleInput.style.display = 'block';
        });

        tituloActividad.addEventListener('change', function() {
            tituloActividadLabel.innerText = titleInput.value;
            titleInput.style.display = 'none';
            tituloActividadLabel.style.display = 'block';
        });

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    })();
</script>