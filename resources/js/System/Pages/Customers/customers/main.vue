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
            <colgroup>
                <col style="width: 20%;">
                <col style="width: 40%;">
                <col style="width: 20%;">
                <col style="width: 20%;">
            </colgroup>
            <thead>
                <tr class="text-center align-middle">
                    <th class="bg-secondary text-white fw-semibold" colspan="2">CLIENTE</th>
                    <th class="bg-secondary text-white fw-semibold">ESTADO</th>
                    <th class="bg-secondary text-white fw-semibold">ACCIONES</th>
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
                            <td class="text-start">
                                <span v-text="record.document_number" class="text-dark d-block"></span>
                                <span v-text="record.identity_document_type?.name" class="fst-italic d-block text-muted small"></span>
                            </td>
                            <td class="text-start">
                                <span v-text="record.name" class="fw-bold d-block"></span>
                                <a :href="'mailto:'+record.email" class="d-flex align-items-center small" v-if="isDefined(record.email)">
                                    <span>📧</span>
                                    <span v-text="record.email" class="fst-italic ms-1"></span>
                                </a>
                                <a :href="'tel:'+record.phone_number" class="d-inline-flex align-items-center small" v-if="isDefined(record.phone_number)">
                                    <span>📞</span>
                                    <span v-text="record.phone_number" class="fst-italic ms-1"></span>
                                </a>
                                <div class="d-flex flex-wrap">
                                    <template v-if="isDefined(record.birthdate)">
                                        <div class="badge bg-light text-dark rounded-pill me-2 my-1 d-flex align-items-center">
                                            <span>🎂</span>
                                            <span v-text="legibleFormatDate({dateString: record.birthdate, type: 'date'})" class="fst-italic ms-1"></span>
                                        </div>
                                    </template>
                                    <template v-if="isDefined(record.gender)">
                                        <div :class="['badge text-dark rounded-pill me-2 my-1 d-flex align-items-center', {'bg-label-info': ['male'].includes(record.gender), 'bg-label-danger': ['female'].includes(record.gender), 'bg-light': ['other'].includes(record.gender)}]">
                                            <span>🚻</span>
                                            <span v-text="record.formatted_gender" class="fst-italic ms-1"></span>
                                        </div>
                                    </template>
                                </div>
                            </td>
                            <td>
                                <span :class="[getStatusBadgeClasses(record.status), 'badge', 'fw-semibold', 'text-capitalize']" v-text="record.formatted_status"></span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap justify-content-center gap-2 gap-md-1">
                                    <button type="button" class="btn btn-sm btn-warning waves-effect" @click="openModal(record)">
                                        <i class="fa fa-pencil"></i>
                                        <span class="ms-2" v-text="MODULE.texts.actions.edit"></span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success waves-effect" @click="openCarnetModal(record)">
                                        <i class="fa-solid fa-id-badge"></i>
                                        <span class="ms-2" v-text="MODULE.texts.actions.carnet"></span>
                                    </button>
                                </div>
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
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.identityDocumentType"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.identity_document_type_id"
                                xl="4"
                                lg="4">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.identity_document_type"
                                        :options="identityDocumentTypes"
                                        :class="config.forms.classes.select2"
                                        @close="Alerts.tooltips({show: true, time: 500})"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                            <InputText
                                v-model="forms[entity].createUpdate.data.document_number"
                                hasDiv
                                :title="MODULE.texts.form.documentNumber"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                maxlength="15"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.document_number"
                                xl="4"
                                lg="4">
                                <template v-slot:inputGroupAppend>
                                    <template v-if="['dni', 'ruc'].includes(forms[entity].createUpdate.data.identity_document_type?.data?.code)">
                                        <button :class="['btn waves-effect', isUpdate ? 'btn-warning' : 'btn-primary']" type="button" @click="searchDocumentNumber" data-bs-toggle="tooltip" data-bs-placement="top" :title="MODULE.texts.form.searchDocumentTooltip">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </template>
                                </template>
                            </InputText>
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
                            <InputText
                                v-model="forms[entity].createUpdate.data.name"
                                hasDiv
                                :title="MODULE.texts.form.name"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                maxlength="100"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.name"
                                xl="6"
                                lg="6"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.email"
                                hasDiv
                                :title="MODULE.texts.form.email"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                maxlength="100"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.email"
                                xl="6"
                                lg="6"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.phone_number"
                                hasDiv
                                :title="MODULE.texts.form.phoneNumber"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                maxlength="15"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.phone_number"
                                xl="3"
                                lg="3"/>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.gender"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.gender"
                                xl="3"
                                lg="3">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.gender"
                                        :options="genders"
                                        :class="config.forms.classes.select2"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                            <InputDate
                                v-model="forms[entity].createUpdate.data.birthdate"
                                hasDiv
                                :title="MODULE.texts.form.birthdate"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.birthdate"
                                xl="6"
                                lg="6"/>
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

    <!-- Modal: Carnet -->
    <div class="modal fade" :id="forms[entity].carnet.extras.modals.default.id" data-bs-backdrop="static" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="MODULE.texts.modal.carnetTitle"></h5>
                    <button type="button" class="btn-header-modal" data-bs-dismiss="modal">
                        <i class="fa fa-times icon-close-modal"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <CarnetCustomer :customer="forms[entity].carnet.data"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary waves-effect"
                        data-bs-dismiss="modal"
                        v-text="MODULE.texts.modal.close">
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts from "@System/Helpers/Alerts.js";
import * as Crud from "@System/Helpers/Crud.js";
import * as Forms from "@System/Helpers/Forms.js";
import * as Requests from "@System/Helpers/Requests.js";
import * as Utils from "@System/Helpers/Utils.js";

