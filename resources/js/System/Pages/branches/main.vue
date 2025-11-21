<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Filters Section -->
    <section class="filters-section mb-3 mb-md-4">
        <div class="row align-items-end g-3">
            <InputSlot
                hasDiv
                :title="MODULE.texts.filters.filterBy"
                :titleClass="[config?.forms?.classes?.title]"
                xl="3"
                lg="4">
                <template v-slot:input>
                    <v-select
                        v-model="filterByValue"
                        :options="filterByOptions"
                        :class="config?.forms?.classes?.select2 || ''"
                        :clearable="false"
                        :searchable="false"
                        :disabled="entityList?.extras?.loading">
                    </v-select>
                </template>
            </InputSlot>
            <InputText
                v-model="filterWordValue"
                @enterKeyPressed="handleSearch"
                hasDiv
                :title="MODULE.texts.filters.search"
                :titleClass="[config?.forms?.classes?.title]"
                :placeholder="searchPlaceholder"
                :disabled="entityList?.extras?.loading"
                xl="4"
                lg="4">
            </InputText>
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
                        :disabled="entityList?.extras?.loading">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        <span class="ms-2" v-text="MODULE.texts.actions.search"></span>
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary waves-effect"
                        @click="openModal()"
                        :disabled="entityList?.extras?.loading">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        <span class="ms-2" v-text="MODULE.texts.actions.add"></span>
                    </button>
                </template>
            </InputSlot>
        </div>
    </section>

    <!-- List Section -->
    <section class="list-section mb-4">
        <div v-if="entityList.extras.loading" class="py-3 text-center">
            <Loader/>
        </div>
        <div v-else-if="entityList?.records?.total > 0" class="row g-3 g-lg-4">
            <div v-for="record in entityList.records.data" :key="record.id" class="col-12 col-md-6 col-xl-4">
                <div class="card card-list-custom border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column gap-2">
                        <header class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                            <div class="d-flex flex-column">
                                <span class="text-muted small fw-semibold" v-text="record.internal_code"></span>
                                <span class="fs-5 fw-bold text-dark" v-text="record.name" style="min-width: 0;"></span>
                            </div>
                            <span :class="getStatusBadgeClasses(record.status)" v-text="record.formatted_status"></span>
                        </header>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-map-marker-alt text-danger" style="min-width: 20px; flex-shrink: 0;"></i>
                                <span v-if="isDefined(record.address)" class="text-truncate flex-grow-1 small" v-text="record.address" style="min-width: 0;"></span>
                                <span v-else class="text-muted small" v-text="MODULE.texts.card.noAddress"></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-comment-dots text-info" style="min-width: 20px; flex-shrink: 0;"></i>
                                <span v-if="isDefined(record.reference)" class="text-truncate flex-grow-1 small" v-text="record.reference" style="min-width: 0;"></span>
                                <span v-else class="text-muted small" v-text="MODULE.texts.card.noReference"></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-phone text-primary" style="min-width: 20px; flex-shrink: 0;"></i>
                                <span v-if="isDefined(record.telephone)" class="text-truncate flex-grow-1 small" v-text="record.telephone" style="min-width: 0;"></span>
                                <span v-else class="text-muted small" v-text="MODULE.texts.card.noTelephone"></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-envelope text-primary" style="min-width: 20px; flex-shrink: 0;"></i>
                                <span v-if="isDefined(record.email)" class="text-truncate flex-grow-1 small" v-text="record.email" style="min-width: 0;"></span>
                                <span v-else class="text-muted small" v-text="MODULE.texts.card.noEmail"></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-users text-success" style="min-width: 20px; flex-shrink: 0;"></i>
                                <span class="text-muted small" v-text="formatCapacity(record.capacity)" style="min-width: 0;"></span>
                            </div>
                        </div>
                    </div>
                    <footer class="card-footer bg-transparent border-0 pt-0 mt-auto">
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
                    </footer>
                </div>
            </div>
        </div>
        <div v-else-if="entityList?.records?.total === 0" class="text-center">
            <WithoutData type="image"/>
        </div>
    </section>

    <!-- Pagination -->
    <nav v-if="entityList && entityList.records && entityList.records.last_page > 1 && entityList.records.total > 0 && entityList.extras && !entityList.extras.loading" class="d-flex justify-content-center" aria-label="Pagination">
        <Paginator
            :links="entityList.records.links"
            @clickPage="listEntity"
        />
    </nav>

    <!-- Modal: Create/Update -->
    <div
        v-if="forms[entity]?.createUpdate?.extras?.modals?.default?.id"
        class="modal fade"
        :id="forms[entity].createUpdate.extras.modals.default.id"
        data-bs-backdrop="static"
        tabindex="-1"
        :aria-labelledby="`${forms[entity].createUpdate.extras.modals.default.id}-title`"
        aria-hidden="true"
        role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                    <h5
                        class="modal-title text-uppercase fw-bold"
                        :id="`${forms[entity].createUpdate.extras.modals.default.id}-title`"
                        v-text="modalTitles[isUpdate ? 'update' : 'store']">
                    </h5>
                    <button
                        type="button"
                        class="a-close-modal"
                        data-bs-dismiss="modal"
                        :aria-label="MODULE.texts.modal.close"
                        :aria-describedby="`${forms[entity].createUpdate.extras.modals.default.id}-title`">
                        <i class="fa fa-times"></i>
                    </button>
            </div>
            <div class="modal-body">
                    <form @submit.prevent="saveEntity" :aria-label="modalTitles[isUpdate ? 'update' : 'store']">
                        <div class="row g-3">
                            <InputText
                                v-model="forms[entity].createUpdate.data.internal_code"
                                hasDiv
                                :title="MODULE.texts.form.internalCode"
                                :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                isRequired
                                maxlength="50"
                                :placeholder="MODULE.texts.form.placeholders.internalCode"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.internal_code"
                                :aria-label="MODULE.texts.form.internalCode"
                                xl="4"
                                lg="4"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.name"
                                hasDiv
                                :title="MODULE.texts.form.name"
                                :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                isRequired
                                maxlength="100"
                                :placeholder="MODULE.texts.form.placeholders.name"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.name"
                                :aria-label="MODULE.texts.form.name"
                                xl="8"
                                lg="8"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.address"
                                hasDiv
                                :title="MODULE.texts.form.address"
                                :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                maxlength="100"
                                :placeholder="MODULE.texts.form.placeholders.address"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.address"
                                :aria-label="MODULE.texts.form.address"
                                xl="12"
                                lg="12"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.reference"
                                hasDiv
                                :title="MODULE.texts.form.reference"
                                :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                maxlength="150"
                                :placeholder="MODULE.texts.form.placeholders.reference"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.reference"
                                :aria-label="MODULE.texts.form.reference"
                                xl="12"
                                lg="12"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.telephone"
                                hasDiv
                                :title="MODULE.texts.form.telephone"
                                :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                maxlength="25"
                                :placeholder="MODULE.texts.form.placeholders.telephone"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.telephone"
                                :aria-label="MODULE.texts.form.telephone"
                                xl="6"
                                lg="6"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.email"
                                hasDiv
                                :title="MODULE.texts.form.email"
                                :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                maxlength="120"
                                :placeholder="MODULE.texts.form.placeholders.email"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.email"
                                :aria-label="MODULE.texts.form.email"
                                xl="6"
                                lg="6"/>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.capacity"
                                hasDiv
                                :title="MODULE.texts.form.capacity"
                                :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                :decimals="0"
                                :minValue="0"
                                :placeholder="MODULE.texts.form.placeholders.capacity"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.capacity"
                                :aria-label="MODULE.texts.form.capacity"
                                xl="4"
                                lg="4">
                                <template v-slot:default>
                                    <i
                                        class="fa fa-info-circle cursor-pointer text-i-help mx-1"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        :title="MODULE.texts.form.capacityTooltip"
                                        :aria-label="MODULE.texts.form.capacityTooltip">
                                    </i>
                                </template>
                            </InputNumber>
                            <InputText
                                v-model="forms[entity].createUpdate.data.map_url"
                                hasDiv
                                :title="MODULE.texts.form.mapUrl"
                                :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                maxlength="255"
                                :placeholder="MODULE.texts.form.placeholders.mapUrl"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.map_url"
                                :aria-label="MODULE.texts.form.mapUrl"
                                xl="8"
                                lg="8"/>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.status"
                                :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.status"
                                xl="4"
                                lg="4">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.status"
                                        :options="statuses"
                                        :class="config?.forms?.classes?.select2 || ''"
                                        :clearable="false"
                                        :searchable="false"
                                        :aria-label="MODULE.texts.form.status"/>
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
                        :aria-label="MODULE.texts.modal.close"
                        v-text="MODULE.texts.modal.close">
                    </button>
                    <button
                        type="button"
                        :class="['btn waves-effect', isUpdate ? 'btn-warning' : 'btn-primary']"
                        @click="saveEntity"
                        :disabled="isSaving"
                        :aria-label="MODULE.texts.modal.save">
                        <i class="fa fa-save" aria-hidden="true"></i>
                        <span class="ms-2" v-text="MODULE.texts.modal.save"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts from "../../Helpers/Alerts.js";
