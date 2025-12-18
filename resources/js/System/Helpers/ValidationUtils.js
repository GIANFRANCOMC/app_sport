/**
 * Utilidades de validación
 */

/**
 * Verifica si un valor está definido (no es null, undefined o string vacío)
 * @param {Object} options - {value: *}
 * @returns {boolean} true si está definido
 */
export function isDefined({value}) {
    return value != "" && value != null && value != undefined;
}

/**
 * Obtiene errores de validación de un objeto de errores
 * @param {Object} options - {errors: Object}
 * @returns {Array} Array de errores
 */
export function getErrors({errors}) {
    return Object.values(errors).filter(valueValidate => {
        return isDefined({value: valueValidate}) && Array.isArray(valueValidate) && valueValidate.length > 0;
    });
}

