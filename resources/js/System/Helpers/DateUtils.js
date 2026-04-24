/**
 * Utilidades para manejo de fechas
 */

/**
 * Obtiene la fecha actual formateada
 * @param {string} type - Tipo de formato: "date" o "datetime"
 * @returns {string} Fecha formateada
 */
export function getCurrentDate(type = "date") {
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = String(currentDate.getMonth() + 1).padStart(2, "0");
    const currentDay = String(currentDate.getDate()).padStart(2, "0");
    const currentHour = String(currentDate.getHours()).padStart(2, "0");
    const currentMinute = String(currentDate.getMinutes()).padStart(2, "0");

    if (type === "date") {
        return `${currentYear}-${currentMonth}-${currentDay}`;
    } else if (type === "datetime") {
        return `${currentYear}-${currentMonth}-${currentDay}T${currentHour}:${currentMinute}`;
    }

    return `${currentYear}-${currentMonth}-${currentDay}`;
}

/**
 * Convierte una cadena ISO a formato datetime-local
 * @param {string} isoString - Cadena ISO
 * @returns {string} Fecha en formato datetime-local
 */
export function parseISOToDatetimeLocal(isoString) {
    const date = new Date(isoString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

/**
 * Formatea una fecha de forma legible
 * @param {Object} options - {dateString: string, type: "datetime"|"date"|"weekday_date"|"weekday_datetime"|"time", separator: string}
 * @returns {string} Fecha formateada
 */
export function legibleFormatDate({dateString = null, type = "datetime", separator = "/"}) {
    try {
        if (!dateString) throw new Error("Invalid date string");

        let date;
        let year, month, day, hours = 0, minutes = 0;

        if (dateString.includes("T")) {
            date = new Date(dateString);
            if (isNaN(date.getTime())) throw new Error("Invalid ISO date format.");
            year = date.getFullYear();
            month = date.getMonth() + 1;
            day = date.getDate();
            hours = date.getHours();
            minutes = date.getMinutes();
        } else if (dateString.includes(" ")) {
            const [datePart, timePart] = dateString.split(" ");
            [year, month, day] = datePart.split("-").map(Number);
            [hours, minutes] = timePart.split(":").map(Number);
        } else {
            [year, month, day] = dateString.split("-").map(Number);
        }

        date = new Date(year, month - 1, day, hours, minutes);
        if (isNaN(date.getTime())) {
            throw new Error("Invalid date format. Use 'YYYY-MM-DD' or 'YYYY-MM-DD HH:mm'.");
        }

        const formattedDay = String(day).padStart(2, "0");
        const formattedMonth = String(month).padStart(2, "0");
        const ampm = hours >= 12 ? "PM" : "AM";
        const formattedHours = String(hours % 12 || 12).padStart(2, "0");
        const formattedMinutes = String(minutes).padStart(2, "0");

        if (type === "date") {
            return `${formattedDay}${separator}${formattedMonth}${separator}${year}`;
        } else if (type === "weekday_date") {
            const weekday = new Intl.DateTimeFormat("es", {weekday: "long"}).format(date);
            const weekdayCap = weekday.charAt(0).toUpperCase() + weekday.slice(1);
            return `${weekdayCap}, ${formattedDay}${separator}${formattedMonth}${separator}${year}`;
        } else if (type === "weekday_datetime") {
            const weekday = new Intl.DateTimeFormat("es", {weekday: "long"}).format(date);
            const weekdayCap = weekday.charAt(0).toUpperCase() + weekday.slice(1);
            return `${weekdayCap}, ${formattedDay}${separator}${formattedMonth}${separator}${year} · ${formattedHours}:${formattedMinutes} ${ampm}`;
        } else if (type === "datetime") {
            return `${formattedDay}${separator}${formattedMonth}${separator}${year} ${formattedHours}:${formattedMinutes} ${ampm}`;
        } else if (type === "time") {
            return `${formattedHours}:${formattedMinutes} ${ampm}`;
        }

        return `${formattedDay}${separator}${formattedMonth}${separator}${year} ${formattedHours}:${formattedMinutes} ${ampm}`;
    } catch (e) {
        return dateString;
    }
}

/**
 * Agrega duración a una fecha
 * @param {Object} options - {startDate: Date|string, type: string, quantity: number, setEndOfDay: boolean}
 * @returns {string} Nueva fecha formateada
 */
export function addDuration({startDate, type, quantity, setEndOfDay = false}) {
    const date = new Date(startDate);

    try {
        switch (type) {
            case "hour":
                date.setHours(date.getHours() + quantity);
                break;
            case "day":
                date.setDate(date.getDate() + quantity);
                break;
            case "today":
                date.setDate(date.getDate() + (quantity <= 0 ? 0 : (quantity - 1)));
                date.setHours(23, 59, 59, 999);
                break;
            case "month":
                date.setMonth(date.getMonth() + quantity);
                break;
            case "year":
                date.setFullYear(date.getFullYear() + quantity);
                break;
        }

        if (setEndOfDay && ["day", "today", "month", "year"].includes(type)) {
            date.setHours(23, 59, 59, 999);
        }
    } catch (e) {
        date.setDate(date.getDate());
    }

    return isNaN(date.getTime()) ? "" : parseISOToDatetimeLocal(date.toString());
}

/**
 * Formatea la diferencia de días de forma legible
 * @param {Object} options - {diff: number}
 * @returns {string} Diferencia formateada
 */
export function diffDaysLegible({diff}) {
    let diffDaysLegible = "";
    let numberDiff = Number(diff);

    if (isNaN(numberDiff)) return "Not identified";

    if (numberDiff === 0) {
        diffDaysLegible = "Today";
    } else {
        let absNumberDiff = Math.abs(numberDiff);
        let daysLegible = absNumberDiff > 1 ? "days" : "day";
        diffDaysLegible = `${numberDiff > 0 ? "In" : "Ago"} ${absNumberDiff} ${daysLegible}`;
    }

    return diffDaysLegible;
}

