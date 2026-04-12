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
                                        class="br-btn br-btn-sm br-btn-secondary"
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
                                            class="br-btn br-btn-xs br-btn-danger"
                                            aria-label="Cancelar"
                                            @click="cancelDashboardDateEdit">
                                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="br-btn br-btn-xs br-btn-success"
                                            aria-label="Aplicar"
                                            :disabled="!canApplyDashboardDate"
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

        <!-- Gráfico: título aparte; área del chart sin card (fondo transparente) -->
        <section class="row g-3 mb-4 pt-2" aria-labelledby="br-dashboard-chart-title">
            <div class="col-12">
                <div class="text-center mb-0 br-dashboard-chart-section__title-wrap">
                    <span
                        id="br-dashboard-chart-title"
                        class="form-label fw-bold fs-6 mb-0 d-inline-block br-dashboard-chart-section__title"
                        role="heading"
                        aria-level="2">
                        Ventas por hora
                    </span>
                    <p class="br-dashboard-chart-section__subtitle small text-muted mb-0">
                        {{ dashboardChartHoursRangeCaption }}
                    </p>
                </div>
                <div class="br-dashboard-chart-panel">
                    <div class="br-dashboard-chart-wrap br-dashboard-chart-wrap--hourly">
                        <canvas
                            id="dashboardSalesHourlyChart"
                            class="chartjs br-dashboard-chart-canvas"
                            data-height="300"
                            aria-label="Gráfico de barras: ventas por hora del día consultado"
                            role="img"></canvas>
                        <div
                            v-if="dashboardChartNoSales"
                            class="br-dashboard-chart-empty-overlay"
                            role="status"
                            aria-live="polite">
                            <span class="br-dashboard-chart-empty-overlay__text">SIN VENTAS</span>
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

