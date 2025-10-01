import api from "../utils/api.js";
import { fechaEnRango } from "../utils/date.js";
import { abrirModal } from "./index.js";

export function fetchCampanias() {
    api.get({
        source: "campanias",
        action: "listar",
        onSuccess: (campanias) => {
            const campaniasContainer = document.getElementById("campaniasList");
            campaniasContainer.innerHTML = "";
            if (campanias.length === 0) {
                campaniasContainer.innerHTML = "<p class='text-muted'>No hay campañas disponibles.</p>";
                return;
            }
            let html = "";
            const estadoCampania = (fecha_inicio, fecha_fin) => {
                if (fechaEnRango(fecha_inicio, fecha_fin)) return "success"
                else return "secondary"
            }

            campanias.forEach(campania => {
                html += `
                    <tr>
                        <td>${campania.nombre}</td>
                        <td>${campania.fecha_incio}</td>
                        <td>${campania.fecha_fin || 'N/A'}</td>
                        <td><div class="badge bg-${estadoCampania(campania.fecha_inicio, campania.fecha_fin)}">${campania.estado}</div></td>
                    </tr>
                `;
            });
            campaniasContainer.innerHTML = html;
        }
    });
}

export function fetchPlantillas() {

}

export function guardarCampania() {
    const form = document.getElementById("formCampania");
    const formData = new FormData(form);
    const campania = formData.get('nombre').replace(' ', '_').toLowerCase();
    formData.append("campania", campania);

    const action = formData.get("idcampania") ? "actualizar_campania" : "crear_campania";
    api.post({
        source: "ajustes",
        action: action,
        data: formData,
        onSuccess: () => {
            $("#ajustesModal").modal("hide");
            fetchCampanias();
        }
    });
}

document.addEventListener("click", function (e) {
    if (e.target.closest("#btnNuevaCampania")) {
        abrirModal("campania", "Nueva campaña", "lg", null, { ocultarFooter: true });
    }

    if (e.target.closest("#btnEditarCampania")) {
        abrirModal("campania", "Editar campaña", "lg", e.target.closest("button").dataset.id);
    }

    if (e.target.closest("#btnEliminarCampania")) {
        const id = e.target.closest("button").dataset.id;

        if (confirm("¿Está seguro de que desea eliminar esta campaña? Esta acción no se puede deshacer.")) {
            const formData = new FormData();
            formData.append("idcampania", id);

            api.post({
                source: "campanias",
                action: "eliminar",
                data: formData,
                onSuccess: () => {
                    fetchCampanias();
                }
            });
        }
    }
});