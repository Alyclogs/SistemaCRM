<?php
require_once __DIR__ . '/../../models/clientes/ClienteModel.php';

$id = $_GET['id'] ?? null;
$model = new ClienteModel();
$cliente = null;
$mensaje = '';

if ($id) {
    $cliente = $model->obtenerCliente($id);
}

if (!$cliente) {
    $mensaje = 'No se ha encontrado al cliente';
}
?>

<?php if (!empty($mensaje)): ?>
    <div class="h-100 w-100 align-items-center d-flex justify-content-center">
        <div class="alert alert-danger"><?= $mensaje ?></div>
    </div>
<?php else: ?>
    <div class="page-content">
        <input type="hidden" name="">
        <div class="row h-100">
            <div class="col-3">
                <div class="container-shadow d-flex flex-column justify-content-between gap-3 h-100">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between p-3 mb-2">
                            <div class="info-row gap-2">
                                <img class="user-icon" data-type="cliente" data-id="<?= $cliente['idcliente'] ?>" src="<?= $cliente['foto'] ?>" alt="Foto de <?= $cliente['nombres'] ?>">
                                <div class="d-flex flex-column">
                                    <h5 class="text-large mb-2"><?= $cliente['nombres'] . ' ' . $cliente['apellidos'] ?></h5>
                                    <?php
                                    $claseChip = 'info';
                                    switch ($cliente['estado']) {
                                        case 'PROSPECTO':
                                            $claseChip = 'warning';
                                            break;
                                        case 'CLIENTE':
                                            $claseChip = 'success';
                                            break;
                                        default:
                                            $claseChip = 'info';
                                            break;
                                    }
                                    ?>
                                    <div class="chip chip-<?= $claseChip ?>"><?= $cliente['estado'] ?></div>
                                </div>
                            </div>
                            <div class="icons-row">
                                <button class="btn btn-icon bg-light" id="btnEditCliente" data-id="<?= $cliente['idcliente'] ?>" title="Editar cliente"><?php include('./assets/svg/edit.svg') ?></button>
                            </div>
                        </div>
                        <div class="info-container">
                            <h6 class="fw-bold mb-2">Detalles:</h6>
                            <div class="d-flex flex-column gap-1">
                                <div class="info-row">
                                    <?php include('./assets/svg/document-text-2.svg') ?>
                                    <span>DNI: <?= $cliente['num_doc'] ?></span>
                                </div>
                                <div class="info-row">
                                    <?php include('./assets/svg/call.svg') ?>
                                    <span><?= $cliente['telefono'] ?></span>
                                </div>
                                <div class="info-row">
                                    <?php include('./assets/svg/sms.svg') ?>
                                    <span><?= $cliente['correo'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="info-container">
                            <h6 class="fw-bold mb-2">Organización:</h6>
                            <div class="info-row">
                                <img class="user-icon sm clickable" data-type="empresa" data-id="<?= $cliente['idempresa'] ?>" src="<?= $cliente['empresa_foto'] ?>" alt="Foto de <?= $cliente['empresa_nombre'] ?>">
                                <div class="d-flex flex-column">
                                    <span class="user-link clickable" data-type="empresa" data-id="<?= $cliente['idempresa'] ?>"><?= $cliente['empresa_nombre'] ?></span>
                                    <span class="text-small">RUC: <?= $cliente['empresa_ruc'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="info-container">
                            <h6 class="fw-bold mb-2">Propietario:</h6>
                            <div class="busqueda-grupo">
                                <div class="info-row gap-3 py-1 px-2" id="usuarioActual">
                                    <div class="info-row">
                                        <img id="usuarioActualFoto" class="user-icon sm" src="<?= $_SESSION['foto'] ?>" alt="Foto de <?= $_SESSION['nombre'] ?>">
                                        <div class="d-flex flex-column">
                                            <span id="usuarioActualNombre"><?= $_SESSION['nombre'] ?></span>
                                            <span class="text-small">Propietario</span>
                                        </div>
                                    </div>
                                    <button class="btn btn-icon sm">
                                        <?php include('./assets/svg/arrow-down-02.svg') ?>
                                    </button>
                                </div>
                                <div class="resultados-busqueda" data-parent="usuarioActual" style="top: 2.5px;"></div>
                            </div>
                        </div>
                    </div>
                    <?php if ($cliente['estado'] === 'PROSPECTO'): ?>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-default bg-accent" id="btnConvertirCliente">Convertir a cliente</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-9 container-shadow">
                <div class="tabs-container">
                    <div class="tab-item selected" data-value="notas">
                        <?php include('./assets/svg/document-text-2.svg') ?>
                        <span>Notas</span>
                    </div>
                    <div class="tab-item" data-value="whatsapp">
                        <?php include('./assets/svg/message-text.svg') ?>
                        <span>Whatsapp</span>
                    </div>
                    <div class="tab-item" data-value="correo">
                        <?php include('./assets/svg/sms.svg') ?>
                        <span>Correo</span>
                    </div>
                    <div class="tab-item" data-value="archivos">
                        <?php include('./assets/svg/paperclip.svg') ?>
                        <span>Archivos</span>
                    </div>
                    <div class="tab-item w-100 h-100 disable-hover"></div>
                </div>
                <div class="p-2" style="max-height: 100%; overflow-y: auto;">
                    <div class="mb-3">
                        <div id="tabContainer" class="p-3" style="height: 320px; overflow-y: auto;">
                            <div id="notasContainer"></div>
                            <div id="whatsappContainer"></div>
                            <div id="correoContainer"></div>
                            <div id="archivosContainer"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">Historial de actividades</h6>
                        <div id="historialContainer" class="p-3" style="height: 320px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>