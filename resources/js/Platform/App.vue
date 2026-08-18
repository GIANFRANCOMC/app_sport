<template>
    <div class="platform-app">
        <header class="platform-nav">
            <div class="platform-nav__inner">
                <button class="platform-brand platform-reset-button" type="button" @click="openList">
                    <span class="platform-brand__mark">G</span>
                    <span>Gympe <small>Administración SaaS</small></span>
                </button>
                <nav class="platform-nav__links" aria-label="Administración">
                    <button class="platform-nav__link is-active" type="button" @click="openList">
                        <i class="fa-solid fa-building"></i> Clientes
                    </button>
                </nav>
                <div class="platform-user">
                    <span class="platform-secure"><i class="fa-solid fa-shield-halved"></i> Landlord</span>
                    <span class="platform-user__avatar">{{ userInitial }}</span>
                    <span class="platform-user__name">{{ config.user?.name }}</span>
                    <button class="platform-icon-button" type="button" title="Cerrar sesión" :disabled="loggingOut" @click="logout">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </div>
            </div>
        </header>

        <div v-if="notice.message" :class="['platform-notice', `is-${notice.type}`]" role="status">
            <i :class="notice.type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation'"></i>
            <span>{{ notice.message }}</span>
            <button type="button" @click="notice.message = ''"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <main class="platform-main">
            <TenantDetail
                v-if="tenantId"
                :key="tenantId"
                :tenant-id="tenantId"
                :api-base="config.routes.tenantApi"
                @back="openList"
                @notify="showNotice"/>
            <TenantIndex
                v-else
                :api-base="config.routes.tenantApi"
                @open="openTenant"
                @notify="showNotice"/>
        </main>
    </div>
</template>

<script>
import api, {errorMessage} from "./api";
import TenantDetail from "./pages/TenantDetail.vue";
import TenantIndex from "./pages/TenantIndex.vue";

export default {
    name: "PlatformApp",
    components: {TenantDetail, TenantIndex},
    props: {
        config: {type: Object, required: true}
    },
    data() {
        return {
            tenantId: this.resolveTenantId(),
            loggingOut: false,
            notice: {type: "success", message: ""},
            noticeTimer: null
        };
    },
    computed: {
        userInitial() {
            return (this.config.user?.name || "A").trim().charAt(0).toUpperCase();
        }
    },
    mounted() {
        window.addEventListener("popstate", this.syncLocation);
    },
    beforeUnmount() {
        window.removeEventListener("popstate", this.syncLocation);
        window.clearTimeout(this.noticeTimer);
    },
    methods: {
        resolveTenantId() {
            const match = window.location.pathname.match(/\/tenants\/(\d+)\/?$/);
            return match ? Number(match[1]) : (this.config.initialTenantId || null);
        },
        syncLocation() {
            this.tenantId = this.resolveTenantId();
        },
        openList() {
            if(window.location.pathname !== this.config.routes.tenants) {
                window.history.pushState({}, "", this.config.routes.tenants);
            }
            this.tenantId = null;
        },
        openTenant(tenant) {
            const url = `${this.config.routes.tenants}/${tenant.id}`;
            window.history.pushState({}, "", url);
            this.tenantId = tenant.id;
        },
        showNotice(payload) {
            window.clearTimeout(this.noticeTimer);
            this.notice = typeof payload === "string"
                ? {type: "success", message: payload}
                : {type: payload.type || "success", message: payload.message};
            this.noticeTimer = window.setTimeout(() => { this.notice.message = ""; }, 6000);
        },
        async logout() {
            if(this.loggingOut || !window.confirm("¿Deseas cerrar la sesión de administración?")) return;

            this.loggingOut = true;
            try {
                await api.post(this.config.routes.logout);
                window.location.assign(this.config.routes.login);
            } catch(error) {
                this.showNotice({type: "danger", message: errorMessage(error, "No fue posible cerrar la sesión.")});
                this.loggingOut = false;
            }
        }
    }
};
</script>
