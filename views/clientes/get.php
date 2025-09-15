<?php
require_once __DIR__ . '/../../models/Clientes/ClienteModel.php';

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
<?php endif; ?>
<div class="row">
    <div class="col-3">
        <div class="container-shadow">
            <div class="d-flex justify-content-between">
                <img class="user-icon" data-type="cliente" data-id="<?= $cliente['idcliente'] ?>" src="<?= $cliente['foto'] ?>" alt="Foto de <?= $cliente['nombre'] ?>">
                <div class="icons-row">
                    <button class="btn-icon bg-light" id="btnEditCliente" data-id="<?= $cliente['idcliente'] ?>" title="Editar cliente">${myIcons.edit}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6"></div>
    <div class="col-3"></div>
</div>