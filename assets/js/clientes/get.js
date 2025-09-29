import api from "../utils/api.js";
import { formatearDateTime, formatearRangoFecha } from "../utils/date.js";
import { abrirModal, guardarRegistro } from "./utils.js";

let clienteActual = document.getElementById("clienteActual").value;
let tipoCliente = document.getElementById("tipoCliente").value;
let selectedFiltro = '';

export function fetchHistorial() {
    if (!clienteActual || !tipoCliente) return;

    const container = document.getElementById('historialContainer');
    container.innerHTML = `<span>Cargando...</span>`;
    let html = '';

    api.get({
        source: "clientes",
        action: "obtenerHistorial",
        params: [
            { name: "id", value: clienteActual },
            { name: "tipo", value: tipoCliente }
        ],
        onSuccess: function (items) {
            const actividades = (items.actividades || []).map(x => ({ ...x, _tipo: "actividad" }));
            const notas = (items.notas || []).map(x => ({ ...x, _tipo: "nota" }));
            const whatsapps = (items.whatsapps || []).map(x => ({ ...x, _tipo: "whatsapp" }));
            const correos = (items.correos || []).map(x => ({ ...x, _tipo: "correo" }));
            const archivos = (items.archivos || []).map(x => ({ ...x, _tipo: "archivo" }));
            const cambios = (items.cambios || []).map(x => ({ ...x, _tipo: "cambio" }));

            const todos = [...actividades, ...notas, ...whatsapps, ...correos, ...archivos, ...cambios];
            todos.sort((a, b) => new Date(b.fecha_creacion || b.fecha || b.fecha_envio) - new Date(a.fecha_creacion || a.fecha || a.fecha_envio));

            const itemsFiltrados = selectedFiltro === 'todas' ? todos :
                selectedFiltro === 'actividad' ? actividades :
                    selectedFiltro === 'nota' ? notas :
                        selectedFiltro === 'whatsapp' ? whatsapps :
                            selectedFiltro === 'correo' ? correos :
                                selectedFiltro === 'archivo' ? archivos :
                                    selectedFiltro === 'cambios' ? cambios : [];

            if (itemsFiltrados.length === 0) {
                container.innerHTML = `<span>No se encontraron registros</span>`;
                return;
            }

            itemsFiltrados.forEach(item => {
                switch (item._tipo) {
                    case "actividad": html += renderActividad(item); break;
                    case "nota": html += renderNota(item); break;
                    case "whatsapp": html += renderWhatsapp(item); break;
                    case "correo": html += renderCorreo(item); break;
                    case "archivo": html += renderArchivo(item); break;
                    case "cambio": html += renderCambio(item); break;
                }
            });

            container.innerHTML = html;
        }
    });
}

function renderActividad(actividad) {
    const actividadEstados = {
        realizado: { color: "#17c493ff", svg: "success" },
        pendiente: { color: "#ebaf2eff", svg: "clock" },
        vencido: { color: "#d63a47", svg: "error" }
    };
    const actividadTipos = {
        llamada: { svg: "telefono", bg: "#A6D6D6", text: "#65acacff" },
        videollamada: { svg: "video", bg: "#F7CFD8", text: "#c78491ff" },
        reunion: { svg: "reunion", bg: "#F4F8D3", text: "#c0c77fff" }
    };

    return `
        <div class="timeline-item">
            <div class="timeline-point"></div>
            <div class="actividad-container timeline-content">
                <div class="chip" style="background-color: ${actividadTipos[actividad.tipo]?.bg}; width: 52px; height: 52px;">
                    ${window.getIcon(actividadTipos[actividad.tipo]?.svg, actividadTipos[actividad.tipo]?.text, 26)}
                </div>
                <div class="d-flex flex-column">
                    <div class="info-row">
                        ${window.getIcon(actividadEstados[actividad.estado.toLowerCase()]?.svg, actividadEstados[actividad.estado.toLowerCase()]?.color)}
                        <span class="fw-bold">${actividad.nombre}</span>
                    </div>
                    <div class="info-row gap-4 text-muted text-sm">
                        <div class="info-row">
                            <span>${formatearRangoFecha(actividad.fecha, actividad.hora_inicio, actividad.hora_fin)}</span>
                            <span>•</span>
                            <span>${actividad.usuario}</span>
                        </div>
                        <div class="info-row ms-3">${window.icons.user}<span>${actividad.cliente}</span></div>
                    </div>
                </div>
            </div>
        </div>`;
}

