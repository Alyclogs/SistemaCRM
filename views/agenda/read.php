<?php
require_once __DIR__ . '/../../models/usuarios/UsuarioModel.php';

$pdo = Database::getConnection();
$usuarioModel = new UsuarioModel($pdo);
$usuarios = $usuarioModel->obtenerUsuarios();
?>

<div class="page-body p-4 mx-2">
    <div class="animate__animated animate__fadeInUp page-header">
        <div class="d-flex align-items-center gap-3">
            <h5 class="page-title">Calendario</h5>
            <button class="btn btn-default bg-accent" id="btnNuevaActividad">
                <?php include('assets/svg/add.svg') ?>
                <span>Nueva Actividad</span>
            </button>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-icon border" id="btnRefresh"><?php include('./assets/svg/refresh-arrow-01.svg') ?></button>
            <div class="busqueda-grupo" data-type="Estado">
                <button class="btn btn-outline boton-filtro selected" id="usuarioActual"><?php include('./assets/svg/filter.svg') ?><span class="selected-filtro" data-parent="usuarios" id="usuarioActual"><?= $_SESSION['nombre'] ?></span></button>
                <div class="resultados-busqueda" data-parent="usuarioActual" style="min-width: 180px; right: 0px; top: 2.5rem;">
                    <?php foreach ($usuarios as $usuario): ?>
                        <div class="resultado-item filtro-item <?= $usuario['idusuario'] === $_SESSION['idusuario'] ? 'selected' : '' ?>" data-id="<?= $usuario['idusuario'] ?>" data-value="<?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?>"><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-content">
        <div id="calendar"></div>
    </div>

    <div id="popupPreview" class="popup">
        <div class="buttons-row buttons-actividad mb-3">
            <button class="btn btn-outline btn-actividad selected" data-type="llamada"><?php include('./assets/svg/call.svg') ?></button>
            <button class="btn btn-outline btn-actividad" data-type="videollamada"><?php include('./assets/svg/video.svg') ?></button>
            <button class="btn btn-outline btn-actividad" data-type="reunion"><?php include('./assets/svg/profile-2user.svg') ?></button>
        </div>
        <input id="titleInput" class="form-control mb-2"></input>
        <div class="mb-2" id="infoDate"></div>
        <div class="d-flex w-100 justify-content-end gap-1">
            <button class="btn btn-default" id="btnDetallesActividad">Agregar</button>
        </div>
    </div>

    <div id="popupActividad" class="popup"></div>
    <div id="popupActualizar" class="popup"></div>
</div>

<script type="module" src="./assets/js/actividades/index.js"></script>