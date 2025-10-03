<div class="row g-4">
    <div class="col-lg-5">
        <form id="formPlantilla">
            <div class="pb-4">
                <div class="mb-3">
                    <label for="templateName" class="form-label">Nombre de la Plantilla</label>
                    <input type="text" class="form-control" id="templateName" required placeholder="Ej: Landing Page de Producto">
                </div>

                <div class="mb-3">
                    <label for="templateDescription" class="form-label">Descripción</label>
                    <textarea class="form-control" id="templateDescription" rows="3" required placeholder="Detalle el propósito y contenido de la plantilla."></textarea>
                </div>
            </div>

            <div class="mb-3">
                <div class="file-upload-wrapper" id="fileUploadWrapper">
                    <input type="file" class="file-upload-input" id="fileInput" accept=".html">
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
        </form>
    </div>

    <div class="col-lg-7">
        <div class="px-2 h-100">
            <h6 class="card-title text-center mb-3">Previsualización del Archivo HTML</h6>
            <div class="h-100 container-border bg-light disable-hover" id="previewMessage">
                Selecciona un archivo HTML para ver su contenido aquí.
            </div>
            <iframe id="previewIframe" class="preview-iframe d-none" sandbox="allow-scripts allow-same-origin"></iframe>
        </div>
    </div>
</div>

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