<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="row align-items-end g-3 mb-3 mb-md-4">
        <InputSlot
            hasDiv
            title="Filtrar por"
            :titleClass="[config.forms.classes.title]"
            xl="3"
            lg="4">
            <template v-slot:input>
                <v-select
                    v-model="lists.entity.filters.filter_by"
                    :options="filterByOptions"
                    :class="config.forms.classes.select2"
                    :clearable="false"
                    :searchable="false"/>
            </template>
        </InputSlot>
        <InputText
            v-model="lists.entity.filters.word"
            @enterKeyPressed="listEntity({})"
            hasDiv
            title="Búsqueda"
            :titleClass="[config.forms.classes.title]"
            :placeholder="'Buscar por ' + (lists.entity.filters.filter_by?.label || '...').toLowerCase()"
            xl="4"
            lg="4"/>
        <InputSlot
            hasDiv
            :isInputGroup="false"
            :divInputClass="['d-flex flex-wrap justify-content-start gap-2 gap-md-3']"
            xl="5"
            lg="4">
            <template v-slot:input>
                <button type="button" class="btn btn-info-1 waves-effect" @click="listEntity({})" :disabled="lists.entity.extras.loading">
                    <i class="fa fa-search"></i>
                    <span class="ms-2">Buscar</span>
                </button>
            </template>
        </InputSlot>
    </div>
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
                    <th class="bg-secondary text-white fw-semibold" colspan="2">DETALLE</th>
                    <th class="bg-secondary text-white fw-semibold">ESTADO</th>
                    <th class="bg-secondary text-white fw-semibold">ACCIONES</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0 bg-white">
                <template v-if="lists.entity.extras.loading">
                    <tr class="text-center">
                        <td colspan="99" class="py-4">
                            <Loader/>
                        </td>
                    </tr>
                </template>
                <template v-else>
                    <template v-if="lists.entity.records.total > 0">
                        <tr v-for="record in lists.entity.records.data" :key="record.id" class="text-center">
                            <td class="text-start">
                                <div class="d-flex justify-content-start flex-wrap mb-1">
                                    <div :class="['badge rounded-pill fst-italic fw-bold text-uppercase', 'bg-label-'+getType({record})?.data?.color]" :title="getType({record})?.label">
                                        <i :class="['fa', getType({record})?.data?.icon]"></i>
                                        <span v-text="getType({record})?.label" class="ms-1"></span>
                                    </div>
                                </div>
                                <span v-text="record.document_number" class="text-dark d-block"></span>
                                <span v-text="record.identity_document_type?.name" class="fst-italic d-block text-muted small"></span>
                            </td>
                            <td class="text-start">
                                <span v-text="record.name" class="fw-bold d-block"></span>
                                <a :href="'mailto:'+record.email" class="d-flex align-items-center small" v-if="isDefined({value: record.email})">
                                    <span>📧</span>
                                    <span v-text="record.email" class="fst-italic ms-1"></span>
                                </a>
                                <a :href="'tel:'+record.phone_number" class="d-inline-flex align-items-center small" v-if="isDefined({value: record.phone_number})">
                                    <span>📞</span>
                                    <span v-text="record.phone_number" class="fst-italic ms-1"></span>
                                </a>
                                <template v-if="!isDefined({value: record.email}) && !isDefined({value: record.phone_number})">
                                    <span class="small text-muted fst-italic">Sin información de contacto</span>
                                </template>
                            </td>
                            <td>
                                <div class="small mb-1 d-inline-flex align-items-center" v-if="isDefined({value: record.created_at})">
                                    <span>📅</span>
                                    <span v-text="legibleFormatDate({dateString: record.created_at, type: 'datetime'})" class="text-dark fst-italic ms-1"></span>
                                </div>
                                <span :class="['badge', 'fw-semibold', { 'bg-label-primary': ['in_progress'].includes(record.status), 'bg-label-success': ['resolved'].includes(record.status), 'bg-label-danger': ['pending'].includes(record.status) }]" v-text="record.formatted_status"></span>
                            </td>
                            <td>
                                <InputSlot
                                    hasDiv
                                    :isInputGroup="false"
                                    :divInputClass="['d-flex flex-wrap justify-content-center gap-2 gap-md-1']"
                                    xl="12"
                                    lg="12">
                                    <template v-slot:input>
                                        <button type="button" class="btn btn-sm btn-primary waves-effect" @click="modalCreateUpdateEntity({record})">
                                            <i class="fa-solid fa-screwdriver-wrench"></i>
                                            <span class="ms-2">Gestionar</span>
                                        </button>
                                    </template>
                                </InputSlot>
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
    <div class="d-flex justify-content-center" v-if="!lists.entity.extras.loading && lists.entity.records?.total > 0">
        <Paginator :links="lists.entity.records.links" @clickPage="listEntity"/>
    </div>

    <!-- Modals -->
    <div class="modal fade" :id="forms.entity.createUpdate.extras.modals.default.id" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="forms.entity.createUpdate.extras.modals.default.titles[isUpdate ? 'update' : 'store']"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white py-1">
                                    <span>👤</span>
                                    <span class="ms-2">Datos del cliente</span>
                                </div>
                                <div class="card-body py-2">
                                    <div class="text-start mb-2">
                                        <span v-text="forms.entity.createUpdate.data.document_number" class="text-dark d-block"></span>
                                        <span v-text="forms.entity.createUpdate.data.identity_document_type?.label" class="fst-italic d-block text-muted small"></span>
                                    </div>
                                    <span v-text="forms.entity.createUpdate.data.name" class="fw-bold d-block"></span>
                                    <div v-if="isDefined({value: forms.entity.createUpdate.data.email})">
                                        <a :href="'mailto:'+forms.entity.createUpdate.data.email" class="d-inline-flex align-items-center small">
                                            <span>📧</span>
                                            <span v-text="forms.entity.createUpdate.data.email" class="fst-italic ms-1"></span>
                                        </a>
                                    </div>
                                    <div v-if="isDefined({value: forms.entity.createUpdate.data.phone_number})">
                                        <a :href="'tel:'+forms.entity.createUpdate.data.phone_number" class="d-inline-flex align-items-center small">
                                            <span>📞</span>
                                            <span v-text="forms.entity.createUpdate.data.phone_number" class="fst-italic ms-1"></span>
                                        </a>
                                    </div>
                                    <template v-if="!isDefined({value: forms.entity.createUpdate.data.email}) && !isDefined({value: forms.entity.createUpdate.data.phone_number})">
                                        <span class="small text-muted fst-italic">Sin información de contacto</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-danger text-white py-1">
                                    <span>📝</span>
                                    <span class="ms-2">Detalle del reclamo</span>
                                </div>
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-center flex-wrap mt-1 mb-2">
                                        <div :class="['badge rounded-pill fst-italic fw-bold text-uppercase', 'bg-label-'+getType({record: forms.entity.createUpdate.data})?.data?.color]" :title="getType({record: forms.entity.createUpdate.data})?.label">
                                            <i :class="['fa', getType({record: forms.entity.createUpdate.data})?.data?.icon]"></i>
                                            <span v-text="getType({record: forms.entity.createUpdate.data})?.label" class="ms-1"></span>
                                        </div>
                                    </div>
                                    <div class="text-start mb-2">
                                        <span class="text-dark fw-bold colon-at-end">Sucursal</span>
                                        <span v-text="forms.entity.createUpdate.data?.branch?.name || 'No especifica'" class="ms-2"></span>
                                    </div>
                                    <div class="text-start mb-2">
                                        <span class="text-dark fw-bold colon-at-end">Descripción</span>
                                        <span v-text="forms.entity.createUpdate.data.description || 'No registrada'" class="ms-2"></span>
                                    </div>
                                    <div class="text-start mb-2">
                                        <span class="text-dark fw-bold colon-at-end">Pedido del cliente</span>
                                        <span v-text="forms.entity.createUpdate.data.request || 'No registrado'" class="ms-2"></span>
                                    </div>
                                    <div class="text-start mb-2">
                                        <span class="text-dark fw-bold colon-at-end">Fecha y hora</span>
                                        <span v-text="legibleFormatDate({dateString: forms.entity.createUpdate.data.created_at, type: 'datetime'})" class="ms-2"></span>
                                    </div>
                                    <div class="d-flex justify-content-start align-items-center flex-wrap">
                                        <span class="text-dark fw-bold colon-at-end">Estado actual</span>
                                        <span :class="['badge', 'fw-semibold', 'ms-2', { 'bg-label-primary': ['in_progress'].includes(forms.entity.createUpdate.data.copy?.status?.code), 'bg-label-success': ['resolved'].includes(forms.entity.createUpdate.data.copy?.status?.code), 'bg-label-danger': ['pending'].includes(forms.entity.createUpdate.data.copy?.status?.code) }]" v-text="forms.entity.createUpdate.data.copy?.status?.label"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-secondary text-white py-1">
                                    <span>🖥️</span>
                                    <span class="ms-2">Información técnica</span>
                                </div>
                                <div class="card-body py-2">
                                    <div class="text-start mb-2">
                                        <span class="text-dark fw-bold colon-at-end">IP enviada</span>
                                        <span v-text="forms.entity.createUpdate.data.submitted_ip || 'N/A'" class="small text-muted fst-italic ms-2"></span>
                                    </div>
                                    <div class="text-start mb-2">
                                        <span class="text-dark fw-bold colon-at-end">Plataforma</span>
                                        <span v-text="forms.entity.createUpdate.data.submitted_platform || 'N/A'" class="small text-muted fst-italic ms-2"></span>
                                    </div>
                                    <div class="text-start mb-2">
                                        <span class="text-dark fw-bold colon-at-end">Navegador</span>
                                        <span v-text="forms.entity.createUpdate.data.submitted_browser || 'N/A'" class="small text-muted fst-italic ms-2"></span>
                                    </div>
                                    <div class="text-start">
                                        <span class="text-dark fw-bold colon-at-end">User Agent</span>
                                        <span v-text="forms.entity.createUpdate.data.submitted_user_agent || 'N/A'" class="small text-muted fst-italic ms-2"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-success text-white py-1">
                                    <span>🗂️</span>
                                    <span class="ms-2">Gestión administrativa</span>
                                </div>
                                <div class="card-body py-3">
                                    <div class="row g-3">
                                        <InputSlot
                                            hasDiv
                                            title="Estado"
                                            isRequired
                                            :isInputGroup="false"
                                            :divInputClass="['d-flex flex-wrap justify-content-start align-items-end gap-2 gap-md-3']"
                                            hasTextBottom
                                            :textBottomInfo="forms.entity.createUpdate.errors?.status"
                                            xl="12"
                                            lg="12">
                                            <template v-slot:input>
                                                <div v-for="option in statuses" :key="option.value" class="form-check">
                                                    <label class="cursor-pointer">
                                                        <input class="form-check-input" type="radio" :value="option" v-model="forms.entity.createUpdate.data.status"/>
                                                        <span class="fw-bold" v-text="option.label"></span>
                                                    </label>
                                                </div>
                                            </template>
                                        </InputSlot>
                                        <InputTextArea
                                            v-model="forms.entity.createUpdate.data.admin_response"
                                            hasDiv
                                            title="Respuesta del administrador"
                                            isRequired
                                            maxlength="600"
                                            rows="5"
                                            hasTextBottom
                                            :textBottomInfo="forms.entity.createUpdate.errors?.admin_response"
                                            xl="12"
                                            lg="12"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" :class="['btn waves-effect', isUpdate ? 'btn-primary' : 'btn-primary']" @click="createUpdateEntity()">
                        <i class="fa fa-save"></i>
                        <span class="ms-2">Guardar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts    from "@System/Helpers/Alerts.js";
