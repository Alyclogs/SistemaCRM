<div class="list-view campania-form first-step">
    <div class="list-items">
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
    <div class="list-sections">
        <form class="section-body" id="formCampania">
            <div class="section-item show" id="campaniaInfoSection" data-step="1">
                <div class="mb-4">
                    <div class="page-header mb-1">
                        <h6>Información de campaña</h6>
                    </div>
                    <span class="text-muted">Edita la información de la campaña</span>
                </div>

                <div class="mb-3">
                    <label for="nombreInput" class="form-label">Nombre de la campaña:</label>
                    <input type="text" id="nombreInput" class="form-control" value="" placeholder="Campaña 1">
                </div>
                <div class="mb-3">
                    <label for="descripcionInput" class="form-label">Descripción de la campaña:</label>
                    <textarea id="descripcionInput" class="form-control" rows="2" placeholder="Campaña de prueba"></textarea>
                </div>
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
                <div class="d-flex flex-column gap-2 section-body">
                    <div class="chip chip-outline chip-light p-3 w-100">
                        <div class="row w-100 align-items-center g-0">
                            <div class="col-8">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="useGlobalTimeSwitch">
                                    <label class="form-check-label fw-bold" for="useGlobalTimeSwitch">Usar la misma hora para todos los correos</label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-flex h-100 w-100 align-items-center justify-content-center">
                                    <input type="time" class="form-control w-100" id="globalTimeInput" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chip chip-info chip-outline bg-info disable-hover text-default p-3 mb-3 w-100 row g-0 gap-0">
                        <div class="col-6">
                            <p class="fw-bold">Modalidad de programación de fechas</p>
                        </div>
                        <div class="col-6">
                            <div class="d-flex w-100 gap-3 align-items-center">
                                <button type="button" class="btn btn-modalidad bg-text-info w-100 selected" data-modalidad="dias_especificos">Por días específicos</button>
                                <button type="button" class="btn btn-modalidad bg-text-info w-100" data-modalidad="dias_semana">Por días de semana</button>
                            </div>
                        </div>
                    </div>
                    <div id="campaniaProgramacionPlantillas" class="d-flex flex-column gap-3 py-2 pe-2" style="overflow-y: auto; max-height: 280px;"></div>
                </div>
            </div>
        </form>
    </div>
    <div class="buttons-navegacion p-2 d-flex align-items-center justify-content-between gap-2 d-none">
        <button class="btn btn-outline btn-navegacion" id="btnRegresar" disabled><?php include('../../../assets/svg/arrow-left-02.svg') ?><span>Volver</span></button>
        <button class="btn btn-default btn-navegacion" id="btnSiguiente"><?php include('../../../assets/svg/arrow-right-02.svg') ?><span>Siguiente</span></button>
    </div>
</div>