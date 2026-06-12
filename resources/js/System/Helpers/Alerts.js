import { requestRoute } from "./Constants.js";

/**
 * Obtiene la URL absoluta del logomark para que funcione en cualquier ruta
 * @returns {string} URL del logomark desde la raíz del dominio
 */
function getLogomarkSrc() {
    const path = window.ownerApp?.assets?.img?.logomark;
    return path ? `/${path.replace(/^\//, "")}` : "";
}

/** Paleta para `swals()` (alineada con `br-branding.css`). */
const SWAL_BR = {
    primary: "#2899e5",
    secondary: "#1a1a35",
    textMuted: "#64748b",
    surface: "#ffffff",
    shadow: "0 4px 18px rgba(26, 26, 53, 0.1)"
};

/** Layout y tiempos del loader circular (solo `buildSwalLoadingHtml`). */
const SWAL_LOAD = Object.freeze({
    segments: 20,
    chaseCycleS: 2.8,
    holdEndPct: 86,
    resetStartPct: 91,
    buildPhaseEndPct: 78,
    orbitPx: 40
});

function swalBrPrimaryRgbCsv() {
    const n = Number.parseInt(SWAL_BR.primary.slice(1), 16);
    return `${(n >> 16) & 255},${(n >> 8) & 255},${n & 255}`;
}

/** Keyframes acumulativos; radio vía `--br-swal-orbit` en `.br-swal-load`. */
function buildSwalAccumKeyframes(segmentCount, primaryRgb) {

    const {buildPhaseEndPct, holdEndPct, resetStartPct} = SWAL_LOAD;
    const baseT = "rotate(var(--br-t)) translateY(calc(-1 * var(--br-swal-orbit)))";
    const dim = `opacity:0.2;transform:${baseT} scale(0.78);box-shadow:none;filter:brightness(0.95)`;
    const bright = `opacity:1;transform:${baseT} scale(1.06);box-shadow:0 0 14px rgba(${primaryRgb},0.55),0 0 4px rgba(${primaryRgb},0.35);filter:brightness(1)`;

    if(segmentCount <= 1) {

        return `@keyframes brSwalAccum0{0%,100%{${dim}}2%,${holdEndPct}%{${bright}}${resetStartPct}%,100%{${dim}}}`;

    }

    const stepOn = buildPhaseEndPct / (segmentCount - 1);

    let out = "";

    for(let i = 0; i < segmentCount; i++) {

        if(i === 0) {

            out += `@keyframes brSwalAccum0{0%,100%{${dim}}0.55%,${holdEndPct}%{${bright}}${resetStartPct}%,100%{${dim}}}`;
            continue;

        }

        const onPct = Number((i * stepOn).toFixed(3));
        const beforeOn = Number(Math.max(0, onPct - 0.09).toFixed(3));
        out += `@keyframes brSwalAccum${i}{0%,100%{${dim}}${beforeOn}%{${dim}}${onPct}%,${holdEndPct}%{${bright}}${resetStartPct}%,100%{${dim}}}`;

    }

    return out;

}

let swalLoadStaticPrefix = null;

