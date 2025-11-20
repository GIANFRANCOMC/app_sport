import { requestRoute, generalConfig } from "./Constants.js";
import * as Requests from "./Requests.js";
import * as Constants from "./Constants.js";
import { toastrs } from "./Alerts.js";

import axios from "axios";

export function getEssential() {

    return {
        ownerApp: window?.ownerApp ?? null,
        user: window?.user ?? null,
        company: window?.company ?? null,
        sections: window?.sections ?? [],
        preferences: window?.preferences ?? [],
    };

}

export function navbarItem(id, {type = "active", addClass = null}) {

    try {

        $(`.${id}`).addClass(type); // Favorites

        document.getElementById(id).classList.add(type);

        if(isDefined({value: addClass})) {

            document.getElementById(id).classList.add(addClass);

        }

    }catch(e) {

    }

}

export function isDefined({value}) {

    return value != "" && value != null && value != undefined;

}

export function isNumber({value, minValue = 0}) {

    let number = Number(value);

    return !isNaN(number) && Number(number) >= minValue;

}

export function generateCode({length = 12}) {

    const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    let randomString = "";

    for(let i = 0; i < length; i++) {

        const randomIndex = Math.floor(Math.random() * characters.length);
        randomString += characters[randomIndex];

    }

    return randomString;

}

export function uuid(length = 36) {

    return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)).substr(0, length);

}

export function getCurrentDate(type = "date") {

    // Get current date
    const currentDate = new Date();

    // Get current year
    const currentYear = currentDate.getFullYear();

    // Get current month (adding leading zero if necessary)
    const currentMonth = ('0' + (currentDate.getMonth() + 1)).slice(-2);

    // Get current day of month (adding leading zero if necessary)
    const currentDay = ('0' + currentDate.getDate()).slice(-2);

    const currentHour = currentDate.getHours().toString().padStart(2, '0');
    const currentMinute = currentDate.getMinutes().toString().padStart(2, '0');

    // Build date in "YYYY-MM-DD" format
    let formattedDate = "";

    if(type == "date") {

        formattedDate = `${currentYear}-${currentMonth}-${currentDay}`;

    }else if(type = "datetime") {

        formattedDate = `${currentYear}-${currentMonth}-${currentDay}T${currentHour}:${currentMinute}`;

    }

    return formattedDate;

}

export function parseISOToDatetimeLocal(isoString) {

    // Create a Date object from ISO string
    const date = new Date(isoString);

    // Extract date components
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Month (0-11) to (1-12)
    const day = String(date.getDate()).padStart(2, '0'); // Day of month
    const hours = String(date.getHours()).padStart(2, '0'); // Hours
    const minutes = String(date.getMinutes()).padStart(2, '0'); // Minutes

    // Format as "YYYY-MM-DDTHH:MM"
    return `${year}-${month}-${day}T${hours}:${minutes}`;

}

export function cloneJson(json) {

    return JSON.parse(JSON.stringify(json));

}

export function calculateTotal({item}) {

    const quantity = Number(item?.quantity),
          price    = Number(item?.price);

    const total = (isNaN(quantity) || isNaN(price)) ? 0 : fixedNumber(quantity * price);

    return total;

}

export function fixedNumber(value, decimals = null) {

    return Number(value).toFixed(decimals ?? generalConfig.forms.inputs.round);

}

export function separatorNumber(value) {

    const number = isDefined({value}) ? value : 0;

    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

}

export function normalizeOptional(value) {

    if(!isDefined({value})) {

        return null;

    }

    const sanitized = String(value ?? "").trim();

    return sanitized === "" ? null : sanitized;

}

export function isValidEmail(value) {

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(String(value ?? "").trim());

}

export function isValidUrl(value) {

    const candidate = typeof value === "string" ? value : value?.url;

    if(!isDefined({value: candidate})) {

        return false;

    }

    try {

        const url = new URL(String(candidate ?? "").trim());
        return ["http:", "https:"].includes(url.protocol);

    }catch(e) {

        return false;

    }

}

