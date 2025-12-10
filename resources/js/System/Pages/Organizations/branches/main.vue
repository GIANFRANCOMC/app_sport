<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Filters Section -->
    <section class="filters-section mb-4 mb-md-4">
        <div class="row align-items-end g-3">
            <InputSlot
                hasDiv
                :title="MODULE.texts.filters.filterBy"
                :titleClass="[config.forms.classes.title]"
                xl="3"
                lg="4">
                <template v-slot:input>
                    <v-select
                        v-model="filterByValue"
                        :options="filterByOptions"
                        :class="config.forms.classes.select2"
                        :clearable="false"
                        :searchable="false"
                        :disabled="entityList.extras.loading"/>
                </template>
            </InputSlot>
            <InputText
                v-model="filterWordValue"
                @enterKeyPressed="handleSearch"
                hasDiv
                :title="MODULE.texts.filters.search"
                :titleClass="[config.forms.classes.title]"
                :placeholder="searchPlaceholder"
                :disabled="entityList.extras.loading"
                xl="4"
                lg="4"/>
            <InputSlot
                hasDiv
                :isInputGroup="false"
                :divInputClass="['d-flex flex-wrap justify-content-start gap-2 gap-md-3']"
                xl="5"
                lg="4">
                <template v-slot:input>
                    <button
                        type="button"
                        class="btn btn-info-1 waves-effect"
                        @click="handleSearch"
                        :disabled="entityList.extras.loading">
                        <i class="fa fa-search"></i>
                        <span class="ms-2" v-text="MODULE.texts.actions.search"></span>
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary waves-effect"
                        @click="openModal()"
                        :disabled="entityList.extras.loading">
                        <i class="fa fa-plus"></i>
                        <span class="ms-2" v-text="MODULE.texts.actions.add"></span>
                    </button>
                </template>
            </InputSlot>
        </div>
    </section>

    <!-- List Section -->
    <section class="list-section mb-3 mb-md-3">
        <Loader v-if="entityList.extras.loading"/>
        <WithoutData v-else-if="entityList.records.total === 0" type="image"/>
        <div v-else-if="entityList.records.total > 0" class="row g-3 g-lg-4">
            <div v-for="record in entityList.records.data" :key="record.id" class="col-12 col-md-6 col-xl-4">
                <div class="card card-list-custom border-0 shadow-sm h-100">
                    <div class="card-header">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div class="d-flex flex-column flex-grow-1 flex-min-w-0">
                                <span class="text-muted small fw-semibold" v-text="record.internal_code"></span>
                                <span class="fs-5 fw-bold text-dark text-truncate" v-text="record.name"></span>
                            </div>
                            <span :class="[getStatusBadgeClasses(record.status), 'flex-shrink-none']" v-text="record.formatted_status"></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            <div v-for="field in getCardFields(record)" :key="field.key" class="d-flex align-items-center gap-2">
                                <i :class="[field.icon, 'icon-fixed-width']"></i>
                                <span v-if="field.value" class="text-truncate flex-grow-1 small flex-min-w-0" v-text="field.value"></span>
                                <span v-else class="text-muted small" v-text="field.placeholder"></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
                            <a v-if="isDefined(record.map_url)" :href="record.map_url" class="btn btn-sm btn-outline-success waves-effect" target="_blank" rel="noopener noreferrer">
                                <i class="fa fa-map-location-dot"></i>
                                <span class="ms-2" v-text="MODULE.texts.actions.viewMap"></span>
                            </a>
                            <button type="button" class="btn btn-sm btn-warning waves-effect" :class="isDefined(record.map_url) ? '' : 'ms-auto'" @click="openModal(record)">
                                <i class="fa fa-pencil"></i>
                                <span class="ms-2" v-text="MODULE.texts.actions.edit"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pagination -->
    <nav v-if="!entityList.extras.loading && entityList.records.total > 0" class="d-flex justify-content-center">
        <Paginator :links="entityList.records.links" @clickPage="listEntity"/>
    </nav>

    <!-- Modal: Create/Update -->
    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.default.id" data-bs-backdrop="static" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="modalTitles[isUpdate ? 'update' : 'store']"></h5>
                    <button type="button" class="btn-header-modal" data-bs-dismiss="modal">
                        <i class="fa fa-times icon-close-modal"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveEntity">
                        <div class="row g-3">
                            <InputText
                                v-model="forms[entity].createUpdate.data.internal_code"
                                hasDiv
                                :title="MODULE.texts.form.internalCode"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                maxlength="50"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.internal_code"
                                xl="4"
                                lg="4"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.name"
                                hasDiv
                                :title="MODULE.texts.form.name"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                maxlength="100"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.name"
                                xl="8"
                                lg="8"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.address"
                                hasDiv
                                :title="MODULE.texts.form.address"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                maxlength="100"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.address"
                                xl="12"
                                lg="12"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.reference"
                                hasDiv
                                :title="MODULE.texts.form.reference"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                maxlength="150"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.reference"
                                xl="12"
                                lg="12"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.telephone"
                                hasDiv
                                :title="MODULE.texts.form.telephone"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                maxlength="25"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.telephone"
                                xl="4"
                                lg="4"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.email"
                                hasDiv
                                :title="MODULE.texts.form.email"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                maxlength="120"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.email"
                                xl="8"
                                lg="8"/>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.capacity"
                                hasDiv
                                :title="MODULE.texts.form.capacity"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                :decimals="0"
                                :minValue="0"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.capacity"
                                xl="4"
                                lg="4">
                                <template v-slot:default>
                                    <i class="fa fa-info-circle cursor-pointer text-i-help me-1" data-bs-toggle="tooltip" data-bs-placement="top" :title="MODULE.texts.form.capacityTooltip"></i>
                                </template>
                            </InputNumber>
                            <InputText
                                v-model="forms[entity].createUpdate.data.map_url"
                                hasDiv
                                :title="MODULE.texts.form.mapUrl"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                maxlength="255"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.map_url"
                                xl="8"
                                lg="8"/>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.status"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.status"
                                xl="4"
                                lg="4">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.status"
                                        :options="statuses"
                                        :class="config.forms.classes.select2"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary waves-effect"
                        data-bs-dismiss="modal"
                        v-text="MODULE.texts.modal.close">
                    </button>
                    <button
                        type="button"
                        :class="['btn waves-effect', isUpdate ? 'btn-warning' : 'btn-primary']"
                        @click="saveEntity"
                        :disabled="isSaving">
                        <i class="fa fa-save"></i>
                        <span class="ms-2" v-text="MODULE.texts.modal.save"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts from "../../../Helpers/Alerts.js";
