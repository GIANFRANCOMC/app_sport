<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Filters -->
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

    <!-- Records -->
    <div class="list-section mb-1 mb-md-1 table-responsive">
        <table class="table table-hover">
            <thead>
                <tr class="text-center align-middle">
                    <th class="bg-secondary text-white fw-semibold" style="width: 20%;">NOMBRE</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 20%;">MARCA / MODELO</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 20%;">IP / PUERTO</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 15%;">SUCURSAL</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 10%;">ESTADO</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 15%;">ACCIONES</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0 bg-white">
                <tr v-if="entityList.extras.loading">
                    <td colspan="99">
                        <Loader/>
                    </td>
                </tr>
                <template v-else-if="entityList.records.total > 0">
                    <tr v-for="record in entityList.records.data" :key="record.id">
                        <td class="text-center">
                            <span v-text="record.name" class="fw-bold d-block"></span>
                            <small v-if="record.serial_number" v-text="`Serie: ${record.serial_number}`" class="text-muted"></small>
                        </td>
                        <td class="text-center">
                            <span v-text="record.brand" class="fw-semibold d-block"></span>
                            <small v-text="record.model" class="text-muted"></small>
                        </td>
                        <td class="text-center">
                            <span v-text="record.ip_address" class="fw-semibold d-block"></span>
                            <small v-text="`Puerto: ${record.port}`" class="text-muted"></small>
                        </td>
                        <td class="text-center">
                            <span v-text="record.branch?.name" class="fw-semibold"></span>
                        </td>
                        <td class="text-center">
                            <span :class="[getStatusBadgeClasses(record.status), 'flex-shrink-none']" v-text="record.formatted_status"></span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-warning waves-effect" @click="openModal(record)">
                                <i class="fa fa-pencil"></i>
                                <span class="ms-2" v-text="MODULE.texts.actions.edit"></span>
                            </button>
                        </td>
                    </tr>
                </template>
                <tr v-else>
                    <td colspan="99">
                        <WithoutData type="image"/>
                    </td>
                </tr>
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
                                :title="MODULE.texts.form.branch"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.branch_id"
                                xl="12"
                                lg="12">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.branch"
                                        :options="branches"
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
                                maxlength="255"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.name"
                                xl="12"
                                lg="12"/>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.brand"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.brand"
                                xl="6"
                                lg="6">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.brand"
                                        :options="brands"
                                        :class="config.forms.classes.select2"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.model"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.model"
                                xl="6"
                                lg="6">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.model"
                                        :options="modelsByBrand"
                                        :class="config.forms.classes.select2"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                            <InputText
                                v-model="forms[entity].createUpdate.data.serial_number"
                                hasDiv
                                :title="MODULE.texts.form.serialNumber"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                maxlength="100"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.serial_number"
                                xl="6"
                                lg="6"/>
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
                            <InputText
                                v-model="forms[entity].createUpdate.data.ip_address"
                                hasDiv
                                :title="MODULE.texts.form.ipAddress"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                maxlength="45"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.ip_address"
                                xl="6"
                                lg="6"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.port"
                                hasDiv
                                :title="MODULE.texts.form.port"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                isRequired
                                type="number"
                                :minValue="1"
                                :maxValue="65535"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.port"
                                xl="6"
                                lg="6"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.device_id"
                                hasDiv
                                :title="MODULE.texts.form.deviceId"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                type="number"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.device_id"
                                xl="6"
                                lg="6"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.description"
                                hasDiv
                                :title="MODULE.texts.form.description"
                                :titleClass="[config.forms.classes.title, 'fw-semibold']"
                                type="textarea"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.description"
                                xl="12"
                                lg="12"/>
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
    entity: "biometric_devices",
    menuId: "menu-infrastructure-biometric-devices",
    pageTitle: "Dispositivos Biométricos",
    breadcrumbParent: "Infraestructura",
    perPage: 10
};

const FORM_FIELDS = {
    branch: null,
    name: "",
    brand: null,
    model: null,
    serial_number: "",
    ip_address: "",
    port: 4370,
    device_id: "",
    description: "",
    status: null
};

const FORM_FIELD_CONFIG = {
    branch: {getCode: true},
    name: {trim: true},
    brand: {getCode: true},
    model: {getCode: true},
    serial_number: {trim: true},
    ip_address: {trim: true},
    port: {toNumber: true, minValue: 1, maxValue: 65535},
    device_id: {toNumber: true, allowEmpty: true},
    description: {normalize: true},
    status: {getCode: true}
};

const VALIDATION_RULES = {
    branch: {required: true},
    name: {required: true},
    brand: {required: true},
    model: {required: true},
    serial_number: {required: false},
    ip_address: {required: true, ip: true},
    port: {required: true, number: true, min: 1, max: 65535},
    device_id: {required: false, number: true},
    description: {required: false},
    status: {required: true}
};

