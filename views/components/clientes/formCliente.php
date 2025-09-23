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
    <input type="hidden" name="idexistente" value="<?= $cliente['idcliente'] ?? '' ?>">

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
            <div class="row">
                <div class="col-6 mb-3">
                    <label for="nombresInput" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombresInput" name="nombres" value="<?= $cliente['nombres'] ?? '' ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                </div>
                <div class="col-6 mb-3">
                    <label for="apellidosInput" class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellidosInput" name="apellidos" value="<?= $cliente['apellidos'] ?? '' ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                </div>
                <div class="col-6 mb-3">
                    <label for="numDocInput" class="form-label">Número de documento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="numDocInput" name="num_doc" value="<?= $cliente['num_doc'] ?? '' ?>" required maxlength="8">
                </div>
                <div class="col-6 mb-3">
                    <label for="telefonoInput" class="form-label">Teléfono <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="telefonoInput" name="telefono" value="<?= $cliente['telefono'] ?? '' ?>" required maxlength="9">
                </div>
                <div class="col-6 mb-3">
                    <label for="estadoSelect" class="form-label">Estado <span class="text-danger">*</span></label>
                    <select class="form-select" id="estadoSelect" name="idestado">
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?= $estado['idestado'] ?>" <?= isset($cliente['idestado']) && $cliente['idestado'] == $estado['idestado'] ? 'selected' : '' ?>><?= $estado['estado'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label for="organizacionInput" class="form-label">Organización <span class="text-danger">*</span></label>
                    <div class="busqueda-grupo disable-auto">
                        <input type="text" class="form-control" id="organizacionInput" name="empresa" value="<?= $cliente['empresa_nombre'] ?? '' ?>" required>
                        <input type="hidden" name="idempresa" id="idOrganizacionInput" value="">
                        <div class="resultados-busqueda" data-parent="organizacionInput" style="top: 2.5rem;"></div>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label for="correoInput" class="form-label">Correo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="correoInput" name="correo" value="<?= $cliente['correo'] ?? '' ?>">
                </div>
            </div>
        </div>
    </div>
</form>