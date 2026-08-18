<template>
    <section>
        <div v-if="loading" class="platform-detail-skeleton"><div></div><div></div><div></div></div>
        <template v-else-if="tenant">
            <div class="platform-heading platform-heading--detail">
                <div class="d-flex align-items-start gap-3">
                    <button class="platform-icon-button" type="button" title="Volver a clientes" @click="$emit('back')"><i class="fa-solid fa-arrow-left"></i></button>
                    <div><span class="platform-eyebrow">Cliente tenant</span><h1 class="platform-title">{{ tenant.slug }}</h1><p class="platform-subtitle"><a v-if="tenant.url" :href="tenant.url" target="_blank" rel="noopener">{{ tenant.domain }}</a><span v-if="tenant.domain"> · </span>{{ tenant.database_name }}</p></div>
                </div>
                <div class="platform-status-control"><span :class="['platform-status', `platform-status--${tenant.status}`]">{{ statusLabel(tenant.status) }}</span><select v-model="statusForm" class="form-select form-select-sm"><option value="active">Activo</option><option value="inactive">Inactivo</option><option value="suspended">Suspendido</option></select><button class="btn btn-sm platform-btn-subtle" :disabled="savingStatus || statusForm === tenant.status" @click="saveStatus">{{ savingStatus ? "Guardando…" : "Actualizar" }}</button></div>
            </div>

            <div class="platform-detail-grid">
                <div class="platform-card platform-modules-card">
                    <div class="platform-card__head platform-card__head--sticky">
                        <div><strong>Módulos habilitados</strong><p class="platform-subtitle">Define las funciones visibles dentro de la organización.</p></div>
                        <div class="d-flex align-items-center gap-2"><span class="platform-chip">{{ enabledModuleIds.length }} / {{ modules.length }}</span><button class="btn btn-sm platform-btn-primary text-white" :disabled="savingModules" @click="saveModules"><span v-if="savingModules" class="spinner-border spinner-border-sm me-1"></span>Guardar cambios</button></div>
                    </div>
                    <div class="platform-card__body platform-module-categories">
                        <section v-for="category in groupedModules" :key="category.name" class="platform-module-category">
                            <header><span>{{ category.name }}</span><small>{{ category.enabled }} activos</small></header>
                            <div v-for="section in category.sections" :key="section.name" class="platform-module-section">
                                <h3>{{ section.name }}</h3>
                                <div class="platform-module-grid">
                                    <label v-for="module in section.modules" :key="module.id" :class="['platform-module', {'is-enabled': isEnabled(module.id)}]">
                                        <input class="form-check-input" type="checkbox" :checked="isEnabled(module.id)" @change="toggleModule(module.id)">
                                        <span><strong>{{ module.dom_label }}</strong><small>{{ module.group_name || "Acceso directo" }}</small></span>
                                    </label>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <aside>
                    <div class="platform-card mb-4">
                        <div class="platform-card__head"><div><strong>Publicar aviso</strong><p class="platform-subtitle">Comunicación visible para los usuarios del tenant.</p></div></div>
                        <form class="platform-card__body" @submit.prevent="publishAnnouncement">
                            <div class="mb-3"><label class="form-label">Título</label><input v-model.trim="announcementForm.title" class="form-control" maxlength="180" required></div>
                            <div class="mb-3"><label class="form-label">Mensaje</label><textarea v-model.trim="announcementForm.message" class="form-control" rows="4" maxlength="2000" required></textarea></div>
                            <div class="row g-2"><div class="col-6"><label class="form-label">Tipo</label><select v-model="announcementForm.severity" class="form-select"><option value="info">Información</option><option value="success">Éxito</option><option value="warning">Advertencia</option><option value="danger">Importante</option></select></div><div class="col-6 d-flex align-items-end pb-2"><label class="form-check"><input v-model="announcementForm.dismissible" class="form-check-input" type="checkbox"><span class="form-check-label">Descartable</span></label></div><div class="col-6"><label class="form-label">Desde</label><input v-model="announcementForm.starts_at" class="form-control" type="datetime-local"></div><div class="col-6"><label class="form-label">Hasta</label><input v-model="announcementForm.ends_at" class="form-control" type="datetime-local"></div></div>
                            <button class="btn platform-btn-primary text-white w-100 mt-3" :disabled="publishing"><span v-if="publishing" class="spinner-border spinner-border-sm me-1"></span>{{ publishing ? "Publicando…" : "Publicar aviso" }}</button>
                        </form>
                    </div>

                    <div class="platform-card">
                        <div class="platform-card__head"><div><strong>Avisos recientes</strong><p class="platform-subtitle">Últimas 50 comunicaciones.</p></div></div>
                        <div class="platform-announcements">
                            <article v-for="announcement in announcements" :key="announcement.id" :class="['platform-announcement', `is-${announcement.severity}`]">
                                <div><strong>{{ announcement.title }}</strong><span :class="['platform-status', `platform-status--${announcement.status}`]">{{ announcement.status === 'active' ? 'Activo' : 'Inactivo' }}</span></div>
                                <p>{{ announcement.message }}</p>
                                <button type="button" :disabled="updatingAnnouncementId === announcement.id" @click="toggleAnnouncement(announcement)">{{ announcement.status === "active" ? "Desactivar" : "Activar" }}</button>
                            </article>
                            <div v-if="!announcements.length" class="platform-empty platform-empty--small"><i class="fa-regular fa-bell-slash"></i><span>Sin avisos publicados.</span></div>
                        </div>
                    </div>
                </aside>
            </div>
        </template>
    </section>
