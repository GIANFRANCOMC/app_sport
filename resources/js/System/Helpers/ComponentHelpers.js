/**
 * Helpers para componentes Vue
 * Funciones útiles para trabajar con componentes
 */

import * as Alerts from "./Alerts.js";
import * as Utils from "./Utils.js";
import * as Requests from "./Requests.js";
import { TEXT, CSS_CLASSES } from "./ModuleConstants.js";

/**
 * Crea acciones estándar para tablas
 */
export function createTableActions(actions = []) {
    const defaultActions = [
        {
            key: "edit",
            label: TEXT.EDIT,
            icon: "fa fa-pencil",
            class: "btn-warning"
        },
        {
            key: "delete",
            label: TEXT.DELETE,
            icon: "fa fa-trash",
            class: "btn-danger"
        }
    ];

    return [...defaultActions, ...actions];
}

/**
 * Maneja confirmación antes de acción
 */
export function confirmAction(message, onConfirm, onCancel = null) {
    Swal.fire({
        html: `<span>${message}</span>`,
        icon: "question",
        allowOutsideClick: false,
        showCancelButton: true,
        confirmButtonText: TEXT.YES,
        cancelButtonText: TEXT.NO,
        customClass: {
            confirmButton: CSS_CLASSES.BUTTON_PRIMARY,
            cancelButton: CSS_CLASSES.BUTTON_SECONDARY + " ms-3"
        }
    }).then(result => {
        if (result.isConfirmed && onConfirm) {
            onConfirm();
        } else if (result.isDismissed && onCancel) {
            onCancel();
        }
    });
}


