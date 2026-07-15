/**
 * Utilidades especificas del negocio.
 */

import * as Requests from "./Requests.js";
import { toastrs } from "./Alerts.js";
import { isDefined } from "./ValidationUtils.js";

export function getSubscriptions({customer}) {

    const route = Requests.config({entity: "customers", type: "getSubscriptions"});
    return Requests.get({route: `${route}/${customer?.id}`, data: {}});

}

export function getTrackingCustomers({customer, period_type = null, start_date = null, end_date = null, options = {}}) {

    const route = Requests.config({entity: "tracking_customers", type: "getTracking"});
    return Requests.get({route: `${route}/${customer?.id}`, data: {period_type, start_date, end_date, options}});

}

export function findOverlaps(sale, subscriptions) {

    const parseDate = (date) => new Date(date.replace(" ", "T"));
    const saleStart = parseDate(sale.start_date);
    const saleEnd = parseDate(sale.end_date);

    const overlappingPositions = subscriptions
        .map((subscription, index) => {

            const subscriptionStart = parseDate(subscription.start_date);
            const subscriptionEnd = parseDate(subscription.end_date);

            if((saleStart <= subscriptionEnd && saleStart >= subscriptionStart)
                || (saleEnd <= subscriptionEnd && saleEnd >= subscriptionStart)
                || (saleStart <= subscriptionStart && saleEnd >= subscriptionEnd)) {

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

export function sendWhatsapp({phoneNumber, message}) {

    if(!isDefined({value: phoneNumber})) {

        toastrs({type: "error", subtitle: "Ingresa un numero de WhatsApp."});

    }else if(!isDefined({value: message})) {

        toastrs({type: "error", subtitle: "No se pudo preparar el mensaje de WhatsApp."});

    }else {

        const encodedMessage = encodeURIComponent(message);
        const link = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
        window.open(link, "_blank");

    }

}

export function getMessageWhatsapp({data, action, url = null}) {

    if(["reportSale"].includes(action)) {

        const reportUrl = url || Requests.routeReport({
            resource: "sale",
            params: {document: data?.id, type: "a4"},
            extras: {action}
        });

        return `Se ha creado la venta correctamente. Puedes revisar el documento en este enlace seguro por tiempo limitado: ${reportUrl}`;

    }

    return null;

}
