import api from "../utils/api.js";
import { abrirModal, modalAjustes } from "./index.js";

let currentStep = 1;
let totalSteps = 3;

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
                const hoy = new Date();
                const inicio = new Date(fecha_inicio);
                const fin = new Date(fecha_fin);

                if (hoy < inicio) {
                    return { bg: "info", estado: "PRÓXIMA" };
                } else if (hoy >= inicio && hoy <= fin) {
                    return { bg: "success", estado: "ACTIVA" };
                } else {
                    return { bg: "danger", estado: "FINALIZADA" };
                }
            }
            campanias.forEach(campania => {
                const estado = estadoCampania(campania.fecha_incio, campania.fecha_fin);
                html += `
                    <tr>
                        <td>${campania.nombre}</td>
                        <td>${campania.fecha_incio}</td>
                        <td>${campania.fecha_fin || 'N/A'}</td>
                        <td><div class="badge bg-${estado.bg}">${estado.estado}</div></td>
                    </tr>
                `;
            });
            campaniasContainer.innerHTML = html;
        }
    });
}

export function fetchPlantillas(containerId) {
    api.get({
        source: "plantillas",
        action: "listar",
        onSuccess: (plantillas) => {
            const plantillasContainer = document.getElementById(containerId);

            if (plantillas.length === 0) {
                plantillasContainer.innerHTML = "<p class='text-muted'>No hay plantillas disponibles.</p>";
                return;
            }
            let html = "";

            plantillas.forEach(plantilla => {
                html += `
                    <div class="container-border d-flex gap-2">
                        <div class="flex-grow-1">
                            <input type="checkbock" class="form-check" value="${plantilla.idplantilla}">
                            <div class="d-flex flex-column gap-1">
                                <h5>${plantilla.nombre}</h5>
                                <span>${plantilla.descripcion}</span>
                            </div>
                        </div>
                        <button class="btn btn-icon bg-light"><i class="bi bi-eye"></i></button>
                    </div>
                `;
            });
            plantillasContainer.innerHTML = html;
        }
    });
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
            modalAjustes.hide();
            fetchCampanias();
        }
    });
}

export function updateStep() {
    const btnRegresar = modalAjustes.getComponent("#btnRegresar");
    const btnSiguiente = modalAjustes.getComponent("#btnSiguiente");
    const form = modalAjustes.getComponent(".campania-form");

    if (currentStep > 1) {
        btnRegresar.disabled = false;
        form.classList.remove("first-step");
    } else {
        btnRegresar.disabled = true;
        form.classList.add("first-step");
    }

    if (currentStep < totalSteps) {
        btnSiguiente.disabled = false;
    } else {
        btnSiguiente.disabled = true;
    }

    form.querySelectorAll(".section-item").forEach(section => section.classList.remove("show"));
    form.querySelectorAll(".list-item").forEach(el => el.classList.remove("selected"));
    form.querySelector(`.section-item[data-step="${currentStep}"]`).classList.add("show");
    form.querySelector(`.list-item[data-step="${currentStep}"]`).classList.add("selected");
}

document.addEventListener("click", function (e) {
    if (e.target.closest("#btnNuevaCampania")) {
        abrirModal("campania", "Nueva campaña", "lg", null, { ocultarFooter: true });
    }

    if (e.target.closest("#btnEditarCampania")) {
        abrirModal("campania", "Editar campaña", "lg", e.target.closest("button").dataset.id, { ocultarFooter: true });
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

    if (e.target.closest("#btnNuevaPlantillaCorreo")) {
        abrirModal("plantilla", "Nueva plantilla", "xl");
    }

    if (e.target.closest("#btnEditarPlantillaCorreo")) {
        abrirModal("plantilla", "Editar plantilla", "xl");
    }

    if (e.target.closest(".btn-navegacion")) {
        const btn = e.target.closest(".btn-navegacion");
        if (btn.id === "btnRegresar" && currentStep > 1) currentStep--;
        if (btn.id === "btnSiguiente" && currentStep < totalSteps) currentStep++;
        updateStep();
    }
});