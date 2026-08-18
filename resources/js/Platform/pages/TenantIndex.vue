<template>
    <section>
        <div class="platform-heading">
            <div>
                <span class="platform-eyebrow">Administración central</span>
                <h1 class="platform-title">Clientes tenant</h1>
                <p class="platform-subtitle">Controla el acceso, estado y configuración de cada organización.</p>
            </div>
            <button class="btn platform-btn-primary text-white" type="button" @click="showCreate = !showCreate">
                <i :class="showCreate ? 'fa-solid fa-xmark' : 'fa-solid fa-plus'"></i>
                {{ showCreate ? "Cerrar" : "Nuevo cliente" }}
            </button>
        </div>

        <div class="platform-kpi-grid">
            <button v-for="card in countCards" :key="card.code" :class="['platform-card', 'platform-kpi', { 'is-selected': filters.status === card.filter }]" type="button" @click="selectStatus(card.filter)">
                <span :class="['platform-kpi__icon', `is-${card.code}`]"><i :class="card.icon"></i></span>
                <span><strong class="platform-kpi__value">{{ card.value }}</strong><small class="platform-kpi__label">{{ card.label }}</small></span>
            </button>
        </div>

        <form v-if="showCreate" class="platform-card platform-create" @submit.prevent="createTenant">
            <div class="platform-card__head">
                <div><strong>Crear cliente tenant</strong><p class="platform-subtitle">Aprovisiona base de datos, empresa y usuario administrador.</p></div>
                <span class="platform-chip"><i class="fa-solid fa-wand-magic-sparkles"></i> Configuración automática</span>
            </div>
            <div class="platform-card__body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Subdominio</label><div class="input-group"><input v-model.trim="form.slug" class="form-control" required maxlength="60"><span class="input-group-text">.gympe.test</span></div></div>
                    <div class="col-md-4"><label class="form-label">Nombre comercial</label><input v-model.trim="form.commercial_name" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Razón social</label><input v-model.trim="form.legal_name" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Documento</label><input v-model.trim="form.document_number" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Administrador</label><input v-model.trim="form.admin_name" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Correo</label><input v-model.trim="form.admin_email" class="form-control" type="email" required></div>
                    <div class="col-md-3"><label class="form-label">Contraseña</label><input v-model="form.admin_password" class="form-control" type="password" minlength="10" required></div>
                </div>
                <div class="platform-form-actions"><span class="platform-help">El proceso puede tardar mientras se prepara el esquema.</span><button class="btn platform-btn-primary text-white" :disabled="creating"><span v-if="creating" class="spinner-border spinner-border-sm me-2"></span>{{ creating ? "Aprovisionando…" : "Crear cliente" }}</button></div>
            </div>
        </form>

        <div class="platform-card">
            <div class="platform-toolbar">
                <div class="platform-search"><i class="fa-solid fa-magnifying-glass"></i><input v-model="filters.search" type="search" placeholder="Buscar por cliente, dominio o base de datos" @input="scheduleSearch"></div>
                <select v-model="filters.status" class="form-select platform-filter" @change="loadTenants(1)">
                    <option value="">Todos los estados</option><option value="active">Activos</option><option value="inactive">Inactivos</option><option value="suspended">Suspendidos</option><option value="provisioning">En preparación</option>
                </select>
                <button class="platform-icon-button" type="button" title="Actualizar" :disabled="loading" @click="loadTenants(meta.current_page)"><i class="fa-solid fa-rotate"></i></button>
            </div>

            <div v-if="loading" class="platform-table-skeleton"><span v-for="index in 5" :key="index"></span></div>
            <div v-else-if="tenants.length" class="table-responsive">
                <table class="table platform-table align-middle mb-0">
                    <thead><tr><th>Cliente</th><th>Dominio</th><th>Base de datos</th><th>Estado</th><th class="text-end">Acción</th></tr></thead>
                    <tbody><tr v-for="tenant in tenants" :key="tenant.id">
                        <td><button class="platform-tenant-name" type="button" @click="$emit('open', tenant)"><span>{{ tenant.slug.charAt(0).toUpperCase() }}</span><strong>{{ tenant.slug }}</strong></button></td>
                        <td><a v-if="tenant.url" :href="tenant.url" target="_blank" rel="noopener">{{ tenant.domain }} <i class="fa-solid fa-arrow-up-right-from-square"></i></a><span v-else class="text-muted">Sin dominio</span></td>
                        <td><code>{{ tenant.database_name }}</code></td>
                        <td><span :class="['platform-status', `platform-status--${tenant.status}`]">{{ statusLabel(tenant.status) }}</span></td>
                        <td class="text-end"><button class="btn btn-sm platform-btn-subtle" type="button" @click="$emit('open', tenant)">Administrar <i class="fa-solid fa-chevron-right"></i></button></td>
                    </tr></tbody>
                </table>
            </div>
            <div v-else class="platform-empty"><i class="fa-regular fa-building"></i><strong>No encontramos clientes</strong><span>Ajusta los filtros o crea el primer tenant.</span></div>

            <div v-if="meta.last_page > 1" class="platform-pagination"><span>{{ meta.total }} clientes</span><div><button type="button" :disabled="meta.current_page <= 1" @click="loadTenants(meta.current_page - 1)"><i class="fa-solid fa-chevron-left"></i></button><span>{{ meta.current_page }} / {{ meta.last_page }}</span><button type="button" :disabled="meta.current_page >= meta.last_page" @click="loadTenants(meta.current_page + 1)"><i class="fa-solid fa-chevron-right"></i></button></div></div>
        </div>
    </section>
