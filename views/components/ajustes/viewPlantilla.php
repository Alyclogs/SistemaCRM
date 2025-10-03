<?php
require_once __DIR__ . "/../../../models/ajustes/PlantillaModel.php";

$pdo = Database::getConnection();
$plantillaModel = new PlantillaModel($pdo);

$id = $_GET['id'] ?? null;
$plantilla = null;
$mensaje = "";

if ($id) {
    $plantilla = $plantillaModel->obtenerPlantilla($id);

    if (!$plantilla) {
        $mensaje = "No se encontró la plantilla";
    }
}
?>

<div class="p-4 pt-0 h-100 d-flex flex-column">
    <div class="mb-3">
        <div class="info-row mb-3 gap-2">
            <img src="./assets/img/usuariodefault.png" class="user-icon sm" alt="Foto de simulación emisor">
            <div class="d-flex flex-column">
                <span class="fw-bold">Emisor de ejemplo</span>
                <span class="text-muted">correo@ejemplo.com</span>
            </div>
        </div>
        <div class="d-flex flex-column">
            <h5><?= $plantilla['asunto'] ?? "Sin asunto" ?></h5>
            <?= !empty($plantilla['contenido_texto']) ? "<span>" . $plantilla['contenido_texto'] . "</span>" : '' ?>
        </div>
    </div>
    <div class="h-100 container-border bg-light disable-hover" id="previewMessage">
        Aquí se visualizará la plantilla HTML.
    </div>
    <iframe id="previewIframe" class="preview-iframe h-100 d-none" sandbox="allow-scripts allow-same-origin"></iframe>
</div>

<?php if (!empty($plantilla['contenido_html'])): ?>
    <script>
        (() => {
            const previewIframe = document.getElementById('previewIframe');
            const previewMessage = document.getElementById('previewMessage');

            previewIframe.classList.remove('d-none');
            previewMessage.classList.add('d-none');

            const doc = previewIframe.contentWindow.document;
            doc.open();
            doc.write(`<?= str_replace("`", "\`", $plantilla['contenido_html']) ?>`);
            doc.close();
        })();
    </script>
<?php endif; ?>