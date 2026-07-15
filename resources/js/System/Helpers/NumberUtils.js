/**
 * Utilidades para manejo de números
 */

import { generalConfig } from "./Constants.js";
import { isDefined } from "./ValidationUtils.js";

/**
 * Valida si un valor es un número válido
 * @param {Object} options - {value: *, minValue: number}
 * @returns {boolean} true si es válido
 */
export function isNumber({value, minValue = 0, maxValue = null, allowEmpty = false}) {

    if(!isDefined({value})) {

        return allowEmpty;

    }

    const normalizedValue = typeof value === "string" ? value.trim().replace(/,/g, "") : value;

    if(normalizedValue === "") {

        return allowEmpty;

    }

    const number = Number(normalizedValue);

    if(!Number.isFinite(number) || number < Number(minValue)) {

        return false;

    }

    return !isDefined({value: maxValue}) || number <= Number(maxValue);

}

/**
 * Formatea un número con decimales fijos
 * @param {number} value - Valor numérico
 * @param {number|null} decimals - Número de decimales (null usa el config por defecto)
 * @returns {string} Número formateado
 */
export function fixedNumber(value, decimals = null) {
    return Number(value).toFixed(decimals ?? generalConfig.forms.inputs.round);
}

/**
 * Formatea un número con separadores de miles
 * @param {*} value - Valor a formatear
 * @returns {string} Número formateado
 */
export function separatorNumber(value) {
    const number = isDefined({value}) ? value : 0;
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Calcula el total de un item (cantidad * precio)
 * @param {Object} options - {item: {quantity: number, price: number}}
 * @returns {number} Total calculado
 */
export function calculateTotal({item}) {
    const quantity = isNumber({value: item?.quantity}) ? Number(String(item.quantity).replace(/,/g, "")) : 0;
    const price = isNumber({value: item?.price}) ? Number(String(item.price).replace(/,/g, "")) : 0;
    const total = fixedNumber(quantity * price);
    return total;
}


