<?php
require_once __DIR__ . "/../../../models/ajustes/EnvioModel.php";

$pdo = Database::getConnection();
$emisorModel = new EnvioModel($pdo);

$id = $_GET['id'] ?? null;
$emisor = null;
$mensaje = "";

if ($id) {
    $emisor = $emisorModel->obtenerEmisor($id);

    if (!$emisor) {
        echo '<div class="alert alert-danger">No se encontró el emisor</div>';
        exit;
    }
}
?>

<form id="formEmisor">
    <input type="hidden" name="idemisor" value="<?= $emisor['idemisor'] ?? '' ?>">
    <input type="hidden" name="tipo" value="<?= "correo" ?>">
    <div class="mb-3">
        <label class="form-label" for="nombreInput">Nombre del emisor</label>
        <input type="text" class="form-control" id="nombreInput" name="nombre" value="<?= $emisor['nombre'] ?? '' ?>" required placeholder="Ej. Marketing">
    </div>
    <div class="mb-3">
        <label class="form-label" for="correoInput">Correo del emisor</label>
        <input type="email" class="form-control" id="correoInput" name="correo" value="<?= $emisor['correo'] ?? '' ?>" placeholder="correo@ejemplo.com">
    </div>
    <div class="mb-3">
        <label class="form-label" for="descripcionInput">Descripción del emisor</label>
        <textarea class="form-control" id="descripcionInput" name="descripcion" rows="2" placeholder="Ingrese una descripción para el emisor (opcional)"><?= $emisor['descripcion'] ?? '' ?></textarea>
    </div>
</form>