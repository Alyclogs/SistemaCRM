<?php
require_once __DIR__ . '/../../../models/clientes/ClienteModel.php';

$idcliente = $_GET['id'] ?? null;
$clienteModel = new ClienteModel();
$cliente = null;
$estados = [];

if ($idcliente) {
    $cliente = $clienteModel->obtenerCliente($idcliente);

    if (!$cliente) {
        echo '<div class="alert alert-danger">No se encontró el cliente</div>';
        exit;
    }
}
$estados = $clienteModel->obtenerEstadosClientes();
?>

<form method="POST" id="formCliente">
    <input type="hidden" name="idcliente" value="<?= $cliente['idcliente'] ?? '' ?>">

    <div class="row">
        <div class="col-3 flex-column  d-flex align-items-center justify-content-center gap-3">
            <div class="foto-user">
                <img
                    id="prev-image"
                    src="<?php echo $cliente ? htmlspecialchars($cliente['foto']) : ''; ?>"
                    alt="Vista previa"
                    style="max-width: 100%; <?php echo $cliente && !empty($cliente['foto']) ? '' : 'display:none;'; ?> border-radius: 10px;" />
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
            <div class="col-12 mb-3">
                <label for="" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombreInput" name="nombre" value="<?= $cliente['nombre'] ?? '' ?>">
            </div>
            <div class="col-12 mb-3">
                <label for="" class="form-label">Correo <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="correoInput" name="correo" value="<?= $cliente['correo'] ?? '' ?>">
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="" class="form-label">Tipo de documento <span class="text-danger">*</span></label>
                    <select class="form-select" id="tipoDocSelect" name="tipo_doc" value="<?= $cliente['tipo_doc'] ?? '' ?>">
                        <option value="DNI">DNI</option>
                        <option value="RUC">RUC</option>
                    </select>
                </div>
                <div class="col-6">
                    <label for="" class="form-label">Número de documento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="numDocInput" name="num_doc" value="<?= $cliente['num_doc'] ?? '' ?>">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="" class="form-label">Teléfono <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="telefonoInput" name="telefono" value="<?= $cliente['telefono'] ?? '' ?>">
                </div>
                <div class="col-6">
                    <label for="" class="form-label">Estado <span class="text-danger">*</span></label>
                    <select class="form-select" id="estadoSelect" name="idestado">
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?= $estado['idestado'] ?>" <?= isset($cliente['idestado']) && $cliente['idestado'] == $estado['idestado'] ? 'selected' : '' ?>><?= $estado['estado'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>