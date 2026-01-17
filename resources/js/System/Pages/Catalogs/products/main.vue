<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Filters Section -->
    <FiltersSection
        :filter-by-value="filterByValue"
        @update:filterByValue="filterByValue = $event"
        :filter-word-value="filterWordValue"
        @update:filterWordValue="filterWordValue = $event"
        :filter-by-options="filterByOptions"
        :search-placeholder="searchPlaceholder"
        :loading="entityList.extras.loading"
        :filter-by-title="MODULE.texts.filters.filterBy"
        :search-title="MODULE.texts.filters.search"
        :search-button-text="MODULE.texts.actions.search"
        :add-button-text="MODULE.texts.actions.add"
        :show-add-button="true"
        :title-class="[config.forms.classes.title]"
        :select-class="config.forms.classes.select2"
        @search="handleSearch"
        @add="openModal()"/>

    <!-- List Section -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr class="text-center align-middle">
                    <th class="bg-secondary text-white fw-semibold" style="width: 20%;">CÓDIGO INTERNO</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 30%;">NOMBRE</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 20%;">PRECIO DE VENTA</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 5%;"></th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 10%;">ESTADO</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 15%;">ACCIONES</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0 bg-white">
                <template v-if="entityList.extras.loading">
                    <tr class="text-center">
                        <td colspan="99" class="py-4">
                            <Loader/>
                        </td>
                    </tr>
                </template>
                <template v-else>
                    <template v-if="entityList.records.total > 0">
                        <tr v-for="record in entityList.records.data" :key="record.id" class="text-center">
                            <td v-text="record.internal_code" class="fw-bold"></td>
                            <td v-text="record.name" class="text-start"></td>
                            <td>
                                <span v-text="record.currency?.sign"></span>
                                <span v-text="separatorNumber(record.price)" class="ms-2"></span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center gap-3 border border-light rounded px-2 py-1">
                                    <i :class="['fa fa-globe cursor-pointer', record.see_my_web ? 'text-success' : 'text-light']" data-bs-toggle="tooltip" data-bs-placement="top" :title="record.see_my_web ? 'Visible en mi página' : 'No visible en mi página'"></i>
                                    <i v-if="record.see_my_web && record.see_my_web_price" :class="['fa-solid fa-dollar-sign cursor-pointer', record.see_my_web_price ? 'text-success' : 'text-light']" data-bs-toggle="tooltip" data-bs-placement="top" :title="record.see_my_web_price ? 'Precio visible' : 'Precio no visible'"></i>
                                </div>
                            </td>
                            <td>
                                <span :class="[getStatusBadgeClasses(record.status), 'badge', 'fw-semibold', 'text-capitalize']" v-text="record.formatted_status"></span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning waves-effect" @click="openModal(record)">
                                    <i class="fa fa-pencil"></i>
                                    <span class="ms-2" v-text="MODULE.texts.actions.edit"></span>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="text-center" colspan="99">
                                <WithoutData type="image"/>
                            </td>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav v-if="!entityList.extras.loading && entityList.records.total > 0" class="d-flex justify-content-center">
        <Paginator :links="entityList.records.links" @clickPage="listEntity"/>
    </nav>

    <!-- Modal: Create/Update -->
    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.default.id" data-bs-backdrop="static" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
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
                                showCharCounter
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.internal_code"
                                xl="5"
                                lg="5">
                                <template v-slot:inputGroupAppend>
                                    <button type="button" :class="['btn waves-effect', isUpdate ? 'btn-warning' : 'btn-primary']" @click="generateCodeAction" data-bs-toggle="tooltip" data-bs-placement="top" :title="MODULE.texts.form.generateCodeTooltip">
                                        <i class="fa fa-rotate"></i>
                                    </button>
                                </template>
                            </InputText>
                            <InputText
                                v-model="forms[entity].createUpdate.data.name"
                                hasDiv
                                :title="MODULE.texts.form.name"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                maxlength="100"
                                showCharCounter
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.name"
                                xl="7"
                                lg="7"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.description"
                                hasDiv
                                :title="MODULE.texts.form.description"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                maxlength="255"
                                showCharCounter
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.description"
                                xl="12"
                                lg="12"/>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.price"
                                hasDiv
                                :title="MODULE.texts.form.price"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.price"
                                xl="4"
                                lg="4"/>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.min_price"
                                hasDiv
                                :title="MODULE.texts.form.minPrice"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.min_price"
                                xl="4"
                                lg="4"
                                md="6"
                                sm="6"/>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.max_price"
                                hasDiv
                                :title="MODULE.texts.form.maxPrice"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.max_price"
                                xl="4"
                                lg="4"
                                md="6"
                                sm="6"/>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.currency"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.currency_id"
                                xl="6"
                                lg="6">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.currency"
                                        :options="currencies"
                                        :class="config.forms.classes.select2"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.categories"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.categories"
                                xl="6"
                                lg="6">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.categories"
                                        :options="categories"
                                        :class="config.forms.classes.select2"
                                        :clearable="true"
                                        :searchable="true"
                                        :multiple="true"/>
                                </template>
                            </InputSlot>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.status"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.status"
                                xl="6"
                                lg="6">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.status"
                                        :options="statuses"
                                        :class="config.forms.classes.select2"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                            <InputSlot
                                hasDiv
                                :isInputGroup="false"
                                :divInputClass="['d-flex flex-wrap justify-content-end align-items-end h-100']"
                                xl="6"
                                lg="6">
                                <template v-slot:input>
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" v-model="forms[entity].createUpdate.data.see_my_web"/>
                                        <span class="ms-2" v-text="MODULE.texts.form.seeMyWeb"></span>
                                    </label>
                                    <label class="form-check-label" v-if="forms[entity].createUpdate.data.see_my_web">
                                        <input class="form-check-input" type="checkbox" v-model="forms[entity].createUpdate.data.see_my_web_price"/>
                                        <span class="ms-2" v-text="MODULE.texts.form.seeMyWebPrice"></span>
                                    </label>
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
import * as Alerts from "@System/Helpers/Alerts.js";
import { initCrudModule } from "@System/Helpers/ModuleFactory.js";
import * as Forms from "@System/Helpers/Forms.js";
import * as Requests from "@System/Helpers/Requests.js";
import * as Utils from "@System/Helpers/Utils.js";

