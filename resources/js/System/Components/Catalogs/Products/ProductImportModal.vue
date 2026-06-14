<template>
    <div
        :id="modalId"
        class="modal fade br-entity-modal br-product-import-modal"
        tabindex="-1"
        aria-labelledby="productImportModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header">
                    <div>
                        <p class="br-entity-modal__eyebrow mb-1">Catálogo comercial</p>
                        <h2 id="productImportModalTitle" class="modal-title br-entity-modal__title">
                            Carga masiva de productos
                        </h2>
                    </div>
                    <button type="button" class="br-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="modal-body br-entity-modal__body">
                    <p class="br-import-products__intro">
                        Completa la plantilla con el nombre y precio. Los códigos que dejes vacíos se generarán automáticamente.
                    </p>

                    <button
                        type="button"
                        class="br-import-products__template"
                        :disabled="downloadingTemplate || importing"
                        @click="downloadTemplate">
                        <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i>
                        <span>Descargar plantilla</span>
                    </button>

                    <div class="row g-3 mt-1">
                        <div class="col-12">
                            <label for="productImportWarehouse" class="form-label">Almacén para el stock inicial</label>
                            <v-select
                                id="productImportWarehouse"
                                v-model="form.warehouse"
                                :options="warehouseOptions"
                                :clearable="false"
                                :searchable="false"
                                append-to-body>
                                <template #no-options>
                                    <SelectNoOptions/>
                                </template>
                            </v-select>
                            <small v-if="errors.warehouse_id" class="text-danger">
                                {{ firstError(errors.warehouse_id) }}
                            </small>
                        </div>

                        <div class="col-12">
                            <label for="productImportFile" class="form-label">Archivo de productos</label>
                            <input
                                id="productImportFile"
                                ref="fileInput"
                                class="form-control"
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                @change="selectFile">
                            <small class="br-import-products__help">
                                Se aceptan archivos Excel o CSV de hasta 5 MB.
                            </small>
                            <small v-if="errors.file" class="text-danger d-block">
                                {{ firstError(errors.file) }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer br-entity-modal__footer">
                    <button
                        ref="closeButton"
                        type="button"
                        class="br-btn br-btn-cancel"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="br-btn br-btn-primary"
                        :disabled="importing"
                        @click="importProducts">
                        Importar productos
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts from "@System/Helpers/Alerts.js";
import * as Requests from "@System/Helpers/Requests.js";
import SelectNoOptions from "@System/Components/Generics/SelectNoOptions.vue";

export default {
    name: "ProductImportModal",
    components: {SelectNoOptions},
    emits: ["imported"],
    props: {
        modalId: {
            type: String,
            default: "productImportModal"
        },
        importRoute: {
            type: String,
            required: true
        },
        templateRoute: {
            type: String,
            required: true
        },
        warehouses: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            importing: false,
            downloadingTemplate: false,
            form: {
                warehouse: null,
                file: null
            },
            errors: {}
        };
    },
    methods: {
        open() {
            this.reset();
            this.form.warehouse = this.warehouseOptions[0] ?? null;
            Alerts.modals({type: "show", id: this.modalId});
        },
        reset() {
            this.form = {warehouse: null, file: null};
            this.errors = {};

            if(this.$refs.fileInput) {
                this.$refs.fileInput.value = "";
            }
        },
        selectFile(event) {
            this.form.file = event.target.files?.[0] ?? null;
            delete this.errors.file;
        },
        async downloadTemplate() {
            if(this.downloadingTemplate) return;

            this.downloadingTemplate = true;
            Alerts.swals({
                type: "loading",
                message: "Preparando plantilla"
            });

            try {
                await Requests.download({
                    route: this.templateRoute,
                    fileName: "plantilla_productos.xlsx",
                    showAlert: true
                });
            }finally {
                Alerts.swals({show: false});
                this.downloadingTemplate = false;
            }
        },
        async importProducts() {
            this.errors = {};

            if(!this.form.warehouse?.code) {
                this.errors.warehouse_id = ["Selecciona un almacén."];
            }

            if(!this.form.file) {
                this.errors.file = ["Selecciona el archivo completado."];
            }

            if(Object.keys(this.errors).length) return;

            this.importing = true;
            Alerts.swals({type: "loading", message: "Importando productos"});

            const formData = new FormData();
            formData.append("warehouse_id", this.form.warehouse.code);
            formData.append("file", this.form.file);

            const result = await Requests.post({
                route: this.importRoute,
                formData
            });

            this.importing = false;
            Alerts.swals({show: false});

            if(Requests.valid({result})) {
                this.$refs.closeButton?.click();
                Alerts.generateAlert({
                    type: "success",
                    msgContent: result.data.msg
                });
                this.$emit("imported", result.data.data);
                return;
            }

            this.errors = result?.errors ?? result?.data?.errors ?? {};
            const messages = Object.values(this.errors).flat();

            Alerts.generateAlert({
                type: "error",
                messages: messages.length
                    ? messages
                    : [result?.data?.msg || "No se pudo completar la carga masiva."]
            });
        },
        firstError(error) {
            return Array.isArray(error) ? error[0] : error;
        }
    },
    computed: {
        warehouseOptions() {
            return this.warehouses.map(warehouse => ({
                code: warehouse.id,
                label: `${warehouse.branch?.name ? `${warehouse.branch.name} - ` : ""}${warehouse.name}`
            }));
        }
    }
};
</script>
