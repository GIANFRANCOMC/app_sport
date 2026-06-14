<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <section class="br-inventory">
        <header class="br-inventory__header">
            <div>
                <p class="br-inventory__eyebrow">Control de existencias</p>
                <h1 class="br-inventory__title">Inventario</h1>
                <p class="br-inventory__subtitle">
                    Consulta saldos, registra ajustes justificados y revisa la trazabilidad de cada producto.
                </p>
            </div>

            <div class="br-inventory__warehouse">
                <label for="inventoryWarehouse" class="form-label">Almacén</label>
                <v-select
                    id="inventoryWarehouse"
                    v-model="filters.warehouse"
                    :options="warehouses"
                    :class="config.forms.classes.select2"
                    :clearable="false"
                    :searchable="false"/>
            </div>
        </header>

        <div class="br-inventory__views" role="tablist" aria-label="Vistas de inventario">
            <button
                type="button"
                :class="['br-inventory__view', { 'is-active': activeView === 'stock' }]"
                role="tab"
                :aria-selected="activeView === 'stock'"
                @click="changeView('stock')">
                <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
                <span>Existencias</span>
            </button>
            <button
                type="button"
                :class="['br-inventory__view', { 'is-active': activeView === 'kardex' }]"
                role="tab"
                :aria-selected="activeView === 'kardex'"
                @click="changeView('kardex')">
                <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                <span>Kardex</span>
            </button>
        </div>

        <div v-if="activeView === 'stock'" class="table-responsive br-inventory__table-wrap">
            <table class="table br-entity-table br-inventory__table mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-end">Stock actual</th>
                        <th class="text-end">Stock mínimo</th>
                        <th>Situación</th>
                        <th class="text-center">
                            <span class="visually-hidden">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loadingStock">
                        <td colspan="5" class="text-center py-4"><Loader/></td>
                    </tr>
                    <template v-else-if="stockRecords.total > 0">
                        <tr v-for="record in stockRecords.data" :key="record.id">
                            <td>
                                <strong class="br-inventory__product-name">{{ record.name }}</strong>
                                <span class="br-inventory__product-code">{{ record.internal_code }}</span>
                            </td>
                            <td class="text-end">
                                <strong>{{ separatorNumber(record.stock_quantity) }}</strong>
                            </td>
                            <td class="text-end">
                                {{ separatorNumber(record.minimum_stock) }}
                            </td>
                            <td>
                                <span
                                    :class="[
                                        'br-inventory-status',
                                        stockStatus(record).className
                                    ]">
                                    <i :class="stockStatus(record).icon" aria-hidden="true"></i>
                                    {{ stockStatus(record).label }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="br-icon-action br-inventory__movement-action"
                                    data-bs-toggle="modal"
                                    data-bs-target="#inventoryMovementModal"
                                    title="Registrar movimiento"
                                    aria-label="Registrar movimiento"
                                    @click="prepareMovement(record)">
                                    <i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr v-else>
                        <td colspan="5"><WithoutData type="image"/></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="br-inventory__kardex">
            <div class="br-inventory__kardex-filters">
                <div>
                    <label for="movementType" class="form-label">Tipo de movimiento</label>
                    <v-select
                        id="movementType"
                        v-model="filters.movementType"
                        :options="movementTypes"
                        :class="config.forms.classes.select2"
                        :clearable="false"
                        :searchable="false"/>
                </div>
                <button
                    type="button"
                    class="br-btn br-btn-action-search"
                    :disabled="loadingKardex"
                    @click="listKardex({})">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <span>Consultar</span>
                </button>
            </div>

            <div class="table-responsive br-inventory__table-wrap">
                <table class="table br-entity-table br-inventory__kardex-table mb-0">
                    <thead>
                        <tr>
                            <th>Fecha y responsable</th>
                            <th>Producto</th>
                            <th>Movimiento</th>
                            <th class="text-end">Variación</th>
                            <th class="text-end">Saldo</th>
                            <th>Motivo y origen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loadingKardex">
                            <td colspan="6" class="text-center py-4"><Loader/></td>
                        </tr>
                        <template v-else-if="kardexRecords.total > 0">
                            <tr v-for="movement in kardexRecords.data" :key="movement.id">
                                <td>
                                    <strong>{{ formatDate(movement.created_at) }}</strong>
                                    <span class="br-inventory__meta">
                                        {{ movement.user?.name || 'Proceso del sistema' }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ movement.item?.name }}</strong>
                                    <span class="br-inventory__meta">{{ movement.item?.internal_code }}</span>
                                </td>
                                <td>
                                    <span :class="['br-inventory-movement', `is-${movement.movement_type}`]">
                                        {{ movementTypeLabel(movement.movement_type) }}
                                    </span>
                                </td>
                                <td
                                    :class="[
                                        'text-end fw-semibold',
                                        Number(movement.quantity_change) > 0 ? 'text-success' : 'text-danger'
                                    ]">
                                    {{ signedNumber(movement.quantity_change) }}
                                </td>
                                <td class="text-end">
                                    <strong>{{ separatorNumber(movement.quantity_after) }}</strong>
                                    <span class="br-inventory__meta">
                                        Antes: {{ separatorNumber(movement.quantity_before) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ movement.reason }}</strong>
                                    <span class="br-inventory__meta">
                                        {{ originLabel(movement.origin_type) }}
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="6"><WithoutData type="image"/></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-3" v-if="currentRecords.links">
            <Paginator :links="currentRecords.links" @clickPage="listCurrentView"/>
        </div>
    </section>

    <div
        id="inventoryMovementModal"
        class="modal fade br-entity-modal"
        tabindex="-1"
        aria-labelledby="inventoryMovementModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header">
                    <div>
                        <p class="br-entity-modal__eyebrow mb-1">Inventario</p>
                        <h2 id="inventoryMovementModalTitle" class="modal-title br-entity-modal__title">
                            Registrar movimiento
                        </h2>
                    </div>
                    <button type="button" class="br-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="modal-body br-entity-modal__body">
                    <div class="br-inventory__selected-product">
                        <span>Producto</span>
                        <strong>{{ movementForm.item?.name }}</strong>
                        <small>
                            Stock actual: {{ separatorNumber(movementForm.item?.stock_quantity || 0) }}
                        </small>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="manualMovementType">Tipo de movimiento</label>
                            <v-select
                                id="manualMovementType"
                                v-model="movementForm.type"
                                :options="manualMovementTypes"
                                :class="config.forms.classes.select2"
                                :clearable="false"
                                :searchable="false"/>
                            <small v-if="movementErrors.movement_type" class="text-danger">
                                {{ firstError(movementErrors.movement_type) }}
                            </small>
                        </div>

                        <div v-if="movementForm.type?.code !== 'correction'" class="col-12">
                            <label class="form-label" for="movementQuantity">Cantidad</label>
                            <InputNumber
                                id="movementQuantity"
                                v-model="movementForm.quantity"
                                :hasNegative="false"/>
                            <small v-if="movementErrors.quantity" class="text-danger">
                                {{ firstError(movementErrors.quantity) }}
                            </small>
                        </div>

                        <div v-else class="col-12">
                            <label class="form-label" for="resultingBalance">Saldo físico contado</label>
                            <InputNumber
                                id="resultingBalance"
                                v-model="movementForm.resultingBalance"
                                :hasNegative="false"/>
                            <small class="br-inventory__field-help">
                                El sistema calculará automáticamente la diferencia contra el stock actual.
                            </small>
                            <small v-if="movementErrors.resulting_balance" class="text-danger d-block">
                                {{ firstError(movementErrors.resulting_balance) }}
                            </small>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="movementReason">Motivo</label>
                            <textarea
                                id="movementReason"
                                v-model.trim="movementForm.reason"
                                class="form-control"
                                rows="3"
                                maxlength="255"
                                placeholder="Describe por qué se realiza este movimiento"></textarea>
                            <small v-if="movementErrors.reason" class="text-danger">
                                {{ firstError(movementErrors.reason) }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer br-entity-modal__footer">
                    <button
                        ref="closeMovementModal"
                        type="button"
                        class="br-btn br-btn-cancel"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="br-btn br-btn-primary"
                        :disabled="savingMovement"
                        @click="saveMovement">
                        Registrar movimiento
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

const MOVEMENT_TYPES = [
    { code: "", label: "Todos los movimientos" },
    { code: "entry", label: "Entrada" },
    { code: "exit", label: "Salida" },
    { code: "correction", label: "Corrección" }
];

const MANUAL_MOVEMENT_TYPES = MOVEMENT_TYPES.filter(type => type.code);

export default {
    mounted: async function() {
        Utils.navbarItem("menu-parent-items", { addClass: "open" });
        Utils.navbarItem(this.config.entity.page.menu.id, {});
        Alerts.swals({ type: "initParams" });

        const initParams = await this.initParams();

        Alerts.swals({ show: false });

        if(initParams && this.warehouses.length) {
            this.filters.warehouse = this.warehouses[0];
        }
    },
    data() {
        return {
            activeView: "stock",
            loadingStock: false,
            loadingKardex: false,
            savingMovement: false,
            stockRecords: { total: 0, data: [] },
            kardexRecords: { total: 0, data: [] },
            filters: {
                warehouse: null,
                movementType: MOVEMENT_TYPES[0]
            },
            movementForm: {
                item: null,
                type: MANUAL_MOVEMENT_TYPES[0],
                quantity: "",
                resultingBalance: "",
                reason: ""
            },
            movementErrors: {},
            options: {
                warehouses: []
            },
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({ entity: "stocks_management" }),
                    page: {
                        title: "Inventario",
                        active: true,
                        menu: { id: "menu-items-stocks_management" }
                    }
                }
            }
        };
    },
    methods: {
        async initParams() {
            const result = await Requests.get({
                route: this.config.entity.routes.initParams,
                data: { page: "main" },
                showAlert: true
            });

            this.options.warehouses = result.data?.config?.warehouses?.records || [];
            return Requests.valid({ result });
        },
        async listStock({ url = null } = {}) {
            if(!this.filters.warehouse?.code) return;

            this.loadingStock = true;
            const result = await Requests.get({
                route: url || Requests.config({ entity: "stocks_management", type: "list" }),
                data: { warehouse_id: this.filters.warehouse.code }
            });
            this.stockRecords = result?.data || { total: 0, data: [] };
            this.loadingStock = false;
        },
        async listKardex({ url = null } = {}) {
            if(!this.filters.warehouse?.code) return;

            this.loadingKardex = true;
            const result = await Requests.get({
                route: url || Requests.config({ entity: "stocks_management", type: "movements" }),
                data: {
                    warehouse_id: this.filters.warehouse.code,
                    movement_type: this.filters.movementType?.code || ""
                }
            });
            this.kardexRecords = result?.data || { total: 0, data: [] };
            this.loadingKardex = false;
        },
        changeView(view) {
            this.activeView = view;

            if(view === "kardex" && !this.kardexRecords.data.length) {
                this.listKardex({});
            }
        },
        listCurrentView({ url = null } = {}) {
            return this.activeView === "stock"
                ? this.listStock({ url })
                : this.listKardex({ url });
        },
        prepareMovement(record) {
            this.movementForm = {
                item: record,
                type: MANUAL_MOVEMENT_TYPES[0],
                quantity: "",
                resultingBalance: "",
                reason: ""
            };
            this.movementErrors = {};
        },
        async saveMovement() {
            this.savingMovement = true;
            this.movementErrors = {};
            Alerts.swals({ type: "loading", message: "Registrando movimiento" });

            const result = await Requests.post({
                route: Requests.config({ entity: "stocks_management", type: "movements" }),
                data: {
                    warehouse_id: this.filters.warehouse?.code,
                    item_id: this.movementForm.item?.id,
                    movement_type: this.movementForm.type?.code,
                    quantity: this.movementForm.quantity,
                    resulting_balance: this.movementForm.resultingBalance,
                    reason: this.movementForm.reason
                }
            });

            this.savingMovement = false;

            if(Requests.valid({ result })) {
                Alerts.swals({ show: false });
                Alerts.toastrs({
                    type: "success",
                    subtitle: result?.data?.msg || "Movimiento registrado correctamente."
                });
                this.$refs.closeMovementModal?.click();
                await Promise.all([this.listStock({}), this.listKardex({})]);
                return;
            }

            Alerts.swals({ show: false });
            this.movementErrors = result?.errors || result?.data?.errors || {};
            Alerts.generateAlert({
                messages: [result?.data?.msg || "No se pudo registrar el movimiento."]
            });
        },
        stockStatus(record) {
            const stock = Number(record.stock_quantity || 0);
            const minimum = Number(record.minimum_stock || 0);

            if(stock <= 0) {
                return {
                    label: "Sin existencias",
                    icon: "fa-solid fa-circle-xmark",
                    className: "is-empty"
                };
            }

            if(stock <= minimum) {
                return {
                    label: "Stock bajo",
                    icon: "fa-solid fa-triangle-exclamation",
                    className: "is-low"
                };
            }

            return {
                label: "Stock saludable",
                icon: "fa-solid fa-circle-check",
                className: "is-healthy"
            };
        },
        movementTypeLabel(type) {
            return MANUAL_MOVEMENT_TYPES.find(record => record.code === type)?.label || type;
        },
        originLabel(origin) {
            const labels = {
                product_opening: "Creación de producto",
                manual: "Movimiento manual",
                sale: "Venta",
                sale_cancellation: "Anulación de venta",
                purchase: "Compra",
                purchase_cancellation: "Anulación de compra"
            };
            return labels[origin] || origin;
        },
        formatDate(value) {
            if(!value) return "";
            return new Intl.DateTimeFormat("es-PE", {
                dateStyle: "short",
                timeStyle: "short"
            }).format(new Date(value));
        },
        signedNumber(value) {
            const number = Number(value || 0);
            return `${number > 0 ? "+" : ""}${this.separatorNumber(number)}`;
        },
        firstError(error) {
            return Array.isArray(error) ? error[0] : error;
        },
        separatorNumber(value) {
            return Utils.separatorNumber(value || 0);
        }
    },
    computed: {
        breadcrumbTitles() {
            return [{ title: "Catálogo comercial" }, this.config.entity.page];
        },
        warehouses() {
            return (this.options.warehouses || []).map(record => ({
                code: record.id,
                label: `${record.branch?.name ? `${record.branch.name} - ` : ""}${record.name}`
            }));
        },
        movementTypes() {
            return MOVEMENT_TYPES;
        },
        manualMovementTypes() {
            return MANUAL_MOVEMENT_TYPES;
        },
        currentRecords() {
            return this.activeView === "stock" ? this.stockRecords : this.kardexRecords;
        }
    },
    watch: {
        "filters.warehouse": function(value) {
            if(value?.code) {
                this.stockRecords = { total: 0, data: [] };
                this.kardexRecords = { total: 0, data: [] };
                this.listCurrentView({});
            }
        }
    }
};
</script>
