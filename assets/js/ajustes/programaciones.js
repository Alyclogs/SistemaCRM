import api from "../utils/api.js";
import { mostrarToast } from "../utils/utils.js";
import { fetchCampanias, plantillasCache } from "./campanias.js";
import { modalAjustes } from "./index.js";

let campaniaActual = {
    idcampania: null,
    nombre: "",
    descripcion: "",
    modalidadProgramacion: "dias_especificos",
    globalTime: "",
    globalTimeSet: false,
    plantillas: []
};

const pad = n => n.toString().padStart(2, "0");
const getInput = sel => modalAjustes.getComponent(sel);

export function initCampaniaConfig() {
    const inputs = {
        nombre: getInput("#nombreInput"),
        descripcion: getInput("#descripcionInput")
    };
    const buttonsModalidad = modalAjustes.getComponents(".btn-modalidad");
    const stepItems = modalAjustes.getComponents(".list-item");

    Object.entries(inputs).forEach(([key, el]) => {
        if (!el) return;
        el.addEventListener("change", () => {
            campaniaActual[key] = el.value?.trim() || "";
            renderPlantillasSeleccionadas();
        });
    });

    buttonsModalidad.forEach(btn => {
        btn.addEventListener("click", () => {
            buttonsModalidad.forEach(b => b.classList.remove("selected"));
            btn.classList.add("selected");
            campaniaActual.modalidadProgramacion = btn.dataset.modalidad;
            renderPlantillasSeleccionadas();
        });
    });

    stepItems.forEach(step => {
        step.addEventListener("click", () => {
            modalAjustes.setOption("ocultarFooter", step.dataset.step != 3);
            modalAjustes.setOption("size", step.dataset.step != 3 ? "lg" : "xl");
        });
    });

    toggleGlobalTime();
    renderPlantillasSeleccionadas();
}

export function initPlantillaSelection() {
    const list = getInput("#campaniaPlantillasList");
    if (!list) return;
    list.querySelectorAll(".plantilla-checkbox").forEach(cb => {
        cb.addEventListener("change", () => {
            const id = cb.value;
            if (cb.checked) {
                const plantilla = plantillasCache.get(id);
                if (!campaniaActual.plantillas.find(p => p.idplantilla == id)) {
                    campaniaActual.plantillas.push({
                        idplantilla: +plantilla.idplantilla,
                        nombre: plantilla.nombre,
                        descripcion: plantilla.descripcion || "",
                        dias_despues: 0,
                        dia_semana: 0,
                        hora_envio: campaniaActual.globalTime || "08:00",
                        idestado: 1
                    });
                }
            } else {
                campaniaActual.plantillas = campaniaActual.plantillas.filter(p => p.idplantilla != id);
            }
            renderPlantillasSeleccionadas();
        });
    });
}

export function toggleGlobalTime() {
    const chk = getInput("#useGlobalTimeSwitch");
    const input = getInput("#globalTimeInput");

    if (!chk || !input) return;

    chk.onchange = () => {
        campaniaActual.globalTimeSet = chk.checked;
        input.disabled = !chk.checked;
        if (chk.checked) {
            campaniaActual.globalTime = input.value || "08:00";
            actualizarHorasGlobales();
            mostrarToast({ title: "Modo horario global activado", type: "info" });
        } else {
            mostrarToast({ title: "Modo horario global desactivado", type: "warning" });
        }
        renderPlantillasSeleccionadas();
    };

    input.onchange = () => {
        if (campaniaActual.globalTimeSet) {
            campaniaActual.globalTime = input.value;
            actualizarHorasGlobales();
            renderPlantillasSeleccionadas();
        }
    };
}

function actualizarHorasGlobales() {
    const [h = "08", m = "00"] = (campaniaActual.globalTime || "08:00").split(":");
    const hh = pad(h), mm = pad(m);

    campaniaActual.plantillas.forEach(p => {
        p.hora_envio = `${hh}:${mm}`;
        const inputHora = document.querySelector(`#pTime_${p.idplantilla}`);
        if (inputHora) inputHora.value = p.hora_envio;
    });

    mostrarToast({ title: "Todas las horas fueron actualizadas", type: "success" });
}

