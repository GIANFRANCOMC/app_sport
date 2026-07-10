/**
 * Utilidades específicas del negocio
 */

import * as Requests from "./Requests.js";
import { toastrs } from "./Alerts.js";
import { isDefined } from "./ValidationUtils.js";
import { addDuration } from "./DateUtils.js";

/**
 * Obtiene suscripciones de un cliente
 * @param {Object} options - {customer: Object}
 * @returns {Promise} Promesa con las suscripciones
 */
export function getSubscriptions({customer}) {
    const route = Requests.config({entity: "customers", type: "getSubscriptions"});
    return Requests.get({route: `${route}/${customer?.id}`, data: {}});
}

/**
 * Obtiene tracking de clientes
 * @param {Object} options - {customer: Object, period_type: string, options: Object}
 * @returns {Promise} Promesa con el tracking
 */
export function getTrackingCustomers({customer, period_type = null, start_date = null, end_date = null, options = {}}) {
    const route = Requests.config({entity: "tracking_customers", type: "getTracking"});
    return Requests.get({route: `${route}/${customer?.id}`, data: {period_type, start_date, end_date, options}});
}

/**
 * Encuentra solapamientos entre una venta y suscripciones
 * @param {Object} sale - Venta
 * @param {Array} subscriptions - Array de suscripciones
 * @returns {Object} {hasOverlap: boolean, positions: Array}
 */
export function findOverlaps(sale, subscriptions) {
    const parseDate = (date) => new Date(date.replace(" ", "T"));
    const saleStart = parseDate(sale.start_date);
    const saleEnd = parseDate(sale.end_date);

    const overlappingPositions = subscriptions
        .map((subscription, index) => {
            const subscriptionStart = parseDate(subscription.start_date);
            const subscriptionEnd = parseDate(subscription.end_date);

            if (
                (saleStart <= subscriptionEnd && saleStart >= subscriptionStart) ||
                (saleEnd <= subscriptionEnd && saleEnd >= subscriptionStart) ||
                (saleStart <= subscriptionStart && saleEnd >= subscriptionEnd)
            ) {
                return {...subscription, keyArray: index};
            }
            return null;
        })
        .filter(index => index !== null);

    return {
        hasOverlap: overlappingPositions.length > 0,
        positions: overlappingPositions
    };
}

/**
 * Envía mensaje por WhatsApp
 * @param {Object} options - {phoneNumber: string, message: string}
 */
export function sendWhatsapp({phoneNumber, message}) {
    if (!isDefined({value: phoneNumber})) {
        toastrs({type: "error", subtitle: "Unable to send WhatsApp message, please fill in the required fields."});
    } else if (!isDefined({value: message})) {
        toastrs({type: "error", subtitle: "Unable to send WhatsApp message, message not identified."});
    } else {
        const encodedMessage = encodeURIComponent(message);
        const link = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
        window.open(link, "_blank");
    }
}

/**
 * Obtiene mensaje de WhatsApp para una acción
 * @param {Object} options - {data: Object, action: string}
 * @returns {string|null} Mensaje generado
 */
export function getMessageWhatsapp({data, action}) {
    let message = null;

    if (["reportSale"].includes(action)) {
        const information = "¡Se ha creado la venta exitosamente! Para obtener el documento de la venta, visite el siguiente enlace:";
        const url = Requests.routeReport({resource: "sale", params: {document: data?.id, type: "a4"}, extras: {action}});
        message = `${information} ${url}`;
    }

    return message;
}