function renderNota(nota) {
    return `
        <div class="timeline-item">
            <div class="timeline-point"></div>
            <div class="nota-container timeline-content">
                ${window.icons.document}
                <div class="d-flex flex-column">
                    <div class="info-row mb-2 text-muted text-sm">
                        <span>${formatearDateTime(nota.fecha_creacion)}</span>
                        <span>•</span>
                        <span>${nota.usuario}</span>
                    </div>
                    <div class="nota-texto" style="white-space: pre-wrap;">${nota.contenido}</div>
                </div>
            </div>
        </div>`;
}

function renderWhatsapp(whatsapp) {
    return `
        <div class="timeline-item">
            <div class="timeline-point"></div>
            <div class="whatsapp-container timeline-content">
                <div class="chip bg-success" style="width: 52px; height: 52px;">
                    ${window.icons.telefono}
                </div>
                <div class="d-flex flex-column">
                    <div class="info-row mb-2 text-muted text-sm">
                        <span>${formatearDateTime(whatsapp.fecha_envio)}</span>
                        <span>•</span>
                        <span>${whatsapp.usuario}</span>
                    </div>
                    <div>Mensaje enviado automáticamente</div>
                </div>
            </div>
        </div>`;
}

function renderCorreo(correo) {
    return `
        <div class="timeline-item">
            <div class="timeline-point"></div>
            <div class="correo-container timeline-content">
                <div class="chip bg-success" style="width: 52px; height: 52px;">
                    ${window.icons.correo}
                </div>
                <div class="d-flex flex-column">
                    <div class="info-row mb-2 text-muted text-sm">
                        <span>${formatearDateTime(correo.fecha_envio)}</span>
                        <span>•</span>
                        <span>${correo.usuario}</span>
                    </div>
                    <div>Correo enviado automáticamente</div>
                </div>
            </div>
        </div>`;
}

function renderArchivo(archivo) {
    return `
        <div class="timeline-item">
            <div class="timeline-point"></div>
            <div class="archivo-container timeline-content">
                <div class="chip bg-info" style="width: 52px; height: 52px;">
                    ${window.icons.paperclip}
                </div>
                <div class="d-flex flex-column">
                    <div class="info-row mb-2 text-muted text-sm">
                        <span>${formatearDateTime(archivo.fecha_creacion)}</span>
                        <span>•</span>
                        <span>${archivo.usuario}</span>
                    </div>
                    <div>Archivo subido. <a href="${archivo.ruta}" target="_blank">Ver</a></div>
                </div>
            </div>
        </div>`;
}

function renderCambio(cambio) {
    return `
        <div class="timeline-item">
            <div class="timeline-point"></div>
            <div class="cambio-container timeline-content">
                <div class="d-flex flex-column">
                    <div class="mb-1">${cambio.descripcion}</div>
                    <div class="info-row text-muted text-sm">
                        <span>${formatearDateTime(cambio.fecha)}</span>
                        <span>•</span>
                        <span>${cambio.usuario}</span>
                    </div>
                </div>
            </div>
        </div>`;
}

document.addEventListener('click', function (e) {
    if (e.target.closest(".usuario-item")) {
        const target = e.target.closest(".usuario-item");
        const idusuario = target.dataset.id;
        const formData = new FormData();
        formData.append("tipo", tipoCliente === "cliente" ? 1 : 2);
        formData.append("idusuario", idusuario);
        formData.append("idcliente", clienteActual);

        api.post({
            source: "clientes",
            action: "asignarUsuario",
            data: formData,
            onSuccess: () => {
                setTimeout(() => window.location.reload(), 1000);
            }
        });
    }

    if (e.target.closest('.filtro-historial-item')) {
        document.querySelectorAll('.filtro-historial-item').forEach(item => item.classList.remove('selected'));
        const item = e.target.closest('.filtro-historial-item');
        item.classList.add('selected');
        selectedFiltro = item.dataset.value;
        fetchHistorial();
    }

    if (e.target.closest('#btnGuardarCliente')) {
        guardarRegistro(1, () => {
            fetchHistorial();
        });
    }
});

document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener("click", async function () {
        const { type, id } = btn.dataset;
        abrirModal({ id, esNuevo: false, tipo: type === "cliente" ? 1 : 2 });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    selectedFiltro = document.querySelector(".filtro-historial-item.selected").dataset.value;
    fetchHistorial();
});

document.addEventListener("entidadActualizada", function () {
    fetchHistorial();
});