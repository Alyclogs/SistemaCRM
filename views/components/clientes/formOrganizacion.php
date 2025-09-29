<?php
require_once __DIR__ . '/../../../models/clientes/ClienteModel.php';
require_once __DIR__ . '/../../../models/ajustes/AjustesModel.php';

$id = $_GET['id'] ?? null;
$pdo = Database::getConnection();
$empresaModel = new ClienteModel($pdo);
$ajustesModel = new AjustesModel($pdo);
$empresa = null;

if ($id) {
    $empresa = $empresaModel->obtenerOrganizacion($id);

    if (!$empresa) {
        echo '<div class="alert alert-danger">No se encontró la organización</div>';
        exit;
    }
}
$camposExtra = $ajustesModel->obtenerCamposPorTipo(null, 'empresa');
?>

<form method="POST" id="formOrganizacion">
    <input type="hidden" name="id" value="<?= $empresa['idempresa'] ?? '' ?>">

    <div class="row">
        <div class="col-3 flex-column  d-flex align-items-center justify-content-center gap-3">
            <div class="foto-user">
                <img
                    id="prev-image"
                    src="<?php echo $empresa ? htmlspecialchars($empresa['foto']) : ''; ?>"
                    alt="Vista previa"
                    style="max-width: 100%; <?php echo $empresa && !empty($empresa['foto']) ? '' : 'display:none;'; ?> border-radius: 10px;" />
            </div>

            <button type="button" class="btn btn-outline" onclick="document.getElementById('fileInput').click();">
                <span>Adjuntar Foto</span>
            </button>

            <div class="recomendacion-foto">
                <p>Sube tu imagen en <br>formato PNG o JPG</p>
                <p>Dimensiones :800 x 800px</p>
                <p>Peso < 200kb</p>
            </div>

            <input type="file" id="fileInput" name="foto" class="input-file" accept="image/*" style="display:none">
        </div>

        <div class="col-9">
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="razonSocialInput" class="form-label">Razón Social <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="razonSocialInput" name="razon_social" value="<?= $empresa['razon_social'] ?? '' ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                </div>
                <div class="col-12 mb-3">
                    <label for="rucInput" class="form-label">RUC <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="rucInput" name="ruc" value="<?= $empresa['ruc'] ?? '' ?>" required maxlength="11">
                </div>
                <div class="col-12 mb-3">
                    <label for="direccionInput" class="form-label">Dirección (Principal) <span class="text-danger">*</span></label>
                    <textarea type="text" class="form-control" id="direccionInput" name="direccion" rows="2"><?= $empresa['direccion'] ?? '' ?></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label for="direccionRefInput" class="form-label">Dirección (Referencia) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="direccionRefInput" name="direccion_referencia" value="<?= $empresa['direccion_referencia'] ?? '' ?>">
                </div>
                <?php if (!empty($camposExtra)): ?>
                    <?php foreach ($camposExtra as $campo): ?>
                        <div class="col-6 mb-3">
                            <label for="campoExtra_<?= $campo['idcampo'] ?>" class="form-label">
                                <?= htmlspecialchars(ucfirst($campo['nombre'])) ?>
                            </label>

                            <?php if ($campo['tipo_dato'] === 'texto'): ?>
                                <input type="text"
                                    class="form-control"
                                    id="campoExtra_<?= $campo['idcampo'] ?>"
                                    name="extra_<?= $campo['nombre'] ?>"
                                    value="<?= trim($empresa['extra'][$campo['nombre']]) ?? htmlspecialchars($campo['valor_inicial'] ?? '') ?>"
                                    <?= $campo['longitud'] ? 'maxlength="' . (int)$campo['longitud'] . '"' : '' ?>
                                    <?= isset($campo['requerido']) && $campo['requerido'] === 1 ? 'required' : '' ?>>

                            <?php elseif ($campo['tipo_dato'] === 'numero'): ?>
                                <input type="number"
                                    class="form-control"
                                    id="campoExtra_<?= $campo['idcampo'] ?>"
                                    name="extra_<?= $campo['nombre'] ?>"
                                    value="<?= trim($empresa['extra'][$campo['nombre']]) ?? htmlspecialchars($campo['valor_inicial'] ?? '') ?>"
                                    <?= $campo['longitud'] ? 'maxlength="' . (int)$campo['longitud'] . '"' : '' ?>
                                    <?= isset($campo['requerido']) && $campo['requerido'] === 1 ? 'required' : '' ?>>

                            <?php elseif ($campo['tipo_dato'] === 'fecha'): ?>
                                <input type="date"
                                    class="form-control"
                                    id="campoExtra_<?= $campo['idcampo'] ?>"
                                    name="extra_<?= $campo['nombre'] ?>"
                                    value="<?= trim($empresa['extra'][$campo['nombre']]) ?? htmlspecialchars($campo['valor_inicial'] ?? '') ?>"
                                    <?= isset($campo['requerido']) && $campo['requerido'] === 1 ? 'required' : '' ?>>

                            <?php elseif ($campo['tipo_dato'] === 'booleano'): ?>
                                <select class="form-select"
                                    id="campoExtra_<?= $campo['idcampo'] ?>"
                                    name="extra_<?= $campo['nombre'] ?>"
                                    <?= $campo['requerido'] === 1 ? 'required' : '' ?>>
                                    <option value="1" <?= (trim($empresa['extra'][$campo['nombre']]) ?? $campo['valor_inicial']) == 1 ? 'selected' : '' ?>>Sí</option>
                                    <option value="0" <?= (trim($empresa['extra'][$campo['nombre']]) ?? $campo['valor_inicial']) == 2 ? 'selected' : '' ?>>No</option>
                                </select>

                            <?php elseif ($campo['tipo_dato'] === 'opciones' && is_array($campo['valor_inicial'])): ?>
                                <select class="form-select"
                                    id="campoExtra_<?= $campo['idcampo'] ?>"
                                    name="extra_<?= $campo['nombre'] ?>"
                                    <?= isset($campo['requerido']) && $campo['requerido'] === 1 ? 'required' : '' ?>>
                                    <?php foreach ($campo['valor_inicial'] as $opcion): ?>
                                        <option value="<?= htmlspecialchars($opcion) ?>"
                                            <?= (isset($empresa['extra'][$campo['nombre']]) && $empresa['extra'][$campo['nombre']] == $opcion) ? 'selected' : '' ?>>
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
    </div>
</form>