import api from "../utils/api.js";
import { abrirModal, modalAjustes } from "./index.js";

export function fetchCamposExtra() {
    api.get({
        source: "campos",
        action: "listar_campos_extra",
        onSuccess: (campos) => {
            const camposContainer = document.getElementById("camposList");
            camposContainer.innerHTML = "";
            if (campos.length === 0) {
                camposContainer.innerHTML = "<p class='text-muted'>No hay campos personalizados disponibles.</p>";
                return;
            }
            let html = "";
            campos.forEach(campo => {
                html += `
                    <tr>
                        <td>${campo.nombre}</td>
                        <td>${campo.tipo_dato}</td>
                        <td>${campo.longitud || 'N/A'}</td>
                        <td>${campo.requerido ? 'Sí' : 'No'}</td>
                        <td>${campo.tabla}</td>
                        <td>${campo.valor_inicial ? (Array.isArray(campo.valor_inicial) ? campo.valor_inicial.join(", ") : campo.valor_inicial) : 'N/A'}</td>
                        <td>
                            <div class="info-row">
                                <button class="btn btn-icon bg-light" data-id="${campo.idcampo}" id="btnEditarCampo">
                                    ${window.icons.edit}
                                </button>
                                <button class="btn btn-icon bg-light" data-id="${campo.idcampo}" id="btnEliminarCampo">
                                    ${window.icons.trash}
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            camposContainer.innerHTML = html;
        }
    });
}

export function guardarCampo() {
    const form = document.getElementById("formCampo");
    const formData = new FormData(form);
    const campo = formData.get('nombre').replace(' ', '_').toLowerCase();
    formData.append("campo", campo);

    const action = formData.get("idcampo") ? "actualizar_campo" : "crear_campo";
    api.post({
        source: "campos",
        action: action,
        data: formData,
        onSuccess: () => {
            modalAjustes.hide();
            fetchCamposExtra();
        }
    });
}

document.addEventListener("click", function (e) {
    if (e.target.closest("#btnNuevoCampo")) {
        abrirModal("campo", "Nuevo campo personalizado", null);
    }

    if (e.target.closest("#btnEditarCampo")) {
        abrirModal("campo", "Editar campo personalizado", e.target.closest("button").dataset.id);
    }

    if (e.target.closest("#btnEliminarCampo")) {
        const id = e.target.closest("button").dataset.id;

        if (confirm("¿Está seguro de que desea eliminar este campo personalizado? Esta acción no se puede deshacer.")) {
            const formData = new FormData();
            formData.append("idcampo", id);

            api.post({
                source: "campos",
                action: "eliminar_campo",
                data: formData,
                onSuccess: () => {
                    fetchCamposExtra();
                }
            });
        }
    }
});

document.addEventListener("change", function (e) {
    if (e.target.id === 'tablaInput') {
        const value = e.target.value;
        const inputReferencia = document.getElementById('referenciaInput');

        if (value === "clientes") {
            inputReferencia.dataset.type = "clientes";
        }
        if (value === "empresas") {
            inputReferencia.dataset.type = "empresas";
        }
        if (value === "actividades") {
            inputReferencia.dataset.type = "actividades";
        }
    }
});