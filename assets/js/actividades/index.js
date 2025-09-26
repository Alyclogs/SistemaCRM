import CalendarUI from "../utils/calendar.js";
import { formatearFecha, formatearHora, formatearRangoFecha, formatEventDate, generarIntervalosHoras, sumarMinutos } from "../utils/date.js";
import { mostrarToast } from "../utils/utils.js";
import api from "../utils/api.js";

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
const nombreUsuarioActual = document.getElementById('nombreUsuario').value;
var selectedUsuario = usuarioActual;
var selectedNombreUsuario = nombreUsuarioActual;

function fetchActividades(idusuario = usuarioActual) {
    document.querySelectorAll('.popup').forEach(el => el.style.display = "none");
    if (activeEvent) {
        activeEvent.remove();
        activeEvent = null;
    }

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
                    end: end,
                    classNames: ["evento-existente"]
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

        let minutes = start.getMinutes();
        start.setMinutes(minutes < 30 ? 0 : 30, 0, 0);

        let end = new Date(start.getTime() + 30 * 60 * 1000);

        if (activeEvent) activeEvent.remove();
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

            mostrarPopup(info.el, popup);
            infoDate.textContent = formatEventDate(activeEvent.start, activeEvent.end);

            if (titleInput.value.trim() !== "") {
                activeEvent.setProp("title", titleInput.value.trim());
            } else {
                titleInput.placeholder = "Llamada";
            }

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
                tipo: "llamada",
                idestado: 1,
                clientes: []
            };
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
            actualizarActividad(
                info.event.id,
                formatearFecha(info.event.start),
                formatearHora(info.event.start),
                formatearHora(info.event.end)
            );
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
            const popup = document.getElementById('popupActualizar');
            popup.innerHTML = `
            <span class="mb-3">¿Actualizar actividad?</span>
            <div class="d-flex align-items-center justify-content-end gap-2">
                <button class="btn btn-outline" id="btnCancelarActividad">Cancelar</button>
                <button class="btn btn-default" id="btnActualizarActividad">Actualizar</button>
            </div>
            `;

            mostrarPopupMouse(info.jsEvent, popup);

            popup.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', function () {
                    popup.style.display = 'none';
                    if (btn.id === 'btnCancelarActividad') {
                        fetchActividades();
                    }
                    if (btn.id === 'btnActualizarActividad') {
                        actualizarActividad(
                            info.event.id,
                            formatearFecha(info.event.start),
                            formatearHora(info.event.start),
                            formatearHora(info.event.end)
                        );
                    }
                });
            });
        }
    });

    calendar.setOption("eventClick", function (info) {
        if (info.event.extendedProps.preview) return;

        const actividad = actividadesCache.get(info.event.id);
        if (!actividad) return;

        const popup = document.getElementById('popupActividad');

        const icons = {
            "llamada": "telefono",
            "videollamada": "video",
            "reunion": "profile-2user",
        }

        let html = `
            <div class="info-row">
                ${window.getIcon(icons[actividad.tipo], null, 26)}
                <h6>${actividad.usuario} / ${actividad.nombre}</h6>
            </div>
            <div class="mb-2">
                <span class="text-small text-muted">${formatearRangoFecha(actividad.fecha, actividad.hora_inicio, actividad.hora_fin)}</span>
            </div>
            <div class="mb-3">
                <div class="info-row">
                    ${actividad.cliente ? `${window.icons.user} <span>${actividad.cliente}</span>` : ''}
                </div>
                <div class="info-row">
                    ${actividad.extra.descripcion ? `${window.icons.info} <span>${actividad.extra.descripcion}</span>` : ''}
                </div>
                <div class="info-row">
                    ${actividad.notas?.length > 0 ? `${window.icons.document} <span>${actividad.notas[0].contenido}</span>` : ''}
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                <button class="btn btn-outline" id="btnDetallesActividad" data-id="${actividad.idactividad}">Editar</button>
                <button class="btn btn-default bg-text-danger" id="btnEliminarActividad" data-id="${actividad.idactividad}">Eliminar</button>
            </div>
        `;

        popup.innerHTML = html;
        mostrarPopup(info.el, popup);

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
    });
});

