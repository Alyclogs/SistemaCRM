<?php
require_once __DIR__ . '/../../models/clientes/ClienteModel.php';
require_once __DIR__ . '/../../models/Usuarios/UsuarioModel.php';

$pdo = Database::getConnection();
$clienteModel = new ClienteModel($pdo);
$usuarioModel = new UsuarioModel($pdo);
$estados = $clienteModel->obtenerEstadosClientes();
$usuarios = $usuarioModel->obtenerUsuarios();
?>

<div class="page-body">
    <div class="list-view">
        <div class="list-items p-4" style="width: 18%;">
            <div class="list-item list-item-default selected" data-target="clientesBody" data-tipo="1">
                <?php include('assets/svg/profile-2user.svg') ?>
                <h6>Clientes</h6>
            </div>
            <div class="list-item list-item-default" data-target="organizacionesBody" data-tipo="2">
                <?php include('assets/svg/building.svg') ?>
                <h6>Organizaciones</h6>
            </div>
        </div>
        <div class="list-sections p-4" style="width: 100%;">
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
                            <div class="busqueda-grupo clickable">
                                <div class="info-row gap-3 py-1 px-2" id="usuarioActual">
                                    <div class="info-row">
                                        <img id="usuarioActualFoto" class="user-icon sm" src="<?= $_SESSION['foto'] ?>" alt="Foto de <?= $_SESSION['nombre'] ?>">
                                        <span id="usuarioActualNombre"><?= $_SESSION['nombre'] ?></span>
                                    </div>
                                    <button class="btn btn-icon sm">
                                        <?php include('./assets/svg/arrow-down-02.svg') ?>
                                    </button>
                                </div>
                                <div class="resultados-busqueda" data-parent="usuarioActual" style="top: 3rem;">
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <div class="resultado-item usuario-item <?= $_SESSION['idusuario'] === $usuario['idusuario'] ? 'selected' : '' ?>" data-id="<?= $usuario['idusuario'] ?>" data-value="<?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?>"><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></div>
                                    <?php endforeach; ?>
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
    <div class="floating-container bottom-right">
        <div class="floating-content" id="floatingButton">
            <button class="btn btn-lg btn-default shadow-lg" id="btnCrearCampania">
                <?php include('assets/svg/add.svg') ?>
                <span>Crear campaña</span>
            </button>
        </div>
    </div>
</div>

<script type="module" src="./assets/js/clientes/index.js"></script>