import CalendarUI from "./calendar.js";
import { formatearFecha, formatearHora, formatEventDate } from "./date.js";

const baseurl = 'http://localhost/SistemaCRM/';
const calendarUI = new CalendarUI();
var calendar = null;
var popup = document.getElementById('popup');
var activeEvent = null;
var actividadActual = {};
const defaultActividades = ["Llamada", "Videollamada", "Reunión"];

document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    calendar = calendarUI.buildCalendar(calendarEl);

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
        activeEvent = info.event;
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
            activeEvent.setProp("title", titleInput.value);
        };

        actividadActual = {
            title: activeEvent.title,
            fecha: formatearFecha(activeEvent.start),
            start: formatearHora(activeEvent.start),
            end: formatearHora(activeEvent.end),
            type: "llamada"
        }
    });

    calendar.setOption("eventResize", function (info) {
        const infoDate = popup.querySelector('#infoDate');

        if (activeEvent && info.event.id === activeEvent.id) {
            infoDate.textContent = formatEventDate(info.event.start, info.event.end);
        } else {
            popup.style.display = "none";
            actividadActual.start = formatearHora(info.event.start);
            actividadActual.end = formatearHora(info.event.end);
        }
    });

    calendar.setOption("eventDrop", function (info) {
        const infoDate = popup.querySelector('#infoDate');

        if (activeEvent && info.event.id === activeEvent.id) {
            infoDate.textContent = formatEventDate(info.event.start, info.event.end);
        } else {
            popup.style.display = "none";
            actividadActual.fecha = formatearFecha(info.event.start);
            actividadActual.start = formatearHora(info.event.start);
            actividadActual.end = formatearHora(info.event.end);
        }
    });

    calendar.render();
});

document.addEventListener('click', function (e) {
    if (e.target.closest('.btn-actividad')) {
        const button = e.target.closest('.btn-actividad');
        const esPopup = !!e.target.closest('.popup');
        const source = e.target.closest(esPopup ? '.popup' : '#actividadModal');
        const buttons = source.querySelector('.buttons-actividad');
        const actividad = button.dataset.type;

        buttons.querySelectorAll('button').forEach(btn => btn.classList.remove('selected'));
        button.classList.add('selected');
        actividadActual.type = actividad;

        if (esPopup) {
            const titleInput = source.querySelector('#titleInput');
            const labels = {
                llamada: "Llamada",
                videollamada: "Videollamada",
                reunion: "Reunión"
            };

            if (labels[actividad]) {
                titleInput.placeholder = labels[actividad];
                if (defaultActividades.includes(activeEvent.title)) {
                    activeEvent.setProp("title", labels[actividad]);
                }
            }
        }
    }
    if (e.target.closest('#btnDetallesActividad')) {
        e.target.closest('.popup').style.display = 'none';
        fetch(baseurl + "views/components/agenda/formActividad.php")
            .then(res => res.text())
            .then(html => {
                $("#actividadModalLabel").text("Editar actividad");
                $("#actividadModalBody").html(html);
                $("#actividadModal").modal('show');
            }).catch(e => {
                mostrarToast({
                    message: "Ocurrió un error al mostrar el formulario",
                    type: "danger"
                });
                console.error(e);
            });
    }
});