</template>

<script>
import api, {errorMessage} from "../api";

const emptyForm = () => ({slug: "", commercial_name: "", legal_name: "", document_number: "", admin_name: "", admin_email: "", admin_password: "", admin_password_confirmation: ""});

export default {
    name: "TenantIndex",
    props: {apiBase: {type: String, required: true}},
    emits: ["open", "notify"],
    data() {
        return {tenants: [], counts: {total: 0, active: 0, inactive: 0, suspended: 0, provisioning: 0}, meta: {current_page: 1, last_page: 1, total: 0}, filters: {search: "", status: ""}, loading: true, creating: false, showCreate: false, form: emptyForm(), searchTimer: null};
    },
    computed: {
        countCards() {
            return [
                {code: "total", label: "Total", value: this.counts.total, filter: "", icon: "fa-solid fa-building"},
                {code: "active", label: "Activos", value: this.counts.active, filter: "active", icon: "fa-solid fa-circle-check"},
                {code: "inactive", label: "Inactivos", value: this.counts.inactive, filter: "inactive", icon: "fa-solid fa-circle-pause"},
                {code: "suspended", label: "Suspendidos", value: this.counts.suspended, filter: "suspended", icon: "fa-solid fa-shield-halved"}
            ];
        }
    },
    mounted() { this.loadTenants(); },
    beforeUnmount() { window.clearTimeout(this.searchTimer); },
    methods: {
        async loadTenants(page = 1) {
            this.loading = true;
            try {
                const {data} = await api.get(this.apiBase, {params: {...this.filters, page, per_page: 20}});
                this.tenants = data.data || []; this.counts = data.counts || this.counts; this.meta = data.meta || this.meta;
            } catch(error) { this.$emit("notify", {type: "danger", message: errorMessage(error, "No fue posible cargar los clientes.")}); }
            finally { this.loading = false; }
        },
        scheduleSearch() { window.clearTimeout(this.searchTimer); this.searchTimer = window.setTimeout(() => this.loadTenants(1), 320); },
        selectStatus(status) { this.filters.status = this.filters.status === status && status !== "" ? "" : status; this.loadTenants(1); },
        statusLabel(status) { return {active: "Activo", inactive: "Inactivo", suspended: "Suspendido", provisioning: "En preparación"}[status] || status; },
        async createTenant() {
            this.creating = true;
            try {
                this.form.admin_password_confirmation = this.form.admin_password;
                const {data} = await api.post(this.apiBase, this.form);
                this.$emit("notify", data.message); this.form = emptyForm(); this.showCreate = false; await this.loadTenants(1);
            } catch(error) { this.$emit("notify", {type: "danger", message: errorMessage(error, "No fue posible crear el cliente.")}); }
            finally { this.creating = false; }
        }
    }
};
</script>
