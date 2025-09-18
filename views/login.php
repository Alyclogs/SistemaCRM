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

<link rel="stylesheet" href="./assets/css/general.css">
<div class="d-flex vh-100 d-flex align-items-center justify-content-center">
    <div class="p-4 container-shadow">
        <div class="p-4 d-flex flex-column">
            <h2 class="text-center text-large my-4">Iniciar sesión</h2>

            <?php if ($error === true): ?>
                <div class="alert alert-danger">Usuario o contraseña incorrectos</div>
            <?php endif; ?>

            <div class="mb-4">
                <form method="POST" action="http://localhost/SistemaCRM/controller/Usuarios/procesarLogin.php">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <div class="grupo-input login">
                            <div class="px-1">
                                <?php include('./assets/svg/user.svg') ?>
                            </div>
                            <input type="text" class="form-control underline pb-0" name="usuario" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="grupo-input login mb-4">
                            <div class="px-1">
                                <?php include('./assets/svg/lock.svg') ?>
                            </div>
                            <input type="password" class="form-control underline pb-0" name="password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </form>
            </div>
        </div>
    </div>
</div>