/**
 * Clase base para módulos CRUD
 * Proporciona funcionalidad común para todos los módulos CRUD
 * Facilita la creación de nuevos módulos sin repetir código
 */

import * as Requests from "./Requests.js";
import * as Alerts from "./Alerts.js";
import * as Utils from "./Utils.js";
import * as Constants from "./Constants.js";
import { getStatusLabelClasses, TEXT } from "./ModuleConstants.js";

export class BaseCrudModule {
    constructor(config = {}) {
        this.entity = config.entity || "entity";
        this.menuId = config.menuId || `menu-${this.entity}`;
        this.pageTitle = config.pageTitle || "Entidad";
        this.parentMenuId = config.parentMenuId || null;
        this.customRoutes = config.customRoutes || {};
        
        this.initData();
    }

    /**
     * Inicializa la estructura de datos del módulo
     */
    initData() {
        this.lists = {
            entity: {
                extras: {
                    loading: false,
                    route: Requests.config({entity: this.entity, type: "list"})
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
        };

        this.forms = {
            entity: {
                createUpdate: {
                    extras: {
                        modals: {
                            default: {
                                id: Utils.uuid(),
                                titles: {
                                    store: `Agregar ${this.pageTitle}`,
                                    update: `Editar ${this.pageTitle}`
                                }
                            }
                        }
                    },
                    data: {},
                    errors: {}
                }
            }
        };

        this.options = {};
        
        this.config = {
            ...Constants.generalConfig,
            entity: {
                ...Requests.config({entity: this.entity}),
                page: {
                    title: this.pageTitle,
                    active: true,
                    menu: {
                        id: this.menuId
                    }
                }
            }
        };

        // Agregar rutas personalizadas
        if (Object.keys(this.customRoutes).length > 0) {
            Object.assign(this.config.entity.routes, this.customRoutes);
        }
    }

    /**
     * Obtiene la configuración para usar en Vue
     */
    getVueConfig() {
        return {
            lists: this.lists,
            forms: this.forms,
            options: this.options,
            config: this.config
        };
    }

    /**
     * Inicializa parámetros del módulo
     */
    async initParams(page = "main") {
        const initParams = await Requests.get({
            route: this.config.entity.routes.initParams,
            data: { page },
            showAlert: true
        });

        if (Requests.valid({ result: initParams })) {
            this.options = initParams.data?.config || {};
        }

        return Requests.valid({ result: initParams });
    }

    /**
     * Lista registros de la entidad
     */
    async listEntity(url = null, filters = {}) {
        const filterJson = {
            filter_by: this.lists.entity.filters.filter_by?.code || "all",
            word: this.lists.entity.filters.word || "",
            ...filters
        };

        this.lists.entity.extras.loading = true;
        
        try {
            const response = await Requests.get({
                route: url || this.lists.entity.extras.route,
                data: filterJson
            });
            
            if (Requests.valid({ result: response })) {
                this.lists.entity.records = response.data;
            }
        } finally {
            this.lists.entity.extras.loading = false;
        }
    }

    /**
     * Abre modal para crear/editar
     */
    modalCreateUpdateEntity(record = null) {
        const functionName = "modalCreateUpdateEntity";
        
        this.clearForm({ functionName });
        this.formErrors({ functionName, type: "clear" });

        if (Utils.isDefined({ value: record })) {
            this.populateForm(record);
        } else {
            this.setDefaultFormValues();
        }

        Alerts.modals({
            type: "show",
            id: this.forms.entity.createUpdate.extras.modals.default.id
        });
        
        this.tooltips({ show: true, time: 500 });
    }

    /**
     * Crea o actualiza un registro
     */
    async createUpdateEntity() {
        const functionName = "createUpdateEntity";
        
        Alerts.swals({});
        this.formErrors({ functionName, type: "clear" });

        let form = Utils.cloneJson(this.forms.entity.createUpdate.data);
        
        const validateForm = this.validateForm({ functionName, form, extras: { type: "descriptive" } });

        if (validateForm?.bool) {
            form = this.prepareFormData(form);
            
            const isUpdate = Utils.isDefined({ value: form.id });
            const createUpdate = await (isUpdate
                ? Requests.patch({ route: this.config.entity.routes.update, data: form, id: form.id })
                : Requests.post({ route: this.config.entity.routes.store, data: form }));

            if (Requests.valid({ result: createUpdate })) {
                Alerts.modals({
                    type: "hide",
                    id: this.forms.entity.createUpdate.extras.modals.default.id
                });
                
                Alerts.generateAlert({
                    type: "success",
                    msgContent: createUpdate?.data?.msg
                });

                this.clearForm({ functionName });
                this.listEntity({
                    url: `${this.lists.entity.extras.route}?page=${this.lists.entity.records?.current_page ?? 1}`
                });
            } else {
                this.formErrors({
                    functionName,
                    type: "set",
                    errors: createUpdate?.errors ?? []
                });
                
                Alerts.toastrs({
                    type: "error",
                    subtitle: createUpdate?.data?.msg
                });
                
                Alerts.swals({ show: false });
            }
        } else {
            Alerts.generateAlert({
                messages: Utils.getErrors({ errors: validateForm }),
                msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`
            });
        }
    }

    /**
     * Limpia el formulario
     */
    clearForm({ functionName }) {
        if (["modalCreateUpdateEntity", "createUpdateEntity"].includes(functionName)) {
            this.forms.entity.createUpdate.data = {};
            this.forms.entity.createUpdate.errors = {};
        }
    }

    /**
     * Maneja errores del formulario
     */
    formErrors({ functionName, type = "clear", errors = [] }) {
        if (["modalCreateUpdateEntity", "createUpdateEntity"].includes(functionName)) {
            this.forms.entity.createUpdate.errors = ["set"].includes(type) ? errors : {};
        }
    }

    /**
     * Valida el formulario (debe ser sobrescrito en clases hijas)
     */
    validateForm({ functionName, form = null, extras = null }) {
        return { bool: true };
    }

    /**
     * Prepara los datos del formulario antes de enviar (debe ser sobrescrito si es necesario)
     */
    prepareFormData(form) {
        return form;
    }

    /**
     * Pobla el formulario con datos del registro (debe ser sobrescrito en clases hijas)
     */
    populateForm(record) {
        this.forms.entity.createUpdate.data = { ...record };
    }

    /**
     * Establece valores por defecto del formulario (debe ser sobrescrito en clases hijas)
     */
    setDefaultFormValues() {
        // Implementar en clases hijas
    }

    /**
     * Obtiene clases CSS para badge de estado
     */
    getStatusBadgeClasses(status) {
        return getStatusLabelClasses(status);
    }

    /**
     * Utilidades comunes
     */
    isDefined({ value }) {
        return Utils.isDefined({ value });
    }

    tooltips({ show = true, time = 10 }) {
        Alerts.tooltips({ show, time });
    }
}

