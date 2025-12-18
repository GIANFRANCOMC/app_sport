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
        errors.push(`${fieldName ? `${fieldName}: ` : ""}Es obligatorio`);
    }

    if (rules.email && Utils.isDefined({ value }) && !Utils.isValidEmail(value)) {
        errors.push(`${fieldName ? `${fieldName}: ` : ""}Formato de correo inválido`);
    }

    if (rules.url && Utils.isDefined({ value }) && !Utils.isValidUrl(value)) {
        errors.push(`${fieldName ? `${fieldName}: ` : ""}URL inválida`);
    }

    if (rules.number && Utils.isDefined({ value }) && !Utils.isNumber({ value, minValue: rules.min ?? 0 })) {
        errors.push(`${fieldName ? `${fieldName}: ` : ""}Debe ser un número válido`);
    }

    if (rules.minLength && Utils.isDefined({ value }) && String(value).length < rules.minLength) {
        errors.push(`${fieldName ? `${fieldName}: ` : ""}Debe tener al menos ${rules.minLength} caracteres`);
    }

    if (rules.maxLength && Utils.isDefined({ value }) && String(value).length > rules.maxLength) {
        errors.push(`${fieldName ? `${fieldName}: ` : ""}No debe exceder ${rules.maxLength} caracteres`);
    }

    if (rules.min && Utils.isDefined({ value }) && Number(value) < rules.min) {
        errors.push(`${fieldName ? `${fieldName}: ` : ""}Debe ser al menos ${rules.min}`);
    }

    if (rules.max && Utils.isDefined({ value }) && Number(value) > rules.max) {
        errors.push(`${fieldName ? `${fieldName}: ` : ""}No debe ser mayor que ${rules.max}`);
    }

    if (rules.custom && typeof rules.custom === "function") {
        const customError = rules.custom(value);
        if (customError) {
            errors.push(customError);
        }
    }

    return errors;
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
    numberRange: (min, max) => ({ required: true, number: true, min, max })
};

