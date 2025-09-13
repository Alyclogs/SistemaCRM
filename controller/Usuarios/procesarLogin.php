<?php
require_once __DIR__ . '/../../models/usuarios/UsuarioModel.php';
session_start();
$base_url = 'http://localhost/SistemaCRM/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    $modelo = new UsuarioModel();
    $datosUsuario = $modelo->verificarUsuario($usuario, $password);

    if ($datosUsuario) {
        // Login correcto, guardamos en sesión
        $_SESSION['idusuario']   = $datosUsuario['idusuario'];
        $_SESSION['usuario']     = $datosUsuario['usuario'];
        $_SESSION['nombre']      = $datosUsuario['nombres'] . ' ' . $datosUsuario['apellidos'];
        $_SESSION['rol']         = $datosUsuario['nombre_rol'];
        header("Location: " . $base_url . "index.php?p=home");
        exit;
    } else {
        header("Location: " . $base_url . "index.php?p=login&error=true");
        exit;
    }
} else {
    // Si acceden directamente sin POST
    header("Location: " . $base_url . "index.php?p=login");
    exit;
}