/** Estilos + ticks del loader (constantes; se construye una vez por carga del módulo). */
function getSwalLoadStaticPrefix() {

    if(swalLoadStaticPrefix !== null) {

        return swalLoadStaticPrefix;

    }

    const {segments, chaseCycleS, orbitPx} = SWAL_LOAD;
    const step = 360 / segments;
    const primaryRgb = swalBrPrimaryRgbCsv();
    const keyframes = buildSwalAccumKeyframes(segments, primaryRgb);
    const ticks = Array.from({length: segments}, (_, i) => `<span class="br-swal-load__tick" style="--br-t:${i * step}deg;animation:brSwalAccum${i} ${chaseCycleS}s linear infinite;"></span>`).join("");

    swalLoadStaticPrefix = {
        styleAndTicks: `
                <style>
                    .br-swal-load-popup.swal2-popup {
                        width: min(20rem, calc(100vw - 1.25rem));
                        padding: 1.2rem 1.35rem 1.1rem;
                        border-top: 3px solid ${SWAL_BR.primary};
                        border-radius: 0.5rem;
                        box-shadow: 0 1rem 2.6rem rgba(26, 26, 53, 0.2);
                    }
                    .br-swal-load-popup .swal2-html-container {
                        margin: 0;
                        padding: 0;
                    }
                    .br-swal-load__wrap {
                        padding: 0.15rem 0.25rem 0.25rem;
                        text-align: center;
                        font-family: inherit;
                    }
                    .br-swal-load {
                        --br-swal-orbit: 31px;
                        position: relative;
                        width: 72px;
                        height: 72px;
                        margin: 0 auto 0.75rem;
                    }
                    .br-swal-load__orbit {
                        position: absolute;
                        inset: 0;
                    }
                    .br-swal-load__tick {
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        width: 6px;
                        height: 2px;
                        margin: -1px 0 0 -3px;
                        border-radius: 2px;
                        background: ${SWAL_BR.primary};
                        transform: rotate(var(--br-t)) translateY(calc(-1 * var(--br-swal-orbit)));
                        transform-origin: center center;
                        pointer-events: none;
                    }
                    .br-swal-load__logo-wrap {
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        z-index: 1;
                        width: 58px;
                        height: 58px;
                        border-radius: 50%;
                        background: ${SWAL_BR.surface};
                        box-shadow: ${SWAL_BR.shadow};
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 4px;
                    }
                    .br-swal-load__logo {
                        width: 100%;
                        height: 100%;
                        max-width: 39px;
                        max-height: 39px;
                        object-fit: contain;
                        display: block;
                        transform: scale(1.12);
                        transform-origin: center center;
                    }
                    .br-swal-load-msg__title {
                        margin: 0 0 0.3rem;
                        font-size: 0.98rem;
                        font-weight: 700;
                        line-height: 1.35;
                        color: ${SWAL_BR.secondary};
                        letter-spacing: 0;
                    }
                    .br-swal-load-msg__hint {
                        margin: 0;
                        font-size: 0.76rem;
                        font-weight: 400;
                        line-height: 1.4;
                        color: ${SWAL_BR.textMuted};
                        max-width: 17rem;
                        margin-inline: auto;
                    }
                    ${keyframes}
                </style>
                <div class="br-swal-load__wrap">
                    <div class="br-swal-load" role="status" aria-live="polite" aria-busy="true">
                        <div class="br-swal-load__orbit" aria-hidden="true">${ticks}</div>
                        <div class="br-swal-load__logo-wrap">
            `,
        logoSuffix: `
                        </div>
                    </div>
                    <div class="br-swal-load-msg">
            `
    };

    return swalLoadStaticPrefix;

}

/** HTML del Swal de carga (solo lo usa `swals()`). */
function buildSwalLoadingHtml({message, logoSrc}) {

    const {styleAndTicks, logoSuffix} = getSwalLoadStaticPrefix();
    const titleLine = message.trim().replace(/\.\s*$/, "");
    const titleHtml = titleLine ? `<p class="br-swal-load-msg__title">${titleLine}</p>` : "";

    return `${styleAndTicks}<img src="${logoSrc}" alt="" class="br-swal-load__logo" width="52" height="52" decoding="async">${logoSuffix}${titleHtml}
                        <p class="br-swal-load-msg__hint">Estamos procesando la información. No cierres esta ventana.</p>
                    </div>
                </div>
            `;

}

