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
        echo '<div class="alert alert-danger">No se encontró la plantilla</div>';
        exit;
    }
}
?>

<div class="row g-4">
    <div class="col-lg-5">
        <form id="formPlantilla">
            <input type="hidden" name="idplantilla" value="<?= $plantilla['idplantilla'] ?>">
            <div class="d-flex flex-column h-100">
                <div class="mb-3">
                    <label for="templateName" class="form-label">Nombre de la Plantilla</label>
                    <input type="text" class="form-control" id="templateName" name="nombre" value="<?= $plantilla['nombre'] ?? '' ?>" required placeholder="Ej: Landing Page de Producto">
                </div>

                <div>
                    <label for="templateDescription" class="form-label">Descripción</label>
                    <textarea class="form-control" id="templateDescription" name="descripcion" rows="3" required placeholder="Detalle el propósito y contenido de la plantilla."><?= $plantilla['descripcion'] ?? '' ?></textarea>
                </div>

                <div class="w-100 my-3" style="border-bottom: 1px solid var(--default-border-color)"></div>

                <div class="d-flex flex-column">
                    <div class="mb-3">
                        <label for="asuntoInput" class="form-label">Asunto</label>
                        <input type="text" class="form-control" id="asuntoInput" name="asunto" value="<?= $plantilla['asunto'] ?? '' ?>" required placeholder="Ej: Correo de bienvenida">
                    </div>
                    <div class="mb-3">
                        <label for="contenidoInput" class="form-label">Cuerpo del correo</label>
                        <textarea class="form-control" id="contenidoInput" name="contenido_texto" value="<?= $plantilla['contenido_texto'] ?? '' ?>" rows="5" required placeholder="Ingrese el contenido a enviar en el correo."></textarea>
                    </div>
                    <div>
                        <label class="form-label" for="fileInput">Contenido HTML</label>
                        <div class="file-upload-wrapper" id="fileUploadWrapper">
                            <input type="file" class="file-upload-input" id="fileInput" name="contenido_html" value="<?= htmlspecialchars($plantilla['contenido_html'] ?? '', ENT_QUOTES, 'UTF-8') ?>" accept=".html">
                            <i class="bi bi-cloud-arrow-up upload-icon"></i>
                            <div class="upload-text">Arrastra y suelta tu archivo aquí</div>
                            <div class="upload-subtext">o haz clic para seleccionar</div>
                        </div>
                        <div class="progress-container" id="progressContainer">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                    role="progressbar" style="width: 0%" id="progressBar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-7">
        <div class="px-2 h-100 d-flex flex-column">
            <h6 class="text-center mb-2">Previsualización del Archivo HTML</h6>
            <div class="h-100 container-border bg-light disable-hover" id="previewMessage">
                Selecciona un archivo HTML para ver su contenido aquí.
            </div>
            <iframe id="previewIframe" class="preview-iframe h-100 d-none" sandbox="allow-scripts allow-same-origin"></iframe>
        </div>
    </div>
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

<script>
    (() => {
        const htmlFileInput = document.getElementById('fileInput');
        const previewIframe = document.getElementById('previewIframe');
        const previewMessage = document.getElementById('previewMessage');
        const templateForm = document.getElementById('templateForm');

        const fileUploadWrapper = document.getElementById('fileUploadWrapper');
        const removeBtn = document.getElementById('removeBtn');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');

        // Eventos de drag and drop
        fileUploadWrapper.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadWrapper.classList.add('dragover');
        });

        fileUploadWrapper.addEventListener('dragleave', () => {
            fileUploadWrapper.classList.remove('dragover');
        });

        fileUploadWrapper.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadWrapper.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });

        // Evento de selección de archivo
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });

        function handleFile(file) {
            progressContainer.classList.add('active');
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                progressBar.style.width = progress + '%';
                if (progress >= 100) {
                    clearInterval(interval);
                    setTimeout(() => {
                        progressContainer.classList.remove('active');
                        loadFilePreview(file);
                    }, 1000);
                }
            }, 200);
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // 1. Función para cargar y mostrar el archivo en el iframe
        const loadFilePreview = (file) => {

            if (file && file.type === 'text/html') {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const htmlContent = e.target.result;

                    // Muestra el iframe y oculta el mensaje
                    previewIframe.classList.remove('d-none');
                    previewMessage.classList.add('d-none');

                    // Carga el contenido en el iframe
                    const doc = previewIframe.contentWindow.document;
                    doc.open();
                    doc.write(htmlContent);
                    doc.close();
                };

                reader.onerror = function() {
                    previewIframe.classList.add('d-none');
                    previewMessage.classList.remove('d-none');
                    previewMessage.textContent = 'Error al leer el archivo.';
                    console.error('Error al leer el archivo:', file.name);
                };

                reader.readAsText(file);
            } else {
                // Limpia la previsualización si no es un archivo HTML válido o si se cancela
                previewIframe.classList.add('d-none');
                previewMessage.classList.remove('d-none');
                previewMessage.textContent = 'Selecciona un archivo HTML para ver su contenido aquí.';
                if (previewIframe.contentWindow) {
                    previewIframe.contentWindow.document.write(''); // Limpiar iframe
                }
                if (file) {
                    // Muestra un mensaje de advertencia si se selecciona un tipo incorrecto
                    previewMessage.classList.remove('alert-info');
                    previewMessage.classList.add('alert-warning');
                    previewMessage.textContent = 'Tipo de archivo no válido. Por favor, selecciona un archivo .html.';
                }
            }
        };
    })();
</script>