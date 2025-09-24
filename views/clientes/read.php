<?php
require_once __DIR__ . '/../../models/clientes/ClienteModel.php';

$clienteModel = new ClienteModel();
$estados = $clienteModel->obtenerEstadosClientes();
?>

<div class="page-body">
    <div class="animate__animated animate__fadeInUp page-header">
        <div class="d-flex gap-3 align-items-center">
            <h5 class="page-title">Clientes</h5>
            <button class="btn btn-default bg-accent" id="btnNuevoRegistro" data-bs-toggle="modal" data-bs-target="#clienteModal">
                <?php include('assets/svg/add.svg') ?>
                <span>Nuevo registro</span>
            </button>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <button class="btn btn-icon border" id="btnRefresh"><?php include('./assets/svg/refresh-arrow-01.svg') ?></button>
            <div class="busqueda-grupo" data-type="Tipo">
                <button class="btn btn-outline boton-filtro selected" id="tiposClientes"><?php include('./assets/svg/filter.svg') ?><span class="selected-filtro" data-parent="tiposClientes" id="tipoCliente">Clientes</span></button>
                <div class="resultados-busqueda" data-parent="tiposClientes" style="min-width: 180px; right: 0px; top: 2.5rem;">
                    <div class="resultado-item filtro-item selected" data-id="1" data-value="Personas"><?php include('./assets/svg/profile.svg') ?><span>Personas</span></div>
                    <div class="resultado-item filtro-item" data-id="2" data-value="Organizaciones"><?php include('./assets/svg/building.svg') ?><span>Organizaciones</span></div>
                </div>
            </div>
            <div class="busqueda-grupo" data-type="Estado">
                <button class="btn btn-outline boton-filtro" id="estadosClientes"><?php include('./assets/svg/filter.svg') ?><span class="selected-filtro" data-parent="estadosClientes" id="estadoCliente">Estado</span></button>
                <div class="resultados-busqueda" data-parent="estadosClientes" style="min-width: 180px; right: 0px; top: 2.5rem;">
                    <?php if (!empty($estados)): ?>
                        <?php foreach ($estados as $estado): ?>
                            <div class="resultado-item filtro-item" data-id="<?= $estado['idestado'] ?>" data-value="<?= $estado['estado'] ?>"><?= $estado['estado'] ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">
        <div class="d-flex flex-column gap-3">
            <table class="animate__animated animate__fadeInUp table align-middle" id="tablaClientes" style="display: none;">
                <thead id="tablaClientesHead">
                    <th id="td-cliente">Cliente</th>
                    <th id="td-organizacion">Organización</th>
                    <th id="td-dni">DNI</th>
                    <th id="td-telefono">Teléfono</th>
                    <th id="td-correo">Correo</th>
                    <th id="td-estado">Estado</th>
                    <th></th>
                </thead>
                <tbody id="tablaClientesBody"></tbody>
            </table>

            <table class="animate__animated animate__fadeInUp table align-middle" id="tablaOrganizaciones" style="display: none;">
                <thead id="tablaOrganizacionesHead">
                    <th id="td-organizacion">Organizacion</th>
                    <th id="td-ruc">RUC</th>
                    <th id="td-direccion">Dirección</th>
                    <th></th>
                </thead>
                <tbody id="tablaOrganizacionesBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/clientes/read.js"></script>