const MODULE_CONFIG = {
    entity: "products",
    menuId: "menu-items-products",
    pageTitle: "Productos",
    breadcrumbParent: "Catálogo comercial",
    perPage: 15
};

const FORM_FIELDS = {
    internal_code: "",
    name: "",
    description: "",
    price: "",
    min_price: "",
    max_price: "",
    currency: null,
    categories: [],
    see_my_web: false,
    see_my_web_price: false,
    status: null
};

const FORM_FIELD_CONFIG = {
    internal_code: {trim: true},
    name: {trim: true},
    description: {normalize: true},
    price: {toNumber: true, minValue: 0},
    min_price: {toNumber: true, minValue: 0},
    max_price: {toNumber: true, minValue: 0},
    currency: {getCode: true},
    categories: {getArray: true},
    see_my_web: {toBoolean: true},
    see_my_web_price: {toBoolean: true},
    status: {getCode: true}
};

const VALIDATION_RULES = {
    internal_code: {required: true},
    name: {required: true},
    description: {required: false},
    price: {required: true, number: true, min: 0},
    min_price: {required: false, number: true, min: 0},
    max_price: {required: false, number: true, min: 0},
    currency: {required: true},
    categories: {required: false},
    status: {required: true}
};

const ERROR_LABELS = {
    internal_code: "Código interno",
    name: "Nombre",
    description: "Descripción",
    price: "Precio de venta",
    min_price: "Precio mínimo",
    max_price: "Precio máximo",
    currency: "Moneda",
    categories: "Categorías",
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
        edit: "Editar"
    },
    form: {
        internalCode: "Código interno",
        name: "Nombre",
        description: "Descripción",
        price: "Precio de venta",
        minPrice: "Precio mínimo",
        maxPrice: "Precio máximo",
        currency: "Moneda",
        categories: "Categorías",
        status: "Estado",
        seeMyWeb: "Visualizar en mi página",
        seeMyWebPrice: "Visualizar precio",
        generateCodeTooltip: "Generar aleatoriamente"
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
    {code: "description", label: "Descripción"},
    {code: "price", label: "Precio de venta"}
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
    name: "ProductsMain",
    data() {

        const crudModule = initCrudModule({entity: MODULE.config.entity, menuId: MODULE.config.menuId, pageTitle: MODULE.config.pageTitle});

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

        Utils.navbarItem("menu-parent-items", {addClass: "open"});
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

            this.options.categories = response?.data?.config?.categories ?? {};
            this.options.currencies = response?.data?.config?.currencies ?? {};
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

                const categoryItems = (record?.category_items ?? []).map(e => e?.category_id);

                Object.keys(this.MODULE.formFields).forEach(key => {

                    if(key === "status") {

                        entityForms.data.status = this.statuses.find(e => e.code === record?.status) || null;

                    }else if(key === "currency") {

                        entityForms.data.currency = this.currencies.find(e => e.code === record?.currency_id) || null;

                    }else if(key === "categories") {

                        entityForms.data.categories = this.categories.filter(e => categoryItems.includes(e.code));

                    }else if(key === "see_my_web" || key === "see_my_web_price") {

                        entityForms.data[key] = Boolean(record?.[key] ?? false);

                    }else {

                        entityForms.data[key] = record?.[key] ?? this.MODULE.formFields[key];

                    }

                });

            }else {

                entityForms.data.internal_code = this.generateCode({length: 7});
                entityForms.data.currency = this.currencies[0] || null;
                entityForms.data.status   = this.statuses[0] || null;

            }

            Alerts.modals({type: "show", id: entityForms.extras.modals.default.id});
            Alerts.tooltips({show: true, time: 500});

        },
        generateCodeAction() {

            this.forms[this.entity].createUpdate.data.internal_code = this.generateCode({length: 7});
            Alerts.toastrs({type: "success", subtitle: "Código interno generado correctamente."});
            Alerts.tooltips({show: false});

        },
        async saveEntity() {

            if(this.isSaving) return;

            const entityForms = this.forms[this.entity].createUpdate;

            Alerts.swals({});

            entityForms.errors = {};
            this.isSaving = true;

            try {

                const formData   = Utils.cloneJson(entityForms.data);
                const validation = this.validateFormData(formData);

                if(!validation.bool) {

                    Alerts.generateAlert({messages: Utils.getErrors({errors: validation.errors}), msgContent: this.config.messages.errorValidate});
                    this.isSaving = false;
                    return;

                }

                const preparedData  = this.prepareFormData(formData);
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

                    Forms.handleFormResponseErrors({result, formErrorsObject: entityForms.errors, config: this.config});

                }

            }catch(error) {

                Alerts.generateAlert({type: "error", messages: [error], msgContent: this.config.messages.catchError});

            }finally {

                this.isSaving = false;

            }

        },
        prepareFormData(formData) {

            const prepared = Utils.cloneJson(formData);

            // Prepare currency
            if(prepared.currency?.code) {

                prepared.currency_id = prepared.currency.code;
                delete prepared.currency;

            }

            // Prepare categories
            if(Array.isArray(prepared.categories)) {

                prepared.categories = prepared.categories.map(category => ({
                    category_id: category.code
                }));

            }

            // Prepare status
            if(prepared.status?.code) {

                prepared.status = prepared.status.code;

            }

            return Forms.prepareFormData(prepared, this.MODULE.formFieldConfig);

        },
        validateFormData(formData) {

            const result = Forms.validateFormData(formData, this.MODULE.validationRules, {isDescriptive: true, errorLabels: this.MODULE.errorLabels});

            // Custom validation for price ranges
            const minPrice = parseFloat(formData.min_price) || 0;
            const maxPrice = parseFloat(formData.max_price) || 0;
            const price = parseFloat(formData.price) || 0;

            if(minPrice > 0 && maxPrice > 0) {

                if(maxPrice < minPrice) {

                    if(!result.errors.max_price) result.errors.max_price = [];
                    result.errors.max_price.push(`${this.MODULE.errorLabels.max_price}: Debe ser mayor o igual al precio mínimo`);
                    result.bool = false;

                }else if(price < minPrice || price > maxPrice) {

                    if(!result.errors.price) result.errors.price = [];
                    result.errors.price.push(`${this.MODULE.errorLabels.price}: Debe estar entre ${minPrice} y ${maxPrice}`);
                    result.bool = false;

                }

            }else if(minPrice > 0 && price < minPrice) {

                if(!result.errors.price) result.errors.price = [];
                result.errors.price.push(`${this.MODULE.errorLabels.price}: Debe ser mayor o igual al precio mínimo`);
                result.bool = false;

            }else if(maxPrice > 0 && price > maxPrice) {

                if(!result.errors.price) result.errors.price = [];
                result.errors.price.push(`${this.MODULE.errorLabels.price}: Debe ser menor o igual al precio máximo`);
                result.bool = false;

            }

            return result;

        },
        // Utils
        // Others
        isDefined(value) {

            return Utils.isDefined({value});

        },
        generateCode({length}) {

            return Utils.generateCode({length});

        },
        separatorNumber(value) {

            return Utils.separatorNumber(value);

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
        categories() {

            return (this.options?.categories?.records ?? []).map(e => ({code: e.id, label: e.name}));

        },
        currencies() {

            return (this.options?.currencies?.records ?? []).map(e => ({code: e.id, label: e.plural_name}));

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
