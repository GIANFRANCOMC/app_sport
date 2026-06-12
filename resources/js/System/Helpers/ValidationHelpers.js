/**
 * Helpers de validación
 * Funciones reutilizables para validación de formularios
 */

import * as Utils from "./Utils.js";
import { VALIDATION } from "./ModuleConstants.js";

/**
 * Valida un campo según reglas
 */
export function validateField(value, rules, fieldName = "") {
    const errors = [];

    if (rules.required && !Utils.isDefined({ value })) {
        errors.push("Campo obligatorio.");
    }

    if (rules.email && Utils.isDefined({ value }) && !Utils.isValidEmail(value)) {
        errors.push("Ingrese un correo válido.");
    }

    if (rules.url && Utils.isDefined({ value }) && !Utils.isValidUrl(value)) {
        errors.push("Ingrese una URL válida.");
    }

    if (rules.ip && Utils.isDefined({ value }) && !Utils.isValidIp(value)) {
        errors.push("Ingrese una dirección IP válida.");
    }

    if (rules.number && Utils.isDefined({ value }) && !Utils.isNumber({ value, minValue: rules.min ?? 0 })) {
        errors.push("Ingrese un número válido.");
    }

    if (rules.min !== undefined && rules.min !== null && Utils.isDefined({ value }) && Number(value) < rules.min) {
        errors.push(`El valor mínimo permitido es ${rules.min}.`);
    }

    if (rules.max !== undefined && rules.max !== null && Utils.isDefined({ value }) && Number(value) > rules.max) {
        errors.push(`El valor máximo permitido es ${rules.max}.`);
    }

    // Ejecutar validación custom primero, ya que si falla el formato básico,
    // no tiene sentido validar longitud u otras reglas
    if (rules.custom && typeof rules.custom === "function") {
        const customError = rules.custom(value);
        if (customError) {
            errors.push(customError);
            // Si hay error custom, retornar solo ese error (es el más importante)
            return errors;
        }
    }

    // Solo validar longitud si no hay error custom (formato básico correcto)
    if (rules.minLength && Utils.isDefined({ value }) && String(value).length < rules.minLength) {
        errors.push(`Debe tener al menos ${rules.minLength} caracteres.`);
    }

    if (rules.maxLength && Utils.isDefined({ value }) && String(value).length > rules.maxLength) {
        errors.push(`Debe tener como máximo ${rules.maxLength} caracteres.`);
    }

    // Retornar solo el primer error si hay múltiples
    return errors.length > 0 ? [errors[0]] : errors;
}

/**
 * Valida un formulario completo
 */
export function validateForm(formData, validationRules, config = {}) {
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
 * Valida que un valor contenga solo dígitos (0-9)
 * @param {*} value - Valor a validar
 * @param {string} fieldName - Nombre del campo para el mensaje de error (opcional)
 * @returns {string|null} Mensaje de error o null si es válido
 */
export function validateOnlyDigits(value, fieldName = "") {
    if(!value) return null;
    const stringValue = String(value);
    if(!/^\d+$/.test(stringValue)) {
        return fieldName ? `${fieldName}: Debe contener solo números.` : "Debe contener solo números.";
    }
    return null;
}

/**
 * Reglas de validación comunes
 */
export const CommonValidationRules = {
    required: { required: true },
    email: { required: true, email: true },
    url: { required: true, url: true },
    number: { required: true, number: true },
    optionalEmail: { email: true },
    optionalUrl: { url: true },
    optionalNumber: { number: true },
    textMaxLength: (maxLength) => ({ maxLength }),
    textMinLength: (minLength) => ({ required: true, minLength }),
    numberRange: (min, max) => ({ required: true, number: true, min, max }),
    onlyDigits: (fieldName = "") => ({ custom: (value) => validateOnlyDigits(value, fieldName) })
};
