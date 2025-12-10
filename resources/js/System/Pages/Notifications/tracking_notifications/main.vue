<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr class="text-center align-middle">
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 25%;">DESTINATARIO</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 30%;">ASUNTO</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 15%;">FECHA DE CREACIÓN</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 15%;">ESTADO</th>
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
                            <td class="text-start">
                                <span class="ms-2" v-text="isDefined({value: record?.formatted_extras_json?.customer?.name}) ? record?.formatted_extras_json?.customer?.name : 'N/A'"></span>
                                <ul>
                                    <li>
                                        <i class="fa fa-envelope"></i>
                                        <span class="ms-2" v-text="isDefined({value: record.to}) ? record.to : 'N/A'"></span>
                                    </li>
                                    <li>
                                        <i class="fa fa-phone"></i>
                                        <span class="ms-2" v-text="isDefined({value: record?.formatted_extras_json?.customer?.phone}) ? record?.formatted_extras_json?.customer?.phone : 'N/A'"></span>
                                    </li>
                                </ul>
                            </td>
                            <td class="text-start">
                                <span v-text="record.subject" class="fw-bold d-block"></span>
                            </td>
                            <td>
                                <span v-text="legibleFormatDate({dateString: record.created_at, type: 'date'})" class="d-block fw-semibold"></span>
                                <span v-text="legibleFormatDate({dateString: record.created_at, type: 'time'})" class="d-block fw-semibold"></span>
                            </td>
                            <td>
                                <span :class="['badge', 'fw-semibold', { 'bg-label-success': ['sent'].includes(record.status), 'bg-label-warning': ['pending'].includes(record.status), 'bg-label-danger': ['failed'].includes(record.status) }]" v-text="record.formatted_status"></span>
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
                                            <i class="fa fa-eye"></i>
                                            <span class="ms-2">Detalle</span>
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
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold">Detalle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row justify-content-center g-1">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Destinatario:</span>
                            <span class="ms-2" v-text="forms.entity.createUpdate.extras.modals.actions.data?.to"></span>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Asunto:</span>
                            <span class="ms-2" v-text="forms.entity.createUpdate.extras.modals.actions.data?.subject"></span>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Fecha de creación:</span>
                            <span class="ms-2" v-text="legibleFormatDate({dateString: forms.entity.createUpdate.extras.modals.actions.data?.created_at, type: 'datetime'})"></span>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Estado:</span>
                            <span class="ms-2" v-text="forms.entity.createUpdate.extras.modals.actions.data?.formatted_status"></span>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <span class="fw-semibold">• Contenido <small>(Referencia)</small></span>
                            <p ref="emailIframe" v-html="forms.entity.createUpdate.extras.modals.actions.data?.body" class="mt-2 user-select-none pe-none border"></p>
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
            this.listEntity({});

        }

    },
    data() {
        return {
            lists: {
                entity: {
                    extras: {
                        loading: false,
                        route: Requests.config({entity: "tracking_notifications", type: "list"})
                    },
                    filters: {
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
                    ...Requests.config({entity: "tracking_notifications"}),
                    page: {
                        title: "Notificaciones",
                        active: true,
                        menu: {
                            id: "menu-item-trackings-notifications"
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
            const filterJson = {};

            this.lists.entity.extras.loading = true;
            this.lists.entity.records        = (await Requests.get({route: url || this.lists.entity.extras.route, data: filterJson}))?.data;
            this.lists.entity.extras.loading = false;

        },
        // Forms
        modalActionsEntity({record = null}) {

            this.forms.entity.createUpdate.extras.modals.actions.data = record;

            Alerts.modals({type: "show", id: this.forms.entity.createUpdate.extras.modals.actions.id});

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

        }
    },
    watch: {
        "lists.entity.filters.status": function(newValue, oldValue) {

            // this.listEntity({});

        },
        "forms.entity.createUpdate.extras.modals.actions.data.body": function(newValue, oldValue) {

            this.$nextTick(() => {

                if(this.$refs.emailIframe) {

                    const iframe = this.$refs.emailIframe;

                    if(!iframe || !iframe.contentWindow) return;

                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (!doc) return;

                    doc.open();
                    doc.write(newValue || "");
                    doc.close();

                }

            });

        }
    }
};
</script>
