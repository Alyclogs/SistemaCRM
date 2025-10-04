import api from "../utils/api.js";
import { calcularSemanas } from "../utils/date.js";
import { mostrarToast } from "../utils/utils.js";
import { plantillasCache } from "./campanias.js";
import { modalAjustes } from "./index.js";

let campaniaActual = {};

export function initCampaniaConfig() {
    const inputNombre = modalAjustes.getComponent("#nombreInput");
    const inputDescripcion = modalAjustes.getComponent("#descripcionInput");
    const inputFechaInicio = modalAjustes.getComponent("#fechaInicioInput");
    const inputFechaFin = modalAjustes.getComponent("#fechaFinInput");
    const buttonsModalidad = modalAjustes.getComponents(".btn-modalidad");
    const stepItems = modalAjustes.getComponents(".list-item");

    if (!campaniaActual) window.campaniaActual = {};
    if (!campaniaActual.plantillas) campaniaActual.plantillas = [];
    if (!campaniaActual.modalidadProgramacion) campaniaActual.modalidadProgramacion = "dias_especificos";

    [inputNombre, inputDescripcion, inputFechaInicio, inputFechaFin].forEach(input => {
        input.addEventListener("change", () => {
            switch (input.id) {
                case "nombreInput": campaniaActual.nombre = inputNombre.value.trim(); break;
                case "descripcionInput": campaniaActual.descripcion = inputDescripcion.value.trim(); break;
                case "fechaInicioInput": campaniaActual.fecha_inicio = inputFechaInicio.value || null; break;
                case "fechaFinInput": campaniaActual.fecha_fin = inputFechaFin.value || null; break;
            }
            renderPlantillasSeleccionadas();
        });
    });

    buttonsModalidad.forEach(button => {
        button.addEventListener("click", function () {
            buttonsModalidad.forEach(b => b.classList.remove("selected"));
            button.classList.add("selected");
            campaniaActual.modalidadProgramacion = button.dataset.modalidad;
            renderPlantillasSeleccionadas();
        });
    });

    stepItems.forEach(stepItem => {
        stepItem.addEventListener("click", function () {
            const step = stepItem.dataset.step;
            if (step == 3) {
                modalAjustes.setOption("ocultarFooter", false);
            } else {
                modalAjustes.setOption("ocultarFooter", true);
            }
        });
    });

    renderPlantillasSeleccionadas();
}

export function initPlantillaSelection() {
    const checkboxes = modalAjustes.getComponent("#campaniaPlantillasList").querySelectorAll("input[type='checkbox']");
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function () {
            const id = Number(checkbox.value);

            if (checkbox.checked) {
                if (campaniaActual.plantillas.findIndex(p => p.idplantilla == id) === -1) {
                    const plantilla = plantillasCache.get(id);
                    campaniaActual.plantillas.push(plantilla);
                }
            } else {
                campaniaActual.plantillas = campaniaActual.plantillas.filter(p => p.idplantilla != id);
            }

            toggleGlobalTime();
            renderPlantillasSeleccionadas();
        });
    });
}

export function toggleGlobalTime() {
    const checkboxGlobalTime = modalAjustes.getComponent("#useGlobalTimeSwitch");
    const inputHora = modalAjustes.getComponent("#globalTimeInput");

    inputHora.removeEventListener("change", handleHoraChange);
    inputHora.addEventListener("change", handleHoraChange);

    checkboxGlobalTime.addEventListener("change", function () {
        campaniaActual.globalTimeSet = checkboxGlobalTime.checked;

        if (campaniaActual.globalTimeSet) {
            inputHora.disabled = false;
            campaniaActual.globalTime = inputHora.value || "";

            actualizarHorasGlobales();
            mostrarToast({
                title: "Modo horario global activado",
                type: "info"
            });
        } else {
            inputHora.disabled = true;
            mostrarToast({
                title: "Modo horario global desactivado",
                type: "warning"
            });
        }
    });

    function handleHoraChange() {
        if (campaniaActual.globalTimeSet) {
            campaniaActual.globalTime = inputHora.value;
            actualizarHorasGlobales();
            mostrarToast({
                title: "Todas las horas de envío fueron actualizadas",
                type: "success"
            });
        }
    }

    function actualizarHorasGlobales() {
        if (!campaniaActual.plantillas) return;

        campaniaActual.plantillas.forEach(p => {
            const fechaInput = modalAjustes.getComponent(
                `input[type="date"][data-idplantilla="${p.idplantilla}"]`
            );
            if (!fechaInput) return;

            const fecha = fechaInput.value;
            p.fecha_envio = `${fecha}T${campaniaActual.globalTime}`;
        });

        renderPlantillasSeleccionadas();
    }
}

