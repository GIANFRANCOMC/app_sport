<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="row align-items-end g-3 mb-3 mb-md-2">
        <InputSlot
            hasDiv
            :isInputGroup="false"
            :divInputClass="['d-flex flex-wrap justify-content-center align-items-end gap-2 gap-md-3 mb-2']"
            xl="12"
            lg="12">
            <template v-slot:input>
                <template v-if="isDefined({value: customerCurrent?.customer})">
                    <button type="button" class="btn btn-primary btn-sm waves-effect" @click="modalCreateUpdateEntity({})">
                        <i class="fa fa-search"></i>
                        <span class="ms-2">Realizar otra búsqueda</span>
                    </button>
                    <button type="button" class="btn btn-info-1 btn-sm waves-effect" @click="getTrackingCustomers({refresh: true})">
                        <i class="fa fa-sync"></i>
                        <span class="ms-2">Refrescar expediente</span>
                    </button>
                </template>
                <template v-else>
                    <button type="button" class="btn btn-primary btn-sm waves-effect" @click="modalCreateUpdateEntity({})">
                        <i class="fa fa-hand-pointer"></i>
                        <span class="ms-2">Seleccione un cliente</span>
                    </button>
                </template>
            </template>
        </InputSlot>
    </div>
    <div class="row g-3">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 ps-1 ps-md-3 pe-0 pe-md-3" v-if="isDefined({value: customerCurrent?.customer})">
            <Timeline :data="customerCurrent ?? {}">
                <template v-slot:statisticsPrepend>
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                        <div class="d-flex justify-content-start align-items-center h-100 text-start">
                            <div class="w-100">
                                <div>
                                    👤 <span class="fs-4 fw-bold" v-text="customerCurrent?.customer?.name ?? ''"></span>
                                </div>
                                <div>
                                    <span v-text="customerCurrent?.customer?.identity_document_type?.name ?? ''" class="fw-bold colon-at-end"></span>
                                    <span class="fw-bold ms-1" v-text="customerCurrent?.customer?.document_number ?? ''"></span>
                                </div>
                                <span v-text="'Periodo: '+periodTypeCurrent" class="text-primary fw-semibold"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                        <div class="d-flex justify-content-start align-items-center h-100 text-start">
                            <div class="w-100" v-if="(customerCurrent?.extras?.options?.information ?? []).some(e => ['sales', 'subscriptions', 'attendances'].includes(e))">
                                <span class="fw-bold colon-at-end">📅 Membresía vigente hasta</span>
                                <template v-if="(customerCurrent?.functions?.subscription_end_dates ?? []).length > 0">
                                    <ul>
                                        <li v-for="(record, indexRecord) in (customerCurrent?.functions?.subscription_end_dates ?? [])" :key="indexRecord">
                                            <span v-text="record?.branch?.name" class="colon-at-end fw-bold"></span>
                                            <span v-text="legibleFormatDate({dateString: record?.max_end_date})" class="ms-2"></span>
                                        </li>
                                    </ul>
                                </template>
                                <template v-else>
                                    <div class="alert alert-warning py-0 text-center">
                                        <i class="fa fa-warning"></i>
                                        <span class="ms-2">Sin membresías.</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </Timeline>
        </div>
        <div v-else class="text-center">
            <WithoutData type="image"/>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" :id="forms.entity.createUpdate.extras.modals.default.id" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="forms.entity.createUpdate.extras.modals.default.titles.default"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <InputSlot
                            hasDiv
                            title="Cliente"
                            isRequired
                            xl="12"
                            lg="12">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.customer"
                                    :options="customers"
                                    :class="config.forms.classes.select2"
                                    :clearable="false"
                                    placeholder="Seleccione un cliente ..."/>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            title="Filtrar por periodo"
                            isRequired
                            xl="12"
                            lg="12">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.periodType"
                                    :options="periodTypes"
                                    :class="config.forms.classes.select2"
                                    :clearable="false"
                                    :searchable="false"
                                    placeholder="Seleccione un periodo ..."/>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            title="¿Qué deseas visualizar?"
                            isRequired
                            :isInputGroup="false"
                            :divInputClass="['d-flex flex-wrap justify-content-center align-items-end gap-2 gap-md-3 pt-2 pt-md-2']"
                            xl="12"
                            lg="12">
                            <template v-slot:input>
                                <div class="form-check ms-2">
                                    <label class="cursor-pointer">
                                        <input class="form-check-input" type="checkbox" value="sales" v-model="forms.entity.createUpdate.data.options.information"/>
                                        <span class="fw-bold text-secondary">Ventas</span>
                                    </label>
                                </div>
                                <div class="form-check ms-2">
                                    <label class="cursor-pointer">
                                        <input class="form-check-input" type="checkbox" value="subscriptions" v-model="forms.entity.createUpdate.data.options.information"/>
                                        <span class="fw-bold text-secondary">Membresías</span>
                                    </label>
                                </div>
                                <div class="form-check ms-2">
                                    <label class="cursor-pointer">
                                        <input class="form-check-input" type="checkbox" value="attendances" v-model="forms.entity.createUpdate.data.options.information"/>
                                        <span class="fw-bold text-secondary">Asistencias</span>
                                    </label>
                                </div>
                            </template>
                        </InputSlot>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn waves-effect btn-primary" @click="getTrackingCustomers({refresh: false})">
                        <i class="fa fa-search"></i>
                        <span class="ms-2">Buscar</span>
                    </button>
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

        Utils.navbarItem("menu-item-trackings", {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});
        Alerts.swals({type: "initParams"});

        let initParams = await this.initParams({}),
            initOthers = await this.initOthers({});

        if(initParams && initOthers) {

            Alerts.swals({show: false});
            // this.listEntity({});

        }

    },
    data() {
        return {
            forms: {
                entity: {
                    createUpdate: {
                        extras: {
                            modals: {
                                default: {
                                    id: Utils.uuid(),
                                    titles: {
                                        default: "Búsqueda"
                                    }
                                }
                            }
                        },
                        data: {
                            customer: null,
                            periodType: null,
                            options: {
                                information: []
                            }
                        },
                        history: {
                            customers: {},
                            customerCurrent: null,
                            periodTypeCurrent: null,
                            optionsCurrent: null
                        },
                        errors: {}
                    }
                }
            },
            options: {},
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "tracking_customers"}),
                    page: {
                        title: "Expediente",
                        active: true,
                        menu: {
                            id: "menu-item-trackings-customers"
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

            this.options.customers = initParams.data?.config?.customers;

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                this.forms.entity.createUpdate.data.periodType = this.periodTypes.length > 3 ? this.periodTypes[2] : null;
                this.forms.entity.createUpdate.data.options.information = ["sales", "subscriptions"];

                this.modalCreateUpdateEntity({});

                resolve(true);

            });

        },
        // Forms
        modalCreateUpdateEntity({record = null}) {

            const functionName = "modalCreateUpdateEntity";

            // Alerts.swals({});
            this.clearForm({functionName});
            this.formErrors({functionName, type: "clear"});

            if(this.isDefined({value: record})) {

                //

            }else {

                //

            }

            // Alerts.swals({show: false});
            Alerts.modals({type: "show", id: this.forms.entity.createUpdate.extras.modals.default.id});

        },
        async getTrackingCustomers({refresh = false}) {

            const functionName = "getTrackingCustomers";

            if(refresh) {

                let form = this.forms.entity.createUpdate.history;

                Alerts.swals({});

                const getSubscriptions = await Utils.getTrackingCustomers({customer: {id: form.customerCurrent.code}, period_type: form.periodTypeCurrent.code, options: form.optionsCurrent});

                this.forms.entity.createUpdate.history.customers[form.customerCurrent.code] = Requests.valid({result: getSubscriptions}) ? getSubscriptions?.data?.tracking : {};

                Alerts.swals({show: false});


            }else {

                let form = this.forms.entity.createUpdate.data;

                const validateForm = this.validateForm({functionName, form});

                if(validateForm?.bool) {

                    Alerts.modals({type: "hide", id: this.forms.entity.createUpdate.extras.modals.default.id});
                    Alerts.swals({});

                    const getSubscriptions = await Utils.getTrackingCustomers({customer: {id: form.customer.code}, period_type: form.periodType.code, options: form.options});

                    if(Requests.valid({result: getSubscriptions})) {

                        this.forms.entity.createUpdate.history.customers[form.customer.code] = getSubscriptions?.data?.tracking;

                        // Options history
                        this.forms.entity.createUpdate.history.customerCurrent   = this.forms.entity.createUpdate.data.customer;
                        this.forms.entity.createUpdate.history.periodTypeCurrent = this.forms.entity.createUpdate.data.periodType;
                        this.forms.entity.createUpdate.history.optionsCurrent    = this.forms.entity.createUpdate.data.options;

                    }else {

                        this.forms.entity.createUpdate.history.customers[form.customer.code] = {};

                    }

                    Alerts.swals({show: false});

                }else {

                    Alerts.generateAlert({messages: Utils.getErrors({errors: validateForm}), msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorSearchValidate}</div>`});

                }

            }

        },
        // Forms utils
        clearForm({functionName}) {

            switch(functionName) {
                case "modalCreateUpdateEntity":
                case "createUpdateEntity":
                    // this.forms.entity.createUpdate.data.customer = null;
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

                //

            }else if(["getTrackingCustomers"].includes(functionName)) {

                result.customer    = [];
                result.period_type = [];
                result.options_information = [];

                if(!this.isDefined({value: form?.customer?.code})) {

                    result.customer.push(`Debe seleccionar un cliente.`);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.periodType?.code})) {

                    result.period_type.push(`Debe seleccionar un periodo.`);
                    result.bool = false;

                }

                if((form?.options?.information ?? []).length === 0) {

                    result.options_information.push(`Debe seleccionar al menos un tipo de información.`);
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

        }
    },
    computed: {
        breadcrumbTitles: function() {

            return [{title: "Seguimiento"}, this.config.entity.page];

        },
        customers: function() {

            return this.options?.customers?.records.map(e => ({code: e.id, label: `${e.document_number} - ${e.name}`, data: e}));

        },
        periodTypes: function() {

            return [
                {code: "last_1_days", label: "Último día"},
                {code: "last_7_days", label: "Últimos 7 días"},
                {code: "last_14_days", label: "Últimos 14 días"},
                {code: "last_21_days", label: "Últimos 21 días"},
                {code: "last_1_months", label: "Último mes"},
                {code: "last_3_months", label: "Últimos 3 meses"},
                {code: "last_6_months", label: "Últimos 6 meses"},
                {code: "last_9_months", label: "Últimos 9 meses"},
                {code: "this_year", label: "Este año"},
                // {code: "custom", label: "Seleccionar mes y año"}
            ];

        },
        customerCurrent: function() {

            return this.forms.entity.createUpdate.history?.customers[this.forms.entity.createUpdate.history.customerCurrent?.code] ?? {};

        },
        periodTypeCurrent: function() {

            const code = this.customerCurrent?.extras?.period_type ?? "";
            const found = this.periodTypes.find(p => p.code === code);

            return found?.label ?? "No identificado";

        }
    },
    watch: {
        "forms.entity.createUpdate.data.customer": function(newValue, oldValue) {

            //

        }
    }
};
</script>