function mostrarPopup(el, popup) {
    const margin = 10;

    const rect = el.getBoundingClientRect();
    const popupRect = popup.getBoundingClientRect();

    let top = rect.top + window.scrollY;
    let left = rect.right + window.scrollX + margin;

    if (left + popupRect.width > window.innerWidth + window.scrollX) {
        left = rect.left + window.scrollX - popupRect.width - margin;
    }
    if (top + popupRect.height > window.innerHeight + window.scrollY) {
        top = rect.bottom + window.scrollY - popupRect.height;
    }
    if (top < window.scrollY) {
        top = rect.top + window.scrollY + margin;
    }

    popup.style.top = top + "px";
    popup.style.left = left + "px";
    popup.style.display = "block";
}

function mostrarPopupMouse(jsEvent, popup) {
    const margin = 10;

    popup.style.visibility = "hidden";
    popup.style.display = "block";

    const popupRect = popup.getBoundingClientRect();

    let left = jsEvent.clientX + window.scrollX + margin;
    let top = jsEvent.clientY + window.scrollY + margin;

    if (left + popupRect.width > window.innerWidth + window.scrollX) {
        left = jsEvent.clientX + window.scrollX - popupRect.width - margin;
    }
    if (top + popupRect.height > window.innerHeight + window.scrollY) {
        top = jsEvent.clientY + window.scrollY - popupRect.height - margin;
    }

    popup.style.left = left + "px";
    popup.style.top = top + "px";
    popup.style.visibility = "visible";
}

