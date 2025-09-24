<?php
require_once __DIR__ . '/../../models/usuarios/UsuarioModel.php';
?>

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

<div class="modal fade" id="usuarioModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-large" id="usuarioModalLabel">Agregar nuevo usuario</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="usuarioModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-default" id="btnGuardarUsuario">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/usuarios/index.js"></script>