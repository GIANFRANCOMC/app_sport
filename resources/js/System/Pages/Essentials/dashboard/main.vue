<template>
    <!-- <Breadcrumb :list="breadcrumbTitles"/> -->

    <!-- Content -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="fa-solid fa-cash-register"></i>
                            </span>
                        </div>
                        <h5 class="mb-0" v-text="'S/ '+separatorNumber(fixedNumber(forms.entity.dashboard.data.sales?.all?.total ?? 0))"></h5>
                    </div>
                    <span class="fw-semibold">VENTAS EN TOTAL</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="fa-solid fa-rectangle-xmark"></i>
                            </span>
                        </div>
                        <h5 class="mb-0" v-text="'S/ '+separatorNumber(fixedNumber(forms.entity.dashboard.data.sales?.canceled?.total ?? 0))"></h5>
                    </div>
                    <span class="fw-semibold">VENTAS ANULADAS</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-git-fork ti-md"></i>
                            </span>
                        </div>
                        <h5 class="mb-0" v-text="separatorNumber(forms.entity.dashboard.data.branches?.valid?.count ?? 0)"></h5>
                    </div>
                    <span class="fw-semibold">SUCURSALES</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-secondary h-100">
                <div class="card-body">
                    <InputDate
                        v-model="forms.entity.dashboard.data.date"
                        @change="initData({loading: true})"
                        hasDiv
                        title="Fecha a consultar"
                        isRequired
                        :titleClass="['form-label', 'colon-at-end', 'fw-semibold']"/>
                </div>
            </div>
        </div>
        <!-- <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-secondary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="fa-solid fa-users"></i>
                            </span>
                        </div>
                        <h5 class="mb-0" v-text="separatorNumber(forms.entity.dashboard.data.users?.valid?.count ?? 0)"></h5>
                    </div>
                    <span class="fw-semibold">COLABORADORES</span>
                </div>
            </div>
        </div> -->
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-12 col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 fw-semibold">
                            Ventas creadas
                            <small class="text-muted" v-text="'| Fecha: '+legibleFormatDate({dateString: forms.entity.dashboard.data.date, type: 'date'})"></small>
                        </h5>
                    </div>
                    <div v-show="false">
                        <button class="btn btn-sm btn-primary" @click="initChart()" type="button">
                            <i class="fa-solid fa-sync"></i>
                            <span class="ms-2">Actualizar</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="barChartId" class="chartjs" data-height="230"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="card-title mb-0">
                <h5 class="m-0 fw-semibold">
                    Últimas ventas
                    <small class="text-muted" v-text="'| Fecha: '+legibleFormatDate({dateString: forms.entity.dashboard.data.date, type: 'date'})"></small>
                </h5>
            </div>
            <div v-show="false">
                <button class="btn btn-sm btn-primary" @click="goSalesList()">
                    <i class="fa-solid fa-cash-register"></i>
                    <span class="ms-2">Ir al listado de ventas</span>
                </button>
            </div>
        </div>
        <div class="card-body">
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
                        <template v-if="lastSales.length > 0">
                            <tr v-for="record in lastSales" :key="record.id" class="text-center">
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <PrintSale :modalId="forms.entity.dashboard.extras.modals.actions.id" :data="forms.entity.dashboard.extras.modals.actions.data">
        <template v-slot:extraGroupAppend>
            <div class="row g-2 mt-4">
                <InputText
                    hasDiv
                    title="Número de celular (Whatsapp)"
                    v-model="forms.entity.dashboard.extras.modals.actions.data.whatsapp">
                    <template v-slot:inputGroupAppend>
                        <button class="btn btn-success waves-effect" type="button" @click="sendWhatsapp({data: forms.entity.dashboard.extras.modals.actions.data})" :disabled="!isDefined({value: forms.entity.dashboard.extras.modals.actions.data.whatsapp})">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span class="ms-2">Enviar</span>
                        </button>
                    </template>
                </InputText>
                <InputText
                    v-if="false"
                    hasDiv
                    title="Correo electrónico"
                    v-model="forms.entity.dashboard.extras.modals.actions.data.email">
                    <template v-slot:inputGroupAppend>
                        <button class="btn btn-info-1 waves-effect" type="button" @click="sendEmail({data: forms.entity.dashboard.extras.modals.actions.data})" :disabled="!isDefined({value: forms.entity.dashboard.extras.modals.actions.data.email})">
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

