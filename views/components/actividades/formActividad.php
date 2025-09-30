<?php
require_once __DIR__ . '/../../../models/actividades/ActividadModel.php';
require_once __DIR__ . '/../../../models/ajustes/AjustesModel.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idactividad = $_GET['id'] ?? null;
$pdo = Database::getConnection();
$agendaModel = new ActividadModel($pdo);
$ajustesModel = new AjustesModel($pdo);
$actividad = null;
$actividadCliente = null;
$actividadEmpresa = null;

if ($idactividad) {
    $actividad = $agendaModel->obtenerActividad($idactividad);

    if (!empty($actividad['clientes'])) {
        foreach ($actividad['clientes'] as $relacion) {
            if ($relacion['tipo_cliente'] === 'cliente') {
                $actividadCliente = $relacion;
            }
            if ($relacion['tipo_cliente'] === 'empresa') {
                $actividadEmpresa = $relacion;
            }
        }
    }

    if (!$actividad) {
        echo '<div class="alert alert-danger">No se encontró la actividad</div>';
        exit;
    }
}
$camposExtra = $ajustesModel->obtenerCamposExtraPorTabla(null, 'actividades');
?>

<div class="d-flex gap-3 h-100">
    <div class="flex-grow-1 h-100 pe-3" style="overflow: hidden auto;">
        <form id="formActividad" method="POST">
            <input type="hidden" name="idactividad" id="idactividad" value="<?= $actividad['idactividad'] ?? '' ?>">
            <input type="hidden" name="idestado" id="idestado" value="<?= $actividad['idestado'] ?? '' ?>">
            <div class="d-flex flex-column w-100">
                <div class="buttons-row buttons-actividad mb-3">
                    <button type="button" class="btn btn-outline btn-actividad" data-type="llamada"><?php include('../../../assets/svg/call.svg') ?></button>
                    <button type="button" class="btn btn-outline btn-actividad" data-type="videollamada"><?php include('../../../assets/svg/video.svg') ?></button>
                    <button type="button" class="btn btn-outline btn-actividad" data-type="reunion"><?php include('../../../assets/svg/profile-2user.svg') ?></button>
                </div>
                <div class="mb-4">
                    <div class="titulo-actividad" style="min-width: 220px; max-width: 360px;">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <h5 class="text-large text-break" id="tituloActividadLabel"><?= $actividad['nombre'] ?? "Nueva actividad" ?></h5>
                            <div class="svg-editar" style="display: none;">
                                <?php include('../../../assets/svg/edit.svg') ?>
                            </div>
                        </div>
                        <input type="text" class="form-control titulo-actividad-editando" id="titleInput" style="display: none;" name="nombre" value="<?= $actividad['nombre'] ?? "Nueva actividad" ?>">
                    </div>
                </div>
                <div class="horas-container d-flex gap-2 mb-3">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Duración de la actividad">
                        <?php include('../../../assets/svg/clock.svg') ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" class="form-control" style="max-width: 144px;" name="fecha" id="fechaInput" value="<?= $actividad['fecha'] ?? '' ?>">
                        <div class="busqueda-grupo" style="width: 116px;">
                            <input type="text" class="form-control" name="hora_inicio" id="horaInicioInput" value="<?= $actividad['hora_inicio'] ?? '' ?>" pattern="\d{2}:\d{2}" required>
                            <div class="resultados-busqueda" data-parent="horaInicioInput" style="top: 2.5rem; font-size: 13px;"></div>
                        </div>
                        <span class="px-2">a</span>
                        <div class="busqueda-grupo" style="width: 116px;">
                            <input type="text" class="form-control" name="hora_fin" id="horaFinInput" value="<?= $actividad['hora_fin'] ?? '' ?>" pattern="\d{2}:\d{2}" required>
                            <div class="resultados-busqueda" data-parent="horaFinInput" style="top: 2.5rem; font-size: 13px;"></div>
                        </div>
                    </div>
                </div>
                <div class="prioridad-container d-flex gap-2 w-100 mb-3">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Prioridad de la actividad">
                        <?php include('../../../assets/svg/alarm.svg') ?>
                    </div>
                    <div class="busqueda-grupo" style="width: 110px">
                        <input type="text" class="form-control" id="prioridadInput" name="prioridad" value="<?= $actividad['prioridad'] ?? '' ?>" placeholder="Prioridad">
                        <div class="resultados-busqueda" data-parent="prioridadInput" style="top: 2.5rem;">
                            <div class="resultado-item" data-value="alta">alta</div>
                            <div class="resultado-item" data-value="media">media</div>
                            <div class="resultado-item" data-value="baja">baja</div>
                        </div>
                    </div>
                </div>
                <div class="extra-container w-100 d-flex gap-2 mb-3">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Detalles de la actividad">
                        <?php include('../../../assets/svg/message-add-1.svg') ?>
                    </div>
                    <div class="w-100 d-flex flex-column gap-2">
                        <div id="detailOptions">
                            Agregar una
                            <a class="text-primary <?= isset($actividad) && isset($actividad['descripcion']) ? 'disable-click' : 'clickable' ?>" id="agregarDescripcion" data-target="descripcion-container">descripción</a>,
                            <a class="text-primary <?= isset($actividad) && isset($actividad['direccion']) ? 'disable-click' : 'clickable' ?>" id="agregarDireccion" data-target="direccion-container">dirección</a> o un
                            <a class="text-primary <?= isset($actividad) && isset($actividad['enlace']) ? 'disable-click' : 'clickable' ?>" id="agregarEnlace" data-target="enlace-container">enlace</a>
                        </div>
                        <div id="extraContent">
                            <div id="descripcion-container" class="extra-content d-flex flex-column gap-2 mb-2 <?= isset($actividad) && isset($actividad['descripcion']) ? '' : 'd-none' ?>">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="descripcionInput">Descripción:</label>
                                    <button type="button" class="btn btn-icon sm btn-hide-element" data-elements="#descripcion-container">
                                        <?php include('../../../assets/svg/x.svg') ?>
                                    </button>
                                </div>
                                <textarea class="form-control w-100" id="descripcionInput" name="descripcion" rows="3" placeholder="Ingrese una descripción"><?= isset($actividad) ? $actividad['descripcion'] ?? '' : '' ?></textarea>
                            </div>
                            <div id="direccion-container" class="extra-content d-flex flex-column gap-2 mb-2 <?= isset($actividad) && isset($actividad['direccion']) ? '' : 'd-none' ?>">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="direccionInput">Dirección:</label>
                                    <button type="button" class="btn btn-icon sm btn-hide-element" data-elements="#direccion-container">
                                        <?php include('../../../assets/svg/x.svg') ?>
                                    </button>
                                </div>
                                <input type="text" class="form-control w-100" id="direccionInput" name="direccion" placeholder="Ingrese un dirección" value="<?= isset($actividad) ? $actividad['direccion'] ?? '' : '' ?>">
                                <input type="text" class="form-control w-100" id="direccionReferenciaInput" name="direccion_referencia" placeholder="Ingrese una dirección de referencia" value="<?= isset($actividad) ? $actividad['direccion_referencia'] ?? '' : '' ?>">
                            </div>
                            <div id="enlace-container" class="extra-content d-flex flex-column gap-2 mb-3 <?= isset($actividad) && isset($actividad['enlace']) ? '' : 'd-none' ?>">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="enlaceInput">Enlace:</label>
                                    <button type="button" class="btn btn-icon sm btn-hide-element" data-elements="#enlace-container">
                                        <?php include('../../../assets/svg/x.svg') ?>
                                    </button>
                                </div>
                                <input type="url" class="form-control w-100" id="enlaceInput" name="enlace" placeholder="Ingrese un enlace" value="<?= isset($actividad) ? $actividad['enlace'] ?? '' : '' ?>">
                                <div class="d-flex align-items-center gap-2">
                                    <button class="btn btn-outline w-100" id="generarEnlaceZoom"><?php include('../../../assets/svg/video.svg') ?><span>Generar reunión con Zoom</span></button>
                                    <button class="btn btn-outline w-100" id="generarEnlaceMeet"><?php include('../../../assets/svg/video.svg') ?><span>Generar reunión con Meet</span></button>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($camposExtra)): ?>
                            <?php foreach ($camposExtra as $campo): ?>
                                <?php
                                // Valor actual del actividad o valor por defecto
                                $valorSeleccionado = isset($actividad[$campo['campo']])
                                    ? trim($actividad[$campo['campo']])
                                    : $campo['valor_inicial'];
                                ?>
                                <div class="col-6 mb-3">
                                    <label for="campoExtra_<?= $campo['idcampo'] ?>" class="form-label">
                                        <?= htmlspecialchars(ucfirst($campo['nombre'])) ?>
                                    </label>

                                    <?php if ($campo['tipo_dato'] === 'texto'): ?>
                                        <input type="text"
                                            class="form-control"
                                            id="campoExtra_<?= $campo['idcampo'] ?>"
                                            name="<?= $campo['campo'] ?>"
                                            value="<?= htmlspecialchars($valorSeleccionado ?? '') ?>"
                                            <?= $campo['longitud'] ? 'maxlength="' . (int)$campo['longitud'] . '"' : '' ?>
                                            <?= !empty($campo['requerido']) ? 'required' : '' ?>>

                                    <?php elseif ($campo['tipo_dato'] === 'numero'): ?>
                                        <input type="number"
                                            class="form-control"
                                            id="campoExtra_<?= $campo['idcampo'] ?>"
                                            name="<?= $campo['campo'] ?>"
                                            value="<?= htmlspecialchars($valorSeleccionado ?? '') ?>"
                                            <?= $campo['longitud'] ? 'maxlength="' . (int)$campo['longitud'] . '"' : '' ?>
                                            <?= !empty($campo['requerido']) ? 'required' : '' ?>>

                                    <?php elseif ($campo['tipo_dato'] === 'fecha'): ?>
                                        <input type="date"
                                            class="form-control"
                                            id="campoExtra_<?= $campo['idcampo'] ?>"
                                            name="<?= $campo['campo'] ?>"
                                            value="<?= htmlspecialchars($valorSeleccionado ?? '') ?>"
                                            <?= !empty($campo['requerido']) ? 'required' : '' ?>>

                                    <?php elseif ($campo['tipo_dato'] === 'booleano'): ?>
                                        <select class="form-select"
                                            id="campoExtra_<?= $campo['idcampo'] ?>"
                                            name="<?= $campo['campo'] ?>"
                                            <?= !empty($campo['requerido']) ? 'required' : '' ?>>
                                            <option value="1" <?= $valorSeleccionado == 1 ? 'selected' : '' ?>>Sí</option>
                                            <option value="0" <?= $valorSeleccionado == 0 ? 'selected' : '' ?>>No</option>
                                        </select>

                                    <?php elseif ($campo['tipo_dato'] === 'opciones' && is_array($campo['valor_inicial'])): ?>
                                        <select class="form-select"
                                            id="campoExtra_<?= $campo['idcampo'] ?>"
                                            name="<?= $campo['campo'] ?>"
                                            <?= !empty($campo['requerido']) ? 'required' : '' ?>>
                                            <?php if (!empty($campo['requerido'])): ?>
                                                <option value="" <?= empty($valorSeleccionado) ? 'selected' : '' ?>>Seleccionar</option>
                                            <?php endif; ?>
                                            <?php foreach ($campo['valor_inicial'] as $opcion): ?>
                                                <option value="<?= htmlspecialchars($opcion) ?>"
                                                    <?= ($valorSeleccionado == $opcion) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($opcion) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="notas-container w-100 d-flex gap-2 mb-3" style="height: 120px;">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Notas de la actividad">
                        <?php include('../../../assets/svg/document-text-2.svg') ?>
                    </div>
                    <textarea id="notaInput" class="form-control w-100" name="nota" rows="3" placeholder="Ingrese nota"><?= !empty($actividad['notas']) ? $actividad['notas'][0]['contenido'] : '' ?></textarea>
                </div>
                <div class="cliente-container d-flex gap-2 w-100 mb-3">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Asignada al cliente">
                        <?php include('../../../assets/svg/profile.svg') ?>
                    </div>
                    <div class="busqueda-grupo w-100">
                        <input type="text" class="form-control w-100" id="clienteInput" value="<?= $actividadCliente['nombre'] ?? '' ?>" placeholder="Buscar cliente">
                        <input type="hidden" name="idcliente" id="clienteIdInput" value="<?= $actividadCliente['idreferencia'] ?? '' ?>">
                        <div class="resultados-busqueda disable-auto" data-parent="clienteInput" style="top: 2.5rem; min-width: 300px;"></div>
                    </div>
                </div>
                <div class="organizacion-container d-flex gap-2 w-100 mb-3">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Asignada a la organización">
                        <?php include('../../../assets/svg/building.svg') ?>
                    </div>
                    <div class="busqueda-grupo w-100">
                        <input type="text" class="form-control w-100" id="organizacionInput" value="<?= $actividadEmpresa['nombre'] ?? '' ?>" placeholder="Buscar organización">
                        <input type="hidden" name="idempresa" id="idOrganizacionInput" value="<?= $actividadEmpresa['idreferencia'] ?? '' ?>">
                        <div class="resultados-busqueda disable-auto" data-parent="organizacionInput" style="top: 2.5rem; min-width: 300px;"></div>
                    </div>
                </div>
                <div class="usuario-container d-flex gap-2 w-100 mb-3">
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="Asignada al usuario">
                        <?php include('../../../assets/svg/user.svg') ?>
                    </div>
                    <div class="busqueda-grupo w-100">
                        <input type="text" class="form-control w-100" id="usuarioInput" value="<?= $actividad['usuario'] ?? $_SESSION['nombre'] ?>" placeholder="Buscar usuario" required>
                        <input type="hidden" name="idusuario" id="idUsuarioInput" value="<?= $actividad['idusuario'] ?? $_SESSION['idusuario'] ?>">
                        <div class="resultados-busqueda disable-auto" data-parent="usuarioInput" style="top: 2.5rem; min-width: 300px;"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div style="width: 280px; min-width: 280px; height: 100%;">
        <div id="miniCalendar"></div>
    </div>
</div>

<script>
    (() => {
        const tituloActividad = document.querySelector('.titulo-actividad');
        const tituloActividadLabel = document.getElementById('tituloActividadLabel');
        const titleInput = tituloActividad.querySelector('#titleInput');
        const svgEditar = tituloActividad.querySelector('.svg-editar');

        tituloActividad.addEventListener('mouseover', function() {
            if (titleInput.style.display === 'none') {
                svgEditar.style.display = 'inline';
            }
        });

        tituloActividad.addEventListener('mouseout', function() {
            if (titleInput.style.display === 'none') {
                svgEditar.style.display = 'none';
            }
        });

        tituloActividad.addEventListener('click', function() {
            titleInput.value = tituloActividadLabel.innerText;
            tituloActividadLabel.style.display = 'none';
            svgEditar.style.display = 'none';
            titleInput.style.display = 'block';
            titleInput.focus();
        });

        tituloActividad.addEventListener('change', function() {
            tituloActividadLabel.innerText = titleInput.value;
        });

        titleInput.addEventListener('mouseout', function() {
            tituloActividadLabel.innerText = titleInput.value;
            titleInput.style.display = 'none';
            tituloActividadLabel.style.display = 'block';
            svgEditar.style.display = 'none';
        });

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    })();
</script>