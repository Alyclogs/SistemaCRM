import CalendarUI from "./calendar.js";
import { formatearFecha, formatearHora, formatearRangoFecha, formatEventDate, sumarMinutos } from "./date.js";
import { mostrarToast } from "./utils.js";
import api from "./api.js";

const baseurl = 'http://localhost/SistemaCRM/';
const calendarUI = new CalendarUI();
var calendar = null;
var miniCalendar = null;
var activeEvent = null;
var actividadActual = {};
var actividadesCache = new Map();
const labels = {
    llamada: "Llamada",
    videollamada: "Videollamada",
    reunion: "Reunión"
};
const usuarioActual = document.getElementById('idUsuario').value;

function fetchActividades(idusuario = usuarioActual) {
    api.get({
        source: "actividades",
        action: "listar",
        params: [{ name: "idusuario", value: idusuario }],
        onSuccess: function (actividades) {
            calendar.getEvents().forEach(ev => {
                if (!ev.extendedProps.preview) ev.remove()
            });

            if (actividades.length === 0) {
                return;
            }

            actividades.forEach(act => {
                actividadesCache.set(`${act.idactividad}`, act);

                const start = `${act.fecha}T${act.hora_inicio}`;
                const end = `${act.fecha}T${act.hora_fin}`;

                calendar.addEvent({
                    id: act.idactividad,
                    title: act.nombre,
                    start: start,
                    end: end
                });
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    calendar = calendarUI.buildCalendar(calendarEl);

    calendar.render();
    fetchActividades();

    calendar.setOption("dateClick", function (info) {
        let start = new Date(info.date);

        // Redondear minutos a múltiplos de 30
        let minutes = start.getMinutes();
        start.setMinutes(minutes < 30 ? 0 : 30, 0, 0);

        let end = new Date(start.getTime() + 30 * 60 * 1000);

        calendar.getEvents().forEach(ev => {
            if (ev.extendedProps.preview) ev.remove();
        });
        calendar.addEvent({
            title: "Llamada",
            start,
            end,
            extendedProps: { preview: true }
        });
    });

    calendar.setOption("eventDidMount", function (info) {
        if (info.event.extendedProps.preview) {
            activeEvent = info.event;
            const popup = document.getElementById('popupPreview');
            const infoDate = popup.querySelector('#infoDate');
            const titleInput = popup.querySelector('#titleInput');

            // Posicionar popup al costado del evento
            var rect = info.el.getBoundingClientRect();
            popup.style.top = (rect.top + window.scrollY) + "px";
            popup.style.left = (rect.right + window.scrollX + 10) + "px";
            popup.style.display = "block";

            infoDate.textContent = formatEventDate(activeEvent.start, activeEvent.end);

            // Si el input ya tiene valor, usarlo como título del evento
            if (titleInput.value.trim() !== "") {
                activeEvent.setProp("title", titleInput.value.trim());
            } else {
                titleInput.placeholder = "Llamada";
            }

            // Mantener sincronía en tiempo real
            titleInput.oninput = function () {
                actividadActual.nombre = titleInput.value;
                activeEvent.setProp("title", titleInput.value);
                if (titleInput.value.length === 0) {
                    titleInput.value = labels[actividadActual.tipo];
                }
            };

            actividadActual = {
                nombre: activeEvent.title,
                fecha: formatearFecha(activeEvent.start),
                hora_inicio: formatearHora(activeEvent.start),
                hora_fin: formatearHora(activeEvent.end),
                tipo: "llamada"
            }
        }
    });

    calendar.setOption("eventResize", function (info) {
        if (info.event.extendedProps.preview) {
            const popup = document.getElementById('popupPreview');
            const infoDate = popup.querySelector('#infoDate');

            if (activeEvent && info.event.id === activeEvent.id) {
                infoDate.textContent = formatEventDate(info.event.start, info.event.end);
            } else {
                popup.style.display = "none";
                actividadActual.hora_inicio = formatearHora(info.event.start);
                actividadActual.hora_fin = formatearHora(info.event.end);
            }
        } else {
            actualizarActividad(info.event.id, formatearFecha(info.event.start), formatearHora(info.event.start), formatearHora(info.event.end));
        }
    });

    calendar.setOption("eventDrop", function (info) {
        if (info.event.extendedProps.preview) {
            const popup = document.getElementById('popupPreview');
            const infoDate = popup.querySelector('#infoDate');

            if (activeEvent && info.event.id === activeEvent.id) {
                infoDate.textContent = formatEventDate(info.event.start, info.event.end);
            } else {
                popup.style.display = "none";
                actividadActual.fecha = formatearFecha(info.event.start);
                actividadActual.hora_inicio = formatearHora(info.event.start);
                actividadActual.hora_fin = formatearHora(info.event.end);
            }
        } else {
            actualizarActividad(info.event.id, formatearFecha(info.event.start), formatearHora(info.event.start), formatearHora(info.event.end));
        }
    });

    calendar.setOption("eventClick", function (info) {
        if (!info.event.extendedProps.preview) {
            const actividad = actividadesCache.get(info.event.id);
            if (!actividad) return;

            const popup = document.getElementById('popupActividad');

            const icons = {
                "llamada": window.icons.telefono,
                "videollamada": window.icons.video,
                "reunion": window.icons.video,
            }

            let html = `
            <div class="info-row">
                ${icons[actividad.tipo]}
                <h5>${actividad.usuario} / ${actividad.nombre}</h5>
            </div>
            <div class="mb-2">
                <span class="text-small">${formatearRangoFecha(actividad.fecha, actividad.hora_inicio, actividad.hora_fin)}</span>
            </div>
            <div class="mb-3">
                <div class="info-row">
                ${window.icons.user} <span>${actividad.cliente}</span>
                </div>
                <div class="info-row">
                    ${actividad.notas?.length > 0 ? `${window.icons.document} <span>${actividad.notas[0].contenido}</span>` : ''}
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                <button class="btn-outline" id="btnDetallesActividad" data-id="${actividad.idactividad}">Editar</button>
                <button class="btn-default bg-text-danger" id="btnEliminarActividad">Eliminar</button>
            </div>
        `;

            popup.innerHTML = html;

            const rect = info.el.getBoundingClientRect();
            popup.style.top = (rect.top + window.scrollY) + "px";
            popup.style.left = (rect.right + window.scrollX + 10) + "px";
            popup.style.display = 'block';

            let hideTimeout;

            function scheduleHide() {
                hideTimeout = setTimeout(() => {
                    popup.style.display = "none";
                }, 300);
            }

            function cancelHide() {
                clearTimeout(hideTimeout);
            }

            info.el.addEventListener("mouseleave", scheduleHide);
            info.el.addEventListener("mouseenter", cancelHide);

            popup.addEventListener("mouseleave", scheduleHide);
            popup.addEventListener("mouseenter", cancelHide);
        }
    });
});

function abrirFormActividad(actividad = {}) {
    let url = baseurl + "views/components/actividades/formActividad.php";
    if (actividad.idactividad) {
        url += "?id=" + actividad.idactividad;
    }

    fetch(url)
        .then(res => res.text())
        .then(html => {
            $("#actividadModalBody").html(html);
            $("#actividadModal").modal('show');

            // combinar actividad con defaults
            actividadActual = {
                nombre: actividadActual?.nombre ?? "Llamada",
                tipo: actividadActual?.tipo ?? "llamada",
                fecha: actividadActual?.fecha ?? formatearFecha(new Date()),
                hora_inicio: actividadActual?.hora_inicio ?? formatearHora(new Date()),
                hora_fin: actividadActual?.hora_fin ?? sumarMinutos(formatearHora(new Date()), 30)
            };

            $("#tituloActividadLabel").text(actividadActual.nombre);

            //const quill = new Quill("#notaEditor", { theme: "snow" });

            const modal = document.getElementById("actividadModal");
            const fechaInput = modal.querySelector("#fechaInput");
            const horaInicioInput = modal.querySelector("#horaInicioInput");
            const horaFinInput = modal.querySelector("#horaFinInput");
            const titleInput = modal.querySelector("#titleInput");

            setTimeout(() => {
                miniCalendar = calendarUI.buildCalendarCustom(
                    document.getElementById("miniCalendar"),
                    {
                        initialDate: actividadActual.fecha,
                        scrollTime: actividadActual.hora_inicio,
                        initialView: "timeGridDay"
                    }
                );

                miniCalendar.setOption("eventResize", function (info) {
                    actividadActual.hora_inicio = formatearHora(info.event.start);
                    actividadActual.hora_fin = formatearHora(info.event.end);
                    horaInicioInput.value = actividadActual.hora_inicio.slice(0, 5);
                    horaFinInput.value = actividadActual.hora_fin.slice(0, 5);
                });

                miniCalendar.setOption("eventDrop", function (info) {
                    actividadActual.hora_inicio = formatearHora(info.event.start);
                    actividadActual.hora_fin = formatearHora(info.event.end);
                    horaInicioInput.value = actividadActual.hora_inicio.slice(0, 5);
                    horaFinInput.value = actividadActual.hora_fin.slice(0, 5);
                });

                miniCalendar.render();

                activeEvent = miniCalendar.addEvent({
                    title: actividadActual.nombre,
                    start: actividadActual.fecha + "T" + actividadActual.hora_inicio,
                    end: actividadActual.fecha + "T" + actividadActual.hora_fin,
                    extendedProps: { preview: true }
                });
            }, 500);

            // seleccionar tipo
            const button = modal.querySelector(
                `.btn-actividad[data-type="${actividadActual.tipo}"]`
            );
            if (button) button.classList.add("selected");

            // inputs iniciales
            titleInput.value = actividadActual.nombre;
            fechaInput.value = actividadActual.fecha;
            horaInicioInput.value = actividadActual.hora_inicio.slice(0, 5);
            horaFinInput.value = actividadActual.hora_fin.slice(0, 5);

            // eventos
            titleInput.addEventListener("input", function () {
                actividadActual.nombre = titleInput.value;
                activeEvent.setProp("title", titleInput.value);
                if (titleInput.value.length === 0) {
                    titleInput.value = labels[actividadActual.tipo];
                }
            });

            fechaInput.addEventListener("change", function () {
                actividadActual.fecha = fechaInput.value;
                miniCalendar.gotoDate(actividadActual.fecha);
                const startDate = new Date(`${actividadActual.fecha}T${horaInicioInput.value}`);
                actividadActual.hora_inicio = horaInicioInput.value;
                activeEvent.setStart(startDate);
                const endDate = new Date(`${actividadActual.fecha}T${horaFinInput.value}`);
                actividadActual.hora_fin = horaFinInput.value;
                activeEvent.setEnd(endDate);
            });

            horaInicioInput.addEventListener("change", function () {
                const startDate = new Date(actividadActual.fecha + "T" + horaInicioInput.value);
                actividadActual.hora_inicio = horaInicioInput.value;
                activeEvent.setStart(startDate);
            });

            horaFinInput.addEventListener("change", function () {
                const endDate = new Date(actividadActual.fecha + "T" + horaFinInput.value);
                actividadActual.hora_fin = horaFinInput.value;
                activeEvent.setEnd(endDate);
            });
        })
        .catch(e => {
            mostrarToast({
                message: "Ocurrió un error al mostrar el formulario",
                type: "danger"
            });
            console.error(e);
        });
}

function guardarActividad() {
    const formActividad = document.getElementById("formActividad");
    const modal = $("#actividadModal");

    if (formActividad.checkValidity()) {
        const formData = new FormData(formActividad);
        const action = formData.get("idactividad") ? "actualizar" : "crear";

        formData.append('idusuario', usuarioActual);
        formData.append('tipo', actividadActual.tipo || modal.find('.btn-actividad.selected').data('type'));
        formData.append('fecha', actividadActual.fecha);

        api.post({
            source: "actividades",
            action,
            data: formData,
            onSuccess: function () {
                activeEvent = null;
                actividadActual = {};
                modal.modal('hide');
                fetchActividades();
            }
        });
    } else {
        formActividad.reportValidity();
    }
}

function actualizarActividad(idactividad, fecha, horaInicio, horaFin) {
    const actividad = actividadesCache.get(idactividad);
    if (!actividad) return;

    const payload = {
        idactividad: actividad.idactividad,
        nombre: actividad.nombre,
        fecha,
        hora_inicio: horaInicio,
        hora_fin: horaFin,
        idusuario: actividad.idusuario,
        idcliente: actividad.idcliente,
        tipo: actividad.tipo
    };

    const formData = new FormData();
    Object.entries(payload).forEach(([key, value]) => formData.append(key, value));

    api.post({
        source: "actividades",
        action: "actualizar",
        data: formData,
        onSuccess: fetchActividades
    });
}

document.addEventListener('click', function (e) {
    if (e.target.closest("#btnRefresh")) {
        actividadActual = {};
        activeEvent = null;
        const usuarioActualEl = document.querySelector(`.filtro-item[data-id="${usuarioActual}"]`);
        document.querySelectorAll('.filtro-item').forEach(el => el.classList.remove('selected'));
        document.querySelector('.selected-filtro').textContent = usuarioActualEl.dataset.value;
        const resultados = usuarioActualEl.closest('.busqueda-grupo').querySelector('.resultados-busqueda');
        usuarioActualEl.classList.add('selected');
        resultados.style.display = "none";
        fetchActividades();
    }

    if (e.target.closest('.filtro-item')) {
        const target = e.target.closest('.filtro-item');
        const idusuario = target.dataset.id;
        const nombreUsuario = target.dataset.value;
        const grupo = target.closest('.busqueda-grupo');
        const selected = grupo.querySelector('.selected-filtro');
        const resultados = grupo.querySelector('.resultados-busqueda');
        grupo.querySelectorAll('.filtro-item').forEach(el => el.classList.remove('selected'));
        target.classList.add('selected');
        selected.textContent = nombreUsuario;
        resultados.style.display = 'none';
        fetchActividades(idusuario);
    }

    if (e.target.closest('.btn-actividad')) {
        const button = e.target.closest('.btn-actividad');
        const esPopup = !!e.target.closest('.popup');
        const source = e.target.closest(esPopup ? '.popup' : '#actividadModal');
        const buttons = source.querySelector('.buttons-actividad');
        const actividad = button.dataset.type;

        buttons.querySelectorAll('button').forEach(btn => btn.classList.remove('selected'));
        button.classList.add('selected');
        actividadActual.tipo = actividad;

        const titleInput = source.querySelector('#titleInput');
        const defaultActividades = ["Llamada", "Videollamada", "Reunión"];

        if (labels[actividad]) {
            titleInput.placeholder = labels[actividad];
            if (source.id === 'actividadModal') {
                source.querySelector('#tituloActividadLabel').textContent = labels[actividad];
            }
            if (defaultActividades.includes(activeEvent.title)) {
                activeEvent.setProp("title", labels[actividad]);
            }
        }
    }

    if (e.target.closest('#btnNuevaActividad')) {
        document.querySelector('#popupPreview').style.display = 'none';
        actividadActual = {};
        activeEvent = null;
        abrirFormActividad();
    }

    if (e.target.closest('#btnDetallesActividad')) {
        const idactividad = e.target.closest('#btnDetallesActividad').dataset.id;
        actividadActual = actividadesCache.get(idactividad);
        e.target.closest('.popup').style.display = 'none';
        abrirFormActividad(actividadActual);
    }

    if (e.target.closest('.cliente-item')) {
        const target = e.target.closest('.cliente-item');
        const value = target.dataset.value;
        const id = target.dataset.id;
        const grupo = target.closest('.busqueda-grupo');
        const resultados = grupo.querySelector('.resultados-busqueda');
        const input = grupo.querySelector(`input[id="${resultados.dataset.parent}"]`);
        const hidden = grupo.querySelector(`input[name="idcliente"]`);
        input.value = value;
        hidden.value = id;
        resultados.innerHTML = '';
        resultados.style.display = 'none';
    }

    if (e.target.closest('.usuario-item')) {
        const target = e.target.closest('.usuario-item');
        const value = target.dataset.value;
        const id = target.dataset.id;
        const grupo = target.closest('.busqueda-grupo');
        const resultados = grupo.querySelector('.resultados-busqueda');
        const input = grupo.querySelector(`input[id="${resultados.dataset.parent}"]`);
        const hidden = grupo.querySelector(`input[name="idusuario"]`);
        input.value = value;
        hidden.value = id;
        resultados.innerHTML = '';
        resultados.style.display = 'none';
    }

    if (e.target.closest('#btnGuardarActividad')) {
        guardarActividad();
    }
});

document.addEventListener('input', function (e) {
    if (e.target.id === 'clienteInput') {
        const input = e.target;
        const value = input.value.trim();
        const resultados = input.closest('.busqueda-grupo').querySelector('.resultados-busqueda');

        if (value.length > 2) {
            api.get({
                source: "clientes",
                action: "buscar",
                params: [
                    { name: "filtro", value },
                    { name: "tipo", value: 1 }
                ],
                onSuccess: function (clientes) {
                    const buttonNewCliente = `
                    <div class="resultado-item bg-secondary text-white" id="btnNuevoCliente" data-value="${value}">
                        ${window.getIcon("add", "white")} <span>Agregar "${value}" como nuevo cliente</span>
                    </div>`;

                    let html = '';

                    if (clientes.length > 0) {
                        clientes.forEach(cliente => {
                            html += `
                            <div class="resultado-item cliente-item" 
                                data-id="${cliente.idcliente}" 
                                data-value="${cliente.nombres} ${cliente.apellidos}">
                                <div class="d-flex flex-column gap-2 w-100">
                                    <div class="d-flex align-items-center gap-2">
                                        <img class="user-icon sm" src="${baseurl + cliente.foto}" alt="Foto de ${cliente.nombres} ${cliente.apellidos}">
                                        <span>${cliente.nombres} ${cliente.apellidos}</span>
                                    </div>
                                    <div class="row w-100" style="font-size: 12px">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center gap-1">
                                                ${window.icons.telefono} <span>${cliente.telefono}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center gap-1">
                                                ${window.icons.building} <span>${cliente.empresa}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        });
                        const existeSimilar = clientes.some(cliente =>
                            (`${cliente.nombres} ${cliente.apellidos}`).toLowerCase().includes(value.toLowerCase().trim())
                            || cliente.dni.trim().toLowerCase().includes(value.toLowerCase().trim())
                        );

                        if (!existeSimilar) {
                            html += buttonNewCliente;
                        }
                    } else {
                        // si no hay clientes → solo botón
                        html = buttonNewCliente;
                    }

                    resultados.innerHTML = html;
                    resultados.style.display = 'flex';
                }
            });
        } else {
            resultados.innerHTML = '';
            resultados.style.display = 'none';
        }
    }

    if (e.target.id === 'usuarioInput') {
        const input = e.target;
        const value = input.value;
        const resultados = input.closest('.busqueda-grupo').querySelector('.resultados-busqueda');

        if (value.length > 2) {
            api.get({
                source: "usuarios",
                action: "buscar",
                params: [
                    { name: "filtro", value }
                ],
                onSuccess: function (clientes) {

                    if (clientes.length === 0) {
                        resultados.innerHTML = `<div class="resultado-item not-found">No se encontraron resultados</div>`;
                        resultados.style.display = 'flex';
                        return;
                    }

                    let html = '';

                    clientes.forEach(cliente => {
                        html += `
                            <div class="resultado-item usuario-item" 
                                data-id="${cliente.idcliente}" 
                                data-value="${cliente.nombres} ${cliente.apellidos}">
                                <img class="user-icon sm" src="${baseurl + cliente.foto}" alt="Foto de ${cliente.nombres} ${cliente.apellidos}">
                                <span>${cliente.nombres} ${cliente.apellidos}</span>
                            </div>`;
                    });

                    if (!clientes.some(cliente =>
                        cliente.nombres.toLowerCase().trim() === value.toLowerCase().trim()
                        || cliente.apellidos.toLowerCase().trim() === value.toLowerCase().trim()
                        || cliente.dni.trim() === value.toLowerCase().trim()
                    )) {
                        html += buttonNewCliente;
                    }

                    resultados.innerHTML = html;
                    resultados.style.display = 'flex';
                }
            });
        } else {
            resultados.innerHTML = '';
            resultados.style.display = 'none';
        }
    }
});