<div class="page-body p-4 mx-2">
    <div class="animate__animated animate__fadeInUp page-header">
        <div class="d-flex gap-3 align-items-center">
            <h5 class="page-title">Usuarios</h5>
            <button class="btn btn-default bg-accent" id="btnNuevoUsuario" data-bs-toggle="modal" data-bs-target="#usuarioModal">
                <?php include('assets/svg/add.svg') ?>
                <span>Nuevo registro</span>
            </button>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-icon border" id="btnRefresh"><?php include('./assets/svg/refresh-arrow-01.svg') ?></button>
        </div>
    </div>

    <div class="page-content">
        <div class="d-flex flex-column gap-3">
            <table class="animate__animated animate__fadeInUp table align-middle">
                <thead>
                    <th>Usuario</th>
                    <th>DNI</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th></th>
                </thead>
                <tbody id="tablaUsuariosBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/usuarios/index.js"></script>