import * as Crud from "../../Helpers/Crud.js";
import * as Forms from "../../Helpers/Forms.js";
import * as Requests from "../../Helpers/Requests.js";
import * as Utils from "../../Helpers/Utils.js";

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
    map_url: {normalize: true},
    capacity: {toNumber: true, minValue: 0},
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
    status: "Estado",
    email: "Correo electrónico",
    map_url: "URL del mapa",
    capacity: "Capacidad",
    required: "Es obligatorio"
};

const TEXTS = {
    loading: `Loading ${MODULE_CONFIG.pageTitle}...`,
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
        branch: "Sucursal",
        internalCode: "Código interno",
        notDefined: "No definido",
        status: "Estado",
        details: "Detalles",
        address: "Dirección",
        noAddress: "Sin dirección registrada",
        noReference: "Sin referencia registrada",
        reference: "Referencia",
        telephone: "Teléfono",
        noTelephone: "Sin teléfono registrado",
        email: "Correo electrónico",
        noEmail: "Sin correo registrado",
        capacity: "Capacidad"
    },
    list: {
        totalItems: "sucursales encontradas",
        noData: "No hay sucursales registradas"
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
        status: "Estado",
        placeholders: {
            internalCode: "Ej. SUC-001",
            name: "Ej. Sucursal Centro",
            address: "Ej. Av. Principal 123, Distrito",
            reference: "Ej. Frente al parque principal",
            telephone: "Ej. +51 999 999 999",
            email: "Ej. contacto@sucursal.com",
            capacity: "Ej. 40",
            mapUrl: "https://maps.google.com/..."
        }
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
    texts: TEXTS,
    filterOptions: FILTER_OPTIONS,
    formFields: FORM_FIELDS,
    formFieldConfig: FORM_FIELD_CONFIG,
    validationRules: VALIDATION_RULES,
    errorLabels: ERROR_LABELS
};

