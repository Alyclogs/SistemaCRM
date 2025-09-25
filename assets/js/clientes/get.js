import api from "../utils/api.js";
import { formatearRangoFecha } from "../utils/date.js";

let clienteActual = document.getElementById("clienteActual").value;

function fetchHistorial() {
    if (!clienteActual) return;

    const container = document.getElementById('historialContainer');
    container.innerHTML = `<span>Cargando...</span>`;
    let html = '';

    api.get({
        source: "actividades",
        action: "listar",
        params: [{ name: "idcliente", value: clienteActual }],
        onSuccess: function (actividades) {
            if (actividades.length === 0) {
                container.innerHTML = `<span>No se encontraron actividades</span>`;
                return;
            }
            const actividadEstados = {
                realizado: { color: "#17c493ff", svg: "success" },
                pendiente: { color: "#ebaf2eff", svg: "clock" },
                vencido: { color: "#d63a47", svg: "error" },
                cancelado: { color: "#d63a47", svg: "error" }
            }
            const actividadTipos = {
                llamada: { svg: "telefono", bg: "#A6D6D6", text: "#65acacff" },
                videollamada: { svg: "video", bg: "#F7CFD8", text: "#c78491ff" },
                reunion: { svg: "reunion", bg: "#F4F8D3", text: "#c0c77fff" }
            }
            actividades.forEach((actividad, i) => {
                html += `
                    <div class="timeline-item">
                        <div class="timeline-point"></div>
                        <div class="actividad-container container-border info-row w-100 gap-3">
                            <div class="chip" style="background-color: ${actividadTipos[actividad.tipo].bg}; width: 52px; height: 52px;">
                                ${window.getIcon(actividadTipos[actividad.tipo].svg, actividadTipos[actividad.tipo].text, 26)}
                            </div>
                            <div class="d-flex flex-column">
                                <div class="info-row">
                                    ${window.getIcon(actividadEstados[actividad.estado.toLowerCase()].svg, actividadEstados[actividad.estado.toLowerCase()].color)}
                                    <h6 class="fw-bold">${actividad.nombre}</h6>
                                </div>
                                <div class="info-row gap-4">
                                    <div class="info-row">
                                        <span>${formatearRangoFecha(actividad.fecha, actividad.hora_inicio, actividad.hora_fin)}</span>
                                        <span>•</span>
                                        <span>${actividad.usuario}</span>
                                    </div>
                                    <div class="info-row ms-3">${window.icons.user}<span>${actividad.cliente}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    fetchHistorial();
});