import * as Constants from "@System/Helpers/Constants.js";
import * as Requests  from "@System/Helpers/Requests.js";
import * as Utils     from "@System/Helpers/Utils.js";

export default {
    components: {
        //
    },
    mounted: async function() {

        Utils.navbarItem("menu-item-customer_care", {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});
        Alerts.swals({type: "initParams"});

        let initParams = await this.initParams({}),
            initOthers = await this.initOthers({});

        if(initParams && initOthers) {

            Alerts.swals({show: false});
            this.listEntity({});

        }

    },
    data() {
        return {
            lists: {
                entity: {
                    extras: {
                        loading: false,
                        route: Requests.config({entity: "book_complaints", type: "list"})
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
                entity: {
                    createUpdate: {
                        extras: {
                            modals: {
                                default: {
                                    id: Utils.uuid(),
                                    titles: {
                                        store: "Agregar",
                                        update: "Gestionar"
                                    }
                                }
                            }
                        },
                        data: {
                            id: null,
                            branch: null,
                            identity_document_type: null,
                            document_number: "",
                            name: "",
                            email: "",
                            phone_number: "",
                            type: "",
                            description: "",
                            request: "",
                            evidence: "",
                            admin_response: "",
                            submitted_ip: "",
                            submitted_user_agent: "",
                            submitted_platform: "",
                            submitted_browser: "",
                            status: null,
                            created_at: "",
                            copy: null
                        },
                        errors: {}
                    }
                }
            },
            options: {},
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "book_complaints"}),
                    page: {
                        title: "Libro de reclamaciones y sugerencias",
                        active: true,
                        menu: {
                            id: "menu-item-customer_care-book_complaints"
                        }
                    }
                }
            }
        };
    },
    methods: {
        // Init
        async initParams({}) {

            let initParams = await Requests.get({route: this.config.entity.routes.initParams, data: {page: "main"}, showAlert: true});

            this.options.identityDocumentTypes = initParams.data?.config?.identityDocumentTypes;
            this.options.bookComplaints        = initParams.data?.config?.bookComplaints;

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                this.lists.entity.filters.filter_by = this.filterByOptions[0];

                resolve(true);

            });

        },
        // Entity forms
        async listEntity({url = null}) {

            let filters = Utils.cloneJson(this.lists.entity.filters);
            const filterJson = {filter_by: filters?.filter_by?.code, word: filters.word};

            this.lists.entity.extras.loading = true;
            this.lists.entity.records        = (await Requests.get({route: url || this.lists.entity.extras.route, data: filterJson}))?.data;
            this.lists.entity.extras.loading = false;

        },
        // Forms
        getType({record = null}) {

            return (this.types ?? []).find(e => e.code === record?.type) ?? null;

        },
        modalCreateUpdateEntity({record = null}) {

            const functionName = "modalCreateUpdateEntity";

            // Alerts.swals({});
            this.clearForm({functionName});
            this.formErrors({functionName, type: "clear"});

            if(this.isDefined({value: record})) {

                let identityDocumentType = this.identityDocumentTypes.find(e => e.code === record?.identity_document_type_id),
                    status = this.statuses.find(e => e.code === record?.status);

                this.forms.entity.createUpdate.data.id                     = record?.id;
                this.forms.entity.createUpdate.data.branch                 = record?.branch;
                this.forms.entity.createUpdate.data.identity_document_type = identityDocumentType;
                this.forms.entity.createUpdate.data.document_number        = record?.document_number;
                this.forms.entity.createUpdate.data.name                   = record?.name;
                this.forms.entity.createUpdate.data.email                  = record?.email;
                this.forms.entity.createUpdate.data.phone_number           = record?.phone_number;
                this.forms.entity.createUpdate.data.type                   = record?.type;
                this.forms.entity.createUpdate.data.description            = record?.description;
                this.forms.entity.createUpdate.data.request                = record?.request;
                this.forms.entity.createUpdate.data.evidence               = record?.evidence;
                this.forms.entity.createUpdate.data.admin_response         = record?.admin_response;
                this.forms.entity.createUpdate.data.submitted_ip           = record?.submitted_ip;
                this.forms.entity.createUpdate.data.submitted_user_agent   = record?.submitted_user_agent;
                this.forms.entity.createUpdate.data.submitted_platform     = record?.submitted_platform;
                this.forms.entity.createUpdate.data.submitted_browser      = record?.submitted_browser;
                this.forms.entity.createUpdate.data.status                 = status;
                this.forms.entity.createUpdate.data.created_at             = record?.created_at;
                this.forms.entity.createUpdate.data.copy                   = Utils.cloneJson(this.forms.entity.createUpdate.data);

            }else {

                this.forms.entity.createUpdate.data.identity_document_type = this.identityDocumentTypes[1];
                this.forms.entity.createUpdate.data.status = this.statuses[0];

            }

            // Alerts.swals({show: false});
            Alerts.modals({type: "show", id: this.forms.entity.createUpdate.extras.modals.default.id});
            this.tooltips({show: true, time: 500});

        },
        async createUpdateEntity() {

            const functionName = "createUpdateEntity";

            Alerts.swals({});
            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms.entity.createUpdate.data);

            const validateForm = this.validateForm({functionName, form, extras: {type: "descriptive"}});

            if(validateForm?.bool) {

                form.identity_document_type_id = form?.identity_document_type?.code;
                form.status = form?.status?.code;

                delete form.identity_document_type;
                delete form.copy;

                let createUpdate = await (this.isDefined({value: form.id}) ? Requests.patch({route: this.config.entity.routes.update, data: form, id: form.id}) :
                                                                             Requests.post({route: this.config.entity.routes.store, data: form}));

                if(Requests.valid({result: createUpdate})) {

                    Alerts.modals({type: "hide", id: this.forms.entity.createUpdate.extras.modals.default.id});
                    // Alerts.toastrs({type: "success", subtitle: createUpdate?.data?.msg});
                    // Alerts.swals({show: false});
                    Alerts.generateAlert({type: "success", msgContent: createUpdate?.data?.msg});

                    this.clearForm({functionName});
                    this.listEntity({url: `${this.lists.entity.extras.route}?page=${this.lists.entity.records?.current_page ?? 1}`});

                }else {

                    this.formErrors({functionName, type: "set", errors: createUpdate?.errors ?? []});
                    Alerts.toastrs({type: "error", subtitle: createUpdate?.data?.msg});
                    Alerts.swals({show: false});

                }

            }else {

                // this.formErrors({functionName, type: "set", errors: validateForm});
                // Alerts.toastrs({type: "error", subtitle: this.config.messages.errorValidate});
                // Alerts.swals({show: false});
                Alerts.generateAlert({messages: Utils.getErrors({errors: validateForm}), msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`});

            }

        },
        // Forms utils
        clearForm({functionName}) {

            switch(functionName) {
                case "modalCreateUpdateEntity":
                case "createUpdateEntity":
                    this.forms.entity.createUpdate.data.id                     = null;
                    this.forms.entity.createUpdate.data.branch                 = null;
                    this.forms.entity.createUpdate.data.identity_document_type = null;
                    this.forms.entity.createUpdate.data.document_number        = "";
                    this.forms.entity.createUpdate.data.name                   = "";
                    this.forms.entity.createUpdate.data.email                  = "";
                    this.forms.entity.createUpdate.data.phone_number           = "";
                    this.forms.entity.createUpdate.data.type                   = "";
                    this.forms.entity.createUpdate.data.description            = "";
                    this.forms.entity.createUpdate.data.request                = "";
                    this.forms.entity.createUpdate.data.evidence               = "";
                    this.forms.entity.createUpdate.data.admin_response         = "";
                    this.forms.entity.createUpdate.data.submitted_ip           = "";
                    this.forms.entity.createUpdate.data.submitted_user_agent   = "";
                    this.forms.entity.createUpdate.data.submitted_platform     = "";
                    this.forms.entity.createUpdate.data.submitted_browser      = "";
                    this.forms.entity.createUpdate.data.status                 = null;
                    this.forms.entity.createUpdate.data.created_at             = "";
                    this.forms.entity.createUpdate.data.copy                   = null;
                    break;
            }

        },
        formErrors({functionName, type = "clear", errors = []}) {

            if(["modalCreateUpdateEntity", "createUpdateEntity"].includes(functionName)) {

                this.forms.entity.createUpdate.errors = ["set"].includes(type) ? errors : [];

            }

        },
        validateForm({functionName, form = null, extras = null}) {

            let result = {
                bool: true
            };

            if(["createUpdateEntity"].includes(functionName)) {

                result.admin_response = [];
                result.status         = [];

                const isDescriptive = ["descriptive"].includes(extras?.type);

                if(!this.isDefined({value: form?.admin_response})) {

                    result.admin_response.push(`${isDescriptive ? "Respuesta del administrador:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.status})) {

                    result.status.push(`${isDescriptive ? "Estado:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }

            }

            return result;

        },
        // Others
        isDefined({value}) {

            return Utils.isDefined({value});

        },
        legibleFormatDate({dateString = null, type = "datetime"}) {

            return Utils.legibleFormatDate({dateString, type});

        },
        tooltips({show = true, time = 10}) {

            Alerts.tooltips({show, time});

        }
    },
    computed: {
        breadcrumbTitles: function() {

            return [{title: "Atención al cliente"}, this.config.entity.page];

        },
        filterByOptions: function() {

            return [
                {code: "all", label: "Todos"},
                {code: "document_number", label: "Número de documento"},
                {code: "name", label: "Nombre"},
                {code: "email", label: "Correo electrónico"},
                {code: "phone_number", label: "Celular"}
            ];

        },
        identityDocumentTypes: function() {

            return this.options?.identityDocumentTypes?.records.map(e => ({code: e.id, label: e.name, data: e}));

        },
        types: function() {

            return this.options?.bookComplaints?.types.map(e => ({code: e.code, label: e.label, data: e}));

        },
        statuses: function() {

            return this.options?.bookComplaints?.statuses.map(e => ({code: e.code, label: e.label}));

        },
        isUpdate: function() {

            return this.isDefined({value: this.forms.entity.createUpdate.data?.id});

        }
    },
    watch: {
        "lists.entity.filters.filter_by": function(newValue, oldValue) {

            // this.listEntity({});

        }
    }
};
</script>