function renderPlantillasSeleccionadas() {
    const container = modalAjustes.getComponent("#campaniaProgramacionPlantillas");

    if (!campaniaActual.fecha_inicio || !campaniaActual.fecha_fin) {
        container.innerHTML = `<div class="p-3 my-4 text-center">Especifique una fecha inicio y fecha fin para la campaña</div>`;
        return;
    }
    if (!campaniaActual.plantillas || campaniaActual.plantillas.length === 0) {
        container.innerHTML = `<div class="p-3 my-4 text-center">No se han seleccionado programaciones</div>`;
        return;
    }

    let html = "";
    const modalidad = campaniaActual.modalidadProgramacion || "dias_especificos";

    campaniaActual.plantillas.forEach((plantilla, idx) => {
        if (!plantilla.fecha_envio) plantilla.fecha_envio = campaniaActual.fecha_inicio;
        const idplantilla = plantilla.idplantilla;

        html += `<div class="container-border plantilla-programacion d-flex flex-column">
                    <div class="d-flex gap-2 mb-3">
                        <div class="flex-grow-1 d-flex gap-2">
                            <div class="icon icon-circle bg-success disable-hover">${idx + 1}</div>
                            <div class="flex-grow-1">
                                <p class="fw-bold">${plantilla.nombre}</p>
                                <span class="text-muted">${plantilla.descripcion || "Sin descripción"}</span>
                            </div>
                        </div>
                    </div>`;

        if (modalidad === "dias_especificos") {
            html += `<div class="row">`;

            if (idx > 0) {
                html += `
                <div class="col">
                    <label for="pDaysAfter_${idplantilla}" class="form-label">Días después</label>
                    <input type="number" id="pDaysAfter_${idplantilla}" 
                           class="form-control"
                           value="${plantilla.dias_despues || 0}"
                           onchange="updateFechaEnvioPlantillas(this)">
                </div>`;
            }
        }

        if (modalidad === "dias_semana" && idx > 0) {
            const semanas = calcularSemanas(campaniaActual.fecha_inicio, campaniaActual.fecha_fin);

            html += `
        <div class="row">
            <div class="col-6 mb-3">
                <label for="pSemana_${idplantilla}" class="form-label">Semana</label>
                <div class="busqueda-grupo disable-auto">
                    <input type="text" 
                           class="form-control" 
                           id="pSemana_${idplantilla}" 
                           placeholder="Seleccione semana"
                           value="${plantilla.semana_label || ""}"
                           onfocus="showSemanas(this, '${idplantilla}')">
                    <input type="hidden" id="pSemanaHidden_${idplantilla}" value="${plantilla.semana_inicio || ""}">
                    <div class="resultados-busqueda" data-parent="pSemana_${idplantilla}" style="top: 2.5rem;">
                        ${semanas.map((s, i) => `
                            <div class="resultado-item" onclick="selectSemana('${idplantilla}', '${s.inicio}', '${s.fin}', 'Semana ${i + 1} (${s.inicio} - ${s.fin})')">
                                Semana ${i + 1} (${s.inicio} - ${s.fin})
                            </div>`).join("")}
                    </div>
                </div>
            </div>
            <div class="col-6 mb-3">
                <label class="form-label">Día</label>
                <div class="d-flex gap-1 flex-wrap">
                    ${["L", "M", "X", "J", "V", "S", "D"].map((d, i) => `
                        <button type="button" 
                                class="btn btn-icon ${plantilla.dia_semana === i ? "bg-primary" : "bg-light"}"
                                onclick="selectDiaSemana(${idplantilla}, ${i})">${d}</button>`).join("")}
                </div>
            </div>
        </div>
        <div class="row">`;
        }

        html += `
                <div class="col">
                    <label for="pTime_${idplantilla}" class="form-label">Hora de envío</label>
                    <input type="time" id="pTime_${idplantilla}" 
                           class="form-control" 
                           value="${campaniaActual.globalTime || ''}" 
                           ${campaniaActual.globalTimeSet ? "disabled" : ''}>
                </div>
                <div class="col">
                    <label for="pDate_${idplantilla}" class="form-label">Fecha de envío</label>
                    <input type="date" id="pDate_${idplantilla}" 
                           class="form-control" 
                           value="${plantilla.fecha_envio}" 
                           disabled>
                </div>
            </div>
        </div>`;
    });

    container.innerHTML = html;
}

