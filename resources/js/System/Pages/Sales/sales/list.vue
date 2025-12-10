<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="row align-items-end g-3 mb-3 mb-md-4">
        <InputSlot
            hasDiv
            title="Serie"
            :titleClass="[config.forms.classes.title]"
            xl="6"
            lg="12">
            <template v-slot:input>
                <v-select
                    v-model="lists.entity.filters.serie"
                    :options="series"
                    :class="config.forms.classes.select2"
                    :clearable="true"
                    :searchable="false"
                    placeholder="Seleccione una serie ...">
                    <template #option="{ data }">
                        <span v-text="`${data?.legible_serie} - ${data?.document_type?.name}`" class="d-block fw-bold"></span>
                        <small v-text="data?.branch?.name" class="d-block"></small>
                    </template>
                    <template #selected-option="{ label }">
                        <span v-text="truncate({value: label, length: 50})"></span>
                    </template>
                </v-select>
            </template>
        </InputSlot>
        <InputText
            v-model="lists.entity.filters.sequential"
            @enterKeyPressed="listEntity({})"
            hasDiv
            title="Secuencia"
            :titleClass="[config.forms.classes.title]"
            xl="3"
            lg="6"/>
        <InputDate
            v-model="lists.entity.filters.issue_date"
            @enterKeyPressed="listEntity({})"
            hasDiv
            title="Fecha de emisión"
            :titleClass="[config.forms.classes.title]"
            xl="3"
            lg="6"/>
        <InputSlot
            hasDiv
            title="Cliente"
            :titleClass="[config.forms.classes.title]"
            xl="6"
            lg="6">
            <template v-slot:input>
                <v-select
                    v-model="lists.entity.filters.holder"
                    :options="holders"
                    :class="config.forms.classes.select2"
                    :clearable="true"
                    placeholder="Seleccione un cliente ...">
                    <template #option="{ label }">
                        <span v-text="truncate({value: label, length: 50})" class="d-block"></span>
                    </template>
                    <template #selected-option="{ label }">
                        <span v-text="truncate({value: label, length: 50})"></span>
                    </template>
                </v-select>
            </template>
        </InputSlot>
        <InputSlot
            hasDiv
            title="Estado"
            :titleClass="[config.forms.classes.title]"
            xl="3"
            lg="6">
            <template v-slot:input>
                <v-select
                    v-model="lists.entity.filters.status"
                    :options="statuses"
                    :class="config.forms.classes.select2"
                    :clearable="true"
                    :searchable="false"
                    placeholder="Seleccione"/>
            </template>
        </InputSlot>
        <InputSlot
            hasDiv
            :isInputGroup="false"
            :divInputClass="['d-flex flex-wrap justify-content-start gap-2 gap-md-3']"
            xl="3"
            lg="6">
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
            <thead>
                <tr class="text-center align-middle">
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 20%;">DOCUMENTO</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 25%;">CLIENTE</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 15%;">FECHA DE EMISIÓN</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 15%;">TOTAL</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 15%;">ESTADO</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 10%;">ACCIONES</th>
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
                                <span v-text="record.serie_sequential" class="fw-bold d-block"></span>
                                <small v-text="record.serie?.document_type?.name" class="d-block"></small>
                            </td>
                            <td class="text-start">
                                <span v-text="record.holder?.name" class="fw-bold d-block"></span>
                                <small v-text="record.holder?.document_number" class="d-block"></small>
                            </td>
                            <td>
                                <span v-text="record.formatted_issue_date" class="d-block"></span>
                                <span :class="['badge', 'fw-semibold', 'mt-1', { 'bg-label-success': record.diff_days_issue_date == 0, 'bg-label-warning': record.diff_days_issue_date != 0 }]" v-text="diffDaysLegible({diff: record.diff_days_issue_date})"></span>
                            </td>
                            <td>
                                <span v-text="record.currency?.sign ?? ''" class="fw-semibold"></span>
                                <span v-text="separatorNumber(record.total)" class="fw-semibold ms-1"></span>
                            </td>
                            <td>
                                <span :class="['badge', 'fw-semibold', 'text-capitalize', { 'bg-label-success': ['active'].includes(record.status), 'bg-label-danger': ['inactive', 'canceled'].includes(record.status) }]" v-text="record.formatted_status"></span>
                            </td>
                            <td>
                                <InputSlot
                                    hasDiv
                                    :isInputGroup="false"
                                    :divInputClass="['d-flex flex-wrap justify-content-center gap-2 gap-md-1']"
                                    xl="12"
                                    lg="12">
                                    <template v-slot:input>
                                        <button type="button" class="btn btn-sm btn-primary waves-effect" @click="modalActionsEntity({record})">
                                            <i class="fa fa-gear"></i>
                                            <span class="ms-2">Acciones</span>
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

    <PrintSale :modalId="forms.entity.createUpdate.extras.modals.actions.id" :data="forms.entity.createUpdate.extras.modals.actions.data">
        <template v-slot:extraGroupAppend>
            <div v-if="false && ['active'].includes(forms.entity.createUpdate.extras.modals.actions.data?.status)" class="col-xl-3 col-lg-3 col-md-3 col-sm-3">
                <div class="text-center cursor-pointer p-1" @click="cancelEntity({})">
                    <div class="badge bg-danger p-3 rounded mb-1">
                        <i class="fa-solid fa-rectangle-xmark fs-3"></i>
                    </div>
                    <span class="d-block fw-semibold text-danger">Anular venta</span>
                </div>
            </div>
            <div class="row g-2 mt-4">
                <InputText
                    hasDiv
                    title="Número de celular (Whatsapp)"
                    v-model="forms.entity.createUpdate.extras.modals.actions.data.whatsapp">
                    <template v-slot:inputGroupAppend>
                        <button class="btn btn-success waves-effect" type="button" @click="sendWhatsapp({data: forms.entity.createUpdate.extras.modals.actions.data})" :disabled="!isDefined({value: forms.entity.createUpdate.extras.modals.actions.data.whatsapp})">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span class="ms-2">Enviar</span>
                        </button>
                    </template>
                </InputText>
                <InputText
                    v-if="false"
                    hasDiv
                    title="Correo electrónico"
                    v-model="forms.entity.createUpdate.extras.modals.actions.data.email">
                    <template v-slot:inputGroupAppend>
                        <button class="btn btn-info-1 waves-effect" type="button" @click="sendEmail({data: forms.entity.createUpdate.extras.modals.actions.data})" :disabled="!isDefined({value: forms.entity.createUpdate.extras.modals.actions.data.email})">
                            <i class="fa fa-envelope"></i>
                            <span class="ms-2">Enviar</span>
                        </button>
                    </template>
                </InputText>
            </div>
        </template>
    </PrintSale>
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

        Utils.navbarItem("menu-item-sales", {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});
        Alerts.swals({type: "initParams"});

        let initParams = await this.initParams({}),
            initOthers = await this.initOthers({});

        if(initParams && initOthers) {

            Alerts.swals({show: false});
            await this.listEntity({});

        }

    },
    data() {
        return {
            lists: {
                entity: {
                    extras: {
                        loading: false,
                        route: Requests.config({entity: "sales", type: "list"})
                    },
                    filters: {
                        serie: null,
                        sequential: "",
                        issue_date: "",
                        holder: null,
                        status: null
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
                                actions: {
                                    id: Utils.uuid(),
                                    data: {
                                        id: null,
                                        extras: {},
                                        whatsapp: "",
                                        email: ""
                                    },
                                    errors: {}
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
                    ...Requests.config({entity: "sales"}),
                    page: {
                        title: "Listado",
                        active: true,
                        menu: {
                            id: "menu-item-sales-list"
                        }
                    }
                }
            }
        };
    },
    methods: {
        // Init
        async initParams({}) {

            let initParams = await Requests.get({route: this.config.entity.routes.initParams, data: {page: "list"}, showAlert: true});

            this.options.branches    = initParams.data?.config?.branches;
            this.options.holders     = initParams.data?.config?.customers;
            this.options.salesHeader = initParams.data?.config?.salesHeader;

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                resolve(true);

            });

        },
        // Entity forms
        async listEntity({url = null}) {

            let filters = Utils.cloneJson(this.lists.entity.filters);
            const filterJson = {serie_id: filters?.serie?.code, sequential: filters?.sequential, issue_date: filters.issue_date, holder_id: filters?.holder?.code, status: filters?.status?.code};

            this.lists.entity.extras.loading = true;
            this.lists.entity.records        = (await Requests.get({route: url || this.lists.entity.extras.route, data: {...filterJson}}))?.data;
            this.lists.entity.extras.loading = false;

        },
        modalActionsEntity({record = null}) {

            const whatsapp = record?.holder?.phone_number ?? "";
            const email    = record?.holder?.email ?? "";

            this.forms.entity.createUpdate.extras.modals.actions.data = {...record, extras: {}, whatsapp, email};

            Alerts.modals({type: "show", id: this.forms.entity.createUpdate.extras.modals.actions.id});

        },
        cancelEntity({}) {

            const functionName = "cancelEntity";

            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms.entity.createUpdate.extras.modals.actions.data);

            const validateForm = this.validateForm({functionName, form});

            Alerts.modals({type: "hide", id: this.forms.entity.createUpdate.extras.modals.actions.id});

            if(validateForm?.bool) {

                let el = this;

                Swal.fire({
                    html: `<span class="d-block my-1">¿Desea anular la venta <b>${form?.serie_sequential}</b>?</span>
                           <div class="d-block text-muted mt-2 mb-1">
                                <i class="fa fa-warning text-warning"></i>
                                <span><b>Importante:</b> Si esta venta incluye <u>membresías</u>, estas serán <b>anuladas</b> automáticamente.</span>
                            </div>
                           `,
                    icon: "warning",
                    allowOutsideClick: false,
                    showCancelButton: true,
                    confirmButtonText: "Sí, anular",
                    cancelButtonText: "Cancelar",
                    customClass: {
                        confirmButton: "btn btn-danger waves-effect",
                        cancelButton: "btn btn-secondary waves-effect ms-3"
                    }
                }).then(async function(result) {

                    if(result.isConfirmed) {

                        Alerts.swals({});

                        let cancel = await Requests.patch({route: el.config.entity.routes.cancel, id: form.id});

                        if(Requests.valid({result: cancel})) {

                            Alerts.toastrs({type: "success", subtitle: cancel?.data?.msg});
                            Alerts.swals({show: false});

                            el.listEntity({})

                        }else {

                            Alerts.toastrs({type: "error", subtitle: cancel?.data?.msg});
                            Alerts.swals({show: false});

                        }

                    }else if(result.isDismissed) {

                        //

                    }

                })

            }else {

                Alerts.generateAlert({messages: Utils.getErrors({errors: validateForm}), msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`});

            }

            Alerts.tooltips({show: false});

        },
        // Forms utils
        clearForm({functionName}) {

            switch(functionName) {
                case "createUpdateEntity":
                    //
                    break;
            }

        },
        formErrors({functionName, type = "clear", errors = []}) {

            if(["createUpdateEntity"].includes(functionName)) {

                //

            }else if(["cancelEntity"].includes(functionName)) {

                this.forms.entity.createUpdate.extras.modals.actions.errors = ["set"].includes(type) ? errors : [];

            }

        },
        validateForm({functionName, form = null, extras = null}) {

            let result = {
                bool: true
            };

            if(["createUpdateEntity"].includes(functionName)) {

                //

            }else if(["cancelEntity"].includes(functionName)) {

                result.sale = [];

                if(!this.isDefined({value: form?.id})) {

                    result.sale.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

            }

            return result;

        },
        // Others
        isDefined({value}) {

            return Utils.isDefined({value});

        },
        separatorNumber(value) {

            return Utils.separatorNumber(value);

        },
        truncate({value, length}) {

            return Utils.truncate({value, length});

        },
        diffDaysLegible({diff}) {

            return Utils.diffDaysLegible({diff});

        },
        sendWhatsapp({data = null, action = "reportSale"}) {

            const phoneNumber = this.forms.entity.createUpdate.extras.modals.actions.data.whatsapp;
            const message     = Utils.getMessageWhatsapp({data, action});

            Utils.sendWhatsapp({phoneNumber, message});

        },
        async sendEmail({data = null, action = "reportSale"}) {

            let route = Requests.config({entity: "helpers", type: "sendEmail"});
            const formJson = {serie_sequential: data?.serie_sequential, email: data?.email, message: Utils.getMessageWhatsapp({data, action})};

            Alerts.swals({});

            let sendEmail = await Requests.post({route: route, data: formJson, id: data?.id});

            if(Requests.valid({result: sendEmail})) {

                Alerts.toastrs({type: "success", subtitle: sendEmail?.data?.msg});
                Alerts.swals({show: false});

            }else {

                Alerts.toastrs({type: "error", subtitle: sendEmail?.data?.msg});
                Alerts.swals({show: false});

            }

            Alerts.tooltips({show: false});

        }
    },
    computed: {
        breadcrumbTitles: function() {

            return [{title: "Ventas"}, this.config.entity.page];

        },
        series: function() {

            let series = [];

            let branches = (this.options?.branches?.records ?? []);

            for(let branch of branches) {

                for(let branchSerie of branch.series) {

                    series.push({code: branchSerie.id, label: `(${branch?.name}) ${branchSerie.legible_serie} - ${branchSerie?.document_type?.name}`, data: {...branchSerie, branch}});

                }

            }

            return series;

        },
        holders: function() {

            return this.options?.holders?.records.map(e => ({code: e.id, label: `${e.document_number} - ${e.name}`, data: e}));

        },
        statuses: function() {

            return this.options?.salesHeader?.statuses.map(e => ({code: e.code, label: e.label}));

        }
    }
};
</script>
