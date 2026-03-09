import { requestRoute } from "./Constants.js";

/**
 * Obtiene la URL absoluta del logomark para que funcione en cualquier ruta
 * @returns {string} URL del logomark desde la raíz del dominio
 */
function getLogomarkSrc() {
    const path = window.ownerApp?.assets?.img?.logomark;
    return path ? `/${path.replace(/^\//, "")}` : "";
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

            case "externalConsult":
                message = "Consultando.";
                break;

            case "list":
                message = "Cargando listado.";
                break;

            case "saveForm":
                message = "Guardando formulario.";
                break;
        }

        Swal.fire({
            html: `
                <style>
                    @keyframes fadeInOutSwal {
                        0%, 100% { opacity: 0.2; }
                        50% { opacity: 1; }
                    }

                    .swal-logo {
                        width: 100px;
                        animation: fadeInOutSwal 2s infinite;
                        display: block;
                        margin: 10px auto;
                    }
                </style>
                <span class="h5">${message} Este proceso puede tomar algunos segundos, por favor espere.</span>
                <img src="${getLogomarkSrc()}" class="img-fluid swal-logo mt-1 mb-0">
            `,
            allowOutsideClick: false,
            showConfirmButton: false
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

export function tooltips({show = true, time = 10}) {

    if(show) {

        // time > 0 ? setTimeout(() => $('[data-bs-toggle="tooltip"]').tooltip(), time) : $('[data-bs-toggle="tooltip"]').tooltip();
        time > 0 ? setTimeout(() => $('[data-bs-toggle="tooltip"]').tooltip(), time) : $('[data-bs-toggle="tooltip"]').tooltip();

    }else {

        // time > 0 ? setTimeout(() => $(".tooltip").tooltip("hide"), time) : $(".tooltip").tooltip("hide");
        time > 0 ? setTimeout(() => $(".tooltip").hide(), time) : $(".tooltip").hide();

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
               confirmButtonText: "Entendido",
               customClass: {
                   confirmButton: "btn btn-primary waves-effect"
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
