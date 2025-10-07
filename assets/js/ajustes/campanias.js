import api from "../utils/api.js";
import { formatearDateTimeFull } from "../utils/date.js";
import { ModalComponent } from "../utils/modal.js";
import { abrirModal, modalAjustes } from "./index.js";
import { cargarCampaniaExistente } from "./programaciones.js";

let currentStep = 1;
let totalSteps = 3;
export const plantillasCache = new Map();

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

            const estadoCampania = (fecha_inicio, fecha_fin) => {
                const hoy = new Date();
                const inicio = new Date(fecha_inicio);
                const fin = new Date(fecha_fin);
                if (hoy < inicio) return { bg: "info", estado: "PRÓXIMA" };
                if (hoy >= inicio && hoy <= fin) return { bg: "success", estado: "ACTIVA" };
                return { bg: "danger", estado: "FINALIZADA" };
            };

            let html = "";
            campanias.forEach(campania => {
                const estado = estadoCampania(campania.fecha_inicio, campania.fecha_fin);
                const id = `campania-${campania.idcampania}`;

                html += `
                <tbody class="campania-row-group">
                    <tr class="campania-row" data-id="${campania.idcampania}" data-target="#${id}">
                        <td class="fw-bold">${campania.nombre}</td>
                        <td class="text-break" style="max-width: 220px;">${campania.descripcion || "Sin descripción"}</td>
                        <td>${campania.fecha_inicio}</td>
                        <td>${campania.fecha_fin || "N/A"}</td>
                        <td><div class="badge text-bg-${estado.bg}">${estado.estado}</div></td>
                        <td>
                            <div class="info-row">
                                <button class="btn btn-icon bg-light" data-id="${campania.idcampania}" id="btnEditarCampania">
                                    ${window.icons.edit}
                                </button>
                                <button class="btn btn-icon bg-light" data-id="${campania.idcampania}" id="btnEliminarCampania">
                                    ${window.icons.trash}
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="programaciones-row">
                        <td colspan="6" style="padding:0;">
                            <div id="${id}" class="programaciones-collapse">
                                ${renderProgramacionesTable(campania.programaciones)}
                            </div>
                        </td>
                    </tr>
                </tbody>
                `;
            });
            campaniasContainer.innerHTML = html;

            document.querySelectorAll(".campania-row").forEach(row => {
                row.addEventListener("click", function (e) {
                    if (e.target.closest("button")) return;

                    const target = document.querySelector(this.dataset.target);
                    if (!target) return;

                    if (target.classList.contains("open")) {
                        target.style.maxHeight = target.scrollHeight + "px";
                        requestAnimationFrame(() => (target.style.maxHeight = "0"));
                        target.classList.remove("open");
                        this.classList.remove("expanded");
                    } else {
                        target.classList.add("open");
                        target.style.maxHeight = target.scrollHeight + "px";
                        this.classList.add("expanded");

                        target.addEventListener("transitionend", () => {
                            if (target.classList.contains("open")) {
                                target.style.maxHeight = "none";
                            }
                        }, { once: true });
                    }
                });
            });
        }
    });
}

function renderProgramacionesTable(programaciones = []) {
    if (!programaciones || programaciones.length === 0) {
        return `<div class="p-3 text-center text-muted">
                    No hay programaciones registradas.
                </div>`;
    }

    function getEstadoColor(estado) {
        if (!estado) return "secondary";
        const e = estado.toLowerCase();
        if (e.includes("programado")) return "warning";
        if (e.includes("enviado")) return "success";
        return "secondary";
    }

    let html = `
        <div class="p-3 bg-light-subtle">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Plantilla</th>
                        <th>Emisor</th>
                        <th>Fecha de envío</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
    `;

    programaciones.forEach((p, i) => {
        html += `
            <tr>
                <td>${i + 1}</td>
                <td>${p.plantilla_nombre || "—"}</td>
                <td>${p.emisor || "—"}</td>
                <td>${formatearDateTimeFull(p.fecha_envio)}</td>
                <td>
                    <div class="badge text-bg-${getEstadoColor(p.estado.toLowerCase())}">${p.estado || "PROGRAMADO"}</div>
                </td>
            </tr>
        `;
    });

    html += `</tbody></table></div>`;
    return html;
}

