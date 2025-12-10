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
                <button type="button" class="btn btn-primary waves-effect" @click="modalCreateUpdateEntity({})" :disabled="lists.entity.extras.loading">
                    <i class="fa fa-plus"></i>
                    <span class="ms-2">Agregar</span>
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
                    <th class="bg-secondary text-white fw-semibold" colspan="2">CLIENTE</th>
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
                                <div class="d-flex flex-wrap">
                                    <template v-if="isDefined({value: record.birthdate})">
                                        <div class="badge bg-light text-dark rounded-pill me-2 my-1 d-flex align-items-center">
                                            <span>🎂</span>
                                            <span v-text="legibleFormatDate({dateString: record.birthdate, type: 'date'})" class="fst-italic ms-1"></span>
                                        </div>
                                    </template>
                                    <template v-if="isDefined({value: record.gender})">
                                        <div :class="['badge text-dark rounded-pill me-2 my-1 d-flex align-items-center', {'bg-label-info': ['male'].includes(record.gender), 'bg-label-danger': ['female'].includes(record.gender), 'bg-light': ['other'].includes(record.gender)}]">
                                            <span>🚻</span>
                                            <span v-text="record.formatted_gender" class="fst-italic ms-1"></span>
                                        </div>
                                    </template>
                                </div>
                            </td>
                            <td>
                                <span :class="['badge', 'fw-semibold', 'text-capitalize', { 'bg-label-success': ['active'].includes(record.status), 'bg-label-danger': ['inactive'].includes(record.status) }]" v-text="record.formatted_status"></span>
                            </td>
                            <td>
                                <InputSlot
                                    hasDiv
                                    :isInputGroup="false"
                                    :divInputClass="['d-flex flex-wrap justify-content-center gap-2 gap-md-1']"
                                    xl="12"
                                    lg="12">
                                    <template v-slot:input>
                                        <button type="button" class="btn btn-sm btn-warning waves-effect" @click="modalCreateUpdateEntity({record})">
                                            <i class="fa fa-pencil"></i>
                                            <span class="ms-2">Editar</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success waves-effect" @click="modalCarnetEntity({record})">
                                            <i class="fa-solid fa-id-badge"></i>
                                            <span class="ms-2">Carnet</span>
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
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="forms.entity.createUpdate.extras.modals.default.titles[isUpdate ? 'update' : 'store']"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <InputSlot
                            hasDiv
                            title="Tipo de documento"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.identity_document_type"
                            xl="4"
                            lg="4">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.identity_document_type"
                                    :options="identityDocumentTypes"
                                    @close="tooltips({show: true, time: 500})"
                                    :clearable="false"
                                    :searchable="false"/>
                            </template>
                        </InputSlot>
                        <InputText
                            v-model="forms.entity.createUpdate.data.document_number"
                            hasDiv
                            title="Número de documento"
                            isRequired
                            maxlength="15"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.document_number"
                            xl="4"
                            lg="4">
                            <template v-slot:inputGroupAppend>
                                <template v-if="['dni', 'ruc'].includes(forms.entity.createUpdate.data.identity_document_type?.data.code)">
                                    <button :class="['btn waves-effect', isUpdate ? 'btn-warning' : 'btn-primary']" type="button" @click="searchDocumentNumber({consult: forms.entity.createUpdate})" data-bs-toggle="tooltip" data-bs-placement="top" title="Buscar N° documento">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </template>
                            </template>
                        </InputText>
                        <InputSlot
                            hasDiv
                            title="Estado"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.status"
                            xl="4"
                            lg="4">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.status"
                                    :options="statuses"
                                    :clearable="false"
                                    :searchable="false"/>
                            </template>
                        </InputSlot>
                        <InputText
                            v-model="forms.entity.createUpdate.data.name"
                            hasDiv
                            title="Nombre"
                            isRequired
                            maxlength="100"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.name"
                            xl="6"
                            lg="6"/>
                        <InputText
                            v-model="forms.entity.createUpdate.data.email"
                            hasDiv
                            title="📧 Correo electrónico"
                            maxlength="100"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.email"
                            xl="6"
                            lg="6"/>
                        <InputText
                            v-model="forms.entity.createUpdate.data.phone_number"
                            hasDiv
                            title="📞 Celular"
                            maxlength="15"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.phone_number"
                            xl="3"
                            lg="3"/>
                        <InputSlot
                            hasDiv
                            title="Género"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.gender"
                            xl="3"
                            lg="3">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.gender"
                                    :options="genders"
                                    :clearable="false"
                                    :searchable="false"/>
                            </template>
                        </InputSlot>
                        <InputDate
                            v-model="forms.entity.createUpdate.data.birthdate"
                            hasDiv
                            title="Fecha de nacimiento"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.birthdate"
                            xl="3"
                            lg="3"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" :class="['btn waves-effect', isUpdate ? 'btn-warning' : 'btn-primary']" @click="createUpdateEntity()">
                        <i class="fa fa-save"></i>
                        <span class="ms-2">Guardar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" :id="forms.entity.carnet.extras.modals.default.id" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="forms.entity.carnet.extras.modals.default.titles[isDefined({value: forms.entity.carnet.data?.id}) ? 'update' : 'store']"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <CarnetCustomer
                                :customer="forms.entity.carnet.data"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts    from "../../Helpers/Alerts.js";
