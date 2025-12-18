/**
 * Factory para crear módulos CRUD fácilmente
 * Simplifica la creación de nuevos módulos con configuración mínima
 */

import * as Requests from "./Requests.js";
import * as Constants from "./Constants.js";
import * as Utils from "./Utils.js";
import * as Alerts from "./Alerts.js";
import { CrudMixin } from "./CrudMixin.js";
import { FILTER_BY_OPTIONS, TEXT, CSS_CLASSES } from "./ModuleConstants.js";

/**
 * Crea la estructura inicial para un módulo CRUD (alias legacy)
 * @param {Object} config - Configuración {entity: string, menuId: string, pageTitle: string}
 * @returns {Object} Estructura inicial del módulo
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
                    total: 0,
                    data: [],
                    current_page: 1,
                    last_page: 1,
                    links: []
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

/**
 * Crea la configuración base para un módulo Vue
 * @param {Object} config - Configuración del módulo
 * @returns {Object} Configuración para Vue component
 */
export function createModuleConfig(config = {}) {
    const {
        entity = "entity",
        menuId = `menu-${entity}`,
        pageTitle = "Entidad",
        parentMenuId = null,
        customRoutes = {},
        defaultFilters = {},
        defaultFormData = {}
    } = config;

    const entityRoutes = Requests.config({ entity });
    
    // Agregar rutas personalizadas
    if (Object.keys(customRoutes).length > 0) {
        Object.assign(entityRoutes.routes, customRoutes);
    }

    return {
        lists: {
            entity: {
                extras: {
                    loading: false,
                    route: entityRoutes.routes.list
                },
                filters: {
                    filter_by: null,
                    word: "",
                    ...defaultFilters
                },
                records: {
                    total: 0,
                    data: [],
                    current_page: 1,
                    last_page: 1,
                    links: []
                }
            }
        },
        forms: {
            entity: {
                createUpdate: {
                    extras: {
                        modals: {
                            default: {
                                id: Utils.uuid(),
                                titles: {
                                    store: `Agregar ${pageTitle}`,
                                    update: `Editar ${pageTitle}`
                                }
                            }
                        }
                    },
                    data: {
                        id: null,
                        ...defaultFormData
                    },
                    errors: {}
                }
            }
        },
        options: {},
        config: {
            ...Constants.generalConfig,
            entity: {
                ...entityRoutes,
                page: {
                    title: pageTitle,
                    active: true,
                    menu: {
                        id: menuId,
                        parentId: parentMenuId
                    }
                }
            }
        }
    };
}

/**
 * Crea opciones de filtrado estándar
 * @param {Array} customOptions - Opciones personalizadas adicionales
 * @returns {Array} Array de opciones de filtrado
 */
export function createFilterByOptions(customOptions = []) {
    const defaultOptions = [
        FILTER_BY_OPTIONS.ALL,
        FILTER_BY_OPTIONS.INTERNAL_CODE,
        FILTER_BY_OPTIONS.NAME,
        FILTER_BY_OPTIONS.DESCRIPTION
    ];

    return [...defaultOptions, ...customOptions];
}

/**
 * Inicializa un módulo Vue con configuración estándar
 * @param {Object} config - Configuración del módulo
 * @returns {Object} Objeto con métodos y datos para Vue
 */
export function initVueModule(config = {}) {
    const moduleConfig = createModuleConfig(config);
    
    return {
        mixins: [CrudMixin],
        data() {
            return moduleConfig;
        },
        mounted: async function() {
            const { parentMenuId, menuId } = config;
            
            if (parentMenuId) {
                Utils.navbarItem(parentMenuId, { addClass: "open" });
            }
            
            Utils.navbarItem(menuId || moduleConfig.config.entity.page.menu.id, {});
            Alerts.swals({ type: "initParams" });

            const initParams = await this.initParams({});
            const initOthers = await this.initOthers({});

            if (initParams && initOthers) {
                Alerts.swals({ show: false });
                
                if (config.autoLoadList !== false) {
                    this.listEntity({});
                }
            }
        },
        computed: {
            breadcrumbTitles() {
                const breadcrumbs = config.breadcrumbs || [];
                return [...breadcrumbs, moduleConfig.config.entity.page];
            },
            filterByOptions() {
                return createFilterByOptions(config.customFilterOptions || []);
            }
        }
    };
}

/**
 * Crea un componente de tabla genérico
 * @param {Object} config - Configuración de la tabla
 * @returns {Object} Configuración del componente
 */
export function createTableComponent(config = {}) {
    const {
        columns = [],
        actions = [],
        loading = false,
        records = []
    } = config;

    return {
        props: {
            columns: {
                type: Array,
                default: () => columns
            },
            records: {
                type: Array,
                default: () => records
            },
            loading: {
                type: Boolean,
                default: loading
            },
            showActions: {
                type: Boolean,
                default: true
            }
        },
        emits: ["edit", "delete", "view", "action"],
        methods: {
            handleAction(action, record) {
                this.$emit("action", { action, record });
            }
        }
    };
}

