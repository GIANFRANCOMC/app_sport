<template>
    <div class="br-dashboard">
        <div class="row mb-4 align-items-start br-dashboard__split">
            <div class="col-12 col-md-6 col-lg-8">
                <section class="br-dashboard__kpis br-dashboard__kpis--split row g-2 mb-0" aria-label="Indicadores del día consultado">
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="br-dashboard-kpi br-dashboard-kpi--success h-100">
                            <div class="br-dashboard-kpi__inner">
                                <div class="br-dashboard-kpi__icon" aria-hidden="true">
                                    <i class="fa-solid fa-sack-dollar"></i>
                                </div>
                                <div class="br-dashboard-kpi__body">
                                    <p class="br-dashboard-kpi__label">Ventas en total</p>
                                    <p class="br-dashboard-kpi__value">
                                        S/&nbsp;{{ separatorNumber(fixedNumber(forms.entity.dashboard.data.sales?.all?.total ?? 0)) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="br-dashboard-kpi br-dashboard-kpi--danger h-100">
                            <div class="br-dashboard-kpi__inner">
                                <div class="br-dashboard-kpi__icon" aria-hidden="true">
                                    <i class="fa-solid fa-sack-xmark"></i>
                                </div>
                                <div class="br-dashboard-kpi__body">
                                    <p class="br-dashboard-kpi__label">Ventas anuladas</p>
                                    <p class="br-dashboard-kpi__value">
                                        S/&nbsp;{{ separatorNumber(fixedNumber(forms.entity.dashboard.data.sales?.canceled?.total ?? 0)) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="br-dashboard-kpi br-dashboard-kpi--warning h-100">
                            <div class="br-dashboard-kpi__inner">
                                <div class="br-dashboard-kpi__icon" aria-hidden="true">
                                    <i class="fa-solid fa-building-user"></i>
                                </div>
                                <div class="br-dashboard-kpi__body">
                                    <p class="br-dashboard-kpi__label">Sucursales activas</p>
                                    <p class="br-dashboard-kpi__value">
                                        {{ separatorNumber(forms.entity.dashboard.data.branches?.valid?.count ?? 0) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-12 col-md-6 col-lg-4 br-dashboard__split-date align-self-stretch">
                <section class="br-dashboard__split-pane h-100 d-flex flex-column" aria-label="Fecha consultada">
                    <div class="br-dashboard-date br-dashboard-date--split br-dashboard-date--split-fill flex-grow-1 d-flex flex-column">
                        <div class="br-dashboard-date__content flex-grow-1 d-flex flex-column">
                            <div class="br-dashboard-date__main flex-grow-1 d-flex flex-column">
                                <p v-if="!forms.entity.dashboard.data.dashboardDateEditing" class="br-dashboard-date__eyebrow">
                                    Consulta actual
                                </p>
                                <p
                                    v-if="!forms.entity.dashboard.data.dashboardDateEditing"
                                    class="br-dashboard-date__value"
                                    :title="reportDateLabel">
                                    {{ consultationDateLong }}
                                </p>
                                <div v-if="!forms.entity.dashboard.data.dashboardDateEditing" class="br-dashboard-date__actions">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary br-dashboard-date__btn"
                                        @click="startDashboardDateEdit">
                                        <span>Cambiar fecha a consultar</span>
                                    </button>
                                </div>
                                <div
                                    v-else
                                    class="br-dashboard-date__editor br-dashboard-date__editor--stack d-flex flex-wrap align-items-end justify-content-end gap-2 pt-1 w-100"
                                    role="group"
                                    aria-label="Editar fecha consultada">
                                    <div class="flex-grow-1 align-self-end" style="min-width: 10rem;">
                                        <InputDate
                                            v-model="forms.entity.dashboard.data.dateAux"
                                            hasDiv
                                            title="Fecha a consultar"
                                            isRequired
                                            :max="dashboardConsultDateMax"
                                            :titleClass="['form-label', 'colon-at-end', 'fw-semibold', 'small', 'mb-1']"
                                            :divClass="['mb-0', 'br-dashboard-date__input-wrap']"/>
                                    </div>
                                    <div class="d-flex flex-column gap-1 flex-shrink-0 align-self-end">
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-danger br-dashboard-date__btn"
                                            aria-label="Cancelar"
                                            @click="cancelDashboardDateEdit">
                                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-success br-dashboard-date__btn"
                                            aria-label="Aplicar"
                                            @click="applyDashboardDate">
                                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Gráfico -->
        <section class="row g-3 mb-4" aria-labelledby="br-dashboard-chart-title">
            <div class="col-12">
                <div class="br-dashboard-card card h-100">
                    <div class="br-dashboard-card__header card-header">
                        <div>
                            <h2 id="br-dashboard-chart-title" class="br-dashboard-card__title h5 mb-1">
                                Ventas por franja horaria
                            </h2>
                            <p class="br-dashboard-card__meta mb-0">
                                {{ reportDateLabel }}
                            </p>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div class="br-dashboard-chart-wrap">
                            <canvas
                                id="barChartId"
                                class="chartjs"
                                data-height="260"
                                aria-label="Gráfico de barras de ventas por franja horaria"
                                role="img"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tabla -->
        <!-- <section class="br-dashboard-card card" aria-labelledby="br-dashboard-sales-title">
            <div class="br-dashboard-card__header card-header">
                <div>
                    <h2 id="br-dashboard-sales-title" class="br-dashboard-card__title h5 mb-1">
                        Últimas ventas
                    </h2>
                    <p class="br-dashboard-card__meta mb-0">
                        Hasta 10 movimientos · {{ reportDateLabel }}
                    </p>
                </div>
            </div>
            <div class="card-body px-0 pt-0">
                <div class="table-responsive">
                    <table class="table table-hover br-dashboard-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Documento</th>
                                <th scope="col">Cliente</th>
                                <th scope="col" class="text-center">Emisión</th>
                                <th scope="col" class="text-end">Total</th>
                                <th scope="col" class="text-center">Estado</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="lastSales.length > 0">
                                <tr v-for="record in lastSales" :key="record.id">
                                    <td class="align-middle">
                                        <span class="fw-semibold d-block text-body">{{ record.serie_sequential }}</span>
                                        <small class="text-muted">{{ record.serie?.document_type?.name }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <span class="fw-semibold d-block text-body">{{ record.holder?.name }}</span>
                                        <small class="text-muted">{{ record.holder?.document_number }}</small>
                                    </td>
                                    <td class="align-middle text-center text-nowrap">
                                        {{ record.formatted_issue_date }}
                                    </td>
                                    <td class="align-middle text-end text-nowrap">
                                        <span class="text-muted">{{ record.currency?.sign ?? "" }}</span>
                                        <span class="fw-semibold ms-1">{{ separatorNumber(record.total) }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span
                                            :class="[
                                                'badge',
                                                'rounded-pill',
                                                'px-2',
                                                'fw-semibold',
                                                'text-capitalize',
                                                { 'bg-label-success': ['active'].includes(record.status), 'bg-label-danger': ['inactive', 'canceled'].includes(record.status) }
                                            ]"
                                            v-text="record.formatted_status"></span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <InputSlot
                                            hasDiv
                                            :isInputGroup="false"
                                            :divInputClass="['d-flex flex-wrap justify-content-center gap-2']"
                                            xl="12"
                                            lg="12">
                                            <template v-slot:input>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-primary"
                                                    @click="modalActionsEntity({record})">
                                                    <i class="fa fa-gear" aria-hidden="true"></i>
                                                    <span class="ms-1">Acciones</span>
                                                </button>
                                            </template>
                                        </InputSlot>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td class="text-center py-5" colspan="6">
                                        <WithoutData type="image"/>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section> -->
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
import * as Alerts    from "@System/Helpers/Alerts.js";
import * as Constants from "@System/Helpers/Constants.js";
import * as Requests  from "@System/Helpers/Requests.js";
import * as Utils     from "@System/Helpers/Utils.js";

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
                            dateAux: "",
                            dashboardDateEditing: false,
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
                            id: "menu-parent-dashboard"
                        }
                    }
                }
            }
        };
    },
    methods: {
        startDashboardDateEdit() {

            const max = Utils.getCurrentDate();
            let d = this.forms.entity.dashboard.data.date || max;
            if(d > max) {

                d = max;

            }
            this.forms.entity.dashboard.data.dateAux = d;
            this.forms.entity.dashboard.data.dashboardDateEditing = true;

        },
        cancelDashboardDateEdit() {

            this.forms.entity.dashboard.data.dateAux = "";
            this.forms.entity.dashboard.data.dashboardDateEditing = false;

        },
        applyDashboardDate() {

            this.forms.entity.dashboard.data.date = this.forms.entity.dashboard.data.dateAux;
            this.forms.entity.dashboard.data.dateAux = "";
            this.forms.entity.dashboard.data.dashboardDateEditing = false;

            this.initData({loading: true});

        },
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

            // Config (etiquetas alineadas al flujo comercial típico)
            const intervals = [
                { label: "12:00 a. m. – 2:59 a. m.", start: 0, end: 3 },
                { label: "3:00 a. m. – 5:59 a. m.", start: 3, end: 6 },
                { label: "6:00 a. m. – 8:59 a. m.", start: 6, end: 9 },
                { label: "9:00 a. m. – 11:59 a. m.", start: 9, end: 12 },
                { label: "12:00 p. m. – 2:59 p. m.", start: 12, end: 15 },
                { label: "3:00 p. m. – 5:59 p. m.", start: 15, end: 18 },
                { label: "6:00 p. m. – 8:59 p. m.", start: 18, end: 21 },
                { label: "9:00 p. m. – 11:59 p. m.", start: 21, end: 24 }
            ];

            const totalsByInterval = intervals.map(interval => ({ label: interval.label, total: 0 }));

            const sales = this.forms.entity.dashboard.data.sales?.all?.records ?? [];

            sales.forEach(sale => {

                const saleHour = new Date(sale.created_at).getHours();
                const interval = intervals.find(i => saleHour >= i.start && saleHour < i.end);

                if(interval) {

                    const index = intervals.indexOf(interval);
                    totalsByInterval[index].total += parseFloat(sale.total);

                }

            });

            const barChart = document.getElementById("barChartId");
            const labels   = totalsByInterval.map(i => i.label);
            const data     = totalsByInterval.map(i => i.total);
            const yMax     = roundUpToNearest(Math.max(500, ...data));

            const primary = this.config.colors.charts.default.primaryColor;
            const barFill = this.hexToRgba(primary, 0.88);

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
                                backgroundColor: barFill,
                                borderColor: primary,
                                borderWidth: 1,
                                maxBarThickness: 22,
                                borderRadius: {
                                    topRight: 10,
                                    topLeft: 10
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
                                    color: this.config.colors.charts.default.labelColor,
                                    maxRotation: 45,
                                    minRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 8
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
        hexToRgba(hex, alpha) {

            const h = hex.replace("#", "");
            const n = h.length === 3
                ? h.split("").map(c => c + c).join("")
                : h;
            const num = parseInt(n, 16);
            const r = (num >> 16) & 255;
            const g = (num >> 8) & 255;
            const b = num & 255;
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;

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

        /** YYYY-MM-DD de hoy: tope para el input y bloqueo de fechas futuras en el calendario nativo */
        dashboardConsultDateMax: function() {

            return Utils.getCurrentDate();

        },
        breadcrumbTitles: function() {

            return [this.config.entity.page];

        },
        lastSales: function() {

            return (this.forms.entity.dashboard.data.sales?.all?.records ?? []).slice(0, 10);

        },
        reportDateLabel: function() {

            const d = this.forms.entity.dashboard.data.date;
            if(!d) {
                return "";
            }
            return "Fecha: " + this.legibleFormatDate({dateString: d, type: "date"});

        },
        consultationDateLong: function() {

            const d = this.forms.entity.dashboard.data.date;
            if(!d) {
                return "Selecciona una fecha";
            }
            try {
                const raw = String(d).trim();
                const parts = raw.includes("T") ? raw.split("T")[0].split("-") : raw.split("-");
                if(parts.length >= 3) {
                    const y = parseInt(parts[0], 10);
                    const m = parseInt(parts[1], 10) - 1;
                    const day = parseInt(parts[2], 10);
                    const date = new Date(y, m, day);
                    if(!isNaN(date.getTime())) {
                        const s = new Intl.DateTimeFormat("es-PE", {
                            weekday: "long",
                            day: "numeric",
                            month: "long",
                            year: "numeric"
                        }).format(date);
                        return s.charAt(0).toUpperCase() + s.slice(1);
                    }
                }
            } catch (e) {
                //
            }
            return this.legibleFormatDate({dateString: d, type: "date"});

        }
    }
};
</script>
