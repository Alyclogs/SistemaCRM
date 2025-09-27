<?php
require_once __DIR__ . '/../../../models/usuarios/UsuarioModel.php';

$idusuario = $_GET['id'] ?? null;
$pdo = Database::getConnection();
$usuarioModel = new UsuarioModel($pdo);
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
$estados = $usuarioModel->obtenerEstados();
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
                <div class="col-6 mb-3">
                    <label for="nombresInput" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombresInput" name="nombres" value="<?= $usuario['nombres'] ?? '' ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                </div>
                <div class="col-6 mb-3">
                    <label for="apellidosInput" class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellidosInput" name="apellidos" value="<?= $usuario['apellidos'] ?? '' ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                </div>
                <div class="col-6 mb-3">
                    <label for="numDocInput" class="form-label">Número de documento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="numDocInput" name="num_doc" value="<?= $usuario['num_doc'] ?? '' ?>" required maxlength="8">
                </div>
                <div class="col-6 mb-3">
                    <label for="telefonoInput" class="form-label">Teléfono <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="telefonoInput" name="telefono" value="<?= $usuario['telefono'] ?? '' ?>" required maxlength="9">
                </div>
                <div class="col-12 mb-3">
                    <label for="correoInput" class="form-label">Correo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="correoInput" name="correo" value="<?= $usuario['correo'] ?? '' ?>">
                </div>
                <div class="col-6 mb-3">
                    <label for="rolSelect" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select class="form-select" id="rolSelect" name="idrol">
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['idrol'] ?>" <?= isset($usuario['idrol']) && $usuario['idrol'] == $rol['idrol'] ? 'selected' : '' ?>><?= $rol['rol'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label for="estadoSelect" class="form-label">Estado <span class="text-danger">*</span></label>
                    <select class="form-select" id="estadoSelect" name="idestado">
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?= $estado['idestado'] ?>" <?= isset($usuario['idestado']) && $usuario['idestado'] == $estado['idestado'] ? 'selected' : '' ?>><?= $estado['estado'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label for="userInput" class="form-label">Nombre de usuario <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="userInput" name="usuario" value="<?= $usuario['usuario'] ?? '' ?>" required maxlength="8" autocomplete="new-user">
                </div>
                <div class="col-6 mb-3">
                    <label for="passwordInput" class="form-label">Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="password" id="passwordInput" <?php echo $usuario ? '' : 'required'; ?> maxlength="8" value="" autocomplete="new-password">
                        <button class="input-button" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // Mostrar/ocultar contraseña
    $(document).on('click', '#togglePassword', function() {
        const passwordField = $('#passwordInput');
        const icon = $('#togglePasswordIcon');
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });
</script>
<script>
    (() => {
        function generarNombreUsuarioYPassword() {
            const nombres = document.getElementById('nombresInput').value.trim();
            const apellidos = document.getElementById('apellidosInput').value.trim();
            const dni = document.getElementById('numDocInput').value.trim();
            const usuarioField = document.getElementById('userInput');
            const passwordField = document.getElementById('passwordInput');

            // Validamos que los campos necesarios estén llenos
            if (nombres && apellidos && dni.length === 8) {
                const primeraLetraNombre = nombres.charAt(0).toLowerCase();
                const apellidoPaterno = apellidos.split(' ')[0].toLowerCase();

                // Crear nombre de usuario (ejemplo: jrodriguez)
                const nombreUsuario = primeraLetraNombre + apellidoPaterno;
                usuarioField.value = nombreUsuario;

                // Establecer la contraseña como el DNI
                passwordField.value = dni;
            }
        }

        document.getElementById('nombresInput').addEventListener('input', generarNombreUsuarioYPassword);
        document.getElementById('apellidosInput').addEventListener('input', generarNombreUsuarioYPassword);
        document.getElementById('numDocInput').addEventListener('input', generarNombreUsuarioYPassword);
        document.getElementById('nombresInput').addEventListener('change', generarNombreUsuarioYPassword);
        document.getElementById('apellidosInput').addEventListener('change', generarNombreUsuarioYPassword);
        document.getElementById('numDocInput').addEventListener('change', generarNombreUsuarioYPassword);
    })();
</script>