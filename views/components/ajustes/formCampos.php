<?php
require_once __DIR__ . '/../../../models/ajustes/AjustesModel.php';
$ajustesModel = new AjustesModel();

$id = $_GET['id'] ?? null;
$mensaje = "";
$campo = null;

if ($id) {
    $campo = $ajustesModel->obtenerCampo($id);

    if (!$campo) {
        $mensaje = "No se encontró el campo";
    }
}
?>

<?php if ($mensaje): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
    <?php exit; ?>
<?php endif; ?>

<style>
    .chip-valor {
        display: inline-flex;
        align-items: center;
        background-color: #e9ecef;
        border-radius: 15px;
        padding: 5px 10px;
        font-size: 14px;
        margin: 2px;
    }

    .chip-valor svg {
        width: 16px;
        height: 16px;
        cursor: pointer;
        margin-left: 8px;
    }

    #opcionesContainer:empty:before {
        content: attr(placeholder);
        color: #6c757d;
        pointer-events: none;
    }
</style>

<form method="POST" id="formCampo">
    <input type="hidden" name="idcampo" value="<?= $campo['idcampo'] ?? '' ?>">

    <div class="row">
        <div class="col-6 mb-3">
            <label for="nombreInput" class="form-label">Nombre del campo</label>
            <input type="text" class="form-control" id="nombreInput" name="nombre"
                value="<?= $campo['nombre'] ?? '' ?>" required>
        </div>

        <div class="col-6 mb-3">
            <label for="tipoDatoInput" class="form-label">Tipo de dato</label>
            <select class="form-select" id="tipoDatoInput" name="tipo_dato" required>
                <option value="texto" <?= isset($campo['tipo_dato']) && $campo['tipo_dato'] === 'texto' ? 'selected' : '' ?>>Texto</option>
                <option value="numero" <?= isset($campo['tipo_dato']) && $campo['tipo_dato'] === 'numero' ? 'selected' : '' ?>>Número</option>
                <option value="fecha" <?= isset($campo['tipo_dato']) && $campo['tipo_dato'] === 'fecha' ? 'selected' : '' ?>>Fecha</option>
                <option value="booleano" <?= isset($campo['tipo_dato']) && $campo['tipo_dato'] === 'booleano' ? 'selected' : '' ?>>Booleano</option>
                <option value="opciones" <?= isset($campo['tipo_dato']) && $campo['tipo_dato'] === 'opciones' ? 'selected' : '' ?>>Opciones</option>
            </select>
        </div>

        <div class="col-6 mb-3">
            <label for="longitudInput" class="form-label">Longitud</label>
            <input type="number" class="form-control" id="longitudInput" name="longitud"
                value="<?= $campo['longitud'] ?? '' ?>" required>
        </div>

        <div class="col-6 mb-3">
            <label for="requeridoInput" class="form-label">Requerido</label>
            <select class="form-select" id="requeridoInput" name="requerido" required>
                <option value="texto" <?= isset($campo['requerido']) && $campo['requerido'] === 1 ? 'selected' : '' ?>>Sí</option>
                <option value="numero" <?= isset($campo['requerido']) && $campo['requerido'] === 0 ? 'selected' : '' ?>>No</option>
            </select>
        </div>

        <div class="col-12 mb-3" id="valorInicialContainer">
            <label for="valorInicialInput" class="form-label">Valor inicial</label>
            <div id="opcionesWrapper" class="form-control d-flex flex-wrap gap-2" style="min-height: 40px; align-items: center;">
                <input type="text" id="chipInput" class="border-0 flex-grow-1" placeholder="Escribe y presiona Enter..." style="outline: none;">
            </div>
            <input type="hidden" id="valorInicialInput" name="valor_inicial" value="<?= htmlspecialchars($campo['valor_inicial'] ?? '') ?>">
        </div>

        <div class="col-6 mb-3">
            <label for="tipoReferenciaInput" class="form-label">Asignado a</label>
            <select class="form-select" id="tipoReferenciaInput" name="tipo_referencia" required>
                <option value="cliente" <?= isset($campo['tipo_referencia']) && $campo['tipo_referencia'] === 'cliente' ? 'selected' : '' ?>>Clientes</option>
                <option value="empresa" <?= isset($campo['tipo_referencia']) && $campo['tipo_referencia'] === 'empresa' ? 'selected' : '' ?>>Empresas</option>
                <option value="actividad" <?= isset($campo['tipo_referencia']) && $campo['tipo_referencia'] === 'actividad' ? 'selected' : '' ?>>Actividades</option>
                <option value="proyecto" <?= isset($campo['tipo_referencia']) && $campo['tipo_referencia'] === 'proyecto' ? 'selected' : '' ?>>Proyectos</option>
                <option value="tarea" <?= isset($campo['tipo_referencia']) && $campo['tipo_referencia'] === 'tarea' ? 'selected' : '' ?>>Tareas</option>
            </select>
        </div>

        <div class="col-6 mb-3">
            <label for="idReferenciaInput" class="form-label">Referencia</label>
            <div class="busqueda-grupo">
                <input type="text" class="form-control" id="referenciaInput"
                    value="<?= $campo['referencia'] ?? '' ?>" data-type="cliente"
                    placeholder="Buscar actividad, cliente o empresa..." autocomplete="off">
                <input type="hidden" name="idreferencia" id="idReferenciaInput"
                    value="<?= $campo['idreferencia'] ?? '' ?>">
                <div class="resultados-busqueda disable-auto" data-parent="referenciaInput"
                    style="top: 2.5rem; min-width: 300px;"></div>
            </div>
        </div>
    </div>