const ERROR_LABELS = {
    branch: "Sucursal",
    name: "Nombre del dispositivo",
    brand: "Marca",
    model: "Modelo",
    serial_number: "Número de serie",
    ip_address: "Dirección IP",
    port: "Puerto",
    device_id: "ID del dispositivo",
    description: "Descripción",
    status: "Estado",
    required: "Es obligatorio"
};

const FILTER_OPTIONS = [
    {code: "name", label: "Nombre"},
    {code: "ip_port", label: "IP/Puerto"}
];

const TEXTS = {
    loading: `Cargando ${MODULE_CONFIG.pageTitle}...`,
    filters: {
        filterBy: "Filtrar por",
        search: "Búsqueda"
    },
    actions: {
        search: "Buscar",
        add: "Agregar dispositivo",
        edit: "Editar"
    },
    list: {
        totalItems: "registros",
        noData: "No hay registros"
    },
    form: {
        branch: "Sucursal",
        name: "Nombre del dispositivo",
        brand: "Marca",
        model: "Modelo",
        serialNumber: "Número de serie",
        ipAddress: "Dirección IP",
        port: "Puerto",
        deviceId: "ID del dispositivo (opcional)",
        description: "Descripción",
        status: "Estado"
    },
    modal: {
        close: "Cerrar",
        save: "Guardar"
    }
};

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
    name: "BiometricDevicesMain",
    data() {

        const crudModule = initCrudModule({entity: MODULE.config.entity, menuId: MODULE.config.menuId, pageTitle: MODULE.config.pageTitle});

        // Add filter_by
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

        Utils.navbarItem("menu-parent-infrastructure", {addClass: "open"});
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

            if(response?.data?.config) {

                this.options.branches = response.data.config.branches ?? {records: []};
                this.options.brands = response.data.config.brands ?? [];
                this.options.models = response.data.config.models ?? {};
                this.options.statuses = response.data.config.statuses ?? [];

            }

            return Requests.valid({result: response});

        },
        // List
        async listEntity(params = null) {

            const entityList   = this.lists[this.entity];
            const emptyRecords = {total: 0, data: [], links: []};
            const filters      = Utils.cloneJson(entityList.filters);
            const filterData   = {
                per_page: this.MODULE.config.perPage,
                filter_by: filters.filter_by?.code,
                word: filters.word
            };

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

                entityList.records = {
                    total: response?.data?.total ?? 0,
                    data: response?.data?.data ?? [],
                    links: response?.data?.links ?? []
                };

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

                // Map record data to form
                const branchOption = this.branches.find(b => b.code === record?.branch?.id);
                entityForms.data.branch = branchOption || null;

                entityForms.data.name = record.name || "";
                entityForms.data.serial_number = record.serial_number || "";
                entityForms.data.ip_address = record.ip_address || "";
                entityForms.data.port = record.port || 4370;
                entityForms.data.device_id = record.device_id || "";
                entityForms.data.description = record.description || "";

                // Set brand first
                const brandOption = this.brands.find(b => b.code === record.brand);
                entityForms.data.brand = brandOption || this.brands[0] || null;

                // Set model after brand (to ensure modelsByBrand is updated)
                this.$nextTick(() => {
                    const modelOption = this.modelsByBrand.find(m => m.code === record.model);
                    entityForms.data.model = modelOption || (this.modelsByBrand.length > 0 ? this.modelsByBrand[0] : null);
                });

                // Set status
                const statusOption = this.statuses.find(s => s.code === record.status);
                entityForms.data.status = statusOption || this.statuses[0] || null;

            }else {

                // Set defaults for new record
                if(this.brands.length > 0) {
                    entityForms.data.brand = this.brands[0];
                }
                if(this.statuses.length > 0) {
                    entityForms.data.status = this.statuses[0];
                }
                entityForms.data.port = 4370;

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

                // Map branch to branch_id
                if(preparedData.branch) {
                    preparedData.branch_id = preparedData.branch;
                    delete preparedData.branch;
                }

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
        branches() {

            return (this.options?.branches?.records ?? []).map(e => ({code: e.id, label: e.name, data: e}));

        },
        brands() {

            return (this.options?.brands ?? []).map(e => ({code: e.code, label: e.label}));

        },
        statuses() {

            return (this.options?.statuses ?? []).map(e => ({code: e.code, label: e.label}));

        },
        modelsByBrand() {

            const brand = this.forms[this.entity].createUpdate.data.brand?.code || "ZKTeco";
            const models = this.options.models?.[brand] ?? [];
            return models.map(m => ({code: m.code, label: m.label}));

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
        filterByOptions() {

            return this.MODULE.filterOptions;

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
    },
    watch: {
        "forms.biometric_devices.createUpdate.data.brand": {
            handler(newBrand) {

                // Reset model when brand changes
                this.forms.biometric_devices.createUpdate.data.model = null;

                // Set first model if available (use nextTick to ensure computed is updated)
                this.$nextTick(() => {
                    if(this.modelsByBrand.length > 0) {
                        this.forms.biometric_devices.createUpdate.data.model = this.modelsByBrand[0];
                    }
                });

            },
            immediate: false
        }
    }
};
</script>

<style scoped>
</style>
