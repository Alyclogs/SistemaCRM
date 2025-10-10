<style>
    .campaign-card {
        border: 1px solid var(--default-border-color);
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .campaign-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .campaign-title {
        font-weight: 600;
        margin: 0;
    }

    .programacion-list {
        margin-top: 8px;
    }

    .programacion-item {
        padding: 8px;
        border-radius: 6px;
        border: 1px dashed var(--default-border-color);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .programacion-left {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .programacion-meta {
        font-size: 12px;
        color: #6c757d;
    }

    .preview-row {
        padding: 8px 10px;
        border-radius: 6px;
        border: 1px solid #f0f0f0;
        margin-bottom: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .small-muted {
        font-size: 12px;
        color: #6c757d;
    }
</style>

<div id="patternWizard" class="list-view" style="min-height:320px;">
    <div class="list-items">
        <div class="list-item list-item-default selected" data-target="patronBody">
            <div>
                <h6>1. Patrón</h6>
            </div>
        </div>
        <div class="list-item list-item-default" data-target="fechaBody">
            <div>
                <h6>2. Fecha inicio</h6>
            </div>
        </div>
    </div>

    <div class="list-sections p-1">
        <div class="section-item show" id="patronBody">
            <div class="section-body pe-2" style="overflow:auto; max-height:420px;">
                <div id="campaignsList" class="campaigns-list">
                    <div class="placeholder text-muted">Cargando patrones...</div>
                </div>
            </div>
        </div>

        <div class="section-item" id="fechaBody">
            <div class="section-body p-3">
                <div id="selectedCampaignSummary" class="mb-3"></div>

                <div class="mb-3">
                    <label class="form-label">Fecha de inicio</label>
                    <input type="date" id="patternStartDate" class="form-control" />
                </div>

                <div class="mb-2">
                    <strong>Previsualización de envíos</strong>
                    <small class="text-muted d-block">Las fechas se calcularán en base al patrón seleccionado.</small>
                </div>

                <div id="previewDates" style="max-height:320px; overflow:auto;" class="mt-2">
                    <div class="placeholder text-muted">Seleccione un patrón y una fecha de inicio</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module" src="assets/js/clientes/campanias.js"></script>