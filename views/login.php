<?php
require_once __DIR__ . '/../models/Usuarios/UsuarioModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya hay sesión completa, redirigir a home
if (isset($_SESSION['usuario']) && isset($_SESSION['rol'])) {
    header("Location: index.php?p=home");
    exit;
}

$error = isset($_GET['error']) ? boolval($_GET['error']) : false;
$logout = isset($_GET['logout']) ? boolval($_GET['logout']) : false;

if ($logout) {
    session_destroy();
    header("Location: index.php?p=login");
}
?>

<link rel="stylesheet" href="./assets/css/general.css">
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-lg" style="width: 350px;">
        <h2 class="text-center">Iniciar sesión</h2>

        <?php if ($error === true): ?>
            <div class="alert alert-danger">Usuario o contraseña incorrectos</div>
        <?php endif; ?>

        <form method="POST" action="http://localhost/SistemaCRM/controller/Usuarios/procesarLogin.php">
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>