<div class="page-content px-4 pt-1 pb-4 h-100">
    <div class="d-flex h-100 gap-3">
        <div style="width: 30%;">
            <div class="container-shadow h-100 p-4">
                <h5 class="page-title mb-3">Ajustes</h5>
                <div class="info-container bg-white clickable" data-target="campos">
                    <div class="d-flex gap-3 align-items-center">
                        <?php include('./assets/svg/edit.svg'); ?>
                        <h6>Campos personalizados</h6>
                    </div>
                </div>
                <div class="info-container bg-white clickable" data-target="roles">
                    <div class="d-flex gap-3 align-items-center">
                        <?php include('./assets/svg/profile-2user.svg'); ?>
                        <h6>Roles y permisos</h6>
                    </div>
                </div>
            </div>
        </div>
        <div style="width: 70%;">
            <div class="container-shadow h-100 p-4">
                <div id="rolesSection">
                    <div class="mb-3">
                        <div class="page-header mb-1">
                            <h5 class="page-title">Roles</h5>
                            <button class="btn btn-default" id="btnNuevoRol">Nuevo rol</button>
                        </div>
                        <span class="text-muted">Crea o edita roles</span>
                    </div>
                    <div class="page-content">
                        <div id="rolesList" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>
                <div id="camposSection">
                    <div class="mb-3">
                        <div class="page-header mb-1">
                            <h5 class="page-title">Campos personalizados</h5>
                            <button class="btn btn-default" id="btnNuevoCampo">Nuevo campo</button>
                        </div>
                        <span class="text-muted">Crea o edita campos personalizados</span>
                    </div>
                    <div class="page-content">
                        <div id="camposList" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/ajustes/index.js"></script>