export default {
    name: "BranchesMain",
    components: {},
    data() {

        const crudModule = Crud.initCrudModule({
            entity: MODULE.config.entity,
            menuId: MODULE.config.menuId,
            pageTitle: MODULE.config.pageTitle
        });

        crudModule.lists[MODULE.config.entity].filters.filter_by = MODULE.filterOptions[0];
        crudModule.forms[MODULE.config.entity].createUpdate.data = Forms.initFormData(MODULE.formFields);

        return {
            ...crudModule,
            MODULE: MODULE,
            isSaving: false,
            isInitialized: false
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

            const result = await Requests.get({
                route: this.config.entity.routes.initParams,
                data: {page: "main"},
                showAlert: true
            });

            this.options[this.MODULE.config.entity] = result.data?.config?.[this.MODULE.config.entity];

            return Requests.valid({result});

        },
        async listEntity(params = null) {

            const entityList = this.lists[this.MODULE.config.entity];
            const url = typeof params === "object" && params !== null ? params.url : params;
            const filters = Utils.cloneJson(entityList.filters);
            const filterData = {
                filter_by: filters?.filter_by?.code,
                word: filters.word,
                per_page: this.MODULE.config.perPage
            };

            entityList.extras.loading = true;

            try {

                let requestUrl = url || entityList.extras.route;
                let requestData = {};

                if(url) {

                    const urlObj = new URL(url, window.location.origin);
                    const paramsToSet = {
                        per_page: this.MODULE.config.perPage,
                        filter_by: filterData.filter_by,
                        word: filterData.word
                    };

                    Object.entries(paramsToSet).forEach(([key, value]) => {

                        if(value && !urlObj.searchParams.has(key)) {
                            urlObj.searchParams.set(key, value);
                        }

                    });

                    requestUrl = urlObj.pathname + urlObj.search;

                }else {

                    requestData = filterData;

                }

                const response = await Requests.get({
                    route: requestUrl,
                    data: requestData
                });

                entityList.records = response?.data ?? {total: 0, data: []};

            }catch(error) {

                console.error(`Error loading ${this.MODULE.config.pageTitle}:`, error);
                entityList.records = {total: 0, data: []};

            }finally {

                entityList.extras.loading = false;

            }

        },
        handleSearch() {

            this.listEntity({});

        },
        openModal(record = null) {

            const entityForms = this.forms[this.MODULE.config.entity].createUpdate;

            Forms.clearFormData(entityForms.data, this.MODULE.formFields);
            entityForms.errors = {};

            if(Utils.isDefined({value: record})) {

                entityForms.data.id = record?.id;

                Object.keys(this.MODULE.formFields).forEach(key => {

                    if(key === "status") {
                        entityForms.data.status = this.statuses.find(e => e.code === record?.status) || null;
                    }else {
                        entityForms.data[key] = record?.[key] ?? this.MODULE.formFields[key];
                    }

                });

            }else {

                entityForms.data.internal_code = Utils.generateCode({length: 6});
                entityForms.data.status = this.statuses[0] || null;

            }

            Alerts.modals({
                type: "show",
                id: entityForms.extras?.modals?.default?.id
            });
            Alerts.tooltips({show: true, time: 500});

        },
        async saveEntity() {

            if(this.isSaving) return;

            const entityForms = this.forms[this.MODULE.config.entity].createUpdate;

            Alerts.swals({});
            entityForms.errors = {};
            this.isSaving = true;

            try {

                const formData = Utils.cloneJson(entityForms.data);
                const validation = Forms.validateFormData(formData, this.MODULE.validationRules, {
                    isDescriptive: true,
                    errorLabels: this.MODULE.errorLabels
                });

                if(!validation.bool) {

                    Alerts.generateAlert({
                        messages: Utils.getErrors({errors: validation.errors}),
                        msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`
                    });
                    return;

                }

                const preparedData = Forms.prepareFormData(formData, this.MODULE.formFieldConfig);
                const isUpdate = Utils.isDefined({value: preparedData.id});
                const requestMethod = isUpdate ? Requests.patch : Requests.post;
                const route = isUpdate ? this.config.entity.routes.update : this.config.entity.routes.store;

                const result = await requestMethod({
                    route,
                    data: preparedData,
                    id: preparedData.id
                });

                if(Requests.valid({result})) {

                    Alerts.modals({
                        type: "hide",
                        id: entityForms.extras?.modals?.default?.id
                    });
                    Alerts.generateAlert({
                        type: "success",
                        msgContent: result?.data?.msg
                    });

                    Forms.clearFormData(entityForms.data, this.MODULE.formFields);
                    const entityList = this.lists[this.MODULE.config.entity];
                    const currentPage = entityList?.records?.current_page ?? 1;

                    this.listEntity({
                        url: `${entityList?.extras?.route || ""}?page=${currentPage}`
                    });

                }else {

                    entityForms.errors = result?.errors ?? {};
                    Alerts.toastrs({
                        type: "error",
                        subtitle: result?.data?.msg
                    });
                    Alerts.swals({show: false});

                }

            }catch(error) {

                console.error(`Error saving ${this.MODULE.config.pageTitle}:`, error);
                Alerts.toastrs({
                    type: "error",
                    subtitle: `Error al guardar ${this.MODULE.config.pageTitle.toLowerCase()}. Por favor, intente nuevamente.`
                });
                Alerts.swals({show: false});

            }finally {

                this.isSaving = false;

            }

        },
        isDefined(value) {

            return Utils.isDefined({value});

        },
        formatCapacity(capacity) {

            return Utils.formatCapacity(capacity);

        },
        getStatusBadgeClasses(status) {

            return Utils.getStatusBadgeClasses(status);

        },
        hasValidCapacity(capacity) {

            return Utils.isDefined({value: capacity}) && Utils.isNumber({
                value: capacity,
                minValue: 1
            });

        }
    },
    computed: {
        entity() {

            return this.MODULE.config.entity;

        },
        entityList() {

            return this.lists[this.MODULE.config.entity];

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

            return (this.options?.[this.MODULE.config.entity]?.statuses ?? []).map(e => ({
                code: e.code,
                label: e.label
            }));

        },
        isUpdate() {

            return Utils.isDefined({
                value: this.forms[this.MODULE.config.entity]?.createUpdate?.data?.id
            });

        },
        modalTitles() {

            return this.forms[this.MODULE.config.entity]?.createUpdate?.extras?.modals?.default?.titles || {
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

                return this.entityList.filters?.word || "";

            },
            set(value) {

                this.entityList.filters.word = value;

            }
        },
        searchPlaceholder() {

            const filterBy = this.entityList.filters?.filter_by;

            if(!filterBy) {
                return "Buscar...";
            }

            return `Buscar por ${(filterBy.label || "...").toLowerCase()}`;

        }

    }
};
</script>

<style scoped>
</style>
