export default class CalendarUI {
    buildCalendar(calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "timeGridWeek",
            slotWidth: 100,

            slotDuration: "00:30:00",
            slotLabelInterval: "00:60:00",

            slotMinTime: "09:00",
            slotMaxTime: "18:00",

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
            }
        });
        return calendar;
    }
}