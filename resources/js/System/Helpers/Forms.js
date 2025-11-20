import * as Utils from "./Utils.js";

/**
 * Initializes the data structure for a CRUD form
 * @param {Object} fields - Object with fields and their default values
 * @returns {Object} Form data structure
 */
export function initFormData(fields = {}) {

    const defaultFields = {
        id: null,
        ...fields
    };

    return defaultFields;

}

/**
 * Clears a form by setting default values
 * @param {Object} formData - Form object to clear
 * @param {Object} defaultValues - Default values for each field
 */
export function clearFormData(formData, defaultValues = {}) {

    Object.keys(formData).forEach(key => {

        const defaultValue = defaultValues[key] !== undefined ? defaultValues[key] : (typeof formData[key] === "number" ? null : "");

        formData[key] = defaultValue;

    });

}

/**
 * Prepares form data before sending
 * @param {Object} formData - Form data
 * @param {Object} fieldConfig - Field configuration (trim, normalize, etc.)
 * @returns {Object} Prepared data
 */
export function prepareFormData(formData, fieldConfig = {}) {

    const prepared = Utils.cloneJson(formData);

    Object.keys(prepared).forEach(key => {

        const config = fieldConfig[key] || {};

        if(config.trim !== false && typeof prepared[key] === "string") {

            prepared[key] = String(prepared[key]).trim();

        }

        if(config.normalize === true) {

            prepared[key] = Utils.normalizeOptional(prepared[key]);

        }

        if(config.toNumber === true && Utils.isDefined({value: prepared[key]})) {

            prepared[key] = Utils.isNumber({value: prepared[key], minValue: config.minValue ?? 0}) ? Number(prepared[key]) : null;

        }

        if(config.getCode === true && prepared[key]?.code !== undefined) {

            prepared[key] = prepared[key].code;

        }

    });

    return prepared;

}

/**
 * Validates a generic form
 * @param {Object} formData - Form data
 * @param {Object} rules - Validation rules {field: {required, email, url, number, min, max, custom}}
 * @param {Object} config - Configuration {isDescriptive: boolean, errorLabels: Object}
 * @returns {Object} {bool: boolean, errors: Object}
 */
export function validateFormData(formData, rules = {}, config = {}) {

    const result = {
        bool: true,
        errors: {}
    };

    const isDescriptive = config.isDescriptive === true;
    const errorLabels = config.errorLabels || {};

    Object.keys(rules).forEach(field => {

        const rule = rules[field];
        const value = formData[field];
        const fieldErrors = [];

        // Required
        if(rule.required === true && !Utils.isDefined({value})) {

            const label = errorLabels[field] || field;
            fieldErrors.push(`${isDescriptive ? `${label}:` : ""} ${errorLabels.required || "Required"}`);
            result.bool = false;

        }

        // Email
        if(rule.email === true && Utils.isDefined({value}) && !Utils.isValidEmail(value)) {

            const label = errorLabels[field] || field;
            fieldErrors.push(`${isDescriptive ? `${label}:` : ""} Invalid format`);
            result.bool = false;

        }

        // URL
        if(rule.url === true && Utils.isDefined({value}) && !Utils.isValidUrl(value)) {

            const label = errorLabels[field] || field;
            fieldErrors.push(`${isDescriptive ? `${label}:` : ""} Invalid format`);
            result.bool = false;

        }

        // Number
        if(rule.number === true && Utils.isDefined({value}) && !Utils.isNumber({value, minValue: rule.min ?? 0})) {

            const label = errorLabels[field] || field;
            fieldErrors.push(`${isDescriptive ? `${label}:` : ""} Must be a valid number`);
            result.bool = false;

        }

        // Custom validation
        if(rule.custom && typeof rule.custom === "function") {

            const customError = rule.custom(value, formData);

            if(customError) {

                fieldErrors.push(customError);
                result.bool = false;

            }

        }

        if(fieldErrors.length > 0) {

            result.errors[field] = fieldErrors;

        }

    });

    return result;

}

