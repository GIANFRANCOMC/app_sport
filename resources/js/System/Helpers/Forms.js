/**
 * Helpers para formularios
 * Funciones reutilizables para manejo completo de formularios
 */

import * as Utils from "./Utils.js";
import * as Alerts from "./Alerts.js";
import * as Requests from "./Requests.js";
import { validateField } from "./ValidationHelpers.js";

/**
 * Inicializa la estructura de datos de un formulario CRUD
 * @param {Object} fields - Objeto con campos y sus valores por defecto
 * @returns {Object} Estructura de datos del formulario
 */
export function initFormData(fields = {}) {
    return {
        id: null,
        ...fields
    };
}


/**
 * Limpia un formulario estableciendo valores por defecto
 * @param {Object} formData - Objeto del formulario a limpiar
 * @param {Object} defaultValues - Valores por defecto para cada campo
 */
export function clearFormData(formData, defaultValues = {}) {
    Object.keys(formData).forEach(key => {
        const defaultValue = defaultValues[key] !== undefined
            ? defaultValues[key]
            : (typeof formData[key] === "number" ? null : "");
        formData[key] = defaultValue;
    });
}

/**
 * Prepara datos del formulario antes de enviar
 * @param {Object} formData - Datos del formulario
 * @param {Object} config - Configuración por campo {trim, normalize, toNumber, getCode, removeIfEmpty}
 * @returns {Object} Datos preparados
 */
export function prepareFormData(formData, config = {}) {
    const prepared = Utils.cloneJson(formData);

    Object.keys(prepared).forEach(key => {
        const fieldConfig = config[key] || {};

        // Trim strings
        if (fieldConfig.trim !== false && typeof prepared[key] === "string") {
            prepared[key] = String(prepared[key]).trim();
        }

        // Normalize optional (convierte strings vacíos a null)
        if (fieldConfig.normalize === true) {
            prepared[key] = Utils.normalizeOptional(prepared[key]);
        }

        // Convert to number
        if (fieldConfig.toNumber === true && Utils.isDefined({ value: prepared[key] })) {
            prepared[key] = Utils.isNumber({ value: prepared[key], minValue: fieldConfig.minValue ?? 0 })
                ? Number(prepared[key])
                : null;
        }

        // Get code from object (para selects)
        if (fieldConfig.getCode === true && prepared[key]?.code !== undefined) {
            prepared[key] = prepared[key].code;
        }

        // Remove if empty
        if (fieldConfig.removeIfEmpty === true && !Utils.isDefined({ value: prepared[key] })) {
            delete prepared[key];
        }
    });

    return prepared;
}

/**
 * Maneja errores de formulario de forma centralizada
 * @param {Object|Array} errors - Errores del servidor
 * @param {Object} formErrorsObject - Objeto donde se almacenarán los errores
 */
export function handleFormErrors(errors, formErrorsObject) {
    if (!formErrorsObject) return;

    if (errors && Array.isArray(errors)) {
        errors.forEach(error => {
            const field = error.field || error.key;
            if (field) {
                if (!formErrorsObject[field]) {
                    formErrorsObject[field] = [];
                }
                formErrorsObject[field].push(error.message || error);
            }
        });
    } else if (typeof errors === "object" && errors !== null) {
        Object.assign(formErrorsObject, errors);
    }
}


/**
 * Valida un formulario completo usando reglas de validación
 * @param {Object} formData - Datos del formulario
 * @param {Object} validationRules - Reglas de validación {field: {required, email, url, number, min, max, custom}}
 * @param {Object} config - Configuración {isDescriptive: boolean, errorLabels: Object}
 * @returns {Object} {bool: boolean, errors: Object}
 */
export function validateFormData(formData, validationRules = {}, config = {}) {
    const result = {
        bool: true,
        errors: {}
    };

    const isDescriptive = config.isDescriptive === true;
    const errorLabels = config.errorLabels || {};

    Object.keys(validationRules).forEach(field => {
        const rules = validationRules[field];
        const value = formData[field];
        const fieldName = isDescriptive ? (errorLabels[field] || field) : "";
        const fieldErrors = validateField(value, rules, fieldName);

        if (fieldErrors.length > 0) {
            result.errors[field] = fieldErrors;
            result.bool = false;
        }
    });

    return result;
}

/**
 * Maneja la respuesta de creación/actualización de forma centralizada
 * @param {Object} response - Respuesta de la petición
 * @param {Object} options - Opciones {onSuccess, onError, modalId, formErrorsObject, reloadList}
 * @returns {Promise<boolean>} true si fue exitoso, false en caso contrario
 */
export async function handleCreateUpdateResponse(
    response,
    {
        onSuccess = null,
        onError = null,
        modalId = null,
        formErrorsObject = null,
        reloadList = null
    } = {}
) {
    if (Requests.valid({ result: response })) {
        if (modalId) {
            Alerts.modals({ type: "hide", id: modalId });
        }

        Alerts.generateAlert({
            type: "success",
            msgContent: response?.data?.msg
        });

        if (onSuccess) {
            onSuccess(response);
        }

        if (reloadList) {
            reloadList();
        }

        return true;
    } else {
        if (formErrorsObject) {
            handleFormErrors(response?.errors, formErrorsObject);
        }

        Alerts.toastrs({
            type: "error",
            subtitle: response?.data?.msg
        });

        Alerts.swals({ show: false });

        if (onError) {
            onError(response);
        }

        return false;
    }
}