import * as Crud from "../../../Helpers/Crud.js";
import * as Forms from "../../../Helpers/Forms.js";
import * as Requests from "../../../Helpers/Requests.js";
import * as Utils from "../../../Helpers/Utils.js";

const MODULE_CONFIG = {
    entity: "branches",
    menuId: "menu-item-configuration-branches",
    pageTitle: "Sucursales",
    breadcrumbParent: "Configuración",
    perPage: 6
};

const FORM_FIELDS = {
    internal_code: "",
    name: "",
    address: "",
    reference: "",
    telephone: "",
    email: "",
    capacity: "",
    map_url: "",
    status: null
};

const FORM_FIELD_CONFIG = {
    internal_code: {trim: true},
    name: {trim: true},
    address: {normalize: true},
    reference: {normalize: true},
    telephone: {normalize: true},
    email: {normalize: true},
    capacity: {toNumber: true, minValue: 0},
    map_url: {normalize: true},
    status: {getCode: true}
};

const VALIDATION_RULES = {
    internal_code: {required: true},
    name: {required: true},
    address: {required: false},
    reference: {required: false},
    telephone: {required: false},
    email: {required: false, email: true},
    capacity: {required: false, number: true, min: 0},
    map_url: {required: false, url: true},
    status: {required: true}
};

const ERROR_LABELS = {
    internal_code: "Código interno",
    name: "Nombre",
    email: "Correo electrónico",
    capacity: "Capacidad",
    map_url: "URL del mapa",
    status: "Estado",
    required: "Es obligatorio"
};

const TEXTS = {
    loading: `Cargando ${MODULE_CONFIG.pageTitle}...`,
    filters: {
        filterBy: "Filtrar por",
        search: "Búsqueda"
    },
    actions: {
        search: "Buscar",
        add: "Agregar",
        edit: "Editar",
        viewMap: "Ver mapa"
    },
    card: {
        noAddress: "Sin dirección registrada",
        noReference: "Sin referencia registrada",
        noTelephone: "Sin teléfono registrado",
        noEmail: "Sin correo registrado"
    },
    list: {
        totalItems: "registros",
        noData: "No hay registros"
    },
    form: {
        internalCode: "Código interno",
        name: "Nombre",
        address: "Dirección",
        reference: "Referencia",
        telephone: "Teléfono",
        email: "Correo electrónico",
        capacity: "Capacidad",
        capacityTooltip: "Cantidad de personas",
        mapUrl: "URL del mapa",
        status: "Estado"
    },
    modal: {
        close: "Cerrar",
        save: "Guardar"
    }
};

