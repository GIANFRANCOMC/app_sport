<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="row align-items-start g-3 mb-3 mb-md-4">
        <div class="col-lg-9 col-12">
            <div class="row g-3">
                <InputSlot
                    hasDiv
                    title="Sucursal"
                    :titleClass="[config.forms.classes.title]"
                    xl="12"
                    lg="12">
                    <template v-slot:input>
                        <v-select
                            v-model="lists.entity.filters.branch"
                            :options="branches"
                            :class="config.forms.classes.select2"
                            :clearable="false"
                            :searchable="false"
                            placeholder="Seleccione una sucursal ..."/>
                    </template>
                </InputSlot>
                <InputSlot
                    hasDiv
                    title="Cliente"
                    :titleClass="[config.forms.classes.title]"
                    xl="12"
                    lg="12">
                    <template v-slot:input>
                        <v-select
                            v-model="lists.entity.filters.customer"
                            :options="customers"
                            :class="config.forms.classes.select2"
                            :clearable="true"
                            placeholder="Seleccione un cliente ..."/>
                    </template>
                </InputSlot>
                <InputDate
                    v-model="lists.entity.filters.start_date"
                    @change="listEntity({})"
                    hasDiv
                    title="Desde"
                    :titleClass="[config.forms.classes.title]"
                    xl="6"
                    lg="6"/>
                <InputDate
                    v-model="lists.entity.filters.end_date"
                    @change="listEntity({})"
                    hasDiv
                    title="Hasta"
                    :titleClass="[config.forms.classes.title]"
                    xl="6"
                    lg="6"/>
            </div>
        </div>
        <div class="col-lg-3 col-12">
            <div class="row g-1">
                <div class="col-xl-12">
                    <label :class="[config.forms.classes.title]">Estado</label>
                </div>
                <div class="col-lg-12 col-xl-12">
                    <div class="form-check">
                        <label class="py-1 cursor-pointer">
                            <input :class="['form-check-input', lists.entity.filters.status == '' ? 'bg-secondary border-secondary' : '']" type="radio" value="" v-model="lists.entity.filters.status" @change="listEntity({})"/>
                            <span class="fw-bold text-secondary">Todos los estados</span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-12 col-xl-12">
                    <div class="form-check">
                        <label class="py-1 cursor-pointer">
                            <input :class="['form-check-input', lists.entity.filters.status == 'active' ? 'bg-success border-success' : '']" type="radio" value="active" v-model="lists.entity.filters.status" @change="listEntity({})"/>
                            <span class="fw-bold text-success">Vigente</span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-12 col-xl-12">
                    <div class="form-check">
                        <label class="py-1 cursor-pointer">
                            <input :class="['form-check-input', lists.entity.filters.status == 'inactive' ? 'bg-primary border-primary' : '']" type="radio" value="inactive" v-model="lists.entity.filters.status" @change="listEntity({})"/>
                            <span class="fw-bold text-primary">Vencida</span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-12 col-xl-12">
                    <div class="form-check">
                        <label class="py-1 cursor-pointer">
                            <input :class="['form-check-input', lists.entity.filters.status == 'canceled' ? 'bg-danger border-danger' : '']" type="radio" value="canceled" v-model="lists.entity.filters.status" @change="listEntity({})"/>
                            <span class="fw-bold text-danger">Anulada</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <InputSlot
            hasDiv
            :isInputGroup="false"
            :divInputClass="['d-flex flex-wrap justify-content-start gap-2 gap-md-3']"
            xl="12"
            lg="12">
            <template v-slot:input>
                <button type="button" class="btn btn-info-1 waves-effect" @click="listEntity({})" :disabled="lists.entity.extras.loading">
                    <i class="fa fa-filter"></i>
                    <span class="ms-2">Filtrar membresías</span>
                </button>
            </template>
        </InputSlot>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr class="text-center align-middle">
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 10%;"></th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 20%;">SUCURSAL</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 25%;">CLIENTE</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 15%;">FECHA DE INICIO</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 15%;">FECHA DE FINALIZACIÓN</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 15%;">ACCIONES</th>
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
                            <td>
                                <span :class="['badge', 'fw-semibold', { 'bg-label-success': ['active'].includes(record.status), 'bg-label-primary': ['inactive'].includes(record.status), 'bg-label-danger': ['canceled'].includes(record.status) }]" v-text="record.formatted_status"></span>
                            </td>
                            <td class="text-start">
                                <span v-text="record.branch?.name" class="fw-bold d-block"></span>
                            </td>
                            <td class="text-start">
                                <span v-text="record.customer?.name" class="fw-bold d-block"></span>
                                <small v-text="record.customer?.document_number" class="d-block"></small>
                            </td>
                            <td>
                                <span v-text="legibleFormatDate({dateString: record.start_date, type: 'date'})" class="d-block fw-semibold"></span>
                                <span v-text="legibleFormatDate({dateString: record.start_date, type: 'time'})" class="d-block fw-semibold"></span>
                            </td>
                            <td>
                                <span v-text="legibleFormatDate({dateString: record.end_date, type: 'date'})" class="d-block fw-semibold"></span>
                                <span v-text="legibleFormatDate({dateString: record.end_date, type: 'time'})" class="d-block fw-semibold"></span>
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

    <div class="modal fade" :id="forms.entity.createUpdate.extras.modals.actions.id" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold">Detalle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row justify-content-center g-1">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Cliente:</span>
                            <span class="ms-2" v-text="forms.entity.createUpdate.extras.modals.actions.data?.customer?.name"></span>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Fecha de inicio:</span>
                            <span class="ms-2" v-text="legibleFormatDate({dateString: forms.entity.createUpdate.extras.modals.actions.data?.start_date, type: 'datetime'})"></span>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Fecha de finalización:</span>
                            <span class="ms-2" v-text="legibleFormatDate({dateString: forms.entity.createUpdate.extras.modals.actions.data?.end_date, type: 'datetime'})"></span>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Origen:</span>
                            <span class="ms-2" v-text="forms.entity.createUpdate.extras.modals.actions.data?.formatted_type"></span>
                            <span v-if="isDefined({value: forms.entity.createUpdate.extras.modals.actions.data?.sale_header?.serie_sequential})" class="ms-2 fw-semibold" v-text="forms.entity.createUpdate.extras.modals.actions.data?.sale_header?.serie_sequential"></span>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Estado:</span>
                            <span class="ms-2" v-text="forms.entity.createUpdate.extras.modals.actions.data?.formatted_status"></span>
                        </div>
                        <div v-if="['canceled'].includes(forms.entity.createUpdate.extras.modals.actions.data?.status)" class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Motivo:</span>
                            <span class="ms-2" v-text="forms.entity.createUpdate.extras.modals.actions.data?.motive"></span>
                        </div>
                        <div v-if="['active'].includes(forms.entity.createUpdate.extras.modals.actions.data?.status)" class="col-xl-3 col-lg-3 col-md-3 col-sm-3 mt-4">
                            <div class="text-center cursor-pointer p-1" @click="cancelEntity({})">
                                <div class="badge bg-danger p-3 rounded mb-1">
                                    <i class="fa-solid fa-rectangle-xmark fs-3"></i>
                                </div>
                                <span class="d-block fw-semibold text-danger">Anular membresía</span>
                            </div>
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
            lists: {
                entity: {
                    extras: {
                        loading: false,
                        route: Requests.config({entity: "tracking_subscriptions", type: "list"})
                    },
                    filters: {
                        branch: null,
                        customer: null,
                        start_date: "",
                        end_date: "",
                        status: ""
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
                                        id: null
                                    }
                                }
                            }
                        },
                        data: {
                            id: null,
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
                    ...Requests.config({entity: "tracking_subscriptions"}),
                    page: {
                        title: "Membresías",
                        active: true,
                        menu: {
                            id: "menu-item-trackings-subscriptions"
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

            this.options.branches  = initParams.data?.config?.branches;
            this.options.customers = initParams.data?.config?.customers;

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                this.lists.entity.filters.branch     = this.branches[0];
                // this.lists.entity.filters.start_date = Utils.getCurrentDate("date");
                // this.lists.entity.filters.end_date   = Utils.getCurrentDate("date");

                resolve(true);

            });

        },
        // Entity forms
        async listEntity({url = null}) {

            let filters = Utils.cloneJson(this.lists.entity.filters);
            const filterJson = {branch_id: filters?.branch?.code, customer_id: filters?.customer?.code, start_date: filters?.start_date, end_date: filters?.end_date, status: filters?.status};

            this.lists.entity.extras.loading = true;
            this.lists.entity.records        = (await Requests.get({route: url || this.lists.entity.extras.route, data: filterJson}))?.data;
            this.lists.entity.extras.loading = false;

        },
        // Forms
        modalActionsEntity({record = null}) {

            this.forms.entity.createUpdate.extras.modals.actions.data = record;

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
                    html: `<span class="d-block my-1">¿Desea anular la membresía del cliente <b>${form.customer?.name}</b>?</span>
                           <div class="form-group text-start mt-2">
                                <label class="form-label colon-at-end">Motivo</label>
                                <div class="input-group">
                                    <textarea type="text" class="form-control no-resize" maxlength="999" id="motiveId"></textarea>
                                </div>
                           </div>`,
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

                        const motive = Swal.getHtmlContainer().querySelector("#motiveId").value;

                        Alerts.swals({});

                        let cancel = await Requests.patch({route: el.config.entity.routes.cancel, data: {motive}, id: form.id});

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
                case "modalCreateUpdateEntity":
                case "createUpdateEntity":
                    //
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

            }else if(["cancelEntity"].includes(functionName)) {

                result.msg = [];

                if(!this.isDefined({value: form?.id})) {

                    result.msg.push(`Registro: ${this.config.forms.errors.labels.required}`);
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
        branches: function() {

            return this.options?.branches?.records.map(e => ({code: e.id, label: e.name, data: e}));

        },
        customers: function() {

            return this.options?.customers?.records.map(e => ({code: e.id, label: `${e.document_number} - ${e.name}`, data: e}));

        }
    },
    watch: {
        "lists.entity.filters.branch": function(newValue, oldValue) {

            this.listEntity({});

        },
        "lists.entity.filters.customer": function(newValue, oldValue) {

            this.listEntity({});

        }
    }
};
</script>
