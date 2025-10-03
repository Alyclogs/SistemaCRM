<div class="list-view campania-form first-step">
    <div style="width: 25%;
            border-right: 1px solid var(--bs-border-color);">
        <div class="list-items p-2">
            <div class="list-item list-item-default selected" data-step="1" data-target="campaniaInfoSection">
                <span class="list-item-bullet">1.</span>
                <span>Información de campaña</span>
            </div>
            <div class="list-item list-item-default" data-step="2" data-target="campaniaPlantillasSection">
                <span class="list-item-bullet">2.</span>
                <span>Elegir plantillas</span>
            </div>
            <div class="list-item list-item-default" data-step="3" data-target="campaniaProgramacionSection">
                <span class="list-item-bullet">3.</span>
                <span>Programar fechas</span>
            </div>
        </div>
    </div>
    <div style="width: 75%;">
        <div class="section-list p-2">
            <div class="section-item show" id="campaniaInfoSection" data-step="1">
                <div class="mb-4">
                    <div class="page-header mb-1">
                        <h6>Información de campaña</h6>
                    </div>
                    <span class="text-muted">Edita la información de la campaña</span>
                </div>
                <form class="section-body" id="formCampania">
                    <div class="mb-3">
                        <label for="nombreInput">Nombre de la campaña:</label>
                        <input type="text" id="nombreInput" class="form-control" value="">
                    </div>
                    <div class="mb-3">
                        <label for="descripcionInput">Descripción de la campaña:</label>
                        <textarea id="descripcionInput" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="fechaInicioInput">Fecha de inicio:</label>
                            <input type="date" id="fechaInicioInput" class="form-control" value="">
                        </div>
                        <div class="col">
                            <label for="fechaFinInput">Fecha de finalización:</label>
                            <input type="date" id="fechaFinInput" class="form-control" value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="section-item" id="campaniaPlantillasSection" data-step="2">
                <div class="mb-4">
                    <div class="page-header mb-1">
                        <h6>Elegir plantillas</h6>
                    </div>
                    <span class="text-muted">Selecciona una o más plantillas para la campaña</span>
                </div>
                <div id="campaniaPlantillasList" class="d-flex flex-column gap-2 section-body"></div>
            </div>
            <div class="section-item" id="campaniaProgramacionSection" data-step="3">
                <div class="mb-4">
                    <div class="page-header mb-1">
                        <h6>Programar fechas</h6>
                    </div>
                    <span class="text-muted">Edita la configuración de envíos para la campaña</span>
                </div>
                <div id="programacionContainer" class="d-flex flex-column gap-2 section-body"></div>
            </div>
        </div>
        <div class="buttons-navegacion p-2 d-flex align-items-center justify-content-between gap-2 d-none">
            <button class="btn btn-outline btn-navegacion" id="btnRegresar" disabled><?php include('../../../assets/svg/arrow-left-02.svg') ?><span>Volver</span></button>
            <button class="btn btn-default btn-navegacion" id="btnSiguiente"><?php include('../../../assets/svg/arrow-right-02.svg') ?><span>Siguiente</span></button>
        </div>
    </div>
</div>