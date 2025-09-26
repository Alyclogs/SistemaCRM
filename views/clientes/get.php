<?php
require_once __DIR__ . '/../../models/clientes/ClienteModel.php';
require_once __DIR__ . '/../../models/actividades/ActividadModel.php';
require_once __DIR__ . '/../../models/Usuarios/UsuarioModel.php';

$id = $_GET['id'] ?? null;
$model = new ClienteModel();
$actividadModel = new ActividadModel();
$usuarioModel = new UsuarioModel();
$cliente = null;
$mensaje = '';

if ($id) {
    $cliente = $model->obtenerCliente($id);
}

if (!$cliente) {
    $mensaje = 'No se ha encontrado al cliente';
}
$usuarios = $usuarioModel->obtenerUsuarios();
$estadosActividad = $actividadModel->obtenerEstados();
?>

<link rel="stylesheet" href="./assets/css/actividad.css">

<?php if (!empty($mensaje)): ?>
    <div class="h-100 w-100 align-items-center d-flex justify-content-center">
        <div class="alert alert-danger"><?= $mensaje ?></div>
    </div>
<?php else: ?>
    <div class="page-content px-4 pt-1 pb-4">
        <input type="hidden" id="clienteActual" value="<?= $cliente['idcliente'] ?? '' ?>">
        <input type="hidden" id="tipoCliente" value="<?= 'cliente' ?>">
        <div class="d-flex h-100 gap-3">
            <div style="width: 30%;">
                <div class="container-shadow d-flex flex-column justify-content-between gap-3 h-100">
                    <div class="flex-grow-1">
                        <div class="info-container">
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
                                                $claseChip = 'danger';
                                                break;
                                        }
                                        ?>
                                        <div class="chip chip-<?= $claseChip ?>"><?= $cliente['estado'] ?? 'SIN ESTADO' ?></div>
                                    </div>
                                </div>
                                <div class="icons-row">
                                    <button class="btn btn-icon bg-light btn-edit" id="btnEditCliente" data-target="modalForm" data-id="<?= $cliente['idcliente'] ?>" data-type="cliente" title="Editar cliente"><?php include('./assets/svg/edit.svg') ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="info-container">
                            <h6 class="fw-bold mb-2">Detalles:</h6>
                            <div class="d-flex flex-column gap-1">
                                <?php if (isset($cliente['num_doc'])): ?>
                                    <div class="info-row">
                                        <?php include('./assets/svg/document-text-2.svg') ?>
                                        <span>DNI: <?= $cliente['num_doc'] ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($cliente['telefono'])): ?>
                                    <div class="info-row">
                                        <?php include('./assets/svg/call.svg') ?>
                                        <span><?= $cliente['telefono'] ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($cliente['correo'])): ?>
                                    <div class="info-row">
                                        <?php include('./assets/svg/sms.svg') ?>
                                        <span><?= $cliente['correo'] ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!isset($cliente['correo']) && !isset($cliente['telefono']) && !isset($cliente['telefono'])): ?>
                                    <span class="text-primary clickable btn-edit" data-id="<?= $cliente['idcliente'] ?>" data-type="cliente" data-target="inlineForm">Agregar detalles</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-container">
                            <h6 class="fw-bold mb-2">Organización:</h6>
                            <?php if (isset($cliente['correo'])): ?>
                                <div class="info-row">
                                    <img class="user-icon sm clickable" data-type="empresa" data-id="<?= $cliente['idempresa'] ?>" src="<?= $cliente['empresa_foto'] ?>" alt="Foto de <?= $cliente['empresa_nombre'] ?>">
                                    <div class="d-flex flex-column">
                                        <span class="user-link clickable" data-type="empresa" data-id="<?= $cliente['idempresa'] ?>"><?= $cliente['empresa_nombre'] ?></span>
                                        <span class="text-small">RUC: <?= $cliente['empresa_ruc'] ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-primary clickable btn-edit" data-id="<?= $cliente['idcliente'] ?>" data-type="empresa" data-target="inlineForm">Agregar organización</span>
                            <?php endif; ?>
                        </div>
                        <div class="info-container">
                            <h6 class="fw-bold mb-2">Asesor:</h6>
                            <div class="busqueda-grupo">
                                <div class="info-row gap-3 py-1 px-2" id="usuarioActual">
                                    <div class="info-row">
                                        <img id="usuarioActualFoto" class="user-icon sm" src="<?= $cliente['usuario_foto'] ?>" alt="Foto de <?= $cliente['usuario'] ?>">
                                        <div class="d-flex flex-column">
                                            <span id="usuarioActualNombre"><?= $cliente['usuario'] ?></span>
                                            <span class="text-small">Asesor</span>
                                        </div>
                                    </div>
                                    <button class="btn btn-icon sm">
                                        <?php include('./assets/svg/arrow-down-02.svg') ?>
                                    </button>
                                </div>
                                <div class="resultados-busqueda" data-parent="usuarioActual" style="top: 3rem;">
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <div class="resultado-item usuario-item <?= $cliente['idusuario'] === $usuario['idusuario'] ? 'selected' : '' ?>" data-id="<?= $usuario['idusuario'] ?>" data-value="<?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?>"><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></div>
                                    <?php endforeach; ?>
                                </div>
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
            <div style="width: 70%;">
                <div class="container-shadow h-100">
                    <div class="tabs-container">
                        <div class="tab-item selected" data-value="notas">
                            <?php include('./assets/svg/document-text-2.svg') ?>
                            <span>Notas</span>
                        </div>
                        <div class="tab-item" data-value="whatsapp">
                            <?php include('./assets/svg/message-text.svg') ?>
                            <span>WhatsApp</span>
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
                            <div id="tabContainer" class="py-3" style="height: 310px; overflow-y: auto;">
                                <div id="notasContainer"></div>
                                <div id="whatsappContainer"></div>
                                <div id="correoContainer"></div>
                                <div id="archivosContainer"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                <h6 class="fw-bold">Historial de actividades</h6>
                                <button class="btn bg-default">
                                    <?php include('./assets/svg/add.svg') ?>
                                    <span>Nueva actividad</span>
                                </button>
                            </div>
                            <div class="w-100 info-row gap-4 mb-3 filtro-historial">
                                <div class="filtro-historial-item category-badge clickable selected" data-id="0" data-value="todas">Todas</div>
                                <div class="filtro-historial-item category-badge clickable" data-id="1" data-value="actividad">Actividades</div>
                                <div class="filtro-historial-item category-badge clickable" data-id="2" data-value="nota">Notas</div>
                                <div class="filtro-historial-item category-badge clickable" data-id="3" data-value="whatsapp">WhatsApp</div>
                                <div class="filtro-historial-item category-badge clickable" data-id="4" data-value="correo">Correos</div>
                                <div class="filtro-historial-item category-badge clickable" data-id="5" data-value="archivo">Archivos</div>
                                <div class="filtro-historial-item category-badge clickable" data-id="6" data-value="cambios">Registro de Cambios</div>
                            </div>
                            <div id="historialContainer" style="height: 310px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="./assets/js/clientes/get.js"></script>
<?php endif; ?>