const MODULE_CONFIG = {
    entity: "customers",
    menuId: "menu-customers",
    pageTitle: "Clientes",
    breadcrumbParent: null,
    perPage: 15
};

const FORM_FIELDS = {
    identity_document_type: null,
    document_number: "",
    name: "",
    email: "",
    phone_number: "",
    gender: null,
    birthdate: "",
    status: null
};

const FORM_FIELD_CONFIG = {
    identity_document_type: {getCode: true},
    document_number: {trim: true},
    name: {trim: true},
    email: {normalize: true},
    phone_number: {normalize: true},
    gender: {getCode: true},
    birthdate: {normalize: true},
    status: {getCode: true}
};

const VALIDATION_RULES = {
    identity_document_type: {required: true},
    document_number: {required: true},
    name: {required: true},
    email: {required: false, email: true},
    phone_number: {required: false},
    gender: {required: false},
    birthdate: {required: false},
    status: {required: true}
};

const ERROR_LABELS = {
    identity_document_type: "Tipo de documento",
    document_number: "Número de documento",
    name: "Nombre",
    email: "Correo electrónico",
    phone_number: "Celular",
    gender: "Género",
    birthdate: "Fecha de nacimiento",
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
        carnet: "Carnet"
    },
    form: {
        identityDocumentType: "Tipo de documento",
        documentNumber: "Número de documento",
        name: "Nombre",
        email: "📧 Correo electrónico",
        phoneNumber: "📞 Celular",
        gender: "Género",
        birthdate: "Fecha de nacimiento",
        status: "Estado",
        searchDocumentTooltip: "Buscar N° documento"
    },
    modal: {
        close: "Cerrar",
        save: "Guardar",
        carnetTitle: "Carnet del cliente"
    }
};