export function cargarCampaniaExistente(idcampania) {
    api.get({
        source: "campanias",
        action: "ver",
        params: [{ name: "idcampania", value: idcampania }],
        onSuccess: campania => {
            if (!campania) return;
            campaniaActual = {
                idcampania: +campania.idcampania,
                nombre: campania.nombre || "",
                descripcion: campania.descripcion || "",
                modalidadProgramacion: campania.modalidadProgramacion || "dias_especificos",
                globalTime: "",
                globalTimeSet: false,
                plantillas: (campania.programaciones || []).map(p => {
                    let hora_envio = "08:00";
                    if (p.hora_envio) hora_envio = p.hora_envio;

                    return {
                        idplantilla: +p.idplantilla,
                        nombre: p.plantilla_nombre || `Plantilla #${p.idplantilla}`,
                        descripcion: p.plantilla_descripcion || "",
                        dias_despues: Number.isFinite(+p.dias_despues) ? +p.dias_despues : (p.dias_despues ?? 0),
                        dia_semana: Number.isFinite(+p.dia_semana) ? +p.dia_semana : (p.dia_semana ?? 0),
                        hora_envio,
                        idestado: +p.idestado || 1
                    };
                })
            };

            campaniaActual.plantillas.forEach(p => {
                const checkbox = modalAjustes.getComponent(`.plantilla-checkbox[value="${p.idplantilla}"]`);
                if (checkbox) checkbox.checked = true;
            });

            const horas = campaniaActual.plantillas.map(p => p.hora_envio || "08:00");
            const todasIguales = horas.length > 0 && horas.every(h => h === horas[0]);
            const globalTimeInput = modalAjustes.getComponent("#globalTimeInput");
            const globalTimeSwitch = modalAjustes.getComponent("#useGlobalTimeSwitch");

            if (todasIguales && horas[0]) {
                campaniaActual.globalTime = horas[0];
                campaniaActual.globalTimeSet = true;
                if (globalTimeInput) { globalTimeInput.disabled = false; globalTimeInput.value = horas[0]; }
                if (globalTimeSwitch) globalTimeSwitch.checked = true;
            } else {
                campaniaActual.globalTimeSet = false;
                if (globalTimeInput) globalTimeInput.disabled = true;
                if (globalTimeSwitch) globalTimeSwitch.checked = false;
            }

            const nombreInput = getInput("#nombreInput");
            const descripcionInput = getInput("#descripcionInput");
            if (nombreInput) nombreInput.value = campaniaActual.nombre;
            if (descripcionInput) descripcionInput.value = campaniaActual.descripcion;

            renderPlantillasSeleccionadas();
        }
    });
}

function calcularPatrones() {
    campaniaActual.plantillas.forEach(p => {
        p.dias_despues = Number.isFinite(+p.dias_despues) ? +p.dias_despues : 0;
        p.dia_semana = Number.isFinite(+p.dia_semana) ? +p.dia_semana : 0;
        p.hora_envio = p.hora_envio || (campaniaActual.globalTimeSet ? campaniaActual.globalTime : "08:00");
    });
}

