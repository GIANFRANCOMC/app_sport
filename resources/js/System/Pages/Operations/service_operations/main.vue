<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <section class="br-service-page">
        <div class="br-service-toolbar">
            <div class="br-service-toolbar__field">
                <label class="form-label">Sucursal</label>
                <v-select
                    v-model="selectedBranch"
                    :options="branchOptions"
                    :clearable="false"
                    :searchable="true"
                    @option:selected="handleBranchChange"/>
            </div>
            <div v-if="!isRestaurant" class="br-service-toolbar__field">
                <label class="form-label">Estado</label>
                <v-select
                    v-model="selectedStatus"
                    :options="statusOptions"
                    :clearable="false"
                    :searchable="false"
                    @option:selected="getSessions()"/>
            </div>
            <div class="br-service-toolbar__actions">
                <button
                    v-if="isRestaurant"
                    type="button"
                    class="br-btn br-btn-action-create"
                    @click="openStationModal">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    <span>Agregar mesa</span>
                </button>
                <button
                    v-else
                    type="button"
                    class="br-btn br-btn-action-create"
                    @click="openSessionModal()">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    <span>Nueva atención</span>
                </button>
            </div>
        </div>

        <Loader v-if="loading"/>

        <template v-else-if="isRestaurant">
            <div class="br-service-legend" aria-label="Leyenda de disponibilidad">
                <span><i class="is-available"></i> Disponible</span>
                <span><i class="is-pending"></i> Pendiente</span>
                <span><i class="is-progress"></i> En atención</span>
            </div>

            <div class="br-service-stations">
                <article
                    v-for="station in stations"
                    :key="station.id"
                    class="br-service-station"
                    :class="stationClass(station)">
                    <header>
                        <span class="br-service-station__icon" aria-hidden="true">
                            <i class="fa-solid fa-utensils"></i>
                        </span>
                        <span>
                            <strong>{{ station.name }}</strong>
                            <small>{{ station.code }} · {{ station.capacity }} personas</small>
                        </span>
                    </header>
                    <div v-if="station.active_session" class="br-service-station__session">
                        <span>{{ station.active_session.customer?.name || 'Cliente general' }}</span>
                        <strong>{{ elapsedLabel(station.active_session) }}</strong>
                        <small>{{ station.active_session.items?.length || 0 }} detalle(s)</small>
                    </div>
                    <div v-else class="br-service-station__session is-empty">
                        <span>Mesa disponible</span>
                        <small>Lista para una nueva atención</small>
                    </div>
                    <button
                        type="button"
                        class="br-btn br-btn-sm"
                        :class="station.active_session ? 'br-btn-secondary' : 'br-btn-primary'"
                        @click="station.active_session ? selectSession(station.active_session) : openSessionModal(station)">
                        {{ station.active_session ? 'Ver atención' : 'Abrir mesa' }}
                    </button>
                </article>
            </div>
            <WithoutData
                v-if="!stations.length"
                text="Aún no hay mesas registradas"
                description="Agrega la primera mesa o estación de atención para esta sucursal."/>
        </template>

        <template v-else>
            <div class="table-responsive">
                <table class="table br-entity-table mb-0">
                    <thead>
                        <tr>
                            <th>Atención</th>
                            <th>Cliente</th>
                            <th>Responsable</th>
                            <th>Inicio</th>
                            <th>Duración</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="session in sessions.data" :key="session.id">
                            <td>
                                <strong>{{ session.reference }}</strong>
                                <small class="d-block text-muted">{{ session.items?.[0]?.name || 'Sin servicio asociado' }}</small>
                            </td>
                            <td>{{ session.customer?.name || 'Cliente general' }}</td>
                            <td>{{ session.assigned_user?.name || 'Sin asignar' }}</td>
                            <td>{{ formatDateTime(session.started_at || session.created_at) }}</td>
                            <td>{{ elapsedLabel(session) }}</td>
                            <td class="text-center">
                                <span class="br-status-label" :class="statusClass(session.status)">
                                    {{ statusLabel(session.status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="br-icon-action br-icon-action-edit"
                                    data-bs-toggle="tooltip"
                                    title="Gestionar atención"
                                    aria-label="Gestionar atención"
                                    @click="selectSession(session)">
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <WithoutData
                v-if="!sessions.data.length"
                text="No hay atenciones para mostrar"
                description="Inicia una atención para medir tiempos y asignar responsables."/>
            <Paginator :links="sessions.links" @clickPage="getSessions"/>
        </template>

        <aside v-if="selectedSession" class="br-service-detail" aria-label="Detalle de la atención">
            <header class="br-service-detail__header">
                <div>
                    <small>{{ selectedSession.reference }}</small>
                    <h2>{{ selectedSession.station?.name || selectedSession.items?.[0]?.name || 'Atención de servicio' }}</h2>
                    <p>
                        {{ selectedSession.customer?.name || 'Cliente general' }}
                        <span v-if="selectedSession.assigned_user"> · {{ selectedSession.assigned_user.name }}</span>
                    </p>
                </div>
                <button type="button" class="br-modal-close" aria-label="Cerrar detalle" @click="selectedSession = null">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </header>

            <div class="br-service-detail__metrics">
                <span><small>Estado</small><strong>{{ statusLabel(selectedSession.status) }}</strong></span>
                <span><small>Tiempo</small><strong>{{ elapsedLabel(selectedSession) }}</strong></span>
                <span><small>Detalles</small><strong>{{ selectedSession.items?.length || 0 }}</strong></span>
            </div>

            <div class="br-service-add-item">
                <v-select
                    v-model="detailForm.item"
                    :options="itemOptions"
                    :clearable="false"
                    :searchable="true"
                    placeholder="Producto o servicio"/>
                <v-select
                    v-model="detailForm.user"
                    :options="userOptions"
                    :clearable="true"
                    :searchable="true"
                    placeholder="Responsable"/>
                <button
                    type="button"
                    class="br-btn br-btn-primary"
                    :disabled="saving || !detailForm.item"
                    @click="addSessionItem">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    <span>Agregar</span>
                </button>
            </div>

            <div class="br-service-detail__items">
                <article v-for="item in selectedSession.items" :key="item.id">
                    <div>
                        <strong>{{ item.name }}</strong>
                        <small>{{ item.assigned_user?.name || 'Sin responsable' }} · {{ elapsedLabel(item) }}</small>
                    </div>
                    <span class="br-status-label" :class="statusClass(item.status)">{{ statusLabel(item.status) }}</span>
                    <button
                        v-if="item.status === 'pending'"
                        type="button"
                        class="br-btn br-btn-sm br-btn-secondary"
                        @click="changeItemStatus(item, 'start')">
                        Iniciar
                    </button>
                    <button
                        v-else-if="item.status === 'in_progress'"
                        type="button"
                        class="br-btn br-btn-sm br-btn-success"
                        @click="changeItemStatus(item, 'complete')">
                        Finalizar
                    </button>
                </article>
            </div>

            <footer class="br-service-detail__footer">
                <button
                    v-if="selectedSession.status === 'pending'"
                    type="button"
                    class="br-btn br-btn-secondary"
                    @click="changeSessionStatus('start')">
                    <i class="fa-solid fa-play" aria-hidden="true"></i>
                    <span>Iniciar atención</span>
                </button>
                <button
                    v-if="selectedSession.items?.length"
                    type="button"
                    class="br-btn br-btn-success"
                    @click="goToPos">
                    <i class="fa-solid fa-cash-register" aria-hidden="true"></i>
                    <span>Cobrar en POS</span>
                </button>
                <button
                    v-if="selectedSession.status === 'in_progress'"
                    type="button"
                    class="br-btn br-btn-primary"
                    @click="changeSessionStatus('complete')">
                    <span>Finalizar sin venta</span>
                </button>
            </footer>
        </aside>
    </section>

    <div id="brServiceStationModal" class="modal fade br-entity-modal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header">
                    <div>
                        <p class="br-entity-modal__eyebrow mb-1">Operación</p>
                        <h2 class="modal-title br-entity-modal__title">Agregar mesa</h2>
                    </div>
                    <button type="button" class="br-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body">
                    <div class="row g-3">
                        <InputText v-model="stationForm.name" title="Nombre" :isRequired="true" :xl="8" :lg="8" :md="12" :sm="12"/>
                        <InputText v-model="stationForm.code" title="Código" :isRequired="true" :xl="4" :lg="4" :md="12" :sm="12"/>
                        <div class="form-group col-md-8">
                            <label class="form-label">Tipo</label>
                            <v-select v-model="stationForm.type" :options="stationTypeOptions" :clearable="false"/>
                        </div>
                        <InputNumber v-model="stationForm.capacity" title="Capacidad" :minValue="1" :decimals="0" :hasDiv="true" :xl="4" :lg="4" :md="12" :sm="12"/>
                    </div>
                </div>
                <div class="modal-footer br-entity-modal__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-action-create" :disabled="saving" @click="saveStation">Agregar mesa</button>
                </div>
            </div>
        </div>
    </div>

    <div id="brServiceSessionModal" class="modal fade br-entity-modal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header">
                    <div>
                        <p class="br-entity-modal__eyebrow mb-1">{{ isRestaurant ? 'Restaurante POS' : 'Servicios' }}</p>
                        <h2 class="modal-title br-entity-modal__title">{{ isRestaurant ? 'Abrir mesa' : 'Nueva atención' }}</h2>
                    </div>
                    <button type="button" class="br-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body">
                    <div class="row g-3">
                        <div v-if="sessionForm.station" class="col-12">
                            <div class="br-service-selected-station">
                                <i class="fa-solid fa-utensils" aria-hidden="true"></i>
                                <span><small>Mesa</small><strong>{{ sessionForm.station.name }}</strong></span>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Cliente</label>
                            <v-select v-model="sessionForm.customer" :options="customerOptions" :clearable="true" :searchable="true" placeholder="Cliente general"/>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Responsable</label>
                            <v-select v-model="sessionForm.user" :options="userOptions" :clearable="true" :searchable="true" placeholder="Selecciona un responsable"/>
                        </div>
                        <div class="form-group col-md-8">
                            <label class="form-label">{{ isRestaurant ? 'Primer producto o servicio' : 'Servicio' }}</label>
                            <v-select v-model="sessionForm.item" :options="itemOptions" :clearable="true" :searchable="true" placeholder="Puede agregarse después"/>
                        </div>
                        <InputNumber v-model="sessionForm.quantity" title="Cantidad" :minValue="0.0001" :decimals="4" :hasDiv="true" :xl="4" :lg="4" :md="12" :sm="12"/>
                        <div class="col-12">
                            <label class="br-entity-switch" for="startServiceImmediately">
                                <input id="startServiceImmediately" v-model="sessionForm.startImmediately" class="form-check-input" type="checkbox" role="switch">
                                <span>
                                    <strong>Iniciar ahora</strong>
                                    <small>Comienza a medir el tiempo desde que confirmes la atención.</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer br-entity-modal__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-action-create" :disabled="saving" @click="saveSession">
                        {{ isRestaurant ? 'Abrir mesa' : 'Crear atención' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts from "@System/Helpers/Alerts.js";
import * as Requests from "@System/Helpers/Requests.js";
import * as Utils from "@System/Helpers/Utils.js";

export default {
    data() {
        const isRestaurant = window.location.pathname.includes("/restaurant");

        return {
            isRestaurant,
            config: Requests.config({entity: "service_operations"}),
            options: {branches: [], users: [], customers: [], items: [], stationTypes: [], sessionStatuses: []},
            selectedBranch: null,
            selectedStatus: null,
            stations: [],
            sessions: {data: [], links: []},
            selectedSession: null,
            loading: false,
            saving: false,
            now: Date.now(),
            timer: null,
            stationForm: {name: "", code: "", type: null, capacity: 2},
            sessionForm: {station: null, customer: null, user: null, item: null, quantity: 1, startImmediately: true},
            detailForm: {item: null, user: null}
        };
    },
    computed: {
        breadcrumbTitles() {
            return [
                {title: "Operación"},
                {title: this.isRestaurant ? "Restaurante POS" : "Servicios en curso", active: true}
            ];
        },
        branchOptions() {
            return this.options.branches.map(record => ({...record, label: record.name}));
        },
        userOptions() {
            return this.options.users.map(record => ({...record, label: record.name}));
        },
        customerOptions() {
            return this.options.customers.map(record => ({...record, label: record.name}));
        },
        itemOptions() {
            const records = this.isRestaurant
                ? this.options.items
                : this.options.items.filter(record => record.type === "service");

            return records.map(record => ({
                ...record,
                label: `${record.name} · S/ ${this.money(record.price)}`
            }));
        },
        stationTypeOptions() {
            return this.options.stationTypes.map(record => ({...record, label: record.label}));
        },
        statusOptions() {
            return [
                {code: "open", label: "Pendientes y en curso"},
                ...this.options.sessionStatuses
            ];
        }
    },
    mounted() {
        Utils.navbarItem("menu-parent-operations", {addClass: "open"});
        Utils.navbarItem(this.isRestaurant ? "menu-restaurant-pos" : "menu-service-sessions", {addClass: "active"});
        this.timer = window.setInterval(() => { this.now = Date.now(); }, 30000);
        this.initParams();
    },
    beforeUnmount() {
        window.clearInterval(this.timer);
    },
    methods: {
        async initParams() {
            this.loading = true;
            const result = await Requests.get({
                route: this.config.routes.initParams,
                data: {page: this.isRestaurant ? "restaurant" : "services"}
            });
            this.loading = false;

            if(!Requests.valid({result})) {
                this.notify(result, "No fue posible cargar la operación.");
                return;
            }

            this.options = result.data.config;
            this.selectedBranch = this.branchOptions[0] || null;
            this.selectedStatus = this.statusOptions[0] || null;
            this.stationForm.type = this.stationTypeOptions.find(type => type.code === "table") || this.stationTypeOptions[0] || null;
            await this.refresh();
        },
        async refresh() {
            if(!this.selectedBranch) return;
            await (this.isRestaurant ? this.getStations() : this.getSessions());
        },
        async handleBranchChange() {
            this.selectedSession = null;
            await this.refresh();
        },
        async getStations() {
            this.loading = true;
            const result = await Requests.get({route: this.config.routes.stations, data: {branch_id: this.selectedBranch.id}});
            this.loading = false;
            if(Requests.valid({result})) this.stations = result.data.data;
        },
        async getSessions(url = null) {
            this.loading = true;
            const status = this.selectedStatus?.code;
            const result = await Requests.get({
                route: url || this.config.routes.sessions,
                data: {
                    branch_id: this.selectedBranch?.id,
                    status,
                    session_type: "catalog_service"
                }
            });
            this.loading = false;

            if(Requests.valid({result})) {
                this.sessions = result.data.data;
            }
        },
        openStationModal() {
            this.stationForm = {
                name: "",
                code: `M${String(this.stations.length + 1).padStart(2, "0")}`,
                type: this.stationTypeOptions.find(type => type.code === "table") || this.stationTypeOptions[0] || null,
                capacity: 2
            };
            Alerts.modals({type: "show", id: "brServiceStationModal"});
        },
        openSessionModal(station = null) {
            this.sessionForm = {station, customer: null, user: null, item: null, quantity: 1, startImmediately: true};
            Alerts.modals({type: "show", id: "brServiceSessionModal"});
        },
        async saveStation() {
            if(!this.stationForm.name?.trim() || !this.stationForm.code?.trim()) {
                Alerts.toastrs({type: "warning", subtitle: "Completa el nombre y código de la mesa."});
                return;
            }

            await this.perform({
                message: "Registrando mesa",
                request: () => Requests.post({
                    route: this.config.routes.stations,
                    data: {
                        branch_id: this.selectedBranch.id,
                        name: this.stationForm.name,
                        code: this.stationForm.code,
                        station_type: this.stationForm.type?.code,
                        capacity: this.stationForm.capacity,
                        status: "active"
                    }
                }),
                modalId: "brServiceStationModal"
            });
        },
        async saveSession() {
            await this.perform({
                message: this.isRestaurant ? "Abriendo mesa" : "Creando atención",
                request: () => Requests.post({
                    route: this.config.routes.sessions,
                    data: {
                        branch_id: this.selectedBranch.id,
                        service_station_id: this.sessionForm.station?.id,
                        customer_id: this.sessionForm.customer?.id,
                        assigned_user_id: this.sessionForm.user?.id,
                        item_id: this.sessionForm.item?.id,
                        quantity: this.sessionForm.quantity,
                        session_type: this.isRestaurant ? "restaurant" : "catalog_service",
                        start_immediately: this.sessionForm.startImmediately
                    }
                }),
                modalId: "brServiceSessionModal",
                selectResult: true
            });
        },
        async selectSession(session) {
            const result = await Requests.get({route: `${this.config.routes.sessions}/${session.id}`});
            if(Requests.valid({result})) {
                this.selectedSession = result.data.data;
                this.detailForm = {item: null, user: this.userOptions.find(user => user.id === this.selectedSession.assigned_user_id) || null};
            }
        },
        async addSessionItem() {
            const sessionId = this.selectedSession.id;
            await this.perform({
                message: "Agregando detalle",
                request: () => Requests.post({
                    route: `${this.config.routes.sessions}/${sessionId}/items`,
                    data: {
                        item_id: this.detailForm.item?.id,
                        assigned_user_id: this.detailForm.user?.id,
                        quantity: 1,
                        start_immediately: this.selectedSession.status === "in_progress"
                    }
                }),
                sessionId
            });
            this.detailForm.item = null;
        },
        async changeSessionStatus(action) {
            const sessionId = this.selectedSession.id;
            await this.perform({
                message: action === "start" ? "Iniciando atención" : "Finalizando atención",
                request: () => Requests.patch({route: `${this.config.routes.sessions}/${sessionId}/${action}`}),
                sessionId
            });
        },
        async changeItemStatus(item, action) {
            await this.perform({
                message: action === "start" ? "Iniciando servicio" : "Finalizando servicio",
                request: () => Requests.patch({route: `${this.config.routes.consult}/items/${item.id}/${action}`}),
                sessionId: this.selectedSession.id
            });
        },
        async perform({message, request, modalId = null, sessionId = null, selectResult = false}) {
            this.saving = true;
            Alerts.loading?.({message});
            const result = await request();
            this.saving = false;
            Alerts.close?.();
            window.Swal?.close?.();

            if(!Requests.valid({result})) {
                this.notify(result, "No fue posible completar la acción.");
                return;
            }

            if(modalId) Alerts.modals({type: "hide", id: modalId});
            this.notify(result, "Operación completada correctamente.", "success");
            await this.refresh();

            const targetId = sessionId || (selectResult ? result.data.data?.id : null);
            if(targetId) await this.selectSession({id: targetId});
        },
        notify(result, fallback, type = "error") {
            Alerts.toastrs({type, subtitle: result?.data?.msg || fallback});
        },
        goToPos() {
            window.location.href = `/sales/pos?service_session_id=${this.selectedSession.id}`;
        },
        stationClass(station) {
            if(!station.active_session) return "is-available";
            return station.active_session.status === "in_progress" ? "is-progress" : "is-pending";
        },
        statusLabel(status) {
            return {pending: "Pendiente", in_progress: "En curso", completed: "Finalizada", canceled: "Cancelada"}[status] || status;
        },
        statusClass(status) {
            return {
                pending: "br-status-pending",
                in_progress: "br-status-active",
                completed: "br-status-completed",
                canceled: "br-status-inactive"
            }[status];
        },
        elapsedLabel(record) {
            if(record.duration_minutes && record.status === "completed") return this.minutesLabel(record.duration_minutes);
            const start = record.started_at || record.created_at;
            if(!start) return "Sin iniciar";
            return this.minutesLabel(Math.max(0, Math.floor((this.now - new Date(start).getTime()) / 60000)));
        },
        minutesLabel(minutes) {
            const value = Number(minutes || 0);
            const hours = Math.floor(value / 60);
            const remaining = value % 60;
            return hours ? `${hours} h ${remaining} min` : `${remaining} min`;
        },
        formatDateTime(value) {
            return value ? new Intl.DateTimeFormat("es-PE", {dateStyle: "short", timeStyle: "short"}).format(new Date(value)) : "-";
        },
        money(value) {
            return Number(value || 0).toLocaleString("es-PE", {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }
};
</script>
