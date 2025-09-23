<?php
require_once __DIR__ . '/../../models/Usuarios/UsuarioModel.php';

$id = $_GET['id'] ?? null;
$model = new UsuarioModel();
$usuario = null;
$mensaje = '';

if ($id) {
    $usuario = $model->obtenerUsuarioPorId($id);
}

if (!$usuario) {
    $mensaje = 'No se ha encontrado al usuario';
}
?>

<?php if (!empty($mensaje)): ?>
    <div class="h-100 w-100 align-items-center d-flex justify-content-center">
        <div class="alert alert-danger"><?= $mensaje ?></div>
    </div>
<?php endif; ?>
<div class="page-content">
    <div class="row">
        <div class="col-3">
            <div class="container-shadow bg-white">
                <div class="d-flex justify-content-between">
                    <img class="user-icon" data-type="usuario" data-id="<?= $usuario['idusuario'] ?>" src="<?= $usuario['foto'] ?>" alt="Foto de <?= $usuario['nombres'] ?>">
                    <div class="icons-row">
                        <button class="btn-icon bg-light" id="btnEditUsuario" data-id="<?= $usuario['idusuario'] ?>" title="Editar usuario"><?php include('./assets/svg/edit.svg') ?></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6"></div>
        <div class="col-3"></div>
    </div>
</div>