export function swals({show = true, type = "default", timeout = 0}) {

    if(show) {

        let message = "";

        switch(type) {
            case "default":
                message = "Cargando.";
                break;

            case "consult":
                message = "Consultando información.";
                break;

            case "initParams":
                message = "";
                break;
        }

        Swal.fire({
            target: document.body,
            html: buildSwalLoadingHtml({message, logoSrc: getLogomarkSrc()}),
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            stopKeydownPropagation: true,
            showConfirmButton: false,
            customClass: {
                container: "br-swal-top-layer",
                popup: "br-swal-load-popup",
                htmlContainer: "br-swal-load-html"
            },
            didOpen: () => {

                const container = Swal.getContainer();

                container?.style.setProperty("z-index", "2147483647", "important");

            }
        });

    }else {

        timeout > 0 ? setTimeout(() => Swal.close(), timeout) : Swal.close();

    }

}

export function toastrs({type = "success", options = null, code = null, title = null, subtitle = null}) {

    let toastrOptions = {};

    if(!title) {

        switch(type) {
            case "error":
                title = "¡Ups! Algo salió mal";
                break;

            case "success":
                title = "Exitoso";
                break;

            case "warning":
                title = "Atención";
                break;
        }

    }

    if(!options) {

        toastrOptions = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            showMethod: "slideDown",
            timeOut: 2000
        };

    }

    toastr.options = toastrOptions;
    toastr[type](subtitle ?? "", title ?? "");

}

/**
 * Controla los tooltips de Bootstrap 5 (misma API que inicializa `main.js` con `bootstrap.Tooltip`).
 *
 * @param {Object} options
 * @param {boolean} [options.show=true] — `false`: oculta y destruye tooltips; `true`: limpia residuos y crea instancias en el DOM actual.
 * @param {number} [options.time] — Retraso en ms (0 = inmediato). Si se omite: `10` con `show: true`, `0` con `show: false`.
 * @param {string} [options.selector='[data-bs-toggle="tooltip"]'] — Selectores de disparadores.
 */
function bootstrapTooltipOptions() {

    return {
        animation: false,
        boundary: "viewport",
        customClass: "br-tooltip",
        delay: {
            show: 0,
            hide: 0
        },
        trigger: "hover focus"
    };

}

/**
 * Recrea un tooltip con la configuración visual global.
 *
 * @param {HTMLElement|null} element
 * @param {string|null} title
 * @returns {Object|null}
 */
export function createTooltip(element, title = null) {

    const Bootstrap = window.bootstrap;

    if(!element || !Bootstrap?.Tooltip) return null;

    const currentTitle = title
        ?? element.getAttribute("title")
        ?? element.getAttribute("data-bs-original-title")
        ?? "";
    const existing = Bootstrap.Tooltip.getInstance(element);

    existing?.dispose();
    element.removeAttribute("data-bs-original-title");
    element.removeAttribute("aria-describedby");

    if(currentTitle) element.setAttribute("title", currentTitle);

    return new Bootstrap.Tooltip(element, bootstrapTooltipOptions());

}

export function tooltips(options = {}) {

    const {show = true, selector = "[data-bs-toggle=\"tooltip\"]"} = options;
    const time = options.time !== undefined ? options.time : (show ? 10 : 0);

    const run = () => {

        const Bootstrap = window.bootstrap;

        if(!Bootstrap?.Tooltip) {

            return;

        }

        const triggers = document.querySelectorAll(selector);

        if(show) {

            document.querySelectorAll(".tooltip").forEach((tooltip) => tooltip.remove());

            triggers.forEach((el) => {

                const currentTitle = el.getAttribute("title") ?? el.getAttribute("data-bs-original-title") ?? "";

                createTooltip(el, currentTitle);

            });

        }else {

            triggers.forEach((el) => {

                const existing = Bootstrap.Tooltip.getInstance(el);

                if(existing) {

                    existing.dispose();

                }

            });

            document.querySelectorAll(".tooltip").forEach((tooltip) => tooltip.remove());

        }

    };

    time > 0 ? setTimeout(run, time) : run();

}

/**
 * Oculta únicamente el tooltip asociado a un disparador y conserva las demás instancias.
 *
 * @param {HTMLElement|null} element
 */
