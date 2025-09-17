export function formatearHora12h(hora24) {
    if (!hora24) return '';
    let [h, m] = hora24.split(':');
    h = parseInt(h);
    let suf = h >= 12 ? 'pm' : 'am';
    h = h % 12;
    if (h === 0) h = 12;
    return `${h.toString().padStart(2, '0')}:${m} ${suf}`;
}

export function convertirHora(horaInput) {
    const [horas, minutos, segundos] = horaInput.split(":").map(Number);
    const horaFormateada = new Date();
    horaFormateada.setHours(horas, minutos, segundos, 0);

    return horaFormateada;
}

export function obtenerFechaHoraPeruFormateada(fechaStr) {
    const fecha = fechaStr ? new Date(fechaStr) : new Date();

    if (isNaN(fecha)) return "Fecha inválida";

    const opciones = {
        timeZone: "America/Lima",
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: false
    };

    return new Intl.DateTimeFormat("es-PE", opciones).format(fecha);
}

export function formatFechaToDDMMYYYY(dateStr) {
    if (!dateStr) return "";
    const [yyyy, mm, dd] = dateStr.split("-");
    return `${dd}/${mm}/${yyyy}`;
}

export function esFechaPasada(fecha, hora) {
    const citaCompleta = new Date(`${fecha}T${hora}`);
    const ahora = new Date();

    return citaCompleta <= ahora;
}

// Sumar minutos a una hora en formato hh:mm
export function sumarMinutos(hora, minutos) {
    let [h, m] = hora.split(':').map(Number);
    let date = new Date(2000, 0, 1, h, m);
    date.setMinutes(date.getMinutes() + minutos);
    let nh = date.getHours().toString().padStart(2, '0');
    let nm = date.getMinutes().toString().padStart(2, '0');
    return `${nh}:${nm}`;
}

export function diferenciaEnMinutos(horaInicio, horaFin) {
    const aMinutos = (hora) => {
        const partes = hora.split(':').map(Number);
        const horas = partes[0] || 0;
        const minutos = partes[1] || 0;
        const segundos = partes[2] || 0;
        return horas * 60 + minutos + segundos / 60;
    };

    const minutosInicio = aMinutos(horaInicio);
    const minutosFin = aMinutos(horaFin);

    return minutosFin - minutosInicio;
}

export function horaAHoraMinutos(hora) {
    const [h, m] = hora.split(':').map(Number);
    return h * 60 + m;
}

export function calcularEdad(fechaNacStr) {
    const hoy = new Date();
    const partes = fechaNacStr.split('-');
    if (partes.length !== 3) return '';
    const anio = parseInt(partes[0], 10);
    const mes = parseInt(partes[1], 10) - 1;
    const dia = parseInt(partes[2], 10);
    const fechaNac = new Date(anio, mes, dia);
    let edad = hoy.getFullYear() - fechaNac.getFullYear();
    const m = hoy.getMonth() - fechaNac.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
        edad--;
    }
    return edad;
}

// Función para recortar segmentos de fecha (rango1 - rango2)
export function recortarSegmento(seg, a, b) {
    const segStart = new Date(seg.start);
    const segEnd = new Date(seg.end);
    const blockStart = new Date(a);
    const blockEnd = new Date(b);

    if (segEnd <= blockStart || segStart >= blockEnd) return [seg];

    const out = [];
    if (segStart < blockStart) out.push({ start: seg.start, end: a });
    if (segEnd > blockEnd) out.push({ start: b, end: seg.end });

    return out;
}

export function ajustarRangoFecha(start, end) {
    const startDate = new Date(start);
    const endDate = new Date(end);

    // Asegurar formato ISO solo con la parte de la fecha
    const startRecur = startDate.toISOString().slice(0, 10); // 'YYYY-MM-DD'
    const endRecur = endDate.toISOString().slice(0, 10);     // 'YYYY-MM-DD'

    return { startRecur, endRecur };
}