</form>

<script>
    (() => {
        const tipoDatoInput = document.getElementById("tipoDatoInput");
        const valorContainer = document.getElementById("valorInicialContainer");
        const hiddenInput = document.getElementById("valorInicialInput");

        function renderValorInicial(tipo, valor) {
            if (tipo === "opciones") {
                valorContainer.innerHTML = `
                <label class="form-label">Valor inicial</label>
                <div id="opcionesWrapper" class="form-control d-flex flex-wrap gap-2" style="min-height: 40px; align-items: center;">
                    <input type="text" id="chipInput" class="border-0 flex-grow-1" placeholder="Escribe y presiona Enter..." style="outline: none;">
                </div>
                <input type="hidden" id="valorInicialInput" name="valor_inicial">
            `;

                const wrapper = document.getElementById("opcionesWrapper");
                const chipInput = document.getElementById("chipInput");
                const hidden = document.getElementById("valorInicialInput");

                // Inicializar valores si existen
                if (valor) {
                    if (Array.isArray(valor)) {
                        // Ya es array, úsalo directamente
                        valor.forEach(op => addChip(wrapper, op, hidden));
                    } else {
                        try {
                            const opciones = JSON.parse(valor);
                            if (Array.isArray(opciones)) {
                                opciones.forEach(op => addChip(wrapper, op, hidden));
                            }
                        } catch (e) {
                            valor.split(",").forEach(op => addChip(wrapper, op.trim(), hidden));
                        }
                    }
                }

                // Evento Enter
                chipInput.addEventListener("keydown", function(e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        const texto = chipInput.value.trim();
                        if (texto !== "") {
                            addChip(wrapper, texto, hidden);
                            chipInput.value = "";
                        }
                    }
                });

            } else {
                let html = "";
                switch (tipo) {
                    case "texto":
                        html = `<input type="text" class="form-control" id="valorInicialInput" name="valor_inicial" value="${valor || ""}">`;
                        break;
                    case "numero":
                        html = `<input type="number" class="form-control" id="valorInicialInput" name="valor_inicial" value="${valor || ""}">`;
                        break;
                    case "fecha":
                        html = `<input type="date" class="form-control" id="valorInicialInput" name="valor_inicial" value="${valor || ""}">`;
                        break;
                    case "booleano":
                        html = `<select class="form-select" id="valorInicialInput" name="valor_inicial">
                                <option value="1" ${valor === "1" ? "selected" : ""}>Sí</option>
                                <option value="0" ${valor === "0" ? "selected" : ""}>No</option>
                            </select>`;
                        break;
                }
                valorContainer.innerHTML = `
                <label for="valorInicialInput" class="form-label">Valor inicial</label>
                ${html}
            `;
            }
        }

        function addChip(container, texto, hidden) {
            const chip = document.createElement("span");
            chip.classList.add("chip-valor");

            // Crea el texto y el botón de eliminar por separado
            const label = document.createElement("span");
            label.textContent = texto;

            const svgString = `<?php include('../../../assets/svg/x.svg') ?>`;
            const parser = new DOMParser();
            const doc = parser.parseFromString(svgString, "image/svg+xml");
            const remove = doc.documentElement;

            remove.addEventListener("click", () => {
                chip.remove();
                updateHidden(container, hidden);
            });

            chip.appendChild(label);
            chip.appendChild(remove);

            container.insertBefore(chip, container.querySelector("#chipInput"));
            updateHidden(container, hidden);
        }

        function updateHidden(container, hidden) {
            // Solo tomamos el texto de los labels, no del chip completo
            const chips = [...container.querySelectorAll(".chip-valor span:first-child")].map(el => el.textContent.trim());
            hidden.value = JSON.stringify(chips);
        }

        // Render inicial con valor cargado
        renderValorInicial(
            tipoDatoInput.value,
            <?= json_encode(json_decode($campo['valor_inicial'] ?? '[]', true)) ?>
        );

        // Cambio dinámico
        tipoDatoInput.addEventListener("change", function() {
            renderValorInicial(this.value, "");
        });
    })();
</script>