let barChartInstance = null;

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
            this.initData({});

        }

    },
    data() {
        return {
            forms: {
                entity: {
                    dashboard: {
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
                        data: {
                            date: "",
                            sales: null,
                            branches: null,
                            users: null
                        }
                    }
                }
            },
            options: {},
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "dashboard"}),
                    page: {
                        title: "Dashboard",
                        active: true,
                        menu: {
                            id: "menu-item-dashboard"
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

                this.forms.entity.dashboard.data.date = Utils.getCurrentDate();

                resolve(true);

            });

        },
        async initData({loading = false}) {

            if(loading) {

                Alerts.swals({type: "consult"});

            }

            if(!this.isDefined({value: this.forms.entity.dashboard.data.date})) {

                this.forms.entity.dashboard.data.date = Utils.getCurrentDate();

            }

            let initData = await Requests.get({route: this.config.entity.routes.initData, data: {page: "main", date: this.forms.entity.dashboard.data.date}, showAlert: true});

            this.forms.entity.dashboard.data.sales    = initData.data?.data?.sales;
            this.forms.entity.dashboard.data.branches = initData.data?.data?.branches;
            this.forms.entity.dashboard.data.users    = initData.data?.data?.users;

            this.initChart();

            if(loading) {

                Alerts.swals({show: false});

            }

            return Requests.valid({result: initData});

        },
        initChart() {

            // Utils
            const roundUpToNearest = (value) => {
                if (value <= 500) return 500;
                if (value <= 1000) return 1000;
                if (value <= 3000) return 3000;
                return Math.ceil(value / 20000) * 20000;
            };

            // Ajust height
            const chartList = document.querySelectorAll(".chartjs");

            chartList.forEach(function(chartListItem) {

                chartListItem.height = chartListItem.dataset.height;

            });

            // Config
            const intervals = [
                { label: "12:00 AM - 02:59 AM", start: 0, end: 3 },
                { label: "03:00 AM - 05:59 AM", start: 3, end: 6 },
                { label: "06:00 AM - 08:59 AM", start: 6, end: 9 },
                { label: "09:00 AM - 11:59 PM", start: 9, end: 12 },
                { label: "12:00 PM - 02:59 PM", start: 12, end: 15 },
                { label: "03:00 PM - 05:59 PM", start: 15, end: 18 },
                { label: "06:00 PM - 08:59 PM", start: 18, end: 21 },
                { label: "09:00 PM - 11:59 AM", start: 21, end: 24 }
            ];

            const totalsByInterval = intervals.map(interval => ({ label: interval.label, total: 0 }));

            // Process data
            const sales = this.forms.entity.dashboard.data.sales.all.records;

            sales.forEach(sale => {

                const saleHour = new Date(sale.created_at).getHours();
                const interval = intervals.find(i => saleHour >= i.start && saleHour < i.end);

                if(interval) {

                    const index = intervals.indexOf(interval);
                    totalsByInterval[index].total += parseFloat(sale.total);

                }

            });

            // Get information
            const barChart = document.getElementById("barChartId");
            const labels   = totalsByInterval.map(i => i.label);
            const data     = totalsByInterval.map(i => i.total);
            const yMax     = roundUpToNearest(Math.max(...data));

            if(barChart) {

                if(barChartInstance) {

                    barChartInstance.destroy();

                }

                barChartInstance = new Chart(barChart, {
                    type: "bar",
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                data: data,
                                backgroundColor: "#28dac6",
                                borderColor: "transparent",
                                maxBarThickness: 20,
                                borderRadius: {
                                    topRight: 15,
                                    topLeft: 15
                                }
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 500
                        },
                        plugins: {
                            tooltip: {
                                backgroundColor: this.config.colors.charts.default.backgroundColor,
                                bodyColor: this.config.colors.charts.default.bodyColor,
                                borderColor: this.config.colors.charts.default.borderColor,
                                borderWidth: 1,
                                rtl: false,
                                titleColor: this.config.colors.charts.default.titleColor
                            },
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: this.config.colors.charts.default.borderColor,
                                    drawBorder: false,
                                    borderColor: this.config.colors.charts.default.borderColor
                                },
                                ticks: {
                                    color: this.config.colors.charts.default.labelColor
                                }
                            },
                            y: {
                                min: 0,
                                max: yMax,
                                grid: {
                                    color: this.config.colors.charts.default.borderColor,
                                    drawBorder: false,
                                    borderColor: this.config.colors.charts.default.borderColor
                                },
                                ticks: {
                                    stepSize: yMax / 5,
                                    color: this.config.colors.charts.default.labelColor
                                }
                            }
                        }
                    }
                });

            };

        },
        // Entity forms
        modalActionsEntity({record = null}) {

            const whatsapp = record?.holder?.phone_number ?? "";
            const email    = record?.holder?.email ?? "";

            this.forms.entity.dashboard.extras.modals.actions.data = {...record, extras: {}, whatsapp, email};

            Alerts.modals({type: "show", id: this.forms.entity.dashboard.extras.modals.actions.id});

        },
        goSalesList() {

            const url = Requests.config({entity: "sales", type: "consult"});

            window.location.href = url;

        },
        // Others
        isDefined({value}) {

            return Utils.isDefined({value});

        },
        fixedNumber(value) {

            return Utils.fixedNumber(value);

        },
        separatorNumber(value) {

            return Utils.separatorNumber(value);

        },
        legibleFormatDate({dateString = null, type = "datetime"}) {

            return Utils.legibleFormatDate({dateString, type});

        },
        sendWhatsapp({data = null, action = "reportSale"}) {

            const phoneNumber = this.forms.entity.dashboard.extras.modals.actions.data.whatsapp;
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

            return [this.config.entity.page];

        },
        lastSales: function() {

            return (this.forms.entity.dashboard.data.sales?.all?.records ?? []).slice(0, 10);

        }
    }
};
</script>
