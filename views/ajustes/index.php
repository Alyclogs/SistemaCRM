<link rel="stylesheet" href="./assets/css/ajustes.css">

<div class="h-100 d-flex flex-column p-4">
    <div class="list-view" data-source="ajustes">
        <div class="bg-white disable-hover"
            style="width: 20%;
            border-right: 1px solid var(--bs-border-color);">
            <h5 class="page-title p-4 pb-0 ms-3">Ajustes</h5>
            <div class="p-4 list-items" data-source="ajustes">
                <div class="list-item list-item-default selected" data-target="rolesSection">
                    <?php include('./assets/svg/profile-2user.svg'); ?>
                    <h6>Roles y permisos</h6>
                </div>
                <div class="list-item list-item-default" data-target="camposSection">
                    <?php include('./assets/svg/edit.svg'); ?>
                    <h6>Campos personalizados</h6>
                </div>
                <div class="list-item-dropdown">
                    <div class="dropdown-button clickable bg-white">
                        <div class="d-flex gap-3 align-items-center">
                            <?php include('./assets/svg/sms.svg'); ?>
                            <h6>Envío de correos</h6>
                        </div>
                        <button class="btn btn-icon sm dropdown-collapse">
                            <div class="svg-collapsed">
                                <?php include('./assets/svg/arrow-right-02.svg'); ?>
                            </div>
                            <div class="svg-expanded">
                                <?php include('./assets/svg/arrow-down-02.svg'); ?>
                            </div>
                        </button>
                    </div>
                    <div class="dropdown-submenu">
                        <div class="dropdown-item list-item list-item-default" data-target="correosSection">Configuración general</div>
                        <div class="dropdown-item list-item list-item-default" data-target="correosPlantillasSection">Plantillas de correos</div>
                        <div class="dropdown-item list-item list-item-default" data-target="correosCampaniasSection">Programación de campañas</div>
                    </div>
                </div>
            </div>
        </div>
        <div style="width: 80%; height: 100%;">
            <div class="h-100 p-4 list-sections">
                <div id="rolesSection" class="section-item show h-100">
                    <div class="mb-4">
                        <div class="page-header mb-1">
                            <h5 class="page-title">Roles</h5>
                            <button class="btn btn-default" id="btnNuevoRol">
                                <?php include('./assets/svg/add.svg') ?><span>Nuevo rol</span>
                            </button>
                        </div>
                        <span class="text-muted">Crea o edita roles</span>
                    </div>
                    <div id="rolesList" class="d-flex flex-column gap-2 section-body"></div>
                </div>
                <div id="camposSection" class="section-item h-100">
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
                        <tbody id="camposList" class="section-body"></tbody>
                    </table>
                </div>
                <div id="correosSection" class="section-item h-100">
                    <div class="mb-4">
                        <div class="page-header mb-1">
                            <h5 class="page-title">Configuración de correo</h5>
                        </div>
                        <span class="text-muted">Edita la configuración de correo</span>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <?php include("./views/components/ajustes/configCorreo.php") ?>
                    </div>
                </div>
                <div id="correosPlantillasSection" class="section-item h-100">
                    <div class="mb-4">
                        <div class="page-header mb-1">
                            <h5 class="page-title">Plantillas de correo</h5>
                            <button class="btn btn-default" id="btnNuevaPlantillaCorreo">
                                <?php include('./assets/svg/add.svg') ?><span>Nueva plantilla</span>
                            </button>
                        </div>
                        <span class="text-muted">Crea o edita plantillas de correo</span>
                    </div>
                    <div id="correosPlantillasList" class="d-flex flex-column gap-2 section-body"></div>
                </div>
                <div id="correosCampaniasSection" class="section-item h-100">
                    <div class="mb-4">
                        <div class="page-header mb-1">
                            <h5 class="page-title">Programación de campañas</h5>
                            <button class="btn btn-default" id="btnNuevaCampania">
                                <?php include('./assets/svg/add.svg') ?><span>Nueva campaña</span>
                            </button>
                        </div>
                        <span class="text-muted">Crea o edita programaciones de campañas</span>
                    </div>
                    <div id="campaniasList" class="d-flex flex-column gap-2 section-body"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/ajustes/index.js"></script>