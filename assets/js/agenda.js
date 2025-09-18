import CalendarUI from "./calendar.js";
import { formatearFecha, formatearHora, formatEventDate } from "./date.js";

const calendarUI = new CalendarUI();
var calendar = null;
var popup = document.getElementById('popup');
var activeEvent = null;
var actividadActual = {};

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
        titleInput.placeholder = "Llamada";

        titleInput.oninput = function () {
            activeEvent.setProp("title", titleInput.value);
        };

        actividadActual = {
            title: activeEvent.title,
            start: formatearHora(activeEvent.start),
            end: formatearHora(activeEvent.end),
            type: "llamada"
        }
        console.log(actividadActual);
    });

    calendar.setOption("eventResize", function (info) {
        const infoDate = popup.querySelector('#infoDate');

        if (activeEvent && info.event.id === activeEvent.id) {
            infoDate.textContent = formatEventDate(info.event.start, info.event.end);
        } else {
            popup.style.display = "none";
            // actualizar actividad
        }
    });

    calendar.setOption("eventDrop", function (info) {
        const infoDate = popup.querySelector('#infoDate');

        if (activeEvent && info.event.id === activeEvent.id) {
            infoDate.textContent = formatEventDate(info.event.start, info.event.end);
        } else {
            popup.style.display = "none";
            // actualizar actividad
        }
    });

    calendar.render();
});

function actualizarTipoActividad(type = "detalles", btn) {
    btn.querySelectorAll('.buttons-row button').forEach(button => button.classList.remove('selected'));
    btn.classList.add('selected');

    if (type === "popup") {

        return;
    }
    if (type === "detalles") {
        return;
    }
}