const FILTER_OPTIONS = [
    {code: "all", label: "Todos los filtros"},
    {code: "internal_code", label: "Código interno"},
    {code: "name", label: "Nombre"},
    {code: "address", label: "Dirección"},
    {code: "reference", label: "Referencia"},
    {code: "telephone", label: "Teléfono"},
    {code: "email", label: "Correo electrónico"}
];

const MODULE = {
    config: MODULE_CONFIG,
    formFields: FORM_FIELDS,
    formFieldConfig: FORM_FIELD_CONFIG,
    validationRules: VALIDATION_RULES,
    errorLabels: ERROR_LABELS,
    texts: TEXTS,
    filterOptions: FILTER_OPTIONS
};

export default {
    name: "BranchesMain",
    components: {},
    data() {

        const crudModule = Crud.initCrudModule({entity: MODULE.config.entity, menuId: MODULE.config.menuId, pageTitle: MODULE.config.pageTitle});

        crudModule.lists[MODULE.config.entity].filters.filter_by = MODULE.filterOptions[0];
        crudModule.forms[MODULE.config.entity].createUpdate.data = Forms.initFormData(MODULE.formFields);

        return {
            ...crudModule,
            MODULE: MODULE,
            isInitialized: false,
            isSaving: false
        };

    },
    mounted: async function() {

        Utils.navbarItem("menu-item-configuration", {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});

        Alerts.swals({type: "initParams"});

        const initParams = await this.initParams();

        this.isInitialized = true;

        if(initParams) {

            Alerts.swals({show: false});
            this.listEntity({});

        }

    },
    methods: {
        async initParams() {

            const response = await Requests.get({route: this.routeActions.initParams, data: {page: "main"}, showAlert: true});

            this.options[this.entity] = response?.data?.config?.[this.entity] ?? {};

            return Requests.valid({result: response});

        },
        // List
        async listEntity(params = null) {

            const entityList   = this.lists[this.entity];
            const emptyRecords = {total: 0, data: []};
            const filters      = Utils.cloneJson(entityList.filters);
            const filterData   = {per_page: this.MODULE.config.perPage, filter_by: filters.filter_by?.code, word: filters.word};

            entityList.extras.loading = true;

            try {

                const url = this.isDefined(params) && typeof params === "object" ? params.url : params;

                let requestUrl  = url || entityList.extras.route;
                let requestData = {};

                if(this.isDefined(url)) {

                    const urlObj = new URL(url, window.location.origin);

                    Object.entries(filterData).forEach(([key, value]) => {

                        if(this.isDefined(value) && !urlObj.searchParams.has(key)) urlObj.searchParams.set(key, value);

                    });

                    requestUrl = `${urlObj.pathname}${urlObj.search}`;

                }else {

                    requestData = filterData;

                }

                const response = await Requests.get({route: requestUrl, data: requestData, showAlert: true});

                entityList.records = response?.data ?? emptyRecords;

            }catch(error) {

                entityList.records = emptyRecords;

            }finally {

                entityList.extras.loading = false;

            }

        },
        handleSearch() {

            this.listEntity({});

        },
        // Forms
        openModal(record = null) {

            const entityForms = this.forms[this.entity].createUpdate;

            entityForms.errors = {};
            Forms.clearFormData(entityForms.data, this.MODULE.formFields);

            if(this.isDefined(record)) {

                entityForms.data.id = record?.id;

                Object.keys(this.MODULE.formFields).forEach(key => {

                    if(key === "status") {

                        entityForms.data.status = this.statuses.find(e => e.code === record?.status) || null;

                    }else {

                        entityForms.data[key] = record?.[key] ?? this.MODULE.formFields[key];

                    }

                });

            }else {

                entityForms.data.internal_code = this.generateCode({length: 7});
                entityForms.data.status        = this.statuses[0];

            }

            Alerts.modals({type: "show", id: entityForms.extras.modals.default.id});
            Alerts.tooltips({show: true, time: 500});

        },
        async saveEntity() {

            if(this.isSaving) return;

            const entityForms = this.forms[this.entity].createUpdate;

            Alerts.swals({});

            entityForms.errors = {};
            this.isSaving = true;

            try {

                const formData   = Utils.cloneJson(entityForms.data);
                const validation = Forms.validateFormData(formData, this.MODULE.validationRules, {isDescriptive: true, errorLabels: this.MODULE.errorLabels});

                if(!validation.bool) {

                    Alerts.generateAlert({messages: Utils.getErrors({errors: validation.errors}), msgContent: this.config.messages.errorValidate});
                    this.isSaving = false;
                    return;

                }

                const preparedData  = Forms.prepareFormData(formData, this.MODULE.formFieldConfig);
                const id            = preparedData.id;
                const isUpdate      = this.isDefined(id);
                const requestMethod = isUpdate ? "patch" : "post";
                const route         = this.routeActions[isUpdate ? "update" : "store"];
                const result        = await Requests[requestMethod]({route, data: preparedData, id});

                if(Requests.valid({result})) {

                    Alerts.modals({type: "hide", id: entityForms.extras.modals.default.id});
                    Alerts.generateAlert({type: "success", msgContent: result.data.msg});

                    Forms.clearFormData(entityForms.data, this.MODULE.formFields);

                    const entityList  = this.entityList;
                    const currentPage = entityList?.records?.current_page ?? 1;

                    this.listEntity({url: `${entityList?.extras?.route || ""}?page=${currentPage}`});

                }else {

                    this.handleErrors({result, entityForms});

                }

            }catch(error) {

                Alerts.generateAlert({type: "error", messages: [error], msgContent: this.config.messages.catchError});

            }finally {

                this.isSaving = false;

            }

        },
        // Utils
        getCardFields(record) {

            return [
                {key: "address", icon: "fa fa-map-marker-alt text-danger", value: this.isDefined(record.address) ? record.address : null, placeholder: this.MODULE.texts.card.noAddress},
                {key: "reference", icon: "fa fa-comment-dots text-info", value: this.isDefined(record.reference) ? record.reference : null, placeholder: this.MODULE.texts.card.noReference},
                {key: "telephone", icon: "fa fa-phone text-primary", value: this.isDefined(record.telephone) ? record.telephone : null, placeholder: this.MODULE.texts.card.noTelephone},
                {key: "email", icon: "fa fa-envelope text-primary", value: this.isDefined(record.email) ? record.email : null, placeholder: this.MODULE.texts.card.noEmail},
                {key: "capacity", icon: "fa fa-users text-success", value: this.isDefined(record.capacity) ? record.capacity : null, placeholder: 0}
            ];

        },
        handleErrors({result, entityForms, setErrors = true, showAlert = true}) {

            const isValidationError = result?.code === 422;
            const hasFieldErrors    = result?.errors && Object.keys(result.errors).length > 0;
            const errorMessage      = result?.data?.msg || this.config.messages.errorValidate;

            if(setErrors) entityForms.errors = result?.errors ?? {};

            if(showAlert) {

                const msgContent = (isValidationError && hasFieldErrors) ? this.config.messages.errorValidateFields : errorMessage;

                Alerts.generateAlert({type: "error", msgContent});

            }

        },
        // Others
        isDefined(value) {

            return Utils.isDefined({value});

        },
        generateCode({length}) {

            return Utils.generateCode({length});

        },
        getStatusBadgeClasses(status) {

            return Utils.getStatusBadgeClasses(status);

        }
    },
    computed: {
        entity() {

            return this.MODULE.config.entity;

        },
        routeActions() {

            return this.config.entity.routes;

        },
        entityList() {

            return this.lists[this.entity];

        },
        breadcrumbTitles() {

            return [
                {title: this.MODULE.config.breadcrumbParent},
                this.config.entity.page
            ];

        },
        filterByOptions() {

            return this.MODULE.filterOptions;

        },
        statuses() {

            return (this.options?.[this.entity]?.statuses ?? []).map(e => ({code: e.code, label: e.label}));

        },
        isUpdate() {

            return this.isDefined(this.forms[this.entity].createUpdate.data.id);

        },
        modalTitles() {

            return this.forms[this.entity].createUpdate.extras.modals.default.titles || {
                store: `AGREGAR ${this.MODULE.config.pageTitle.toUpperCase()}`,
                update: `EDITAR ${this.MODULE.config.pageTitle.toUpperCase()}`
            };

        },
        filterByValue: {
            get() {

                return this.entityList.filters?.filter_by || this.MODULE.filterOptions[0];

            },
            set(value) {

                this.entityList.filters.filter_by = value;

            }
        },
        filterWordValue: {
            get() {

                return this.entityList.filters.word || "";

            },
            set(value) {

                this.entityList.filters.word = value;

            }
        },
        searchPlaceholder() {

            const filterBy = this.entityList.filters.filter_by;

            if(!filterBy) return "Buscar...";

            return `Buscar por ${(filterBy.label || "...").toLowerCase()}`;

        }

    }
};
</script>

<style scoped>
</style>
