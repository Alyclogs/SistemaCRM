import { formatearFecha, formatearHora } from "./date.js";

export default class CalendarUI {
    buildCalendar(calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "timeGridWeek",

            slotDuration: "00:30:00",
            slotLabelInterval: "00:60:00",

            slotMinTime: "06:00:00",
            slotMaxTime: "20:00:00",
            scrollTime: "08:00:00",

            selectable: true,
            editable: true,
            locale: "es",
            headerToolbar: {
                start: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                center: 'title',
                end: 'today prev,next'
            },
            slotLabelFormat: {
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            },
            dayHeaderFormat: {
                weekday: "long",
                day: "2-digit",
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mensual',
                week: 'Semanal',
                day: 'Diario',
                list: 'Lista'
            }
        });
        return calendar;
    }
    buildCalendarCustom(calendarEl, {
        initialView = "timeGridDay",
        initialDate = formatearFecha(new Date()),
        scrollTime = formatearHora(new Date()),
        allDaySlot = false,
    }) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView,
            allDaySlot,
            initialDate,

            slotDuration: "00:30:00",
            slotLabelInterval: "00:60:00",

            slotMinTime: "06:00:00",
            slotMaxTime: "20:00:00",
            scrollTime,

            selectable: true,
            editable: true,
            locale: "es",
            headerToolbar: false,
            slotLabelFormat: {
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            }
        });
        return calendar;
    }
}