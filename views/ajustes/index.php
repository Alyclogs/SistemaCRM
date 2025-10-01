<div class="h-100 d-flex flex-column p-4">
    <div class="d-flex h-100 gap-3">
        <div class="bg-white disable-hover"
            style="width: 20%;
            border-right: 1px solid var(--bs-border-color);">
            <h5 class="page-title p-4">Ajustes</h5>
            <div class="h-100 p-4 d-flex flex-column gap-2">
                <div class="ajuste-item menu-dropdown-button bg-white clickable selected" style="border-bottom: none;" data-target="rolesSection">
                    <div class="d-flex gap-3 align-items-center">
                        <?php include('./assets/svg/profile-2user.svg'); ?>
                        <h6>Roles y permisos</h6>
                    </div>
                </div>
                <div class="ajuste-item menu-dropdown-button bg-white clickable" style="border-bottom: none;" data-target="camposSection">
                    <div class="d-flex gap-3 align-items-center">
                        <?php include('./assets/svg/edit.svg'); ?>
                        <h6>Campos personalizados</h6>
                    </div>
                </div>
                <div class="menu-dropdown" style="border-bottom: none;">
                    <div class="ajuste-item menu-dropdown-button bg-white clickable">
                        <div class="d-flex gap-3 align-items-center">
                            <?php include('./assets/svg/sms.svg'); ?>
                            <h6>Envío de correos</h6>
                        </div>
                        <button class="btn btn-icon sm menu-dropdown-collapse">
                            <div class="svg-collapsed">
                                <?php include('./assets/svg/arrow-down-02.svg'); ?>
                            </div>
                            <div class="svg-expanded">
                                <?php include('./assets/svg/arrow-right-02.svg'); ?>
                            </div>
                        </button>
                    </div>
                    <div class="menu-dropdown-submenu">
                        <div class="menu-dropdown-item ajuste-item clickable bg-white" data-target="correosPlantillasSection" data-target="correosPlantillasSection">Plantillas de correos</div>
                        <div class="menu-dropdown-item ajuste-item clickable bg-white" data-target="correosProgramacionSection" data-target="correosProgramacionSection">Programación de campañas</div>
                    </div>
                </div>
            </div>
        </div>
        <div style="width: 80%; height: 100%;">
            <div class="h-100 p-4">
                <div id="rolesSection" class="ajuste-section h-100" style="display: none;">
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
                <div id="camposSection" class="ajuste-section h-100" style="display: none;">
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
                <div id="correosPlantillasSection" class="ajuste-section h-100" style="display: none;">
                    <div class="mb-4">
                        <div class="page-header mb-1">
                            <h5 class="page-title">Plantillas de correo</h5>
                            <button class="btn btn-default" id="btnNuevaPlantillaCorreo">
                                <?php include('./assets/svg/add.svg') ?><span>Nueva plantilla</span>
                            </button>
                        </div>
                        <span class="text-muted">Crea o edita plantillas de correo</span>
                    </div>
                    <div id="correosPlantillasList" class="d-flex flex-column gap-2"></div>
                </div>
                <div id="correosProgramacionSection" class="ajuste-section h-100" style="display: none;">
                    <div class="mb-4">
                        <div class="page-header mb-1">
                            <h5 class="page-title">Programación de campañas</h5>
                            <button class="btn btn-default" id="btnNuevoCampo">
                                <?php include('./assets/svg/add.svg') ?><span>Nuevo campo</span>
                            </button>
                        </div>
                        <span class="text-muted">Crea o edita programaciones de campañas</span>
                    </div>
                    <div id="correosProgramacionList" class="d-flex flex-column gap-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/ajustes/index.js"></script>