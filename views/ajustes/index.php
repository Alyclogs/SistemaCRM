<div class="h-100 d-flex flex-column p-4">
    <div class="d-flex h-100 gap-3">
        <div class="bg-white disable-hover"
            style="width: 25%;
            border-right: 1px solid var(--bs-border-color);">
            <div class="h-100 p-4 pe-0">
                <h5 class="page-title mb-3">Ajustes</h5>
                <div class="info-container bg-white info-container-secondary clickable selected" style="border-bottom: none;" data-target="campos">
                    <div class="d-flex gap-3 align-items-center">
                        <?php include('./assets/svg/edit.svg'); ?>
                        <h6>Campos personalizados</h6>
                    </div>
                </div>
                <div class="info-container bg-white info-container-secondary clickable" style="border-bottom: none;" data-target="roles">
                    <div class="d-flex gap-3 align-items-center">
                        <?php include('./assets/svg/profile-2user.svg'); ?>
                        <h6>Roles y permisos</h6>
                    </div>
                </div>
            </div>
        </div>
        <div style="width: 75%; height: 100%;">
            <div class="h-100 p-4">
                <div id="rolesSection" class="h-100">
                    <div class="mb-4">
                        <div class="page-header mb-1">
                            <h5 class="page-title">Roles</h5>
                            <button class="btn btn-default" id="btnNuevoRol">
                                <?php include('./assets/svg/add.svg') ?><span>Nuevo rol</span>
                            </button>
                        </div>
                        <span class="text-muted">Crea o edita roles</span>
                    </div>
                    <div id="rolesList" class="d-flex flex-column gap-2"></div>
                </div>
                <div id="camposSection" class="h-100">
                    <div class="mb-4">
                        <div class="page-header mb-1">
                            <h5 class="page-title">Campos personalizados</h5>
                            <button class="btn btn-default" id="btnNuevoCampo">
                                <?php include('./assets/svg/add.svg') ?><span>Nuevo campo</span>
                            </button>
                        </div>
                        <span class="text-muted">Crea o edita campos personalizados</span>
                    </div>
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo de dato</th>
                                <th>Longitud</th>
                                <th>Requerido</th>
                                <th>Asignado a</th>
                                <th>Valor inicial</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="camposList"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/ajustes/index.js"></script>