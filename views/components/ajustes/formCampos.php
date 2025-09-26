<?php
require_once __DIR__ . '/../../../models/ajustes/AjustesModel.php';
$ajustesModel = new AjustesModel();

$id = $_GET['id'] ?? null;
$mensaje = "";
$campo = null;

if ($id) {
    $campo = $ajustesModel->obtenerCampo($id);

    if (!$campo) {
        $mensaje = "No se encontró el campo";
    }
}
?>

<?php if ($mensaje): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
    <?php exit; ?>
<?php endif; ?>

<form method="POST" id="formcampo">
    <input type="hidden" name="idcampo" value="<?= $campo['idcampo'] ?? '' ?>">
    <div class="row">
        <div class="col-6 mb-3">
            <label for="nombre" class="form-label">Nombre del campo</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $campo['nombre'] ?? '' ?>" required>
        </div>
        <div class="col-6 mb-3">
            <label for="tipo" class="form-label">Tipo de dato</label>
            <select class="form-select" id="tipo" name="tipo_dato" required>
                <option value="texto" <?= isset($campo['tipo_dato']) && $campo['tipo_dato'] === 'texto' ? 'selected' : '' ?>>Texto</option>
                <option value="numero" <?= isset($campo['tipo_dato']) && $campo['tipo_dato'] === 'numero' ? 'selected' : '' ?>>Número</option>
                <option value="fecha" <?= isset($campo['tipo_dato']) && $campo['tipo_dato'] === 'fecha' ? 'selected' : '' ?>>Fecha</option>
                <option value="booleano" <?= isset($campo['tipo_dato']) && $campo['tipo_dato'] === 'booleano' ? 'selected' : '' ?>>Booleano</option>
            </select>
        </div>
        <div class="col-12 mb-3">
            <label for="valor_inicial" class="form-label">Valor inicial</label>
            <input type="text" class="form-control" id="valor_inicial" name="valor_inicial" value="<?= $campo['valor_inicial'] ?? '' ?>">
        </div>
    </div>
</form>