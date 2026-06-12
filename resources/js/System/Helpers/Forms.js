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
 * @param {Object} config - Configuración por campo {
 *   trim: boolean,
 *   normalize: boolean,
 *   toNumber: boolean,
 *   toBoolean: boolean,
 *   getCode: boolean,
 *   mapToField: string,
 *   getArray: boolean|{mapTo: string},
 *   removeIfEmpty: boolean
 * }
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

        // Convert to boolean
        if (fieldConfig.toBoolean === true && Utils.isDefined({ value: prepared[key] })) {
            prepared[key] = Boolean(prepared[key]);
        }

        // Get code from object (para selects)
        if (fieldConfig.getCode === true && prepared[key]?.code !== undefined) {
            prepared[key] = prepared[key].code;
        }

        // Map to another field (ej: currency -> currency_id)
        if (fieldConfig.mapToField && typeof fieldConfig.mapToField === "string") {
            if (prepared[key]?.code !== undefined) {
                prepared[fieldConfig.mapToField] = prepared[key].code;
                delete prepared[key];
            }
        }

        // Transform array of objects (ej: categories)
        if (fieldConfig.getArray && Array.isArray(prepared[key])) {
            if (typeof fieldConfig.getArray === "object" && fieldConfig.getArray.mapTo) {
                // Transform array: [{code: 1}, {code: 2}] -> [{category_id: 1}, {category_id: 2}]
                prepared[key] = prepared[key].map(item => {
                    const mappedObj = {};
                    mappedObj[fieldConfig.getArray.mapTo] = item?.code ?? item;
                    return mappedObj;
                });
            }
        }

        // Remove if empty
        if (fieldConfig.removeIfEmpty === true && !Utils.isDefined({ value: prepared[key] })) {
            delete prepared[key];
        }
    });

    return prepared;
}

/**
 * Convierte datos JSON a FormData cuando hay archivos seleccionados.
 * Si no hay archivos, retorna el objeto JSON original.
 * @param {Object} jsonData - Datos del formulario (objeto plano)
 * @param {Object} options - Opciones {excludeFields: string[], fileInputs: {elementId: string, fieldName: string}[]}
 * @param {string[]} options.excludeFields - Campos a excluir al convertir (ej: URLs de imágenes que serán reemplazadas por archivos)
 * @param {Array<{elementId: string, fieldName: string}>} options.fileInputs - Configuración de inputs de archivo
 * @returns {Object|FormData} Objeto JSON si no hay archivos, FormData si hay archivos
 */
export function toFormDataWithFiles(jsonData, options = {}) {
    const { excludeFields = [], fileInputs = [] } = options;

    const hasFiles = fileInputs.some(({ elementId }) => {
        const el = document.getElementById(elementId);
        return el?.files?.length > 0;
    });

    // if (!hasFiles) {
        // return jsonData;
    // }

    const formDataInstance = new FormData();

    Object.keys(jsonData).forEach((key) => {
        if (!excludeFields.includes(key)) {
            const value = jsonData[key];
            if (value !== undefined && value !== null) {
                formDataInstance.append(key, value instanceof Blob ? value : String(value));
            }
        }
    });

    fileInputs.forEach(({ elementId, fieldName }) => {
        const el = document.getElementById(elementId);
        if (el?.files?.length > 0) {
            formDataInstance.append(fieldName, el.files[0]);
        }
    });

    return formDataInstance;
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
                formErrorsObject[field].push(normalizeFieldError(error.message || error));
            }
        });
    } else if (typeof errors === "object" && errors !== null) {
        Object.entries(errors).forEach(([field, messages]) => {
            const fieldMessages = Array.isArray(messages) ? messages : [messages];
            formErrorsObject[field] = fieldMessages.map(normalizeFieldError);
        });
    }
}

export function normalizeFieldError(message) {
    const normalized = String(message ?? "").trim();

    if(!normalized) return "";

    return normalized.replace(/^[^:]{1,80}:\s*/, "");
}

export function getDescriptiveErrors(errors = {}, errorLabels = {}) {
    return Object.entries(errors ?? {}).flatMap(([field, messages]) => {
        const baseField = String(field).split(".")[0];
        const label = errorLabels[field] || errorLabels[baseField] || baseField;
        const fieldMessages = Array.isArray(messages) ? messages : [messages];

        return fieldMessages
            .map(normalizeFieldError)
            .filter(Boolean)
            .map(message => `${label}: ${message}`);
    });
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
 * Maneja errores de respuesta de formulario de forma centralizada
 * @param {Object} options - Includes errorLabels for contextual alert summaries.
 * @param {Object} options.result - Resultado de la petición con code, errors, data
 * @param {Object} options.formErrorsObject - Objeto donde se almacenarán los errores (ej: entityForms.errors)
 * @param {Object} options.config - Configuración con mensajes (ej: this.config.messages)
 * @param {boolean} options.setErrors - Si se deben establecer los errores en el objeto (default: true)
 * @param {boolean} options.showAlert - Si se debe mostrar una alerta (default: true)
 */
export function handleFormResponseErrors({
    result,
    formErrorsObject,
    config = {},
    errorLabels = {},
    setErrors = true,
    showAlert = true
}) {
    const isValidationError = result?.code === 422;
    const hasFieldErrors = result?.errors && Object.keys(result.errors).length > 0;
    const errorMessage = result?.data?.msg || config.messages?.errorValidate || "Por favor, revisar el formulario para continuar.";

    if(setErrors && formErrorsObject) {
        handleFormErrors(result?.errors ?? {}, formErrorsObject);
    }

    if(showAlert) {
        const messages = isValidationError && hasFieldErrors
            ? getDescriptiveErrors(result.errors, errorLabels)
            : [];
        const msgContent = (isValidationError && hasFieldErrors)
            ? (config.messages?.errorValidateFields || "El formulario contiene errores de validación. Por favor, revise los campos marcados en rojo y corrija la información según se indique.")
            : errorMessage;

        Alerts.generateAlert({type: "error", messages, msgContent});
    }
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
