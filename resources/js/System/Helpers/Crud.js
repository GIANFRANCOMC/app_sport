import * as Utils from "./Utils.js";
import * as Requests from "./Requests.js";
import * as Constants from "./Constants.js";

/**
 * Creates the initial structure for a CRUD module
 * @param {Object} config - Configuration {entity: string, menuId: string, pageTitle: string}
 * @returns {Object} Initial module structure
 */
export function initCrudModule(config = {}) {

    const entity = config.entity || "entity";
    const menuId = config.menuId || `menu-item-${entity}`;
    const pageTitle = config.pageTitle || "Entidad";

    return {
        lists: {
            [entity]: {
                extras: {
                    loading: false,
                    route: Requests.config({entity, type: "list"})
                },
                filters: {
                    filter_by: null,
                    word: ""
                },
                records: {
                    total: 0
                }
            }
        },
        forms: {
            [entity]: {
                createUpdate: {
                    extras: {
                        modals: {
                            default: {
                                id: Utils.uuid(),
                                titles: {
                                    store: `AGREGAR ${pageTitle.toUpperCase()}`,
                                    update: `EDITAR ${pageTitle.toUpperCase()}`
                                }
                            }
                        }
                    },
                    data: {},
                    errors: {}
                }
            }
        },
        options: {},
        config: {
            ...Constants.generalConfig,
            entity: {
                ...Requests.config({entity}),
                page: {
                    title: pageTitle,
                    active: true,
                    menu: {
                        id: menuId
                    }
                }
            }
        }
    };

}

