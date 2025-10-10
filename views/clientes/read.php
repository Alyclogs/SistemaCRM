<?php
require_once __DIR__ . '/../../models/clientes/ClienteModel.php';

$pdo = Database::getConnection();
$clienteModel = new ClienteModel($pdo);
$estados = $clienteModel->obtenerEstadosClientes();
?>

<div class="page-body">
    <div class="list-view">
        <div class="list-items">
            <div class="list-item list-item-default selected" data-target="clientesBody" data-tipo="1">
                <?php include('assets/svg/profile-2user.svg') ?>
                <h5>Clientes</h5>
            </div>
            <div class="list-item list-item-default" data-target="organizacionesBody" data-tipo="2">
                <?php include('assets/svg/building.svg') ?>
                <h5>Organizaciones</h5>
            </div>
        </div>
        <div class="list-sections" style="width: 100%;">
            <div class="section-item show" id="clientesBody">
                <div class="section-body">
                    <div class="animate__animated animate__fadeInUp page-header">
                        <div class="d-flex gap-3 align-items-center">
                            <h5 class="page-title">Clientes</h5>
                            <button class="btn btn-default bg-accent" id="btnNuevoCliente" data-bs-toggle="modal" data-bs-target="#clienteModal">
                                <?php include('assets/svg/add.svg') ?>
                                <span>Nuevo registro</span>
                            </button>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <button class="btn btn-icon border" id="btnRefresh"><?php include('./assets/svg/refresh-arrow-01.svg') ?></button>
                            <div class="grupo-input">
                                <?php include('assets/svg/search.svg') ?>
                                <input type="text" id="inputBuscarClientes" placeholder="Buscar clientes...">
                            </div>
                            <div class="info-row">
                                <label for="registrosPaginaInput">Registros por página</label>
                                <input type="number" class="form-control w-auto" id="registrosPaginaInput" value="25" maxlength="2" max="50" min="1">
                            </div>
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
                                <thead id="tablaClientesHead"></thead>
                                <tbody id="tablaClientesBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section-item" id="organizacionesBody">
                <div class="section-body">
                    <div class="animate__animated animate__fadeInUp page-header">
                        <div class="d-flex gap-3 align-items-center">
                            <h5 class="page-title">Organizaciones</h5>
                            <button class="btn btn-default bg-accent" id="btnNuevaOrganizacion" data-bs-toggle="modal" data-bs-target="#organizacionModal">
                                <?php include('assets/svg/add.svg') ?>
                                <span>Nuevo registro</span>
                            </button>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <button class="btn btn-icon border" id="btnRefresh"><?php include('./assets/svg/refresh-arrow-01.svg') ?></button>
                            <div class="grupo-input">
                                <?php include('assets/svg/search.svg') ?>
                                <input type="text" id="inputBuscarOrganizaciones" placeholder="Buscar organizaciones...">
                            </div>
                            <div class="info-row">
                                <label for="registrosPaginaInput">Registros por página</label>
                                <input type="number" class="form-control w-auto" id="registrosPaginaInput" value="25" maxlength="2" max="50" min="1">
                            </div>
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
                        <table class="animate__animated animate__fadeInUp table align-middle" id="tablaOrganizaciones" style="display: none;">
                            <thead id="tablaOrganizacionesHead"></thead>
                            <tbody id="tablaOrganizacionesBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="clientesPager" class="table-pager"></div>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/clientes/index.js"></script>