export function dismissTooltip(element) {

    if(!element) return;

    try {

        const Bootstrap = window.bootstrap;
        const instance = Bootstrap?.Tooltip?.getInstance(element);

        element.blur();
        instance?.hide();

    }catch(error) {

        element.removeAttribute("aria-describedby");

    }

}

export function modals({type = "show", id = null, timeout = 0}) {

    if(["show"].includes(type)) {

        timeout > 0 ? setTimeout(() => $(`#${id}`).modal("show"), timeout) : $(`#${id}`).modal("show");


    }else if(["hide"].includes(type)) {

        timeout > 0 ? setTimeout(() => $(`#${id}`).modal("hide"), timeout) : $(`#${id}`).modal("hide");

    }

}

/**
 * Genera un alert de SweetAlert2 con formato optimizado
 * @param {Array} messages - Array de mensajes para mostrar en tabla
 * @param {String} type - Tipo de alert (success, error, warning, info)
 * @param {String} headerTitle - Título del header del alert
 * @param {String} msgContent - Contenido del mensaje (se envuelve automáticamente con div fw-semibold si no contiene HTML)
 * @param {Array} keys - Claves para la tabla de mensajes
 * @param {Number} width - Ancho del alert
 */
export function generateAlert({messages = [], type = "warning", headerTitle = null, msgContent = null, keys = [], width = 550}) {

	let tableAlertHtml = messages.length > 0 ? generateTableAlert({messages, type, keys}) : "";

	// Envolver msgContent automáticamente con div fw-semibold si es un string simple (sin HTML)
	let formattedMsgContent = msgContent;

	if(msgContent && typeof msgContent === "string") {

		const trimmedContent = msgContent.trim();
		const hasHtml = trimmedContent.startsWith("<") || trimmedContent.includes("<div") || trimmedContent.includes("<span") || trimmedContent.includes("<p");

		if(!hasHtml) {

			formattedMsgContent = `<div class="fw-semibold">${msgContent}</div>`;

		}

	}

	Swal.fire({title             : headerTitle,
               icon              : type,
		       allowOutsideClick : false,
		       allowEscapeKey    : false,
		       html              : `${formattedMsgContent ?? ""} <div>${tableAlertHtml}</div>`,
               width             : width,
               buttonsStyling    : false,
               confirmButtonText: "Entendido",
               customClass: {
                   popup: `br-swal-alert br-swal-alert--${type}`,
                   confirmButton: "br-btn br-btn-primary"
               }});

}

export function generateTableAlert({messages, keys = []}) {

    let header = "",
        content = "";

    if(keys.length === 0) {

        header = `<tr class="text-center align-middle">
                        <td class="text-center text-nowrap fw-semibold">N°</td>
                        <td class="text-center text-nowrap fw-semibold">Mensaje</td>
                  </tr>`;

        content = `${messages.reduce((carry, singleMessage, index) => carry+/*html*/`
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="text-start">${singleMessage}</td>
                    </tr>
                  `, "")}`;

    }else {

        header = `<tr class="text-center align-middle">
                        <td class="text-center text-nowrap fw-semibold">N°</td>
                        ${keys.reduce((carryKey, singleMessageKey, indexKey) => carryKey+/*html*/`
                            <td class="text-center text-nowrap fw-semibold">${singleMessageKey?.label ?? ""}</td>
                        `, "")}
                  </tr>`;

        content = `${messages.reduce((carry, singleMessage, index) => carry+/*html*/`
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        ${keys.reduce((carryKey, singleMessageKey, indexKey) => carryKey+/*html*/`
                            <td class="text-start">${singleMessage.length > 0 ? (singleMessage[0][singleMessageKey?.column] ?? "") : ""}</td>
                        `, "")}
                    </tr>
                   `, "")}`;

    }

	let result = messages.length === 0 ? "" : `
	<table class="table table table-hover table-bordered mt-3">
		<thead class="table-light">
			${header}
		</thead>
		<tbody class="table-border-bottom-0 bg-white">
			${content}
		</tbody>
	</table>
	`;

	return result;

}
