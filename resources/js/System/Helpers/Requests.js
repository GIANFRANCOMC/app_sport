import axios from "axios";
import * as Alerts from "./Alerts.js";
import * as Utils from "./Utils.js";
import { requestRoute, generalConfig } from "./Constants.js";

export function config({entity = "", type = "", extras = null}) {

    let baseRoute = `${requestRoute}`;

    let config = {
        routes: generateRoutes({entity, requestRoute: baseRoute})
    };

    if(Utils.isDefined({value: type})) {

        return config?.routes[type];

    }else {

        return config;

    }

}

export function get({route = "", data = {}, showAlert = false}) {

	return new Promise((resolve, reject) => {

		let requestUrl    = route,
            requestConfig = {};

        let params = {...data};

        requestConfig.params = params;

		axios
		.get(requestUrl, requestConfig)
		.then(response => {

			resolve({data: response.data, bool: true});

		})
		.catch(error => {

            const status = error?.response?.status;
            const errorData = error?.response?.data;
            const errorMessage = errorData?.msg || errorData?.message || error?.message || error;

            if(showAlert) {

                if([500].includes(status)) {

                    Alerts.toastrs({type: "error", subtitle: errorData?.message || errorMessage});

                }else {

                    Alerts.toastrs({type: "error", subtitle: errorMessage});

                }

            }

			resolve({data: errorData || {data: [], msg: errorMessage}, bool: false, code: status});

		})
		.finally(() => {

			Alerts.tooltips({});

		});

	});

}

export function post({route = "", data = {}, id = "", formData = null}) {

	return new Promise((resolve, reject) => {

		let requestURL  = route,
			requestData = formData ?? {...data, id};

        if(!Utils.isDefined({value: requestData?.id})) {

            delete requestData.id;

        }

		axios
		.post(requestURL, requestData)
		.then(response => {

			resolve({data: response.data, bool: true});

		})
		.catch(error => {

            const status = error?.response?.status;
            const errorData = error?.response?.data;
            const errorMessage = errorData?.msg || errorData?.message || error?.message || error;

            if([405].includes(status)) {

                resolve({data: {msg: `${errorData?.message || errorMessage} (Code ${status})`}, bool: false, code: status});

            }else if([422].includes(status)) {

                resolve({data: {msg: `${errorData?.message || errorMessage} (Code ${status})`}, errors: errorData?.errors, bool: false, code: status});

            }else if([404].includes(status)) {

                resolve({data: errorData || {msg: errorMessage}, bool: false, code: status});

            }else {

                resolve({data: errorData || {msg: errorMessage}, bool: false, code: status});

            }

		})
		.finally(() => {

			Alerts.tooltips({});

		});

	});

}

export function patch({route = "", data = {}, id = "", formData = null}) {

	return new Promise((resolve, reject) => {

		let requestURL  = `${route}/${id}`,
			requestData = formData ?? {...data, id};

        if(!Utils.isDefined({value: requestData?.id})) {

            delete requestData.id;

        }

        const isFormData = formData !== null;
        const method = isFormData ? "POST" : "PATCH";
        const requestConfig = isFormData  ? { headers: { "Content-Type": "multipart/form-data" } } : { headers: { "Content-Type": "application/json" } };

        if(isFormData) {

			formData.append("_method", "PATCH");

		}

		axios
        axios({
            method: method,
            url: requestURL,
            data: requestData,
            headers: requestConfig.headers
        })
		.then(response => {

			resolve({data: response.data, bool: true});

		})
		.catch(error => {

            const status = error?.response?.status;
            const errorData = error?.response?.data;
            const errorMessage = errorData?.msg || errorData?.message || error?.message || error;

            if([405].includes(status)) {

                resolve({data: {msg: `${errorData?.message || errorMessage} (Code ${status})`}, bool: false, code: status});

            }else if([422].includes(status)) {

                resolve({data: {msg: `${errorData?.message || errorMessage} (Code ${status})`}, errors: errorData?.errors, bool: false, code: status});

            }else if([404].includes(status)) {

                resolve({data: errorData || {msg: errorMessage}, bool: false, code: status});

            }else {

                resolve({data: errorData || {msg: errorMessage}, bool: false, code: status});

            }

		})
		.finally(() => {

			Alerts.tooltips({});

		});

	});

}

export function valid({result}) {

    return result?.bool && result?.data?.bool;

}

// Configuración de rutas especiales por entidad
const ENTITY_SPECIAL_ROUTES = {
    dashboard: {
        initData: "initData"
    },
    sales: {
        cancel: "cancel"
    },
    tracking_subscriptions: {
        cancel: "cancel"
    },
    tracking_attendances: {
        cancel: "cancel",
        qrCamera: "qrCamera",
        qrScanner: "qrScanner"
    },
    tracking_customers: {
        getTracking: "getTracking"
    },
    customers: {
        getSubscriptions: "getSubscriptions",
        registerBiometricFingerprint: "registerBiometricFingerprint"
    },
    assets_management: {
        assignAssetToBranch: "assignAssetToBranch",
        unassignAssetFromBranch: "unassignAssetFromBranch",
        getAssetAssignments: "getAssetAssignments",
        assetInBranch: "assetInBranch",
        assignToUser: "assignToUser",
        unassignToUser: "unassignToUser"
    },
    reports: {
        sale: "sale"
    },
    helpers: {
        searchDocumentNumber: "searchDocumentNumber",
        sendEmail: "sendEmail"
    }
};

export function generateRoutes({entity, requestRoute}) {

    const baseRoutes = {
        consult: `${requestRoute}/${entity}`,
        list: `${requestRoute}/${entity}/list`,
        get: `${requestRoute}/${entity}/get`,
        create: `${requestRoute}/${entity}/create`,
        store: `${requestRoute}/${entity}`,
        update: `${requestRoute}/${entity}`,
        initParams: `${requestRoute}/${entity}/initParams`
    };

    // Agregar rutas especiales si existen
    const specialRoutes = ENTITY_SPECIAL_ROUTES[entity];
    if (specialRoutes) {
        Object.keys(specialRoutes).forEach(key => {
            baseRoutes[key] = `${requestRoute}/${entity}/${specialRoutes[key]}`;
        });
    }

    return baseRoutes;

}

export function routeReport({resource, params = null, extras = null}) {

    let route = `${config({entity: "reports", type: resource})}`;

    const url = new URL(route);
    const searchParams = url.searchParams;

    Object.keys(params).forEach(key => {

        searchParams.append(key, btoa(params[key]));

    });

    if(["reportSale"].includes(extras?.action)) {

        const expirationDate = Utils.addDuration({startDate: new Date(), type: "month", quantity: 3});

        searchParams.append("expdt", btoa(expirationDate));

    }

    return url.toString();

}
