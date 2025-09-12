<?php
require_once __DIR__ . '/../models/Clientes/ClienteModel.php';

$clienteModel = new ClienteModel();
$estados = $clienteModel->obtenerEstadosClientes();
?>

<div class="page-header">
    <div class="page-title text-large">Clientes</div>
    <button class="btn-default bcg-accent">
        <?php include('assets/svg/add.svg') ?>
        <span>Nuevo registro</span>
    </button>
</div>

<div class="page-main">
    <div class="tabs-container">
        <div class="tab-item">TODOS</div>
        <?php if (!empty($estados)): ?>
            <?php foreach ($estados as $estado): ?>
                <div class="tab-item" data-estado="<?= $estado['idestado'] ?>"><?= $estado['estado'] ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="d-flex flex-column gap-3">
        <div class="page-header">
            <div class="buttons-row buttons-2">
                <button class="btn-outline bcg-white"><?php include('assets/svg/export-arrow-01.svg') ?>Exportar</button>
                <button class="btn-outline bcg-white"><?php include('assets/svg/refresh-arrow-01.svg') ?></button>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <div class="search-input">
                    <?php include('assets/svg/search.svg') ?>
                    <input type="text" id="inputBuscarClientes" placeholder="Buscar clientes...">
                </div>
                <button class="btn-outline bcg-white"><?php include('assets/svg/filter.svg') ?>Filtrar</button>
            </div>
        </div>

        <div class="row align-items-center w-100 h-100" id="clientsContainer"></div>
    </div>
</div>

<script type="module" src="./assets/js/clientes.js"></script>