function renderPlantillasSeleccionadas() {
    const container = modalAjustes.getComponent("#campaniaProgramacionPlantillas");
    if (!container) return;

    if (!campaniaActual.plantillas || campaniaActual.plantillas.length === 0) {
        container.innerHTML = `<div class="p-3 my-4 text-center">No se han seleccionado programaciones</div>`;
        return;
    }

    const modalidad = campaniaActual.modalidadProgramacion || "dias_especificos";

    let html = "";
    campaniaActual.plantillas.forEach((plantilla, idx) => {
        const id = plantilla.idplantilla;
        const hora = plantilla.hora_envio || (campaniaActual.globalTimeSet ? campaniaActual.globalTime : "08:00");
        const diasDespues = plantilla.dias_despues ?? 0;
        const diaSemana = plantilla.dia_semana ?? 0;

        html += `
        <div class="container-border plantilla-programacion d-flex flex-column p-3 mb-3 rounded-3">
            <div class="d-flex gap-2 mb-3 align-items-center">
                <div class="icon icon-circle bg-success disable-hover">${idx + 1}</div>
                <div class="flex-grow-1">
                    <p class="fw-bold mb-0">${plantilla.nombre}</p>
                    <small class="text-muted">${plantilla.descripcion || "Sin descripción"}</small>
                </div>
                <button type="button" id="btnDeseleccionarPlantilla" data-id="${id}" class="btn btn-icon">${window.icons?.trash || 'Eliminar'}</button>
            </div>
        `;

        if (modalidad === "dias_especificos") {
            html += `<div class="row align-items-end">`;
            if (idx > 0) {
                html += `
                    <div class="col mb-3">
                        <label for="pDaysAfter_${id}" class="form-label">Días después</label>
                        <input type="number" id="pDaysAfter_${id}" class="form-control" value="${diasDespues}"
                            oninput="onChangeDiasDespues(${id}, this.value)">
                    </div>`;
            } else {
                html += `
                    <div class="col mb-3">
                        <label class="form-label">Base (inicio)</label>
                        <input type="text" class="form-control" value="Inicio de campaña (base)" disabled>
                    </div>`;
            }

            html += `
                <div class="col mb-3">
                    <label for="pTime_${id}" class="form-label">Hora</label>
                    <div class="input-group">
                        <input type="time" id="pTime_${id}" class="form-control" value="${hora}" onchange="onChangeHora(${id}, this.value)" ${campaniaActual.globalTimeSet ? 'disabled' : ''}>
                        <button class="btn btn-outline-secondary" type="button" title="Aplicar hora a todas" onclick="aplicarHoraATodas('${hora}')"><i class="bi bi-clock-history"></i></button>
                    </div>
                </div>
            </div>`;
        }

        if (modalidad === "dias_semana") {
            html += `
            <div class="row">

                <div class="col mb-3">
                    <label class="form-label">Día</label>
                    <div class="d-flex gap-1 flex-wrap" id="diasSemana_${id}">
                        ${["L", "M", "X", "J", "V", "S", "D"].map((d, i) => {
                const selected = i === diaSemana;
                return `<button type="button" class="btn btn-icon ${selected ? "selected bg-primary text-white" : "bg-light"}" onclick="selectDiaSemana(${id}, ${i})">${d}</button>`;
            }).join("")}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col mb-3">
                    <label for="pTime_${id}" class="form-label">Hora</label>
                    <div class="input-group">
                        <input type="time" id="pTime_${id}" class="form-control" value="${hora}" onchange="onChangeHora(${id}, this.value)" ${campaniaActual.globalTimeSet ? 'disabled' : ''}>
                        <button class="btn btn-outline-secondary" type="button" title="Aplicar hora a todas" onclick="aplicarHoraATodas('${hora}')"><i class="bi bi-clock-history"></i></button>
                    </div>
                </div>
            </div>`;
        }

        html += `</div>`; // cierre plantilla
    });

    container.innerHTML = html;
}

window.selectDiaSemana = function (idplantilla, dia) {
    const plantilla = campaniaActual.plantillas.find(p => p.idplantilla == idplantilla);
    if (!plantilla) return;
    plantilla.dia_semana = +dia;

    const container = document.getElementById(`diasSemana_${idplantilla}`);
    if (container) {
        container.querySelectorAll("button").forEach((btn, i) => {
            if (i === dia) {
                btn.classList.add("selected", "bg-primary", "text-white");
                btn.classList.remove("bg-light");
            } else {
                btn.classList.remove("selected", "bg-primary", "text-white");
                btn.classList.add("bg-light");
            }
        });
    }
};

window.aplicarHoraATodas = function (hora) {
    if (!hora) return;
    campaniaActual.plantillas.forEach(p => p.hora_envio = hora);
    campaniaActual.plantillas.forEach(p => {
        const inputHora = document.querySelector(`#pTime_${p.idplantilla}`);
        if (inputHora) inputHora.value = p.hora_envio;
    });
    mostrarToast({ title: "Hora aplicada a todas las plantillas", type: "success" });
};

