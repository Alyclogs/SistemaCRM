<?php
require_once __DIR__ . '/../models/usuarios/UsuarioModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$logout = isset($_GET['logout']) ? boolval($_GET['logout']) : false;
if ($logout) {
    session_destroy();
    header("Location: index.php?p=login");
}

// Si ya hay sesión completa, redirigir a home
if (isset($_SESSION['usuario']) && isset($_SESSION['rol'])) {
    header("Location: index.php?p=home");
    exit;
}

$error = isset($_GET['error']) ? boolval($_GET['error']) : false;
?>

<link rel="stylesheet" href="./assets/css/login.css">
<div class="d-flex vh-100 d-flex align-items-center justify-content-center">
    <img src="./assets/img/capedu-large.png" alt="Imagen de Capedu" class="fondo-logo">
    <div class="p-4 container-default login-container shadow-lg">
        <div class="p-4 d-flex flex-column">
            <h4 class="text-center my-4">Iniciar sesión</h4>

            <?php if ($error === true): ?>
                <div class="alert alert-danger">Usuario o contraseña incorrectos</div>
            <?php endif; ?>
            <div class="mb-4">
                <form method="POST" action="http://localhost/SistemaCRM/controller/Usuarios/procesarLogin.php">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <div class="grupo-input">
                            <div class="px-1">
                                <?php include('./assets/svg/user.svg') ?>
                            </div>
                            <input type="text" class="form-control" name="usuario" placeholder="Nombre de usuario" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Contraseña</label>
                        <div class="grupo-input mb-4">
                            <div class="px-1">
                                <?php include('./assets/svg/lock.svg') ?>
                            </div>
                            <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-default w-100">Ingresar</button>
                </form>
            </div>
        </div>
    </div>
</div>