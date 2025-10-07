import api from "../utils/api.js";
import { calcularSemanas, formatearFechaVisual, formatearHoraVisual } from "../utils/date.js";
import { mostrarToast } from "../utils/utils.js";
import { fetchCampanias, plantillasCache } from "./campanias.js";
import { modalAjustes } from "./index.js";

/* ---------------------------------
 * 🧠 Estado global
 * --------------------------------- */
let campaniaActual = {
    idcampania: null,
    nombre: "",
    descripcion: "",
    fecha_inicio: "",
    fecha_fin: "",
    modalidadProgramacion: "dias_especificos",
    globalTime: "",
    globalTimeSet: false,
    plantillas: []
};

/* ---------------------------------
 * 🔧 Helpers de fecha
 * --------------------------------- */
const pad = n => n.toString().padStart(2, "0");
const parseDate = str => {
    if (!str) return null;
    const [y, m, d] = str.split("-").map(Number);
    return new Date(y, m - 1, d);
};
const formatMySQL = date =>
    `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:00`;
const getInput = sel => modalAjustes.getComponent(sel);

/* ---------------------------------
 * 🧩 Inicialización
 * --------------------------------- */
export function initCampaniaConfig() {
    const inputs = {
        nombre: getInput("#nombreInput"),
        descripcion: getInput("#descripcionInput"),
        inicio: getInput("#fechaInicioInput"),
        fin: getInput("#fechaFinInput")
    };
    const buttonsModalidad = modalAjustes.getComponents(".btn-modalidad");
    const stepItems = modalAjustes.getComponents(".list-item");

    Object.entries(inputs).forEach(([key, el]) => {
        el.addEventListener("change", () => {
            campaniaActual[key === "inicio" ? "fecha_inicio" : key === "fin" ? "fecha_fin" : key] =
                el.value.trim() || null;
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

export function toggleGlobalTime() {
    const chk = getInput("#useGlobalTimeSwitch");
    const input = getInput("#globalTimeInput");

    chk.onchange = () => {
        campaniaActual.globalTimeSet = chk.checked;
        input.disabled = !chk.checked;
        if (chk.checked) {
            campaniaActual.globalTime = input.value;
            actualizarHorasGlobales();
            mostrarToast({ title: "Modo horario global activado", type: "info" });
        } else {
            mostrarToast({ title: "Modo horario global desactivado", type: "warning" });
        }
    };

    input.onchange = () => {
        if (campaniaActual.globalTimeSet) {
            campaniaActual.globalTime = input.value;
            actualizarHorasGlobales();
        }
    };
}

function actualizarHorasGlobales() {
    const [h, m] = (campaniaActual.globalTime || "08:00").split(":").map(Number);

    campaniaActual.plantillas.forEach(p => {
        const fecha = new Date(p.fecha_envio || `${campaniaActual.fecha_inicio} 00:00:00`);
        fecha.setHours(h, m, 0, 0);
        p.fecha_envio = formatMySQL(fecha);

        const inputHora = document.querySelector(`#pTime_${p.idplantilla}`);
        if (inputHora) inputHora.value = `${String(h).padStart(2, "0")}:${String(m).padStart(2, "0")}`;
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
                fecha_inicio: campania.fecha_inicio,
                fecha_fin: campania.fecha_fin,
                modalidadProgramacion: campania.modalidadProgramacion || "dias_especificos",
                globalTime: "",
                globalTimeSet: false,
                plantillas: (campania.programaciones || []).map(p => ({
                    idplantilla: +p.idplantilla,
                    nombre: p.plantilla_nombre || `Plantilla #${p.idplantilla}`,
                    fecha_envio: p.fecha_envio?.replace(" ", "T") || `${campania.fecha_inicio}T08:00:00`,
                    dias_despues: 0,
                    dia_semana: null,
                    semana_inicio: null,
                    idestado: +p.idestado || 1
                }))
            };

            getInput("#nombreInput").value = campaniaActual.nombre;
            getInput("#descripcionInput").value = campaniaActual.descripcion;
            getInput("#fechaInicioInput").value = campaniaActual.fecha_inicio.split(" ")[0];
            getInput("#fechaFinInput").value = campaniaActual.fecha_fin.split(" ")[0];

            const globalTimeInput = modalAjustes.getComponent("#globalTimeInput");
            const globalTimeSwitch = modalAjustes.getComponent("#useGlobalTimeSwitch");

            campaniaActual.plantillas.forEach(p => {
                const checkbox = modalAjustes.getComponent(`.plantilla-checkbox[value="${p.idplantilla}"]`);
                if (checkbox) checkbox.checked = true;
            });
            const horas = campaniaActual.plantillas.map(p => {
                const [, hora] = p.fecha_envio.split("T"); return hora?.slice(0, 5);
            });
            const todasIguales = horas.every(h => h === horas[0]);
            if (todasIguales && horas[0]) {
                campaniaActual.globalTime = horas[0];
                campaniaActual.globalTimeSet = true;
                globalTimeInput.disabled = false;
                globalTimeSwitch.checked = true;

                if (globalTimeInput) globalTimeInput.value = horas[0];
                if (globalTimeSwitch) globalTimeSwitch.checked = true;
            } else {
                campaniaActual.globalTimeSet = false;
                globalTimeSwitch.checked = false;
                globalTimeInput.disabled = true;
            }

            renderPlantillasSeleccionadas();

            mostrarToast({
                title: `Campaña "${campaniaActual.nombre}" cargada`,
                type: "info"
            });
        }
    });
}

export function initPlantillaSelection() {
    const list = getInput("#campaniaPlantillasList");
    list.querySelectorAll(".plantilla-checkbox").forEach(cb => {
        cb.addEventListener("change", () => {
            const id = cb.value;
            if (cb.checked) {
                const plantilla = plantillasCache.get(id);
                if (!campaniaActual.plantillas.find(p => p.idplantilla == id)) {
                    campaniaActual.plantillas.push({
                        ...plantilla,
                        dias_despues: 0,
                        fecha_envio: `${campaniaActual.fecha_inicio} 08:00:00`,
                        semana_inicio: null,
                        dia_semana: null
                    });
                }
            } else {
                campaniaActual.plantillas = campaniaActual.plantillas.filter(p => p.idplantilla != id);
            }
            renderPlantillasSeleccionadas();
        });
    });
}

function calcularFechas() {
    const modalidad = campaniaActual.modalidadProgramacion;
    const fechaInicio = parseDate(campaniaActual.fecha_inicio);
    const fechaFin = parseDate(campaniaActual.fecha_fin);

    campaniaActual.plantillas.forEach(p => {
        const hora = document.querySelector(`#pTime_${p.idplantilla}`)?.value || "08:00";
        const [h, m] = hora.split(":").map(Number);

        if (modalidad === "dias_especificos") {
            const diasDespues = parseInt(
                document.querySelector(`#pDaysAfter_${p.idplantilla}`)?.value || p.dias_despues || 0,
                10
            );
            p.dias_despues = diasDespues;

            const fechaEnvio = new Date(fechaInicio);
            fechaEnvio.setDate(fechaInicio.getDate() + diasDespues);
            fechaEnvio.setHours(h, m, 0, 0);
            if (fechaEnvio > fechaFin) fechaEnvio.setTime(fechaFin.getTime());
            p.fecha_envio = formatMySQL(fechaEnvio);
        }

        if (modalidad === "dias_semana" && p.semana_inicio && p.dia_semana !== null) {
            const base = parseDate(p.semana_inicio);
            base.setDate(base.getDate() + p.dia_semana);
            base.setHours(h, m, 0, 0);
            if (base <= fechaFin) p.fecha_envio = formatMySQL(base);
        }
    });
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
        if (!plantilla.fecha_envio)
            plantilla.fecha_envio = `${campaniaActual.fecha_inicio} 00:00:00`;

        const idplantilla = plantilla.idplantilla;
        const fechaEnvio = formatearFechaVisual(plantilla.fecha_envio);
        const horaEnvio = formatearHoraVisual(plantilla.fecha_envio);

        html += `
        <div class="container-border plantilla-programacion d-flex flex-column p-3 rounded-3">
            <div class="d-flex gap-2 mb-3 align-items-center">
                <div class="icon icon-circle bg-success disable-hover">${idx + 1}</div>
                <div class="flex-grow-1">
                    <p class="fw-bold mb-0">${plantilla.nombre}</p>
                    <small class="text-muted">${plantilla.descripcion || "Sin descripción"}</small>
                </div>
                <button type="button" id="btnDeseleccionarPlantilla" data-id="${idplantilla}" class="btn btn-icon">${window.icons.trash}</button>
            </div>`;

        if (modalidad === "dias_especificos") {
            html += `<div class="row align-items-end">`;

            if (idx > 0) {
                html += `
                    <div class="col mb-3">
                        <label for="pDaysAfter_${idplantilla}" class="form-label">Días después</label>
                        <input type="number" id="pDaysAfter_${idplantilla}" 
                               class="form-control"
                               value="${plantilla.dias_despues || 0}"
                               oninput="updateFechaEnvioPlantillas(this)">
                    </div>`;
            }

            html += `
                <div class="col mb-3">
                    <label for="pTime_${idplantilla}" class="form-label">Hora de envío</label>
                    <input type="time" id="pTime_${idplantilla}" 
                           class="form-control" 
                           value="${horaEnvio}" 
                           onchange="updateFechaEnvioPlantillas(this)"
                           ${campaniaActual.globalTimeSet ? "disabled" : ""}>
                </div>
                <div class="col mb-3">
                    <label for="pDate_${idplantilla}" class="form-label">Fecha de envío</label>
                    <input type="date" id="pDate_${idplantilla}" 
                           class="form-control" 
                           value="${fechaEnvio}" 
                           disabled>
                </div>
            </div>`;
        }

        if (modalidad === "dias_semana") {
            const semanas = calcularSemanas(campaniaActual.fecha_inicio, campaniaActual.fecha_fin);

            if (!plantilla.semana_inicio) {
                plantilla.semana_inicio = semanas[0].inicio;
                plantilla.semana_label = semanas[0].label;
            }

            if (plantilla.dia_semana === undefined || plantilla.dia_semana === null)
                plantilla.dia_semana = 0;

            html += `
    <div class="row">
        <div class="col mb-3">
            <label for="pSemana_${idplantilla}" class="form-label">Semana</label>
            <div class="busqueda-grupo position-relative">
                <input type="text" 
                    class="form-control" 
                    id="pSemana_${idplantilla}" 
                    placeholder="Seleccione semana"
                    value="${plantilla.semana_label || ''}"
                    ${idx > 0 ? `onfocus="showSemanas(this, '${idplantilla}')"` : ''} readonly>
                <input type="hidden" id="pSemanaHidden_${idplantilla}" value="${plantilla.semana_inicio || ''}">
                <div class="resultados-busqueda disable-auto" data-parent="pSemana_${idplantilla}" style="top: 2.5rem;">
                    ${semanas.map((s, i) => `
                        <div class="resultado-item" 
                            onclick="selectSemana('${idplantilla}', '${s.inicio}', '${s.fin}', '${s.label}')">
                            ${s.label}
                        </div>`).join("")}
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <label class="form-label">Día</label>
            <div class="d-flex gap-1 flex-wrap" id="diasSemana_${idplantilla}">
                ${["L", "M", "X", "J", "V", "S", "D"].map((d, i) => {
                const isSelected = plantilla.dia_semana === i;
                return `
                        <button type="button"
                                class="btn btn-icon ${isSelected ? "selected bg-primary text-white" : "bg-light"}"
                                onclick="selectDiaSemana(${idplantilla}, ${i})" ${idx === 0 ? "disabled" : ""}>
                            ${d}
                        </button>`;
            }).join("")}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col mb-3">
                    <label for="pTime_${idplantilla}" class="form-label">Hora de envío</label>
                    <input type="time" id="pTime_${idplantilla}" 
                           class="form-control" 
                           value="${horaEnvio}" 
                           onchange="updateFechaEnvioPlantillas(this)"
                           ${campaniaActual.globalTimeSet ? "disabled" : ""}>
                </div>
                <div class="col mb-3">
                    <label for="pDate_${idplantilla}" class="form-label">Fecha de envío</label>
                    <input type="date" id="pDate_${idplantilla}" 
                           class="form-control" 
                           value="${fechaEnvio}" 
                           disabled>
                </div>
            </div>`;
        }

        html += `</div>`;
    });

    container.innerHTML = html;
}

window.showSemanas = function (input, idplantilla) {
    const parent = input.closest(".busqueda-grupo");
    const resultados = parent.querySelector(".resultados-busqueda");
    if (resultados) resultados.style.display = "block";

    document.addEventListener("click", function ocultar(e) {
        if (!parent.contains(e.target)) {
            resultados.style.display = "none";
            document.removeEventListener("click", ocultar);
        }
    });
};

window.selectSemana = function (idplantilla, inicio, fin, label) {
    const inputVisible = modalAjustes.getComponent(`#pSemana_${idplantilla}`);
    const inputHidden = modalAjustes.getComponent(`#pSemanaHidden_${idplantilla}`);

    if (!inputVisible || !inputHidden) return;

    inputVisible.value = label;
    inputHidden.value = inicio;

    const plantilla = campaniaActual.plantillas.find(p => p.idplantilla == idplantilla);
    if (!plantilla) return;

    plantilla.semana_inicio = inicio;
    plantilla.semana_label = label;

    actualizarVistaFechas();
};

window.selectDiaSemana = function (idplantilla, dia) {
    const plantilla = campaniaActual.plantillas.find(p => p.idplantilla == idplantilla);
    if (!plantilla) return;

    plantilla.dia_semana = dia;

    const container = modalAjustes.getComponent(`#diasSemana_${idplantilla}`);
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
    actualizarVistaFechas();
};

window.updateFechaEnvioPlantillas = function (input) {
    actualizarVistaFechas();
}

function actualizarVistaFechas() {
    calcularFechas();
    campaniaActual.plantillas.forEach(p => {
        const dateInput = document.querySelector(`#pDate_${p.idplantilla}`);
        if (dateInput) dateInput.value = p.fecha_envio.split(" ")[0];
    });
}

function eliminarPlantillaProgramacion(id) {
    campaniaActual.plantillas = campaniaActual.plantillas.filter(p => p.idplantilla != id);
    const cb = document.querySelector(`#campaniaPlantillasList input[value="${id}"]`);
    if (cb) cb.checked = false;
    renderPlantillasSeleccionadas();
}

export function guardarCampania() {
    const { nombre, fecha_inicio, fecha_fin, plantillas } = campaniaActual;
    if (!nombre?.trim() || !fecha_inicio || !fecha_fin)
        return mostrarToast({ title: "Completa los datos básicos", type: "error" });

    if (!plantillas.length)
        return mostrarToast({ title: "Agrega al menos una plantilla", type: "error" });

    calcularFechas();

    const errores = plantillas.flatMap((p, i) => {
        const idx = i + 1;
        const f = new Date(p.fecha_envio);
        const inicio = new Date(fecha_inicio);
        const fin = new Date(fecha_fin);
        const errs = [];
        if (isNaN(f)) errs.push(`Programación ${idx} sin fecha válida.`);
        if (f < inicio || f > fin) errs.push(`Programación ${idx} fuera del rango.`);
        return errs;
    });

    if (errores.length)
        return mostrarToast({ title: "Errores en campaña", message: errores.join("<br>"), type: "danger" });

    const formData = new FormData();
    formData.append("nombre", nombre);
    formData.append("descripcion", campaniaActual.descripcion || "");
    formData.append("fecha_inicio", fecha_inicio);
    formData.append("fecha_fin", fecha_fin);
    formData.append(
        "programaciones",
        JSON.stringify(
            plantillas.map(p => ({
                idplantilla: p.idplantilla,
                fecha_envio: p.fecha_envio
            }))
        )
    );

    api.post({
        source: "campanias",
        action: "crear",
        data: formData,
        onSuccess: () => {
            campaniaActual = { plantillas: [] };
            fetchCampanias();
            modalAjustes.destroy();
        }
    });
}

document.addEventListener("click", e => {
    const btn = e.target.closest("#btnDeseleccionarPlantilla");
    if (btn) eliminarPlantillaProgramacion(btn.dataset.id);
});