const FILTER_OPTIONS = [
    {code: "all", label: "Todos los filtros"},
    {code: "document_number", label: "Número de documento"},
    {code: "name", label: "Nombre"},
    {code: "email", label: "Correo electrónico"},
    {code: "phone_number", label: "Celular"}
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
    name: "CustomersMain",
    data() {

        const crudModule = Crud.initCrudModule({entity: MODULE.config.entity, menuId: MODULE.config.menuId, pageTitle: MODULE.config.pageTitle});

        crudModule.lists[MODULE.config.entity].filters.filter_by = MODULE.filterOptions[0];
        crudModule.forms[MODULE.config.entity].createUpdate.data = Forms.initFormData(MODULE.formFields);

        // Add carnet form
        crudModule.forms[MODULE.config.entity].carnet = {
            extras: {
                modals: {
                    default: {
                        id: Utils.uuid(),
                        titles: {
                            store: MODULE.texts.modal.carnetTitle,
                            update: MODULE.texts.modal.carnetTitle
                        }
                    }
                }
            },
            data: {
                id: null,
                document_number: "",
                name: ""
            },
            errors: {}
        };

        return {
            ...crudModule,
            MODULE: MODULE,
            isInitialized: false,
            isSaving: false
        };

    },
    mounted: async function() {

        Utils.navbarItem("menu-parent-customers", {addClass: "open"});
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

            this.options.identityDocumentTypes = response?.data?.config?.identityDocumentTypes ?? {};
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

                    if(key === "identity_document_type") {

                        entityForms.data.identity_document_type = this.identityDocumentTypes.find(e => e.code === record?.identity_document_type_id) || null;

                    }else if(key === "status") {

                        entityForms.data.status = this.statuses.find(e => e.code === record?.status) || null;

                    }else if(key === "gender") {

                        entityForms.data.gender = this.genders.find(e => e.code === record?.gender) || null;

                    }else {

                        entityForms.data[key] = record?.[key] ?? this.MODULE.formFields[key];

                    }

                });

            }else {

                entityForms.data.identity_document_type = this.identityDocumentTypes[1] || null;
                entityForms.data.status = this.statuses[0] || null;

            }

            Alerts.modals({type: "show", id: entityForms.extras.modals.default.id});
            Alerts.tooltips({show: true, time: 500});

        },
        openCarnetModal(record = null) {

            const carnetForm = this.forms[this.entity].carnet;

            if(this.isDefined(record)) {

                carnetForm.data.id = record?.id;
                carnetForm.data.document_number = record?.document_number;
                carnetForm.data.name = record?.name;

            }else {

                carnetForm.data.id = null;
                carnetForm.data.document_number = "";
                carnetForm.data.name = "";

            }

            Alerts.modals({type: "show", id: carnetForm.extras.modals.default.id});
            Alerts.tooltips({show: true, time: 500});

        },
        async searchDocumentNumber() {

            const entityForms = this.forms[this.entity].createUpdate;
            const documentNumber = entityForms.data.document_number;
            const identityDocumentType = entityForms.data.identity_document_type;

            if(!this.isDefined(documentNumber)) {

                Alerts.generateAlert({msgContent: "Debe ingresar el número de documento para realizar la búsqueda."});
                return;

            }

            if(!this.isDefined(identityDocumentType)) {

                Alerts.generateAlert({msgContent: "Debe seleccionar el tipo de documento."});
                return;

            }

            Alerts.swals({});

            const route = Requests.config({entity: "helpers", type: "searchDocumentNumber"});
            const formJson = {
                document_number: documentNumber,
                type: identityDocumentType.data?.code
            };

            const response = await Requests.get({route, data: formJson});

            if(Requests.valid({result: response})) {

                const data = response.data.data;

                if(identityDocumentType.data?.code === "dni") {

                    entityForms.data.name = `${data?.first_name || ""} ${data?.last_name || ""} ${data?.second_last_name || ""}`.trim();

                }else if(identityDocumentType.data?.code === "ruc") {

                    entityForms.data.name = data?.legal_name || "";

                }

                Alerts.toastrs({type: "success", subtitle: response?.data?.msg});
                Alerts.swals({show: false});

            }else {

                Alerts.toastrs({type: "error", subtitle: response?.data?.msg});
                Alerts.swals({show: false});

            }

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

                // Prepare identity_document_type_id
                if(preparedData.identity_document_type) {

                    preparedData.identity_document_type_id = preparedData.identity_document_type;
                    delete preparedData.identity_document_type;

                }

                const result = await Requests[requestMethod]({route, data: preparedData, id});

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
        legibleFormatDate({dateString = null, type = "datetime"}) {

            return Utils.legibleFormatDate({dateString, type});

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

            return this.MODULE.config.breadcrumbParent ? [
                {title: this.MODULE.config.breadcrumbParent},
                this.config.entity.page
            ] : [this.config.entity.page];

        },
        filterByOptions() {

            return this.MODULE.filterOptions;

        },
        identityDocumentTypes() {

            return (this.options?.identityDocumentTypes?.records ?? []).map(e => ({code: e.id, label: e.name, data: e}));

        },
        genders() {

            return (this.options?.[this.entity]?.genders ?? []).map(e => ({code: e.code, label: e.label}));

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