window.selectSemana = function (idplantilla, inicio, fin, label) {
    const inputVisible = modalAjustes.getComponent(`#pSemana_${idplantilla}`);
    const inputHidden = modalAjustes.getComponent(`#pSemanaHidden_${idplantilla}`);
    inputVisible.value = label;
    inputHidden.value = inicio;

    const plantilla = campaniaActual.plantillas.find(p => p.idplantilla == idplantilla);
    if (!plantilla) return;
    plantilla.semana_inicio = inicio;
    plantilla.semana_label = label;

    updateFechaEnvioPlantillas();
};

window.selectDiaSemana = function (idplantilla, dia) {
    const plantilla = campaniaActual.plantillas.find(p => p.idplantilla == idplantilla);
    if (!plantilla) return;
    plantilla.dia_semana = dia;

    updateFechaEnvioPlantillas();
};

window.updateFechaEnvioPlantillas = function (input) {
    const modalidad = campaniaActual.modalidadProgramacion || "dias_especificos";
    const fechaFin = new Date(campaniaActual.fecha_fin);

    if (modalidad === "dias_especificos") {
        let fechaBase = new Date(campaniaActual.fecha_inicio);
        campaniaActual.plantillas.forEach((plantilla, idx) => {
            if (idx === 0) {
                plantilla.fecha_envio = campaniaActual.fecha_inicio;
            } else {
                const dias = parseInt(plantilla.dias_despues || 0, 10);
                fechaBase.setDate(fechaBase.getDate() + dias);
                if (fechaBase > fechaFin) fechaBase = fechaFin;
                plantilla.fecha_envio = fechaBase.toISOString().split("T")[0];
            }
        });
    }

    if (modalidad === "dias_semana") {
        campaniaActual.plantillas.forEach(plantilla => {
            if (!plantilla.semana_inicio || plantilla.dia_semana === undefined) return;
            const semanaInicio = new Date(plantilla.semana_inicio);
            semanaInicio.setDate(semanaInicio.getDate() + plantilla.dia_semana);
            if (semanaInicio > fechaFin) return;
            plantilla.fecha_envio = semanaInicio.toISOString().split("T")[0];
        });
    }

    renderPlantillasSeleccionadas();
};

function eliminarPlantillaProgramacion(idplantilla) {
    if (!campaniaActual || !campaniaActual.fecha_inicio) return;

    campaniaActual.plantillas = campaniaActual.plantillas.filter(p => p.idplantilla != idplantilla);
    const checkbox = modalAjustes.getComponent(
        `#campaniaPlantillasList input[type="checkbox"][value="${idplantilla}"]`
    );
    if (checkbox) {
        checkbox.checked = false;
        checkbox.dispatchEvent(new Event("change", { bubbles: true }));
    }
    renderPlantillasSeleccionadas();
}

export function guardarCampania() {
    if (!campaniaActual.nombre || !campaniaActual.fecha_inicio || !campaniaActual.fecha_fin) {
        mostrarToast({ title: "Completa los datos básicos de la campaña", type: "error" });
        return;
    }
    const programaciones = campaniaActual.plantillas.map(p => ({
        idplantilla: p.idplantilla,
        fecha_envio: p.fecha_envio,
        idestado: p.idestado || 1
    }));

    const data = {
        nombre: campaniaActual.nombre,
        descripcion: campaniaActual.descripcion || "",
        fecha_inicio: campaniaActual.fecha_inicio,
        fecha_fin: campaniaActual.fecha_fin,
        nota: campaniaActual.nota || null,
        programaciones: JSON.stringify(programaciones)
    };

    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
        formData.append(key, value);
    });

    api.post({
        source: "campanias",
        action: "crear",
        data: formData,
        onSuccess: () => {
            modalAjustes.destroy();
        }
    });
}

document.addEventListener("click", function (e) {
    if (e.target.closest("#btnDeseleccionarPlantilla")) {
        eliminarPlantillaProgramacion(e.target.dataset.id);
    }
});