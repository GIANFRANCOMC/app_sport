import * as Utils from "./Utils.js";

export const requestRoute = `${window.location.protocol}//${window.location.hostname}`;

export const generalConfig = {
    project: {
        company: "BLAPOS"
    },
    essential: Utils.getEssential(),
    messages: {
        withoutResults: "Sin registros",
        errorValidate: "Por favor, revisar el formulario para continuar.",
        errorSearchValidate: "Para realizar la búsqueda, complete los siguientes campos:",
        catchError: "Ha ocurrido un error al realizar la acción.",
        errorValidateFields: "El formulario contiene errores de validación. Por favor, revise los campos marcados en rojo y corrija la información según se indique."
    },
    forms: {
        classes: {
            title: "fw-bold colon-at-end fs-6",
            select2: "bg-white"
        },
        inputs: {
            maxlength: 999,
            required: "*",
            round: 2,
            minValue: 0,
            maxValue: 9999999,
            maxSize: 2048
        },
        errors: {
            functions: {
                beetwen: {
                    numeric: (min = "", max = "") => `Debe estar entre ${min} y ${max}`
                },
                min: {
                    numeric: (min = "") => `Debe ser al menos ${min}`
                },
                max: {
                    numeric: (min = "") => `No debe ser mayor que ${min}`
                },
                maxSize: {
                    numeric: (max = "") => `El archivo debe pesar menos de ${max} MB`
                }
            },
            labels: {
                required: "Es obligatorio",
                min_number_0: "Debe ser mayor a 0",
                min_equal_number_0: "Debe ser mayor o igual a 0",
                not_valid_extension: "Extensión no válida"
            },
            styles: {
                default: "text-danger"
            }
        }
    },
    colors: {
        charts: {
            default: {
                backgroundColor: "#ffffff",
                bodyColor: "#6f6b7d",
                labelColor: "#7a7883",
                borderColor: "#dbdade",
                titleColor: "#5d596c",
                defaultColor: "#1da1f2",
                primaryColor: "#7367F0",
                successColor: "#28C76F",
                dangerColor: "#EA5455"
            }
        }
    },
    assets: {
        backgrounds: {
            images: {
                bg1: "/System/assets/img/utils/customers/carnet/1.png",
                bg2: "/System/assets/img/utils/customers/carnet/2.png"
            }
        }
    }
};
