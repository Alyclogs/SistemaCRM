<?php
require_once __DIR__ . '/../../../models/clientes/ClienteModel.php';

$id = $_GET['id'] ?? null;
$clienteModel = new ClienteModel();
$empresa = null;

if ($id) {
    $empresa = $clienteModel->obtenerOrganizacion($id);

    if (!$empresa) {
        echo '<div class="alert alert-danger">No se encontró la organización</div>';
        exit;
    }
}
?>

<form method="POST" id="formOrganizacion">
    <input type="hidden" name="idexistente" value="<?= $empresa['idempresa'] ?? '' ?>">

    <div class="row">
        <div class="col-3 flex-column  d-flex align-items-center justify-content-center gap-3">
            <div class="foto-user">
                <img
                    id="prev-image"
                    src="<?php echo $empresa ? htmlspecialchars($empresa['foto']) : ''; ?>"
                    alt="Vista previa"
                    style="max-width: 100%; <?php echo $empresa && !empty($empresa['foto']) ? '' : 'display:none;'; ?> border-radius: 10px;" />
            </div>

            <button type="button" class="btn-outline" onclick="document.getElementById('fileInput').click();">
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
            </div>
        </div>
    </div>
</form>