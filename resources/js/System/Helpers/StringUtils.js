/**
 * Utilidades para manejo de strings
 */

import { isDefined } from "./ValidationUtils.js";

/**
 * Trunca un string a una longitud máxima
 * @param {Object} options - {value: string, length: number}
 * @returns {string} String truncado
 */
export function truncate({value, length = 40}) {
    if (!value) return "";
    return value.length > length ? value.slice(0, length) + "..." : value;
}


/**
 * Normaliza un valor opcional (convierte strings vacíos a null)
 * @param {*} value - Valor a normalizar
 * @returns {*} Valor normalizado
 */
export function normalizeOptional(value) {
    if (!isDefined({value})) {
        return null;
    }

    const sanitized = String(value ?? "").trim();
    return sanitized === "" ? null : sanitized;
}

/**
 * Valida si un string es un email válido
 * @param {*} value - Valor a validar
 * @returns {boolean} true si es válido
 */
export function isValidEmail(value) {
    const email = String(value ?? "").trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email) && !/[A-Z]/.test(email);
}

/**
 * Valida si un string es una URL válida
 * @param {*} value - Valor a validar
 * @returns {boolean} true si es válido
 */
export function isValidUrl(value) {
    const candidate = typeof value === "string" ? value : value?.url;

    if (!isDefined({value: candidate})) {
        return false;
    }

    try {
        const url = new URL(String(candidate ?? "").trim());
        return ["http:", "https:"].includes(url.protocol);
    } catch (e) {
        return false;
    }
}

/**
 * Valida si un string es una dirección IP válida (IPv4)
 * @param {*} value - Valor a validar
 * @returns {boolean} true si es válido
 */
export function isValidIp(value) {
    const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
    return ipRegex.test(String(value ?? "").trim());
}

/**
 * Genera un código aleatorio
 * @param {Object} options - {length: number}
 * @returns {string} Código generado
 */
export function generateCode({length = 12}) {
    const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    let randomString = "";

    for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * characters.length);
        randomString += characters[randomIndex];
    }

    return randomString;
}

/**
 * Genera una contraseña aleatoria
 * @param {Object} options - {length: number}
 * @returns {string} Contraseña generada
 */
export function generatePassword({length = 10}) {
    const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*()_";
    let randomString = "";

    for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * characters.length);
        randomString += characters[randomIndex];
    }

    return randomString;
}

/**
 * Codifica texto a Base64 UTF-8
 * @param {string} text - Texto a codificar
 * @returns {string} Texto codificado
 */
export function encodeBase64UTF8(text) {
    const bytes = new TextEncoder().encode(text);
    const base64 = btoa(String.fromCharCode(...bytes));
    return base64;
}

/**
 * Decodifica Base64 UTF-8 a texto
 * @param {string} base64 - Texto codificado
 * @returns {string} Texto decodificado
 */
export function decodeBase64UTF8(base64) {
    const binary = atob(base64);
    const bytes = Uint8Array.from(binary, char => char.charCodeAt(0));
    const text = new TextDecoder().decode(bytes);
    return text;
}