// Helpers de fechas
export function dateStrToDate(str) {
    return new Date(str + 'T00:00:00');
}
export function dateToDateStr(date) {
    return date.toISOString().slice(0, 10);
}
export function dateStrToLocalDate(dateStr) {
    const [y, m, d] = dateStr.split('-').map(Number);
    return new Date(y, m - 1, d); // constructor con componentes usa zona local
}

// Devuelve "YYYY-MM-DD" usando la fecha local (NO UTC)
export function dateToDateStrLocal(d) {
    return d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0');
}

export function dayBefore(str) {
    const d = dateStrToDate(str);
    d.setDate(d.getDate() - 1);
    return dateToDateStr(d);
}
export function dayAfter(str) {
    const d = dateStrToDate(str);
    d.setDate(d.getDate() + 1);
    return dateToDateStr(d);
}
export function buildDate(fechaStr, horaStr) {
    return new Date(`${fechaStr}T${horaStr}:00`);
}

export function formatearFecha(fecha) {
    const yyyy = fecha.getFullYear();
    const mm = String(fecha.getMonth() + 1).padStart(2, '0');
    const dd = String(fecha.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

/***
 * @param {Date} fecha
 * @returns {string} hora formateada en formato 'HH:mm:ss'
 */
export function formatearHora(fecha) {
    const HH = fecha.getHours();
    const mm = fecha.getMinutes();
    const ss = fecha.getSeconds();
    return `${HH}:${mm}:${ss}`;
}

export function ajustarFecha(fechaStr, dias) {
    const [year, month, day] = fechaStr.split('-').map(Number);
    const fecha = new Date(year, month - 1, day); // mes base 0
    fecha.setDate(fecha.getDate() + dias);
    return fecha.toISOString().split('T')[0];
}

export function formatearFechaLocal(fechaStr) {
    const [anio, mes, dia] = fechaStr.split('-').map(Number);
    const fecha = new Date(anio, mes - 1, dia);
    const opcionesFecha = { month: 'long', day: 'numeric', year: 'numeric' };
    const fechaTexto = fecha.toLocaleDateString('es-ES', opcionesFecha);
    return fechaTexto.charAt(0).toUpperCase() + fechaTexto.slice(1);
}

export function formatearFechaLocalSimple(fechaStr) {
    const [anio, mes, dia] = fechaStr.split('-').map(Number);
    const fecha = new Date(anio, mes - 1, dia);
    const opcionesFecha = { weekday: 'long', day: 'numeric', month: 'long' };
    const fechaTexto = fecha.toLocaleDateString('es-ES', opcionesFecha);
    return fechaTexto.charAt(0).toUpperCase() + fechaTexto.slice(1);
}

export function formatearFechaFull(fechaStr) {
    const fecha = new Date(fechaStr);
    const opciones = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    };
    const fechaTexto = fecha.toLocaleString('es-ES', opciones);
    return fechaTexto.charAt(0).toUpperCase() + fechaTexto.slice(1);
}

export function formatearFechaSimple(fechaISO) {
    const dias = ["DOM", "LUN", "MAR", "MIE", "JUE", "VIE", "SAB"];
    const [year, month, day] = fechaISO.split("-").map(Number);

    const fecha = new Date(year, month - 1, day);

    const diaSemana = dias[fecha.getDay()];
    const diaMes = fecha.getDate();
    return `${diaSemana} ${diaMes}`;
}

export function obtenerDiaSemana(fecha) {
    const [anio, mes, dia] = fecha.split('-').map(Number);
    const fechaObj = new Date(anio, mes - 1, dia);
    const nombreDia = fechaObj.toLocaleDateString("es-ES", { weekday: "long" });
    return nombreDia;
}

export function formatEventDate(start, end) {
    const timeFormatter = new Intl.DateTimeFormat('es-ES', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });

    const dateFormatter = new Intl.DateTimeFormat('es-ES', {
        day: 'numeric',
        month: 'long'
    });

    const startTime = timeFormatter.format(start).replace('.', '').toLowerCase();
    const endTime = timeFormatter.format(end).replace('.', '').toLowerCase();
    const date = dateFormatter.format(start);
    return `${startTime} - ${endTime}, ${date}`;
}