function abrirFormActividad(actividad = {}) {
    let url = window.baseurl + "views/components/actividades/formActividad.php";
    if (actividad.idactividad) {
        url += "?id=" + actividad.idactividad;
    }

    fetch(url)
        .then(res => res.text())
        .then(html => {
            $("#actividadModalBody").html(html);
            $("#actividadModal").modal('show');

            actividadActual = {
                idactividad: actividadActual?.idactividad ?? null,
                idusuario: actividadActual?.idusuario ?? selectedUsuario,
                usuario: actividadActual?.usuario ?? selectedNombreUsuario,
                idcliente: actividadActual?.idcliente ?? null,
                nombre: actividadActual?.nombre ?? "Llamada",
                tipo: actividadActual?.tipo ?? "llamada",
                fecha: actividadActual?.fecha ?? formatearFecha(new Date()),
                hora_inicio: actividadActual?.hora_inicio ?? formatearHora(new Date()),
                hora_fin: actividadActual?.hora_fin ?? sumarMinutos(formatearHora(new Date()), 30),
                clientes: actividadActual?.clientes ?? []
            };

            $("#tituloActividadLabel").text(actividadActual.nombre);

            const modal = document.getElementById("actividadModal");
            const fechaInput = modal.querySelector("#fechaInput");
            const horaInicioInput = modal.querySelector("#horaInicioInput");
            const horaFinInput = modal.querySelector("#horaFinInput");
            const titleInput = modal.querySelector("#titleInput");
            const usuarioInput = modal.querySelector("#usuarioInput");
            const idUsuarioInput = modal.querySelector("#idUsuarioInput");
            const selectorHoraInicio = modal.querySelector(`.resultados-busqueda[data-parent="${horaInicioInput.id}"]`);
            const selectorHoraFin = modal.querySelector(`.resultados-busqueda[data-parent="${horaFinInput.id}"]`);

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
                    id: actividadActual.idactividad,
                    title: actividadActual.nombre,
                    start: actividadActual.fecha + "T" + actividadActual.hora_inicio,
                    end: actividadActual.fecha + "T" + actividadActual.hora_fin,
                    extendedProps: { preview: true, mini: true }
                });

                calendar.getEvents().forEach((event) => {
                    if (event.id !== activeEvent.id
                        && (!actividadActual.idactividad
                            || actividadesCache.get(activeEvent.id)?.idusuario === actividadActual?.idusuario)) {
                        miniCalendar.addEvent(event);
                    }
                });
                generarIntervalosHoras(calendar, selectorHoraInicio);
                generarIntervalosHoras(calendar, selectorHoraFin);

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
            idUsuarioInput.value = actividadActual.idusuario;
            usuarioInput.value = actividadActual.usuario;

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

            horaInicioInput.addEventListener("click", function () {
                selectorHoraInicio.style.display = 'flex';

                selectorHoraInicio.querySelectorAll('.resultado-item').forEach(item => {
                    item.addEventListener('click', function () {
                        horaInicioInput.value = this.dataset.value.slice(0, 5);

                        const startDate = new Date(actividadActual.fecha + "T" + horaInicioInput.value);
                        actividadActual.hora_inicio = horaInicioInput.value;
                        activeEvent.setStart(startDate);

                        selectorHoraInicio.style.display = 'none';
                    });
                });
            });

            horaFinInput.addEventListener("click", function () {
                selectorHoraFin.style.display = 'flex';

                selectorHoraFin.querySelectorAll('.resultado-item').forEach(item => {
                    item.addEventListener('click', function () {
                        horaFinInput.value = this.dataset.value.slice(0, 5);

                        const endDate = new Date(actividadActual.fecha + "T" + horaFinInput.value);
                        actividadActual.hora_fin = horaFinInput.value;
                        activeEvent.setEnd(endDate);

                        selectorHoraFin.style.display = 'none';
                    });
                });
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

    if (formActividad && !formActividad.checkValidity()) {
        formActividad.reportValidity();
        return;
    }

    const datos = { ...actividadActual };
    if (formActividad) {
        Object.assign(datos, Object.fromEntries(new FormData(formActividad).entries()));
    }

    const extra = {};
    if (formActividad) {
        formActividad.querySelectorAll(".extra-content").forEach(field => {
            if (field.value?.trim() !== "") {
                extra[field.name] = field.value.trim();
            }
        });
    }

    const formData = new FormData();

    if (datos.clientes) {
        formData.append("clientes", JSON.stringify(datos.clientes));
    }
    Object.entries(datos).forEach(([key, value]) => {
        if (key !== "clientes") {
            formData.append(key, value);
        }
    });
    if (Object.keys(extra).length > 0) {
        formData.append("extra", JSON.stringify(extra));
    }

    if (formData.get("hora_inicio") >= formData.get("hora_fin")) {
        return mostrarToast({
            title: "Ingrese horas válidas",
            type: "danger"
        });
    }

    const action = formData.get("idactividad") ? "actualizar" : "crear";

    api.post({
        source: "actividades",
        action,
        data: formData,
        onSuccess: () => {
            modal?.modal("hide");
            fetchActividades(selectedUsuario);
        }
    });
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
        clientes: actividad.clientes,
        idestado: actividad.idestado,
        tipo: actividad.tipo
    };

    const formData = new FormData();
    Object.entries(payload).forEach(([key, value]) => formData.append(key, value));

    api.post({
        source: "actividades",
        action: "actualizar",
        data: formData,
        onSuccess: () => fetchActividades(selectedUsuario)
    });
}

function eliminarActividad(idactividad) {
    if (!actividadesCache.get(idactividad)) return;

    const formData = new FormData();
    formData.append('idactividad', idactividad);

    api.post({
        source: "actividades",
        action: "eliminar",
        data: formData,
        onSuccess: () => fetchActividades(selectedUsuario)
    });
}

document.addEventListener('click', function (e) {
    if (e.target.closest("#btnRefresh")) {
        actividadActual = {};
        activeEvent = null;
        selectedUsuario = usuarioActual;
        selectedNombreUsuario = nombreUsuarioActual;
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
        const nombreUsuario = target.dataset.value;
        const grupo = target.closest('.busqueda-grupo');
        const selected = grupo.querySelector('.selected-filtro');
        const resultados = grupo.querySelector('.resultados-busqueda');
        selectedUsuario = target.dataset.id;
        selectedNombreUsuario = nombreUsuario;
        grupo.querySelectorAll('.filtro-item').forEach(el => el.classList.remove('selected'));
        target.classList.add('selected');
        selected.textContent = nombreUsuario;
        resultados.style.display = 'none';
        fetchActividades(selectedUsuario);
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
                actividadActual.nombre = labels[actividad];
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
        document.querySelector('#popupPreview').style.display = 'none';
        const idactividad = e.target.closest('#btnDetallesActividad').dataset.id;
        if (idactividad) {
            actividadActual = actividadesCache.get(idactividad);
        }
        if (activeEvent) {
            activeEvent.remove();
            activeEvent = null;
        }
        abrirFormActividad(actividadActual);
    }

    if (e.target.closest('#btnNuevoCliente')) {
        const target = e.target.closest('#btnNuevoCliente');
        const modal = document.getElementById('actividadModal');
        const value = target?.dataset?.value;
        const nombreArr = value?.split(' ');
        const nombres = nombreArr?.[0] ?? null;
        const apellidos = nombreArr?.[1] ?? null;
        if (!value || !nombres);

        const formData = new FormData();
        formData.append("nombres", nombres);
        if (apellidos) formData.append("apellidos", apellidos);

        api.post({
            source: "clientes",
            action: "crear",
            data: formData,
            onSuccess: function (response) {
                const clienteInput = modal.querySelector('#clienteInput');
                const clienteIdInput = modal.querySelector('#clienteIdInput');
                const resultados = target.closest('.busqueda-grupo').querySelector('.resultados-busqueda');
                clienteInput.value = value;
                clienteIdInput.value = response.id;
                if (!actividadActual.clientes.some(c => c.idreferencia === response.id && c.tipo_cliente === "cliente")) {
                    actividadActual.clientes.push({ idreferencia: id, tipo_cliente: "cliente" });
                }

                resultados.style.display = "none";
            }
        });
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
        if (!actividadActual.clientes.some(c => c.idreferencia === id && c.tipo_cliente === "cliente")) {
            actividadActual.clientes.push({ idreferencia: id, tipo_cliente: "cliente" });
        }

        resultados.innerHTML = '';
        resultados.style.display = 'none';
    }

    if (e.target.closest("#btnNuevaOrganizacion")) {
        e.stopPropagation();
        const value = e.target.closest("#btnNuevaOrganizacion").dataset.value;
        const formData = new FormData();
        formData.append("razon_social", value);
        let idOrganizacion = null;

        api.post({
            source: "clientes",
            action: "createOrganizacion",
            data: formData,
            onSuccess: (response) => {
                idOrganizacion = response.id;
                const hiddenId = document.getElementById("idOrganizacionInput");
                hiddenId.value = idOrganizacion;
                if (!actividadActual.clientes.some(c => c.idreferencia === id && c.tipo_cliente === "empresa")) {
                    actividadActual.clientes.push({ idreferencia: idOrganizacion, tipo_cliente: "empresa" });
                }
            }
        });
        const resultados = document.querySelector(`[data-parent="organizacionInput"]`);
        resultados.innerHTML = "";
        resultados.style.display = "none";
    }

    if (e.target.closest(".org-item")) {
        const target = e.target.closest(".org-item");

        const input = document.getElementById("organizacionInput");
        const hiddenId = document.getElementById("idOrganizacionInput");
        input.value = target.dataset.value;
        hiddenId.value = target.dataset.id;
        if (!actividadActual.clientes.some(c => c.idreferencia === target.dataset.id && c.tipo_cliente === "empresa")) {
            actividadActual.clientes.push({ idreferencia: target.dataset.id, tipo_cliente: "empresa" });
        }

        const resultados = document.querySelector(`[data-parent="organizacionInput"]`);
        resultados.innerHTML = "";
        resultados.style.display = "none";
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
        document.querySelectorAll('.popup').forEach(el => el.style.display = 'none');
        if (activeEvent) {
            activeEvent.remove();
            activeEvent = null;
        }
        guardarActividad();
    }

    if (e.target.closest('#btnEliminarActividad')) {
        document.querySelectorAll('.popup').forEach(el => el.style.display = 'none');
        const idactividad = e.target.closest('#btnEliminarActividad').dataset.id;
        if (confirm("¿Seguro que desea eliminar la actividad?")) {
            eliminarActividad(idactividad);
        }
    }

    if (e.target.closest('#detailOptions')) {
        const link = e.target.closest('a');
        if (!link) return;
        e.preventDefault();

        const container = e.target.closest(".extra-container");
        const detailOptions = container.querySelector("#detailOptions");

        /*
        detailOptions.querySelectorAll("a").forEach(a => {
            a.classList.add("clickable");
            a.classList.remove("disable-click");
        });
        */

        link.classList.remove("clickable");
        link.classList.add("disable-click");

        if (link.id === "agregarDescripcion") container.querySelector(".descripcion-container").style.display = "flex";
        if (link.id === "agregarDireccion") container.querySelector(".direccion-container").style.display = "flex";
        if (link.id === "agregarEnlace") container.querySelector(".enlace-container").style.display = "flex";
    }

    if (!e.target.closest('.fc-view')
        && !e.target.closest('.popup')) {
        if (activeEvent && !activeEvent.extendedProps.mini) {
            activeEvent.remove();
            activeEvent = null;
        }
        document.querySelectorAll('.popup').forEach(el => el.style.display = 'none');
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
                                        <img class="user-icon sm" src="${window.baseurl + cliente.foto}" alt="Foto de ${cliente.nombres} ${cliente.apellidos}">
                                        <div class="d-flex flex-column" style="font-size: 13px">
                                            <span>${cliente.nombres} ${cliente.apellidos}</span>
                                            <div class="info-row gap-4">
                                                <div class="info-row">
                                                    ${window.icons.telefono} <span>${cliente.telefono}</span>
                                                </div>
                                                <div class="info-row">
                                                    ${window.icons.building} <span>${cliente.empresa_nombre}</span>
                                                </div>
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
                                <img class="user-icon sm" src="${window.baseurl + cliente.foto}" alt="Foto de ${cliente.nombres} ${cliente.apellidos}">
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

    if (e.target.id === 'organizacionInput') {
        const target = e.target;
        const value = target.value;
        const resultados = document.querySelector(`[data-parent="${target.id}"]`);
        let html = '';

        const buttonNewOrganizacion = `
            <div class="resultado-item bg-secondary text-white" id="btnNuevaOrganizacion" data-value="${value}">
                ${window.getIcon("add", "white")}<span>Agregar ${value} como nueva organización</span>
            </div>
            `;

        if (value.length > 2) {
            api.get({
                source: "clientes",
                action: "buscarOrganizaciones",
                params: [
                    { name: "filtro", value }
                ],
                onSuccess: (organizaciones) => {
                    if (organizaciones.length === 0) {
                        html = buttonNewOrganizacion;
                        resultados.innerHTML = html;
                        resultados.style.display = "flex";
                        return;
                    }

                    organizaciones.forEach(org => {
                        html += `<div class="resultado-item org-item" data-value="${org.razon_social}" data-id="${org.idempresa}">
                                ${window.icons.building}${org.razon_social}
                            </div>`;
                    });

                    if (!organizaciones.some(org =>
                        org.razon_social.toLowerCase().trim() === value.toLowerCase().trim()
                        || org.ruc.toLowerCase().trim() === value.toLowerCase().trim()
                    )) {
                        html += buttonNewOrganizacion;
                    }

                    resultados.innerHTML = html;
                    resultados.style.display = "flex";
                }
            });
        } else {
            resultados.innerHTML = '';
            resultados.style.display = "none";
        }
    }
});