export function fetchEmisores(containerId, tipo) {
    api.get({
        source: "emisores",
        action: "listar",
        onSuccess: (emisores) => {
            const emisoresContainer = document.getElementById(containerId);
            if (tipo) emisores = emisores.filter(e => e.tipo === tipo);

            if (emisores.length === 0) {
                emisoresContainer.innerHTML = "<p class='text-muted'>No hay emisores disponibles.</p>";
                return;
            }

            let html = "";
            const estadosEmisor = {
                0: { bg: "danger", estado: "inactivo" },
                1: { bg: "success", estado: "activo" }
            }

            emisores.forEach(emisor => {
                const estado = estadosEmisor[emisor.activo];
                html += `
                    <tr>
                        <td>${emisor.nombre ?? 'Sin nombre'}</td>
                        <td class="text-break" style="max-width: 220px;">${emisor.descripcion ?? 'Sin descripción'}</td>
                        <td>${tipo === "correo" ? emisor.correo : emisor.telefono}</td>
                        <td><div class="badge text-bg-${estado.bg}">${estado.estado}</div></td>
                        <td>
                            <div class="info-row">
                                <button class="btn btn-icon bg-light" data-id="${emisor.idemisor}" id="btnEditarEmisorCorreo">
                                    ${window.icons.edit}
                                </button>
                                <button class="btn btn-icon bg-light" data-id="${emisor.idemisor}" id="btnEliminarEmisorCorreo">
                                    ${window.icons.trash}
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            emisoresContainer.innerHTML = html;
        }
    });
}

export function fetchPlantillas(containerId, { selectable = false, editable = true, onRender = null } = {}) {
    api.get({
        source: "plantillas",
        action: "listar",
        onSuccess: (plantillas) => {
            const plantillasContainer = document.getElementById(containerId);
            console.log(plantillasContainer);

            if (plantillas.length === 0) {
                plantillasContainer.innerHTML = "<p class='text-muted'>No hay plantillas disponibles.</p>";
                return;
            }
            let html = "";

            plantillas.forEach(plantilla => {
                plantillasCache.set(`${plantilla.idplantilla}`, plantilla);
                html += `
                    <div class="border rounded p-3 d-flex gap-3 align-items-center">
                        <div class="flex-grow-1 d-flex gap-3 align-items-center">
                            ${selectable ? `<input type="checkbox" class="plantilla-checkbox form-check form-check-input" value="${plantilla.idplantilla}">` : ''}
                            <div class="d-flex flex-column gap-1">
                                <h5>${plantilla.nombre}</h5>
                                <span>${plantilla.descripcion || 'Sin descripción'}</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <button type="button" class="btn btn-icon bg-light" id="btnPrevisualizarPlantilla" data-id="${plantilla.idplantilla}"><i class="bi bi-eye"></i></button>
                        ${editable
                        ? `<button type="button" class="btn btn-icon bg-light" id="btnEditarPlantillaCorreo" data-id="${plantilla.idplantilla}">${window.icons.edit}</button>
                            <button type="button" class="btn btn-icon bg-light" id="btnEliminarPlantillaCorreo" data-id="${plantilla.idplantilla}">${window.icons.trash}</button>`
                        : ''
                    }
                    </div>
                    </div>
                `;
            });
            plantillasContainer.innerHTML = html;
            if (typeof onRender === "function") onRender();
        }
    });
}

export function guardarPlantilla() {
    const form = document.getElementById("formPlantilla");
    const formData = new FormData(form);

    const action = formData.get("idplantilla") ? "actualizar" : "crear";
    api.post({
        source: "plantillas",
        action: action,
        data: formData,
        onSuccess: () => {
            modalAjustes.hide();
            fetchPlantillas("correosPlantillasList");
        }
    });
}

export function guardarEmisor() {
    const form = document.getElementById("formEmisor");
    const formData = new FormData(form);

    const action = formData.get("idemisor") ? "actualizar" : "crear";
    api.post({
        source: "emisores",
        action: action,
        data: formData,
        onSuccess: () => {
            modalAjustes.hide();
            fetchPlantillas("correoEmisoresList", "correo");
        }
    });
}

export function previsualizarPlantilla(idplantilla) {
    const modalPrevisualizar = new ModalComponent("previsualizador", { size: "lg", height: "760px", ocultarFooter: true });
    modalPrevisualizar.getComponent("title").style.display = "none";

    fetch(window.baseurl + "views/components/ajustes/viewPlantilla.php?id=" + idplantilla)
        .then(res => res.text())
        .then(html => modalPrevisualizar.show("Previsualizar plantilla", html));
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
        abrirModal("campania", "Nueva campaña", null, { size: "lg", ocultarFooter: true });
    }

    if (e.target.closest("#btnEditarCampania")) {
        abrirModal("campania", "Editar campaña", e.target.closest("button").dataset.id, { size: "lg", ocultarFooter: true });
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
        abrirModal("plantilla", "Nueva plantilla", null, { size: "xl", onRender: () => modalAjustes.getComponent("#plantillaTypeInput").value = "correo" });
    }

    if (e.target.closest("#btnEditarPlantillaCorreo")) {
        abrirModal("plantilla", "Editar plantilla", e.target.closest("button").dataset.id, { size: "xl", });
    }

    if (e.target.closest("#btnEliminarPlantillaCorreo")) {
        const id = e.target.closest("button").dataset.id;

        if (confirm("¿Está seguro de que desea eliminar esta plantilla? Esta acción no se puede deshacer.")) {
            const formData = new FormData();
            formData.append("idplantilla", id);

            api.post({
                source: "plantillas",
                action: "eliminar",
                data: formData,
                onSuccess: () => {
                    fetchPlantillas("correosPlantillasList");
                }
            });
        }
    }

    if (e.target.closest("#btnPrevisualizarPlantilla")) {
        const idplantilla = e.target.closest('button').dataset.id;
        previsualizarPlantilla(idplantilla);
    }

    if (e.target.closest("#btnNuevoEmisorCorreo")) {
        abrirModal("emisorCorreo", "Nuevo emisor de correo");
    }

    if (e.target.closest("#btnEditarEmisorCorreo")) {
        abrirModal("emisorCorreo", "Editar emisor de correo", e.target.closest("button").dataset.id);
    }

    if (e.target.closest("#btnEliminarEmisorCorreo")) {
        const id = e.target.closest("button").dataset.id;

        if (confirm("¿Está seguro de que desea eliminar este emisor? Esta acción no se puede deshacer.")) {
            const formData = new FormData();
            formData.append("idemisor", id);

            api.post({
                source: "emisores",
                action: "eliminar",
                data: formData,
                onSuccess: () => {
                    fetchPlantillas("correoEmisoresList", "correo");
                }
            });
        }
    }

    if (e.target.closest(".btn-navegacion")) {
        const btn = e.target.closest(".btn-navegacion");
        if (btn.id === "btnRegresar" && currentStep > 1) currentStep--;
        if (btn.id === "btnSiguiente" && currentStep < totalSteps) currentStep++;
        updateStep();
    }
});