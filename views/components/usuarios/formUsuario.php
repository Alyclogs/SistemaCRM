<?php
require_once __DIR__ . '/../../../models/usuarios/UsuarioModel.php';

$idusuario = $_GET['id'] ?? null;
$usuarioModel = new UsuarioModel();
$usuario = null;
$roles = [];

if ($idusuario) {
    $usuario = $usuarioModel->obtenerUsuarioPorId($idusuario);

    if (!$usuario) {
        echo '<div class="alert alert-danger">No se encontró el usuario</div>';
        exit;
    }
}
$roles = $usuarioModel->obtenerRoles();
?>

<form method="POST" id="formUsuario">
    <input type="hidden" name="idusuario" value="<?= $usuario['idusuario'] ?? '' ?>">

    <div class="row">
        <div class="col-3 flex-column  d-flex align-items-center justify-content-center gap-3">
            <div class="foto-user">
                <img
                    id="prev-image"
                    src="<?php echo $usuario ? htmlspecialchars($usuario['foto']) : ''; ?>"
                    alt="Vista previa"
                    style="max-width: 100%; <?php echo $usuario && !empty($usuario['foto']) ? '' : 'display:none;'; ?> border-radius: 10px;" />
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
                    <label for="" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombreInput" name="nombre" value="<?= $usuario['nombre'] ?? '' ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                </div>
                <div class="col-6 mb-3">
                    <label for="" class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellidosInput" name="apellidos" value="<?= $usuario['apellidos'] ?? '' ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                </div>
                <div class="col-6 mb-3">
                    <label for="" class="form-label">Correo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="correoInput" name="correo" value="<?= $usuario['correo'] ?? '' ?>">
                </div>
                <div class="col-6 mb-3">
                    <label for="" class="form-label">Número de documento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="numDocInput" name="num_doc" value="<?= $usuario['num_doc'] ?? '' ?>" required maxlength="8">
                </div>
                <div class="col-6 mb-3">
                    <label for="" class="form-label">Teléfono <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="telefonoInput" name="telefono" value="<?= $usuario['telefono'] ?? '' ?>" required maxlength="9">
                </div>
                <div class="col-6 mb-3">
                    <label for="" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select class="form-select" id="rolSelect" name="idrol">
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['idrol'] ?>" <?= isset($usuario['idrol']) && $usuario['idrol'] == $rol['idrol'] ? 'selected' : '' ?>><?= $rol['rol'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>