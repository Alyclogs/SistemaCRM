<?php
require_once __DIR__ . '/../../models/clientes/ClienteModel.php';
require_once __DIR__ . '/../../models/proyectos/ProyectoModel.php';
require_once __DIR__ . '/../../models/tareas/TareaModel.php';
require_once __DIR__ . '/../../models/colaboradores/ColaboradorModel.php';

$clienteModel = new ClienteModel();
$proyectoModel = new ProyectoModel();
$colaboradorModel = new ColaboradorModel();
$tareaModel = new TareaModel();

$source = $_GET['source'] ?? null;
$selectionType = $_GET['type'] ?? 'single';
$items = [];
$initSelected = [];
$message = "";

$id = $_GET['id'] ?? null;

if ($source) {
    $source = strtolower($source);

    switch ($source) {
        case 'clientes':
            $items = $clienteModel->obtenerClientes();
            $items = array_map(function ($c) {
                return [
                    "id" => $c['idcliente'],
                    "nombre" => $c['nombre']
                ];
            }, $items);
            break;

        case 'colaboradores':
            $items = $colaboradorModel->obtenerColaboradores();
            $items = array_map(function ($c) {
                return [
                    "id" => $c['idcolaborador'],
                    "nombre" => trim($c['nombres'] . ' ' . $c['apellidos'])
                ];
            }, $items);
            break;

        case 'proyectos':
            if ($id) {
                $initSelected = $proyectoModel->obtenerProyectosPorCliente($id);
                $initSelected = array_map(function ($p) {
                    return [
                        "id" => $p['idproyecto'],
                        "nombre" => $p['nombre']
                    ];
                }, $initSelected);
            }
            $items = $proyectoModel->obtenerProyectos();
            $items = array_map(function ($p) {
                return [
                    "id" => $p['idproyecto'],
                    "nombre" => $p['nombre']
                ];
            }, $items);
            break;

        case 'tareas':
            if ($id) {
                $items = $tareaModel->obtenerTareasPorProyecto($id);
                $items = array_map(function ($t) {
                    return [
                        "id" => $t['idtarea'],
                        "nombre" => $t['nombre']
                    ];
                }, $items);
            } else {
                $message = "No se ha seleccionado un proyecto";
                $items = [];
            }
            break;
    }

    if (count($items) === 0) {
        $message = "No se han encontrado " . $source;
    }
} else {
    $message = "No se ha especificado el origen";
}
?>


<div class="modal fade" id="selectorModal"
    data-source="<?= $source ?>"
    data-type="<?= $selectionType ?>"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    tabindex="-1"
    aria-labelledby="selectorModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 34vh;">
        <input type="hidden" id="selectedId" name="selectedId" value="<?= $id ?? '' ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-large" id="selectorModalLabel">Seleccione</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="selectorModalBody">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-danger"><?= $message ?></div>
                <?php else: ?>
                    <div class="d-flex flex-column selector-items" id="selectorItems">
                        <?php foreach ($items as $item): ?>
                            <div class="selector-item" data-id="<?= $item['id'] ?>">
                                <?= $item['nombre'] ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn-default" id="btnSeleccionar">Seleccionar</button>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const selectionType = "<?= $selectionType ?>";
        const selectorItems = document.getElementById("selectorItems");
        const initSelected = <?= json_encode(array_column($initSelected, 'id')) ?>;
        console.log(initSelected);

        if (selectorItems && initSelected.length > 0) {
            initSelected.forEach(id => {
                const item = selectorItems.querySelector(`.selector-item[data-id="${id}"]`);
                if (item) item.classList.add("selected");
            });
        }

        if (selectorItems) {
            selectorItems.addEventListener("click", function(e) {
                const item = e.target.closest(".selector-item");
                if (!item) return;

                if (selectionType === "single") {
                    document.querySelectorAll("#selectorItems .selector-item.selected")
                        .forEach(el => el.classList.remove("selected"));
                    item.classList.add("selected");
                } else if (selectionType === "multiple") {
                    item.classList.toggle("selected");
                }
            });
        }
    })();
</script>