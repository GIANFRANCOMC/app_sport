import axios from "axios";
import * as Alerts from "./Alerts.js";
import * as Utils from "./Utils.js";
import { requestRoute, generalConfig } from "./Constants.js";

export function route() {

    return `${requestRoute}`;

}

export function config({entity = "", type = "", extras = null}) {

    let requestRoute = route({});

    let config = {
        routes: generateRoutes({entity, requestRoute})
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

            if(showAlert) {

                if([500].includes(error?.response?.status)) {

                    Alerts.toastrs({type: "error", subtitle: error?.response?.data?.message});

                }else {

                    Alerts.toastrs({type: "error", subtitle: error});

                }

            }

			resolve({data: {data: [], msg: error}, bool: false});

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

            if([405].includes(error?.response?.status)) {

                resolve({data: {msg: `${error?.response?.data?.message} (Code ${error?.response?.status})`}, bool: false, code: error?.response?.status});

            }else if([422].includes(error?.response?.status)) {

                resolve({data: {msg: `${error?.response?.data?.message} (Code ${error?.response?.status})`}, errors: error?.response?.data?.errors, bool: false, code: error?.response?.status});

            }else {

                resolve({data: {msg: error}, bool: false, code: error?.response?.status});

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

            if([405].includes(error?.response?.status)) {

                resolve({data: {msg: `${error?.response?.data?.message} (Code ${error?.response?.status})`}, bool: false, code: error?.response?.status});

            }else if([422].includes(error?.response?.status)) {

                resolve({data: {msg: `${error?.response?.data?.message} (Code ${error?.response?.status})`}, errors: error?.response?.data?.errors, bool: false, code: error?.response?.status});

            }else {

                resolve({data: {msg: error}, bool: false, code: error?.response?.status});

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

export function generateRoutes({entity, requestRoute}) {

    let routes = {
        consult: `${requestRoute}/${entity}`,
        list: `${requestRoute}/${entity}/list`,
        get: `${requestRoute}/${entity}/get`,
        create: `${requestRoute}/${entity}/create`,
        store: `${requestRoute}/${entity}`,
        update: `${requestRoute}/${entity}`,
        initParams: `${requestRoute}/${entity}/initParams`,
    };

    if(["dashboard"].includes(entity)) {

        routes.initData = `${requestRoute}/${entity}/initData`;

    }else if(["sales"].includes(entity)) {

        routes.cancel = `${requestRoute}/${entity}/cancel`;

    }else if(["tracking_subscriptions"].includes(entity)) {

        routes.cancel = `${requestRoute}/${entity}/cancel`;

    }else if(["tracking_attendances"].includes(entity)) {

        routes.cancel    = `${requestRoute}/${entity}/cancel`;
        routes.qrCamera  = `${requestRoute}/${entity}/qrCamera`;
        routes.qrScanner = `${requestRoute}/${entity}/qrScanner`;

    }else if(["tracking_customers"].includes(entity)) {

        routes.getTracking = `${requestRoute}/${entity}/getTracking`;

    }else if(["customers"].includes(entity)) {

        routes.getSubscriptions = `${requestRoute}/${entity}/getSubscriptions`;

    }else if(["assets_management"].includes(entity)) {

        routes.assignAssetToBranch     = `${requestRoute}/${entity}/assignAssetToBranch`;
        routes.unassignAssetFromBranch = `${requestRoute}/${entity}/unassignAssetFromBranch`;
        routes.getAssetAssignments     = `${requestRoute}/${entity}/getAssetAssignments`;
        routes.assetInBranch           = `${requestRoute}/${entity}/assetInBranch`;
        routes.assignToUser            = `${requestRoute}/${entity}/assignToUser`;
        routes.unassignToUser          = `${requestRoute}/${entity}/unassignToUser`;

    }else if(["reports"].includes(entity)) {

        routes.sale = `${requestRoute}/${entity}/sale`;

    }else if(["helpers"].includes(entity)) {

        routes.searchDocumentNumber = `${requestRoute}/${entity}/searchDocumentNumber`;
        routes.sendEmail = `${requestRoute}/${entity}/sendEmail`;

    }

    return routes;

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