export function truncate({value, length = 40}) {

    if(!value) return "";
    return value.length > length ? value.slice(0, length) + "..." : value;

}

export function diffDaysLegible({diff}) {

    let diffDaysLegible = "";
    let numberDiff = Number(diff);

    if(isNaN(numberDiff)) return "Not identified";

    if(numberDiff === 0) {

        diffDaysLegible = "Today";

    }else {

        let absNumberDiff = Math.abs(numberDiff);
        let daysLegible   = absNumberDiff > 1 ? "days" : "day";

        diffDaysLegible = `${numberDiff > 0 ? "In" : "Ago"} ${absNumberDiff} ${daysLegible}`;

    }

    return diffDaysLegible;

}

export function getErrors({errors}) {

    let propsValidate = Object.values(errors).filter(valueValidate => {

        return isDefined({value: valueValidate}) && Array.isArray(valueValidate) && valueValidate.length > 0;

    });

    return propsValidate;

}

export function addDuration({startDate, type, quantity, setEndOfDay = false}) {

    const date = new Date(startDate);

    try {

        switch(type) {
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

        if(setEndOfDay && ["day", "today", "month", "year"].includes(type)) {

            date.setHours(23, 59, 59, 999);

        }

    }catch(e) {

        date.setDate(date.getDate());

    }

    return isNaN(date.getTime()) ? "" : parseISOToDatetimeLocal(date.toString());

}


export function getSubscriptions({customer}) {

    let route = Requests.config({entity: "customers", type: "getSubscriptions"});

    return Requests.get({route: `${route}/${customer?.id}`, data: {}});

}

export function getTrackingCustomers({customer, period_type = null, options = {}}) {

    let route = Requests.config({entity: "tracking_customers", type: "getTracking"});

    return Requests.get({route: `${route}/${customer?.id}`, data: {period_type, options}});

}

export function findOverlaps(sale, subscriptions) {

    const parseDate = (date) => new Date(date.replace(" ", "T"));
    const saleStart = parseDate(sale.start_date);
    const saleEnd = parseDate(sale.end_date);

    const overlappingPositions = subscriptions
        .map((subscription, index) => {
            const subscriptionStart = parseDate(subscription.start_date);
            const subscriptionEnd = parseDate(subscription.end_date);

            // Check if dates overlap
            if (
                (saleStart <= subscriptionEnd && saleStart >= subscriptionStart) || // Start is within an active subscription
                (saleEnd <= subscriptionEnd && saleEnd >= subscriptionStart) ||     // End is within an active subscription
                (saleStart <= subscriptionStart && saleEnd >= subscriptionEnd)      // Sale fully overlaps an active subscription
            ) {
                return {...subscription, keyArray: index};
            }
            return null;
        })
        .filter(index => index !== null); // Filter only positions with overlaps

    return {
        hasOverlap: overlappingPositions.length > 0,
        positions: overlappingPositions
    };

};

export function legibleFormatDate({dateString = null, type = "datetime", separator = "/"}) {

    try {

        if (!dateString) throw new Error("Invalid date string");

        let date;
        let year, month, day, hours = 0, minutes = 0;

         if(dateString.includes("T")) {

            date = new Date(dateString);

            if(isNaN(date.getTime())) throw new Error("Invalid ISO date format.");

            year    = date.getFullYear();
            month   = date.getMonth() + 1;
            day     = date.getDate();
            hours   = date.getHours();
            minutes = date.getMinutes();

        }else if(dateString.includes(" ")) {

            // Format with time (YYYY-MM-DD HH:mm)
            const [datePart, timePart] = dateString.split(" ");
            [year, month, day] = datePart.split("-").map(Number);
            [hours, minutes] = timePart.split(":").map(Number);

        }else {

            // Format without time (YYYY-MM-DD)
            [year, month, day] = dateString.split("-").map(Number);

        }

        // Create date ensuring it respects local timezone
        date = new Date(year, month - 1, day, hours, minutes);

        // Validate if date is valid
        if(isNaN(date.getTime())) {
            throw new Error("Invalid date format. Use 'YYYY-MM-DD' or 'YYYY-MM-DD HH:mm'.");
        }

        // Extract components
        const formattedDay = day.toString().padStart(2, "0");
        const formattedMonth = month.toString().padStart(2, "0");

        // Convert time to 12h format
        const ampm = hours >= 12 ? "PM" : "AM";
        const formattedHours = (hours % 12 || 12).toString().padStart(2, "0");
        const formattedMinutes = minutes.toString().padStart(2, "0");

        if(type === "date") {

            return `${formattedDay}${separator}${formattedMonth}${separator}${year}`;

        }else if (type === "datetime") {

            return `${formattedDay}${separator}${formattedMonth}${separator}${year} ${formattedHours}:${formattedMinutes} ${ampm}`;

        }else if (type === "time") {

            return `${formattedHours}:${formattedMinutes} ${ampm}`;

        }

        return `${formattedDay}${separator}${formattedMonth}${separator}${year} ${formattedHours}:${formattedMinutes} ${ampm}`;

    }catch (e) {

        return dateString; // Return original date in case of error

    }

}

export function sendWhatsapp({phoneNumber, message}) {

    if(!isDefined({value: phoneNumber})) {

        toastrs({type: "error", subtitle: "Unable to send WhatsApp message, please fill in the required fields."});

    }else if(!isDefined({value: message})) {

        toastrs({type: "error", subtitle: "Unable to send WhatsApp message, message not identified."});

    }else {

        let encodedMessage = encodeURIComponent(message);

        let link = "https://wa.me/"+phoneNumber+"?text="+encodedMessage;

        window.open(link, "_blank");

    }

}

export function getMessageWhatsapp({data, action}) {

    let message = null;

    if(["reportSale"].includes(action)) {

        const information = "¡Se ha creado la venta exitosamente! Para obtener el documento de la venta, visite el siguiente enlace:";
        const url = Requests.routeReport({resource: "sale", params: {document: data?.id, type: "a4"}, extras: {action}});

        message = `${information} ${url}`;

    }

    return message;

}

export function encodeBase64UTF8(text) {

    const bytes = new TextEncoder().encode(text);
    const base64 = btoa(String.fromCharCode(...bytes));

    return base64;

}

export function decodeBase64UTF8(base64) {

    const binary = atob(base64);
    const bytes = Uint8Array.from(binary, char => char.charCodeAt(0));
    const text = new TextDecoder().decode(bytes);

    return text;

}

export function getAsset(path, {type = "storage", back = 0}) {

    if(["storage"].includes(type)) {

        const baseUrl = `/${type}/`;

        return `${baseUrl}${path}`;

    }else if(["public"].includes(type)) {

        const baseUrl = `/${type}/`;

        return `${baseUrl}${path}`;

    }else if(["none"].includes(type)) {

        return back == 1 ? `../${path}` : `${path}`;

    }

}


/**
 * Formats the capacity of people
 * @param {number|null} capacity - Numeric capacity
 * @returns {string} Formatted text
 */
export function formatCapacity(capacity) {

    if(!isDefined({value: capacity})) {

        return "Capacidad no definida";

    }

    const numericCapacity = Number(capacity);

    if(isNaN(numericCapacity) || numericCapacity <= 0) {

        return "Capacidad no definida";

    }

    const label = numericCapacity === 1 ? "persona" : "personas";

    return `${separatorNumber(numericCapacity)} ${label}`;

}

/**
 * Gets CSS classes for a status badge
 * @param {string} status - Status (active, inactive, etc.)
 * @param {Object} variants - Color variants by status
 * @returns {Array} Array of CSS classes
 */
export function getStatusBadgeClasses(status, variants = {}) {

    const defaultVariants = {
        active: "bg-label-success",
        inactive: "bg-label-danger",
        ...variants
    };

    return [
        "badge",
        "fw-semibold",
        "px-3",
        "py-2",
        "text-capitalize",
        defaultVariants[status] ?? "bg-label-secondary"
    ];

}