let dashboardSalesChartInstance = null;

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

            if(!this.canApplyDashboardDate) {

                return;

            }
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
        /** Etiqueta eje X: hora en 12 h + a. m. / p. m. (índice 0–23) */
        dashboardChartFormatHourLabelAmpm(h) {

            if(h === 0) {

                return "12:00 a. m.";

            }
            if(h === 12) {

                return "12:00 p. m.";

            }
            if(h < 12) {

                return `${h}:00 a. m.`;

            }
            return `${h - 12}:00 p. m.`;

        },
        /** Fin de franja horaria para tooltip (…:59) */
        dashboardChartFormatHourEndAmpm(h) {

            if(h === 0) {

                return "12:59 a. m.";

            }
            if(h === 12) {

                return "12:59 p. m.";

            }
            if(h < 12) {

                return `${h}:59 a. m.`;

            }
            return `${h - 12}:59 p. m.`;

        },
        /** Eje X corto (más ancho por barra, sin diagonal): "9 a. m.", "12 p. m." */
        dashboardChartFormatHourCompactAmpm(h) {

            if(h === 0) {

                return "12 a. m.";

            }
            if(h === 12) {

                return "12 p. m.";

            }
            if(h < 12) {

                return `${h} a. m.`;

            }
            return `${h - 12} p. m.`;

        },
        initChart() {

            /**
             * Eje Y según el pico real: evita forzar 0–100 cuando el máximo es pocos soles (1, 8, …).
             * Margen ~12 % y redondeo a escala legible (1–2–5–10 por orden de magnitud).
             */
            const niceCeilAxisMax = (peak) => {

                const headroom = 1.12;
                if(peak <= 0) {

                    return 10;

                }
                const target = peak * headroom;
                if(target < 1) {

                    return Math.max(1, Math.ceil(target * 100) / 100);

                }
                const exp = Math.floor(Math.log10(target));
                const pow10 = Math.pow(10, exp);
                const n = target / pow10;
                let nice;
                if(n <= 1) {

                    nice = 1;

                }else if(n <= 2) {

                    nice = 2;

                }else if(n <= 5) {

                    nice = 5;

                }else {

                    nice = 10;

                }
                return nice * pow10;

            };

            const chartList = document.querySelectorAll(".chartjs");

            chartList.forEach(function(chartListItem) {

                chartListItem.height = chartListItem.dataset.height;

            });

            const totalsByHour = Array.from({length: 24}, () => 0);

            const sales = this.forms.entity.dashboard.data.sales?.all?.records ?? [];

            sales.forEach((sale) => {

                const saleHour = new Date(sale.created_at).getHours();
                if(saleHour >= 0 && saleHour < 24) {

                    totalsByHour[saleHour] += parseFloat(sale.total);

                }

            });

            const hasAnySale = totalsByHour.some((t) => t > 0);
            let firstHour = 0;
            let lastHour = 23;
            if(hasAnySale) {

                firstHour = totalsByHour.findIndex((t) => t > 0);
                for(let i = 23; i >= 0; i--) {

                    if(totalsByHour[i] > 0) {

                        lastHour = i;
                        break;

                    }

                }

            }

            const sliceHours = [];
            for(let h = firstHour; h <= lastHour; h++) {

                sliceHours.push(h);

            }

            const nBars = sliceHours.length;
            const labels = sliceHours.map((h) => this.dashboardChartFormatHourCompactAmpm(h));
            const data = sliceHours.map((h) => totalsByHour[h]);
            const maxVal = Math.max(0, ...data);
            const yMax = niceCeilAxisMax(maxVal);

            /** Eje Y: 7 intervalos horizontales → 8 marcas (0 … máx.), enteros y paso fijo */
            const yIntervals = 7;
            let yStep = Math.max(1, Math.ceil(yMax / yIntervals));
            let yAxisMax = yStep * yIntervals;
            while(yAxisMax < yMax) {

                yStep += 1;
                yAxisMax = yStep * yIntervals;

            }

            const canvas = document.getElementById("dashboardSalesHourlyChart");
            const primary = this.config.colors.charts.default.primaryColor;
            const barFill = this.hexToRgba(primary, 0.88);
            const barHover = this.hexToRgba(primary, 1);
            const vm = this;
            /** Líneas de grilla un poco más visibles que --border del tema (#e2e8f0), sin pasarse */
            const chartGridColor = "#d1d9e3";

            const maxBarThickness = nBars <= 6 ? 52 : nBars <= 12 ? 42 : nBars <= 18 ? 34 : 26;
            const categoryPercentage = nBars <= 10 ? 0.94 : nBars <= 16 ? 0.9 : 0.86;
            const barPercentage = nBars <= 10 ? 0.98 : 0.95;
            const labelFontSize = nBars > 18 ? 7.5 : nBars > 14 ? 8 : 9.5;
            const tickRotation = nBars > 16 ? 35 : 0;

            if(canvas) {

                if(dashboardSalesChartInstance) {

                    dashboardSalesChartInstance.destroy();

                }

                dashboardSalesChartInstance = new Chart(canvas, {
                    type: "bar",
                    data: {
                        labels,
                        datasets: [
                            {
                                label: "Total por hora (S/)",
                                data,
                                backgroundColor: barFill,
                                borderColor: primary,
                                borderWidth: 1,
                                borderRadius: {
                                    topLeft: 8,
                                    topRight: 8,
                                    bottomLeft: 0,
                                    bottomRight: 0
                                },
                                borderSkipped: false,
                                maxBarThickness: maxBarThickness,
                                hoverBackgroundColor: barHover,
                                hoverBorderColor: primary
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                bottom: tickRotation > 0 ? 22 : 10,
                                left: 2,
                                right: 2
                            }
                        },
                        animation: {
                            duration: 550
                        },
                        interaction: {
                            mode: "index",
                            intersect: false
                        },
                        datasets: {
                            bar: {
                                categoryPercentage: categoryPercentage,
                                barPercentage: barPercentage
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: "bottom",
                                align: "center",
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: "rect",
                                    padding: 18,
                                    boxWidth: 12,
                                    boxHeight: 8,
                                    color: this.config.colors.charts.default.titleColor,
                                    font: {
                                        size: 12,
                                        weight: "600"
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: this.config.colors.charts.default.backgroundColor,
                                bodyColor: this.config.colors.charts.default.bodyColor,
                                borderColor: this.config.colors.charts.default.borderColor,
                                borderWidth: 1,
                                titleColor: this.config.colors.charts.default.titleColor,
                                displayColors: true,
                                padding: 12,
                                callbacks: {
                                    title(items) {

                                        if(!items.length) {

                                            return "";

                                        }
                                        const hour = sliceHours[items[0].dataIndex];
                                        return `${vm.dashboardChartFormatHourLabelAmpm(hour)} – ${vm.dashboardChartFormatHourEndAmpm(hour)}`;

                                    },
                                    label(ctx) {

                                        const v = ctx.parsed.y;
                                        return ` Ventas: S/ ${vm.separatorNumber(vm.fixedNumber(v))}`;

                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: false
                                },
                                grid: {
                                    color: chartGridColor,
                                    drawBorder: false,
                                    borderColor: chartGridColor
                                },
                                ticks: {
                                    color: "#000000",
                                    autoSkip: false,
                                    maxRotation: tickRotation,
                                    minRotation: tickRotation,
                                    font: {
                                        size: labelFontSize
                                    }
                                }
                            },
                            y: {
                                min: 0,
                                max: yAxisMax,
                                title: {
                                    display: true,
                                    text: "Soles (S/)",
                                    color: this.config.colors.charts.default.labelColor,
                                    font: {size: 11, weight: "600"}
                                },
                                grid: {
                                    color: chartGridColor,
                                    drawBorder: false,
                                    borderColor: chartGridColor
                                },
                                ticks: {
                                    color: this.config.colors.charts.default.labelColor,
                                    stepSize: yStep,
                                    precision: 0,
                                    callback(value) {

                                        const n = Math.round(Number(value));
                                        return vm.separatorNumber(String(n));

                                    }
                                }
                            }
                        }
                    }
                });

            }

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
        /** Sin registros de venta en el día consultado (tras cargar datos) */
        dashboardChartNoSales: function() {

            const sales = this.forms.entity.dashboard.data.sales;
            if(sales === null || sales === undefined) {

                return false;

            }
            const records = sales?.all?.records;
            return Array.isArray(records) && records.length === 0;

        },
        /** Rango de horas mostrado en el gráfico (debajo del título); sin texto en el eje X */
        dashboardChartHoursRangeCaption: function() {

            const totalsByHour = Array.from({length: 24}, () => 0);
            const sales = this.forms.entity.dashboard.data.sales?.all?.records ?? [];
            sales.forEach((sale) => {

                const saleHour = new Date(sale.created_at).getHours();
                if(saleHour >= 0 && saleHour < 24) {

                    totalsByHour[saleHour] += parseFloat(sale.total);

                }

            });
            const hasAnySale = totalsByHour.some((t) => t > 0);
            if(!hasAnySale) {

                return "Todas las horas del día";

            }
            const firstHour = totalsByHour.findIndex((t) => t > 0);
            let lastHour = 23;
            for(let i = 23; i >= 0; i--) {

                if(totalsByHour[i] > 0) {

                    lastHour = i;
                    break;

                }

            }
            if(lastHour - firstHour + 1 >= 24) {

                return "Todas las horas del día";

            }
            return `${this.dashboardChartFormatHourLabelAmpm(firstHour)} – ${this.dashboardChartFormatHourEndAmpm(lastHour)}`;

        },
        /** Sin fecha válida en el input: Aplicar visible pero deshabilitado */
        canApplyDashboardDate: function() {

            if(!this.forms.entity.dashboard.data.dashboardDateEditing) {

                return false;

            }
            const raw = this.forms.entity.dashboard.data.dateAux;
            if(raw === null || raw === undefined) {

                return false;

            }
            const s = String(raw).trim().split("T")[0];
            if(!s) {

                return false;

            }
            if(!/^\d{4}-\d{2}-\d{2}$/.test(s)) {

                return false;

            }
            const max = this.dashboardConsultDateMax;
            if(s > max) {

                return false;

            }
            const parts = s.split("-");
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const day = parseInt(parts[2], 10);
            const dt = new Date(y, m, day);
            return !isNaN(dt.getTime());

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
