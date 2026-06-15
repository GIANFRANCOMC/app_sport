<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <main class="br-entity">
        <section class="br-filter-bar">
            <div class="row align-items-end g-2">
                <InputText
                    v-model="word"
                    hasDiv
                    title="Búsqueda"
                    :titleClass="[config.forms.classes.title]"
                    placeholder="Nombre, documento o contacto"
                    xl="8"
                    lg="8"
                    @enterKeyPressed="listSuppliers({})"/>
                <div class="form-group col-xl-4 col-lg-4">
                    <div class="br-filter-bar__actions">
                        <button type="button" class="br-btn br-btn-sm br-btn-action-search" @click="listSuppliers({})">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <span>Buscar</span>
                        </button>
                        <button
                            type="button"
                            class="br-btn br-btn-sm br-btn-action-open-create"
                            data-bs-toggle="modal"
                            data-bs-target="#supplierModal"
                            @click="prepareSupplier">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                            <span>Agregar proveedor</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="br-entity-list">
            <div class="table-responsive">
                <table class="table br-entity-table mb-0">
                    <thead class="br-table-header-surface">
                        <tr>
                            <th>Proveedor</th>
                            <th>Documento</th>
                            <th>Contacto</th>
                            <th>Comunicación</th>
                            <th>Estado</th>
                            <th class="text-center"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading"><td colspan="6" class="py-4"><Loader/></td></tr>
                        <template v-else-if="records.total > 0">
                            <tr v-for="supplier in records.data" :key="supplier.id">
                                <td>
                                    <strong>{{ supplier.name }}</strong>
                                    <span class="br-purchases__meta">{{ supplier.address }}</span>
                                </td>
                                <td>{{ supplier.document_type }} {{ supplier.document_number }}</td>
                                <td>{{ supplier.contact_name || "Sin contacto" }}</td>
                                <td>
                                    <span>{{ supplier.telephone }}</span>
                                    <span class="br-purchases__meta">{{ supplier.email }}</span>
                                </td>
                                <td>
                                    <span :class="['br-status-label', `br-status-${supplier.status}`]">
                                        {{ supplier.status === "active" ? "Activo" : "Inactivo" }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="br-icon-action br-icon-action-edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#supplierModal"
                                        title="Editar proveedor"
                                        :aria-label="`Editar proveedor ${supplier.name}`"
                                        @click="prepareSupplier(supplier)">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr v-else><td colspan="6"><WithoutData type="image"/></td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div v-if="records.links" class="d-flex justify-content-center mt-3">
            <Paginator :links="records.links" @clickPage="listSuppliers"/>
        </div>
    </main>

    <div id="supplierModal" class="modal fade br-entity-modal br-modal-standard" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header">
                    <div>
                        <p class="br-entity-modal__eyebrow mb-1">Compras</p>
                        <h2 class="modal-title br-entity-modal__title">
                            {{ editingId ? "Editar proveedor" : "Agregar proveedor" }}
                        </h2>
                    </div>
                    <button type="button" class="br-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-standard__body">
                    <div class="row g-3">
                        <InputText v-model="form.name" hasDiv title="Razón social o nombre" :titleClass="[config.forms.classes.title]" isRequired xl="6" lg="6"/>
                        <InputText v-model="form.contact_name" hasDiv title="Persona de contacto" :titleClass="[config.forms.classes.title]" xl="6" lg="6"/>
                        <InputText v-model="form.document_type" hasDiv title="Tipo de documento" :titleClass="[config.forms.classes.title]" placeholder="RUC, DNI u otro" xl="4" lg="4"/>
                        <InputText v-model="form.document_number" hasDiv title="Número de documento" :titleClass="[config.forms.classes.title]" xl="4" lg="4"/>
                        <InputText v-model="form.telephone" hasDiv title="Teléfono" :titleClass="[config.forms.classes.title]" xl="4" lg="4"/>
                        <InputText v-model="form.email" hasDiv title="Correo" :titleClass="[config.forms.classes.title]" xl="6" lg="6"/>
                        <InputText v-model="form.address" hasDiv title="Dirección" :titleClass="[config.forms.classes.title]" xl="6" lg="6"/>
                        <div class="form-group col-xl-6 col-lg-6 col-md-12">
                            <label class="form-label">Estado</label>
                            <v-select
                                v-model="form.statusOption"
                                :options="statusOptions"
                                :clearable="false"
                                :searchable="false"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer br-entity-modal__footer">
                    <button ref="closeModal" type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button
                        type="button"
                        :class="['br-btn', editingId ? 'br-btn-action-update' : 'br-btn-action-create']"
                        :disabled="saving"
                        @click="saveSupplier">
                        {{ editingId ? "Actualizar proveedor" : "Agregar proveedor" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts from "@System/Helpers/Alerts.js";
import * as Constants from "@System/Helpers/Constants.js";
import * as Requests from "@System/Helpers/Requests.js";
import * as Utils from "@System/Helpers/Utils.js";

export default {
    mounted() {
        Utils.navbarItem("menu-parent-purchases", {addClass: "open"});
        Utils.navbarItem("menu-purchases-suppliers", {});
        this.listSuppliers({});
    },
    data() {
        return {
            word: "",
            loading: false,
            saving: false,
            editingId: null,
            records: {total: 0, data: []},
            form: {},
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "suppliers"}),
                    page: {title: "Proveedores", active: true, menu: {id: "menu-purchases-suppliers"}}
                }
            }
        };
    },
    methods: {
        async listSuppliers({url = null} = {}) {
            this.loading = true;
            const result = await Requests.get({
                route: url || this.config.entity.routes.list,
                data: {word: this.word}
            });
            this.records = result.data || {total: 0, data: []};
            this.loading = false;
        },
        prepareSupplier(supplier = null) {
            this.editingId = supplier?.id || null;
            this.form = {
                name: supplier?.name || "",
                contact_name: supplier?.contact_name || "",
                document_type: supplier?.document_type || "RUC",
                document_number: supplier?.document_number || "",
                telephone: supplier?.telephone || "",
                email: supplier?.email || "",
                address: supplier?.address || "",
                statusOption: this.statusOptions.find(option => option.code === supplier?.status)
                    || this.statusOptions[0]
            };
        },
        async saveSupplier() {
            this.saving = true;
            Alerts.swals({
                type: "loading",
                message: this.editingId ? "Actualizando proveedor" : "Agregando proveedor"
            });
            const data = {
                ...this.form,
                status: this.form.statusOption?.code
            };
            delete data.statusOption;
            const result = this.editingId
                ? await Requests.patch({
                    route: this.config.entity.routes.update,
                    id: this.editingId,
                    data
                })
                : await Requests.post({
                    route: this.config.entity.routes.store,
                    data
                });
            this.saving = false;
            Alerts.swals({show: false});
            if(Requests.valid({result})) {
                this.$refs.closeModal?.click();
                Alerts.generateAlert({type: "success", msgContent: result.data.msg});
                await this.listSuppliers({});
                return;
            }
            const errors = Object.values(result?.errors || {}).flat();
            Alerts.generateAlert({
                type: "error",
                messages: errors.length ? errors : [result?.data?.msg || "No se pudo agregar el proveedor."]
            });
        }
    },
    computed: {
        breadcrumbTitles() {
            return [{title: "Compras"}, this.config.entity.page];
        },
        statusOptions() {
            return [
                {code: "active", label: "Activo"},
                {code: "inactive", label: "Inactivo"}
            ];
        }
    }
};
</script>