</template>

<script>
import api, {errorMessage} from "../api";

const emptyAnnouncement = () => ({title: "", message: "", severity: "info", starts_at: "", ends_at: "", dismissible: true});

export default {
    name: "TenantDetail",
    props: {tenantId: {type: Number, required: true}, apiBase: {type: String, required: true}},
    emits: ["back", "notify"],
    data() {
        return {tenant: null, modules: [], announcements: [], enabledModuleIds: [], statusForm: "active", announcementForm: emptyAnnouncement(), loading: true, savingStatus: false, savingModules: false, publishing: false, updatingAnnouncementId: null};
    },
    computed: {
        endpoint() { return `${this.apiBase}/${this.tenantId}`; },
        groupedModules() {
            const categories = new Map();
            this.modules.forEach(module => {
                const categoryName = module.category_name || "General";
                const sectionName = module.section_name || "Accesos";
                if(!categories.has(categoryName)) categories.set(categoryName, new Map());
                const sections = categories.get(categoryName);
                if(!sections.has(sectionName)) sections.set(sectionName, []);
                sections.get(sectionName).push(module);
            });
            return [...categories].map(([name, sections]) => ({name, enabled: [...sections.values()].flat().filter(module => this.isEnabled(module.id)).length, sections: [...sections].map(([sectionName, modules]) => ({name: sectionName, modules}))}));
        }
    },
    mounted() { this.loadTenant(); },
    methods: {
        async loadTenant() {
            this.loading = true;
            try {
                const {data} = await api.get(this.endpoint);
                this.tenant = data.data.tenant; this.modules = data.data.modules || []; this.announcements = data.data.announcements || []; this.statusForm = this.tenant.status;
                this.enabledModuleIds = this.modules.filter(module => module.company_status === "active").map(module => Number(module.id));
            } catch(error) { this.$emit("notify", {type: "danger", message: errorMessage(error, "No fue posible cargar el cliente.")}); }
            finally { this.loading = false; }
        },
        isEnabled(id) { return this.enabledModuleIds.includes(Number(id)); },
        toggleModule(id) { const value = Number(id); this.enabledModuleIds = this.isEnabled(value) ? this.enabledModuleIds.filter(current => current !== value) : [...this.enabledModuleIds, value]; },
        statusLabel(status) { return {active: "Activo", inactive: "Inactivo", suspended: "Suspendido", provisioning: "En preparación"}[status] || status; },
        async saveStatus() {
            this.savingStatus = true;
            try { const {data} = await api.patch(`${this.endpoint}/status`, {status: this.statusForm}); this.tenant = {...this.tenant, ...data.data}; this.$emit("notify", data.message); }
            catch(error) { this.$emit("notify", {type: "danger", message: errorMessage(error)}); }
            finally { this.savingStatus = false; }
        },
        async saveModules() {
            this.savingModules = true;
            try { const {data} = await api.put(`${this.endpoint}/modules`, {modules: this.enabledModuleIds}); this.modules = this.modules.map(module => ({...module, company_status: this.isEnabled(module.id) ? "active" : "inactive"})); this.$emit("notify", data.message); }
            catch(error) { this.$emit("notify", {type: "danger", message: errorMessage(error)}); }
            finally { this.savingModules = false; }
        },
        async publishAnnouncement() {
            this.publishing = true;
            try { const payload = {...this.announcementForm, starts_at: this.announcementForm.starts_at || null, ends_at: this.announcementForm.ends_at || null}; const {data} = await api.post(`${this.endpoint}/announcements`, payload); this.announcements.unshift(data.data); this.announcementForm = emptyAnnouncement(); this.$emit("notify", data.message); }
            catch(error) { this.$emit("notify", {type: "danger", message: errorMessage(error)}); }
            finally { this.publishing = false; }
        },
        async toggleAnnouncement(announcement) {
            this.updatingAnnouncementId = announcement.id;
            try { const status = announcement.status === "active" ? "inactive" : "active"; const {data} = await api.patch(`${this.endpoint}/announcements/${announcement.id}`, {status}); const index = this.announcements.findIndex(record => record.id === announcement.id); this.announcements.splice(index, 1, data.data); this.$emit("notify", data.message); }
            catch(error) { this.$emit("notify", {type: "danger", message: errorMessage(error)}); }
            finally { this.updatingAnnouncementId = null; }
        }
    }
};
</script>
