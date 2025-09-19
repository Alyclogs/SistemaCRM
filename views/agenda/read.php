<div class="page-content">
    <div id="calendar" style="max-height: 100%;"></div>
</div>

<div id="popup" class="popup">
    <div class="buttons-row mb-3">
        <button class="btn-outline btn-actividad selected" data-type="llamada"><?php include('./assets/svg/call.svg') ?></button>
        <button class="btn-outline btn-actividad" data-type="videollamada"><?php include('./assets/svg/video.svg') ?></button>
        <button class="btn-outline btn-actividad" data-type="reunion"><?php include('./assets/svg/profile-2user.svg') ?></button>
    </div>
    <input id="titleInput" class="form-control mb-2"></input>
    <div class="mb-2" id="infoDate"></div>
    <div class="d-flex w-100 justify-content-end gap-1">
        <button class="btn-outline" id="btnDetallesActividad">Detalles</button>
        <button class="btn-default" id="btnGuardarActividad">Agregar</button>
    </div>
</div>

<div class="modal fade" id="actividadModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="actividadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-large" id="actividadModalLabel">Agregar nueva actividad</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="actividadModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn-default" id="btnGuardarActividad">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/agenda.js"></script>