window.onChangeHora = function (idplantilla, nuevaHora) {
    const plantilla = campaniaActual.plantillas.find(p => p.idplantilla == idplantilla);
    if (!plantilla) return;
    plantilla.hora_envio = nuevaHora || plantilla.hora_envio || (campaniaActual.globalTimeSet ? campaniaActual.globalTime : "08:00");
};

window.onChangeDiasDespues = function (idplantilla, valor) {
    const plantilla = campaniaActual.plantillas.find(p => p.idplantilla == idplantilla);
    if (!plantilla) return;
    plantilla.dias_despues = Number.isFinite(+valor) ? +valor : 0;
};

window.updateFechaEnvioPlantillas = function () {
    calcularPatrones();
    campaniaActual.plantillas.forEach(p => {
        const inputHora = document.querySelector(`#pTime_${p.idplantilla}`);
        if (inputHora) inputHora.value = p.hora_envio || (campaniaActual.globalTimeSet ? campaniaActual.globalTime : "08:00");
        const inputDias = document.getElementById(`pDaysAfter_${p.idplantilla}`);
        if (inputDias) inputDias.value = p.dias_despues ?? 0;
        const semanaHidden = document.getElementById(`pSemanaHidden_${p.idplantilla}`);
        if (semanaHidden) semanaHidden.value = p.semana_index ?? 1;
        const semanaInput = document.getElementById(`pSemana_${p.idplantilla}`);
        if (semanaInput) semanaInput.value = p.semana_label ?? `Semana ${p.semana_index ?? 1}`;
    });
};

function eliminarPlantillaProgramacion(id) {
    campaniaActual.plantillas = campaniaActual.plantillas.filter(p => p.idplantilla != id);
    const cb = document.querySelector(`#campaniaPlantillasList input[value="${id}"]`);
    if (cb) cb.checked = false;
    renderPlantillasSeleccionadas();
}

export function guardarCampania() {
    // Validaciones básicas
    if (!campaniaActual.nombre?.trim()) {
        mostrarToast({ title: "Completa el nombre de la campaña (patrón)", type: "error" });
        return;
    }
    if (!campaniaActual.plantillas || campaniaActual.plantillas.length === 0) {
        mostrarToast({ title: "Debes agregar al menos una plantilla al patrón", type: "error" });
        return;
    }

    calcularPatrones();

    const programaciones = campaniaActual.plantillas.map(p => {
        const base = {
            idplantilla: p.idplantilla,
            hora: p.hora_envio || (campaniaActual.globalTimeSet ? campaniaActual.globalTime : "08:00"),
            idestado: p.idestado || 1
        };
        if (campaniaActual.modalidadProgramacion === "dias_especificos") {
            base.dias_despues = p.dias_despues ?? 0;
        } else {
            base.semana_index = p.semana_index ?? 1;
            base.dia_semana = p.dia_semana ?? 0;
        }
        return base;
    });

    const data = {
        nombre: campaniaActual.nombre.trim(),
        descripcion: campaniaActual.descripcion || "",
        modalidadProgramacion: campaniaActual.modalidadProgramacion,
        globalTime: campaniaActual.globalTimeSet ? campaniaActual.globalTime : "",
        programaciones: JSON.stringify(programaciones)
    };

    const formData = new FormData();
    Object.entries(data).forEach(([k, v]) => { if (v !== "") formData.append(k, v); });
    if (campaniaActual.idcampania) formData.append("idcampania", campaniaActual.idcampania);

    api.post({
        source: "campanias",
        action: campaniaActual.idcampania ? "actualizar" : "crear",
        data: formData,
        onSuccess: () => {
            campaniaActual = { idcampania: null, nombre: "", descripcion: "", modalidadProgramacion: "dias_especificos", globalTime: "", globalTimeSet: false, plantillas: [] };
            fetchCampanias();
            modalAjustes.hide();
            mostrarToast({ title: "Patrón guardado correctamente", type: "success" });
        }
    });
}

document.addEventListener("click", function (e) {
    const btn = e.target.closest("#btnDeseleccionarPlantilla");
    if (btn) eliminarPlantillaProgramacion(+btn.dataset.id);
});