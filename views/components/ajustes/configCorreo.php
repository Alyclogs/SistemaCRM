<form id="configCorreoForm">
    <div class="info-row gap-2 mb-3">
        <label for="configCorreoInput" class="fw-bold" style="width: 180px;">Configuración 1</label>
        <input type="text" class="form-control w-auto" id="configCorreoInput">
    </div>
</form>
<div class="mb-3">
    <div class="page-header mb-1">
        <h5 class="page-title">Emisores de correo</h5>
        <button class="btn btn-default" id="btnNuevoEmisorCorreo">
            <?php include('./assets/svg/add.svg') ?><span>Nuevo emisor</span>
        </button>
    </div>
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th class="text-wrap" style="max-width: 220px;">Descripción</th>
                <th>Correo</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="correoEmisoresList"></tbody>
    </table>
</div>