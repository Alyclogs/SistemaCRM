<?php
require_once __DIR__ . '/../../models/clientes/ClienteModel.php';

$clienteModel = new ClienteModel();
$estados = $clienteModel->obtenerEstadosClientes();
?>

<div class="animate__animated animate__fadeInUp page-header">
    <div class="page-title text-large">Clientes</div>
    <button class="btn-default bg-accent" id="btnNuevoCliente" data-bs-toggle="modal" data-bs-target="#clienteModal">
        <?php include('assets/svg/add.svg') ?>
        <span>Nuevo registro</span>
    </button>
</div>

<div class="page-main">
    <div class="animate__animated animate__fadeInUp tabs-container">
        <div class="tab-item selected">TODOS</div>
        <?php if (!empty($estados)): ?>
            <?php foreach ($estados as $estado): ?>
                <div class="tab-item" data-estado="<?= $estado['idestado'] ?>"><?= $estado['estado'] ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="d-flex flex-column gap-3">
        <div class="animate__animated animate__fadeInUp page-header">
            <div class="buttons-row buttons-2">
                <button class="btn-outline bg-white"><?php include('assets/svg/export-arrow-01.svg') ?>Exportar</button>
                <button class="btn-outline bg-white"><?php include('assets/svg/refresh-arrow-01.svg') ?></button>
            </div>
            <div class="animate__animated animate__fadeInUp d-flex gap-2 align-items-center">
                <div class="search-input">
                    <?php include('assets/svg/search.svg') ?>
                    <input type="text" id="inputBuscarClientes" placeholder="Buscar clientes...">
                </div>
                <button class="btn-outline bg-white"><?php include('assets/svg/filter.svg') ?>Filtrar</button>
            </div>
        </div>

        <div class="animate__animated animate__fadeInUp" id="clientsContainer"></div>
    </div>
</div>

<div class="modal fade" id="clienteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-large" id="clienteModalLabel">Agregar nuevo cliente</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="clienteModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn-default" id="btnGuardarCliente">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/clientes.js"></script>