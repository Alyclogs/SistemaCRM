<?php
require_once __DIR__ . '/../../models/usuarios/UsuarioModel.php';
?>

<div class="animate__animated animate__fadeInUp page-header">
    <div class="page-title text-large">Usuarios</div>
    <button class="btn-default bg-accent" id="btnNuevoUsuario" data-bs-toggle="modal" data-bs-target="#usuarioModal">
        <?php include('assets/svg/add.svg') ?>
        <span>Nuevo registro</span>
    </button>
</div>

<div class="page-content">
    <div class="d-flex flex-column gap-3">
        <div class="animate__animated animate__fadeInUp page-header">
            <div class="buttons-row buttons-2">
                <button class="btn-outline bg-white"><?php include('assets/svg/export-arrow-01.svg') ?>Exportar</button>
                <button class="btn-outline bg-white"><?php include('assets/svg/refresh-arrow-01.svg') ?></button>
            </div>
            <div class="animate__animated animate__fadeInUp d-flex gap-2 align-items-center">
                <div class="grupo-input">
                    <?php include('assets/svg/search.svg') ?>
                    <input type="text" id="inputBuscarUsuarios" placeholder="Buscar usuarios...">
                </div>
                <button class="btn-outline bg-white"><?php include('assets/svg/filter.svg') ?>Filtrar</button>
            </div>
        </div>

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

<div class="modal fade" id="usuarioModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-large" id="usuarioModalLabel">Agregar nuevo usuario</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="usuarioModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn-default" id="btnGuardarUsuario">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/usuarios.js"></script>