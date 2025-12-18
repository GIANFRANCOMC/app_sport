/**
 * Helpers para componentes Vue
 * Funciones útiles para trabajar con componentes
 */

import * as Alerts from "./Alerts.js";
import * as Utils from "./Utils.js";
import * as Requests from "./Requests.js";
import { TEXT, CSS_CLASSES } from "./ModuleConstants.js";

/**
 * Inicializa un componente CRUD estándar
 */
export function initCrudComponent(component, config = {}) {
    const {
        entity = "entity",
        menuId = `menu-${entity}`,
        parentMenuId = null,
        autoLoadList = true
    } = config;

    // Agregar método mounted si no existe
    if (!component.mounted) {
        component.mounted = async function() {
            if (parentMenuId) {
                Utils.navbarItem(parentMenuId, { addClass: "open" });
            }
            
            Utils.navbarItem(menuId, {});
            Alerts.swals({ type: "initParams" });

            const initParams = await this.initParams?.({});
            const initOthers = await this.initOthers?.({});

            if (initParams && initOthers) {
                Alerts.swals({ show: false });
                
                if (autoLoadList && this.listEntity) {
                    this.listEntity({});
                }
            }
        };
    }

    return component;
}

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
 * Crea columnas estándar para tablas
 */
export function createTableColumns(columns = []) {
    return columns.map(col => ({
        key: col.key,
        label: col.label,
        class: col.class || "",
        style: col.style || {},
        bodyClass: col.bodyClass || "",
        type: col.type || "text",
        formatter: col.formatter || null
    }));
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

/**
 * Maneja acción con confirmación
 */
export async function handleActionWithConfirmation(
    message,
    action,
    {
        onSuccess = null,
        onError = null,
        successMessage = null,
        errorMessage = null
    } = {}
) {
    return new Promise((resolve) => {
        confirmAction(message, async () => {
            try {
                Alerts.swals({});
                const result = await action();
                
                if (result && Requests.valid({ result })) {
                    if (successMessage) {
                        Alerts.generateAlert({
                            type: "success",
                            msgContent: successMessage
                        });
                    }
                    
                    if (onSuccess) {
                        onSuccess(result);
                    }
                    
                    resolve(result);
                } else {
                    if (errorMessage) {
                        Alerts.toastrs({
                            type: "error",
                            subtitle: errorMessage
                        });
                    }
                    
                    if (onError) {
                        onError(result);
                    }
                    
                    Alerts.swals({ show: false });
                    resolve(null);
                }
            } catch (error) {
                Alerts.swals({ show: false });
                Alerts.toastrs({
                    type: "error",
                    subtitle: error?.message || "Ha ocurrido un error"
                });
                
                if (onError) {
                    onError(error);
                }
                
                resolve(null);
            }
        });
    });
}