import * as Constants from "../../Helpers/Constants.js";
import * as Requests  from "../../Helpers/Requests.js";
import * as Utils     from "../../Helpers/Utils.js";

export default {
    components: {
        //
    },
    mounted: async function() {

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
                        route: Requests.config({entity: "customers", type: "list"})
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
                                        update: "Editar"
                                    }
                                }
                            }
                        },
                        data: {
                            id: null,
                            identity_document_type: null,
                            document_number: "",
                            name: "",
                            email: "",
                            phone_number: "",
                            gender: "",
                            birthdate: "",
                            status: null
                        },
                        errors: {}
                    },
                    carnet: {
                        extras: {
                            modals: {
                                default: {
                                    id: Utils.uuid(),
                                    titles: {
                                        store: "Carnet del cliente",
                                        update: "Carnet del cliente"
                                    }
                                }
                            }
                        },
                        data: {
                            id: null,
                            identity_document_type: null,
                            document_number: "",
                            name: "",
                            email: "",
                            phone_number: "",
                            gender: "",
                            birthdate: "",
                            status: null
                        },
                        errors: {}
                    }
                }
            },
            options: {},
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "customers"}),
                    page: {
                        title: "Clientes",
                        active: true,
                        menu: {
                            id: "menu-item-customers"
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
            this.options.customers             = initParams.data?.config?.customers;

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
        modalCreateUpdateEntity({record = null}) {

            const functionName = "modalCreateUpdateEntity";

            // Alerts.swals({});
            this.clearForm({functionName});
            this.formErrors({functionName, type: "clear"});

            if(this.isDefined({value: record})) {

                let identityDocumentType = this.identityDocumentTypes.find(e => e.code === record?.identity_document_type_id),
                    gender               = this.genders.find(e => e.code === record?.gender),
                    status               = this.statuses.find(e => e.code === record?.status);

                this.forms.entity.createUpdate.data.id                     = record?.id;
                this.forms.entity.createUpdate.data.identity_document_type = identityDocumentType;
                this.forms.entity.createUpdate.data.document_number        = record?.document_number;
                this.forms.entity.createUpdate.data.name                   = record?.name;
                this.forms.entity.createUpdate.data.email                  = record?.email;
                this.forms.entity.createUpdate.data.phone_number           = record?.phone_number;
                this.forms.entity.createUpdate.data.gender                 = gender;
                this.forms.entity.createUpdate.data.birthdate              = record?.birthdate;
                this.forms.entity.createUpdate.data.status                 = status;

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
                form.gender = form?.gender?.code;
                form.status = form?.status?.code;

                delete form.identity_document_type;

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
        // Carnet
        modalCarnetEntity({record = null, type = "store"}) {

            const functionName = "modalCarnetEntity";

            this.forms.entity.carnet.extras.modals.default.type = type;

            // Alerts.swals({});
            this.clearForm({functionName});
            this.formErrors({functionName, type: "clear"});

            if(this.isDefined({value: record})) {

                // let identityDocumentType = this.identityDocumentTypes.find(e => e.code === record?.identity_document_type_id),
                //     gender               = this.genders.find(e => e.code === record?.gender),
                //     status               = this.statuses.find(e => e.code === record?.status);

                this.forms.entity.carnet.data.id                     = record?.id;
                // this.forms.entity.carnet.data.identity_document_type = identityDocumentType;
                this.forms.entity.carnet.data.document_number        = record?.document_number;
                this.forms.entity.carnet.data.name                   = record?.name;
                // this.forms.entity.carnet.data.email                  = record?.email;
                // this.forms.entity.carnet.data.phone_number           = record?.phone_number;
                // this.forms.entity.carnet.data.gender                 = gender;
                // this.forms.entity.carnet.data.birthdate              = record?.birthdate;
                // this.forms.entity.carnet.data.status                 = status;

            }else {

                // this.forms.entity.carnet.data.identity_document_type = this.identityDocumentTypes[1];
                // this.forms.entity.carnet.data.status = this.statuses[0];

            }

            // Alerts.swals({show: false});
            Alerts.modals({type: "show", id: this.forms.entity.carnet.extras.modals.default.id});
            this.tooltips({show: true, time: 500});

        },
        // Forms utils
        clearForm({functionName}) {

            switch(functionName) {
                case "modalCreateUpdateEntity":
                case "createUpdateEntity":
                    this.forms.entity.createUpdate.data.id                     = null;
                    this.forms.entity.createUpdate.data.identity_document_type = null;
                    this.forms.entity.createUpdate.data.document_number        = "";
                    this.forms.entity.createUpdate.data.name                   = "";
                    this.forms.entity.createUpdate.data.email                  = "";
                    this.forms.entity.createUpdate.data.phone_number           = "";
                    this.forms.entity.createUpdate.data.gender                 = null;
                    this.forms.entity.createUpdate.data.birthdate              = "";
                    this.forms.entity.createUpdate.data.status                 = null;
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

                result.identity_document_type = [];
                result.document_number        = [];
                result.status                 = [];
                result.name                   = [];

                const isDescriptive = ["descriptive"].includes(extras?.type);

                if(!this.isDefined({value: form?.identity_document_type})) {

                    result.identity_document_type.push(`${isDescriptive ? "Tipo de documento:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.document_number})) {

                    result.document_number.push(`${isDescriptive ? "Número de documento:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.status})) {

                    result.status.push(`${isDescriptive ? "Estado:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.name})) {

                    result.name.push(`${isDescriptive ? "Nombre:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }

            }

            return result;

        },
        async searchDocumentNumber({consult}) {

            let route = Requests.config({entity: "helpers", type: "searchDocumentNumber"});
            const formJson = {document_number: consult.data.document_number, type: consult.data.identity_document_type?.data.code};

            if(!this.isDefined({value: formJson.document_number})) {

                Alerts.generateAlert({msgContent: `Debe ingresar el número de documento para realizar la búsqueda.`});
                return;

            }

            Alerts.swals({});

            let searchDocumentNumber = await Requests.get({route: route, data: formJson});

            if(Requests.valid({result: searchDocumentNumber})) {

                const data = searchDocumentNumber.data.data;

                if(consult.data.identity_document_type?.data.code == "dni") {

                    this.forms.entity.createUpdate.data.name = `${data?.first_name} ${data?.last_name} ${data?.second_last_name}`;

                }else if(consult.data.identity_document_type?.data.code == "ruc") {

                    this.forms.entity.createUpdate.data.name = `${data?.legal_name}`;

                }

                Alerts.toastrs({type: "success", subtitle: searchDocumentNumber?.data?.msg});
                Alerts.swals({show: false});

            }else {

                Alerts.toastrs({type: "error", subtitle: searchDocumentNumber?.data?.msg});
                Alerts.swals({show: false});

            }

            Alerts.tooltips({show: false});

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

            return [this.config.entity.page];

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
        genders: function() {

            return this.options?.customers?.genders.map(e => ({code: e.code, label: e.label}));

        },
        statuses: function() {

            return this.options?.customers?.statuses.map(e => ({code: e.code, label: e.label}));

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
