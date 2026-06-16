<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <main class="br-entity br-purchases">
        <section class="br-filter-bar">
            <div class="row align-items-end g-2">
                <InputText
                    v-model="filters.word"
                    hasDiv
                    title="Búsqueda"
                    :titleClass="[config.forms.classes.title]"
                    placeholder="Proveedor, documento o producto"
                    xl="5"
                    lg="5"
                    @enterKeyPressed="listPurchases({})"/>
                <div class="form-group col-xl-3 col-lg-3 col-md-12">
                    <label class="form-label">Estado</label>
                    <v-select
                        v-model="filters.status"
                        :options="statuses"
                        :clearable="false"
                        :searchable="false"/>
                </div>
                <div class="form-group col-xl-4 col-lg-4 col-md-12">
                    <div class="br-filter-bar__actions">
                        <button type="button" class="br-btn br-btn-sm br-btn-action-search" @click="listPurchases({})">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <span>Buscar</span>
                        </button>
                        <button
                            type="button"
                            class="br-btn br-btn-sm br-btn-action-open-create"
                            data-bs-toggle="modal"
                            data-bs-target="#purchaseModal"
                            @click="preparePurchase">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                            <span>Registrar compra</span>
                        </button>
                        <button
                            type="button"
                            class="br-btn br-btn-sm br-btn-action-export br-btn-action-export--desktop-icon"
                            title="Descargar compras"
                            data-bs-toggle="tooltip"
                            @click="exportPurchases">
                            <i class="fa-solid fa-file-excel" aria-hidden="true"></i>
                            <span class="br-btn-action-export__label">Descargar</span>
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
                            <th>Documento</th>
                            <th>Proveedor</th>
                            <th>Almacén</th>
                            <th class="text-end">Total</th>
                            <th>Recepción</th>
                            <th>Estado</th>
                            <th class="text-center"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="7" class="py-4"><Loader/></td>
                        </tr>
                        <template v-else-if="records.total > 0">
                            <tr v-for="purchase in records.data" :key="purchase.id">
                                <td>
                                    <strong>{{ purchase.formatted_document_type }}</strong>
                                    <span class="br-purchases__meta">
                                        {{ purchase.document_number || `Registro #${purchase.id}` }}
                                        · {{ formatDate(purchase.issue_date) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ purchase.supplier?.name }}</strong>
                                    <span class="br-purchases__meta">{{ purchase.supplier?.document_number }}</span>
                                </td>
                                <td>{{ purchase.warehouse?.name }}</td>
                                <td class="text-end">
                                    <strong>{{ purchase.currency?.sign }} {{ separatorNumber(purchase.total) }}</strong>
                                </td>
                                <td>
                                    <span class="br-purchases__progress-label">{{ purchase.receipt_progress }}%</span>
                                    <div class="br-purchases__progress">
                                        <span :style="{width: `${purchase.receipt_progress}%`}"></span>
                                    </div>
                                </td>
                                <td>
                                    <span :class="['br-purchase-status', `is-${purchase.status}`]">
                                        {{ purchase.formatted_status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="br-purchases__actions">
                                        <button
                                            v-if="['confirmed', 'partial'].includes(purchase.status)"
                                            type="button"
                                            class="br-icon-action br-icon-action-edit"
                                            title="Registrar recepción"
                                            data-bs-toggle="modal"
                                            data-bs-target="#purchaseReceiptModal"
                                            @click="prepareReceipt(purchase.id)">
                                            <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                                        </button>
                                        <button
                                            v-if="purchase.status === 'confirmed'"
                                            type="button"
                                            class="br-icon-action br-purchases__cancel"
                                            title="Anular compra"
                                            @click="cancelPurchase(purchase)">
                                            <i class="fa-solid fa-ban" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="7"><WithoutData type="image"/></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div v-if="records.links" class="d-flex justify-content-center mt-3">
            <Paginator :links="records.links" @clickPage="listPurchases"/>
        </div>
    </main>

    <div id="purchaseModal" class="modal fade br-entity-modal br-modal-standard" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header">
                    <div>
                        <p class="br-entity-modal__eyebrow mb-1">Compras</p>
                        <h2 class="modal-title br-entity-modal__title">Registrar compra</h2>
                        <p class="br-purchases__modal-help">El stock cambiará únicamente cuando registres la recepción.</p>
                    </div>
                    <button type="button" class="br-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-standard__body">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label">Proveedor</label>
                            <v-select v-model="purchaseForm.supplier" :options="suppliers" :clearable="false" searchable/>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Almacén de recepción</label>
                            <v-select v-model="purchaseForm.warehouse" :options="warehouses" :clearable="false" searchable/>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Moneda</label>
                            <v-select v-model="purchaseForm.currency" :options="currencies" :clearable="false" :searchable="false"/>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Tipo de documento</label>
                            <v-select v-model="purchaseForm.documentType" :options="documentTypes" :clearable="false" :searchable="false"/>
                        </div>
                        <div class="col-lg-3">
                            <InputText v-model="purchaseForm.documentNumber" title="Número de documento" :titleClass="[config.forms.classes.title]"/>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Fecha de emisión</label>
                            <input v-model="purchaseForm.issueDate" type="date" class="form-control">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Fecha esperada</label>
                            <input v-model="purchaseForm.expectedDate" type="date" class="form-control">
                        </div>
                    </div>

                    <div class="br-purchases__items">
                        <div class="br-purchases__items-header">
                            <div>
                                <strong>Productos y costos</strong>
                                <small>El costo unitario alimentará la valorización cuando recibas la mercadería.</small>
                            </div>
                            <button type="button" class="br-btn br-btn-sm br-btn-outline-secondary" @click="addPurchaseItem">
                                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                <span>Agregar producto</span>
                            </button>
                        </div>
                        <div v-for="(detail, index) in purchaseForm.items" :key="detail.key" class="br-purchases__item-row">
                            <div>
                                <label class="form-label">Producto {{ index + 1 }}</label>
                                <v-select
                                    v-model="detail.product"
                                    :options="purchaseProductOptions(index)"
                                    :clearable="false"
                                    searchable
                                    append-to-body/>
                            </div>
                            <InputNumber v-model="detail.quantity" title="Cantidad" :titleClass="[config.forms.classes.title]" :hasNegative="false"/>
                            <InputNumber v-model="detail.unitCost" title="Costo unitario" :titleClass="[config.forms.classes.title]" :hasNegative="false">
                                <template #inputGroupPrepend>
                                    <span class="input-group-text br-currency-prefix">{{ purchaseForm.currency?.sign || "S/" }}</span>
                                </template>
                            </InputNumber>
                            <div class="br-purchases__line-total">
                                <span>Subtotal</span>
                                <strong>{{ purchaseForm.currency?.sign }} {{ separatorNumber(lineTotal(detail)) }}</strong>
                            </div>
                            <button
                                type="button"
                                class="br-icon-action br-purchases__remove"
                                :disabled="purchaseForm.items.length === 1"
                                title="Quitar producto"
                                @click="removePurchaseItem(index)">
                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-lg-8">
                            <label class="form-label">Observación</label>
                            <textarea v-model.trim="purchaseForm.observation" class="form-control" rows="2" maxlength="1000"></textarea>
                        </div>
                        <div class="col-lg-4">
                            <div class="br-document-settlement">
                                <div>
                                    <label class="form-label">Impuestos</label>
                                    <v-select
                                        v-model="purchaseForm.taxes"
                                        :options="purchaseTaxes"
                                        multiple
                                        :clearable="true"
                                        :searchable="true"
                                        append-to-body/>
                                </div>
                                <div>
                                    <label class="form-label">Métodos de pago</label>
                                    <v-select
                                        v-model="purchaseForm.payments"
                                        :options="purchasePaymentMethods"
                                        multiple
                                        :clearable="false"
                                        :searchable="true"
                                        append-to-body/>
                                </div>
                                <div class="br-purchases__total">
                                    <span>Subtotal</span>
                                    <strong>{{ purchaseForm.currency?.sign }} {{ separatorNumber(purchaseSubtotal) }}</strong>
                                    <span>Impuestos</span>
                                    <strong>{{ purchaseForm.currency?.sign }} {{ separatorNumber(purchaseTaxTotal) }}</strong>
                                    <span>Total</span>
                                    <strong>{{ purchaseForm.currency?.sign }} {{ separatorNumber(purchaseTotal) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer br-entity-modal__footer">
                    <button ref="closePurchaseModal" type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-action-create" :disabled="saving" @click="savePurchase">Registrar compra</button>
                </div>
            </div>
        </div>
    </div>

    <div id="purchaseReceiptModal" class="modal fade br-entity-modal br-modal-standard" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header">
                    <div>
                        <p class="br-entity-modal__eyebrow mb-1">Compras</p>
                        <h2 class="modal-title br-entity-modal__title">Recibir mercadería</h2>
                        <p class="br-purchases__modal-help">Solo las cantidades recibidas ingresarán al inventario.</p>
                    </div>
                    <button type="button" class="br-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-standard__body">
                    <div v-if="loadingReceipt" class="py-4"><Loader/></div>
                    <template v-else>
                        <div class="br-purchases__receipt-summary">
                            <strong>{{ receiptForm.purchase?.supplier?.name }}</strong>
                            <span>{{ receiptForm.purchase?.warehouse?.name }}</span>
                        </div>
                        <div class="br-purchases__receipt-items">
                            <div v-for="detail in receiptForm.items" :key="detail.purchaseItemId" class="br-purchases__receipt-row">
                                <div>
                                    <strong>{{ detail.name }}</strong>
                                    <span>Pendiente: {{ separatorNumber(detail.remaining) }}</span>
                                </div>
                                <InputNumber v-model="detail.quantity" title="Cantidad recibida" :titleClass="[config.forms.classes.title]" :hasNegative="false"/>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-5">
                                <label class="form-label">Fecha y hora de recepción</label>
                                <input v-model="receiptForm.receivedAt" type="datetime-local" class="form-control">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Observación</label>
                                <textarea v-model.trim="receiptForm.observation" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer br-entity-modal__footer">
                    <button ref="closeReceiptModal" type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-primary" :disabled="savingReceipt || loadingReceipt" @click="saveReceipt">Confirmar recepción</button>
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
    async mounted() {
        Utils.navbarItem("menu-parent-purchases", {addClass: "open"});
        Utils.navbarItem("menu-purchases-list", {});
        Alerts.swals({type: "initParams"});
        await this.initParams();
        Alerts.swals({show: false});
        await this.listPurchases({});
    },
    data() {
        return {
            loading: false,
            saving: false,
            loadingReceipt: false,
            savingReceipt: false,
            exporting: false,
            records: {total: 0, data: []},
            filters: {
                word: "",
                status: {code: "", label: "Todos los estados"}
            },
            options: {suppliers: [], warehouses: [], currencies: [], products: [], taxes: [], paymentMethods: []},
            purchaseForm: {},
            receiptForm: {purchase: null, items: [], receivedAt: "", observation: ""},
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "purchases"}),
                    page: {title: "Compras", active: true, menu: {id: "menu-purchases-list"}}
                }
            }
        };
    },
    methods: {
        async initParams() {
            const result = await Requests.get({
                route: this.config.entity.routes.initParams,
                data: {page: "main"},
                showAlert: true
            });
            const config = result.data?.config || {};
            this.options.suppliers = config.suppliers?.records || [];
            this.options.warehouses = config.warehouses?.records || [];
            this.options.currencies = config.currencies?.records || [];
            this.options.products = config.products?.records || [];
        },
        async listPurchases({url = null} = {}) {
            this.loading = true;
            const result = await Requests.get({
                route: url || this.config.entity.routes.list,
                data: {word: this.filters.word, status: this.filters.status?.code || ""}
            });
            this.records = result.data || {total: 0, data: []};
            this.loading = false;
        },
        newPurchaseItem() {
            return {key: `${Date.now()}-${Math.random()}`, product: null, quantity: "", unitCost: ""};
        },
        preparePurchase() {
            this.purchaseForm = {
                supplier: this.suppliers[0] || null,
                warehouse: this.warehouses[0] || null,
                currency: this.currencies[0] || null,
                documentType: this.documentTypes[0],
                documentNumber: "",
                issueDate: new Date().toISOString().slice(0, 10),
                expectedDate: "",
                taxes: this.purchaseTaxes.filter(tax => tax.data?.is_default),
                payments: this.purchasePaymentMethods.filter(method => method.data?.is_default),
                observation: "",
                items: [this.newPurchaseItem()]
            };
        },
        addPurchaseItem() {
            if(this.purchaseForm.items.length < 100) this.purchaseForm.items.push(this.newPurchaseItem());
        },
        removePurchaseItem(index) {
            if(this.purchaseForm.items.length > 1) this.purchaseForm.items.splice(index, 1);
        },
        purchaseProductOptions(index) {
            const selected = this.purchaseForm.items
                .filter((_, itemIndex) => itemIndex !== index)
                .map(item => Number(item.product?.code))
                .filter(Boolean);
            return this.products.filter(product => !selected.includes(Number(product.code)));
        },
        lineTotal(detail) {
            return Number(detail.quantity || 0) * Number(detail.unitCost || 0);
        },
        async savePurchase() {
            this.saving = true;
            Alerts.swals({type: "loading", message: "Registrando compra"});
            const result = await Requests.post({
                route: this.config.entity.routes.store,
                data: {
                    supplier_id: this.purchaseForm.supplier?.code,
                    warehouse_id: this.purchaseForm.warehouse?.code,
                    currency_id: this.purchaseForm.currency?.code,
                    document_type: this.purchaseForm.documentType?.code,
                    document_number: this.purchaseForm.documentNumber,
                    issue_date: this.purchaseForm.issueDate,
                    expected_date: this.purchaseForm.expectedDate || null,
                    tax: this.purchaseTaxTotal,
                    taxes: this.purchaseForm.taxes.map(tax => ({
                        tax_id: tax.code,
                        rate: tax.data?.rate
                    })),
                    payments: this.purchasePaymentPayload,
                    observation: this.purchaseForm.observation,
                    items: this.purchaseForm.items.map(item => ({
                        item_id: item.product?.code,
                        quantity: item.quantity,
                        unit_cost: item.unitCost
                    }))
                }
            });
            this.saving = false;
            Alerts.swals({show: false});
            if(Requests.valid({result})) {
                this.$refs.closePurchaseModal?.click();
                Alerts.generateAlert({type: "success", msgContent: result.data.msg});
                await this.listPurchases({});
                return;
            }
            Alerts.generateAlert({type: "error", messages: this.responseErrors(result)});
        },
        async prepareReceipt(id) {
            this.loadingReceipt = true;
            const result = await Requests.get({route: `${this.config.entity.routes.consult}/${id}`});
            this.loadingReceipt = false;

            if(!result.bool) {
                Alerts.generateAlert({
                    type: "error",
                    msgContent: result.data?.msg || "No se pudo consultar la compra."
                });
                return;
            }

            const purchase = result.data;
            this.receiptForm = {
                purchase,
                receivedAt: new Date().toISOString().slice(0, 16),
                observation: "",
                items: (purchase.items || [])
                    .filter(item => Number(item.remaining_quantity) > 0)
                    .map(item => ({
                        purchaseItemId: item.id,
                        name: item.name,
                        remaining: Number(item.remaining_quantity),
                        quantity: Number(item.remaining_quantity)
                    }))
            };
        },
        async saveReceipt() {
            this.savingReceipt = true;
            Alerts.swals({type: "loading", message: "Registrando recepción"});
            const result = await Requests.post({
                route: `${this.config.entity.routes.consult}/${this.receiptForm.purchase.id}/receive`,
                data: {
                    received_at: this.receiptForm.receivedAt,
                    observation: this.receiptForm.observation,
                    items: this.receiptForm.items
                        .filter(item => Number(item.quantity) > 0)
                        .map(item => ({
                            purchase_item_id: item.purchaseItemId,
                            quantity: item.quantity
                        }))
                }
            });
            this.savingReceipt = false;
            Alerts.swals({show: false});
            if(Requests.valid({result})) {
                this.$refs.closeReceiptModal?.click();
                Alerts.generateAlert({type: "success", msgContent: result.data.msg});
                await this.listPurchases({});
                return;
            }
            Alerts.generateAlert({type: "error", messages: this.responseErrors(result)});
        },
        async cancelPurchase(purchase) {
            const accepted = await Alerts.generateAlert({
                type: "question",
                msgContent: `¿Deseas anular ${purchase.formatted_document_type.toLowerCase()}?`
            });
            if(!accepted?.isConfirmed) return;
            Alerts.swals({type: "loading", message: "Anulando compra"});
            const result = await Requests.patch({
                route: this.config.entity.routes.consult,
                id: `${purchase.id}/cancel`,
                data: {}
            });
            Alerts.swals({show: false});
            if(Requests.valid({result})) {
                Alerts.generateAlert({type: "success", msgContent: result.data.msg});
                await this.listPurchases({});
                return;
            }
            Alerts.generateAlert({type: "error", msgContent: result.data?.msg});
        },
        async exportPurchases() {
            if(this.exporting) return;
            this.exporting = true;
            Alerts.swals({type: "loading", message: "Preparando reporte"});
            const result = await Requests.download({
                route: this.config.entity.routes.export,
                data: {word: this.filters.word, status: this.filters.status?.code || ""},
                fileName: "compras.xlsx"
            });
            Alerts.swals({show: false});
            this.exporting = false;

            if(!result.bool) {
                Alerts.generateAlert({
                    type: "error",
                    msgContent: result.data?.msg || "No se pudo descargar el reporte."
                });
            }
        },
        responseErrors(result) {
            const errors = Object.values(result?.errors || result?.data?.errors || {}).flat();
            return errors.length ? errors : [result?.data?.msg || "No se pudo completar la operación."];
        },
        formatDate(value) {
            if(!value) return "";
            return new Intl.DateTimeFormat("es-PE").format(new Date(`${value}T00:00:00`));
        },
        separatorNumber(value) {
            return Utils.separatorNumber(value || 0);
        }
    },
    computed: {
        breadcrumbTitles() {
            return [{title: "Compras"}, this.config.entity.page];
        },
        statuses() {
            return [
                {code: "", label: "Todos los estados"},
                {code: "confirmed", label: "Pendiente de recepción"},
                {code: "partial", label: "Recepción parcial"},
                {code: "received", label: "Recibida"},
                {code: "canceled", label: "Anulada"}
            ];
        },
        documentTypes() {
            return [
                {code: "order", label: "Orden de compra"},
                {code: "invoice", label: "Factura de compra"}
            ];
        },
        suppliers() {
            return this.options.suppliers.map(record => ({code: record.id, label: record.name}));
        },
        warehouses() {
            return this.options.warehouses.map(record => ({
                code: record.id,
                label: `${record.branch?.name ? `${record.branch.name} - ` : ""}${record.name}`
            }));
        },
        currencies() {
            return this.options.currencies.map(record => ({
                code: record.id,
                sign: record.sign,
                label: `${record.code} - ${record.sign}`
            }));
        },
        products() {
            return this.options.products.map(record => ({
                code: record.id,
                label: `${record.name} · ${record.internal_code}`
            }));
        },
        purchaseSubtotal() {
            return (this.purchaseForm.items || []).reduce((total, detail) => total + this.lineTotal(detail), 0);
        },
        purchaseTaxTotal() {
            return (this.purchaseForm.taxes || []).reduce((total, tax) => {
                return total + (this.purchaseSubtotal * (Number(tax.data?.rate || 0) / 100));
            }, 0);
        },
        purchaseTotal() {
            return this.purchaseSubtotal + this.purchaseTaxTotal;
        },
        purchaseTaxes() {
            return (this.options.taxes?.records || []).map(tax => ({
                code: tax.id,
                label: `${tax.name} (${Number(tax.rate).toFixed(2)}%)`,
                data: tax
            }));
        },
        purchasePaymentMethods() {
            return (this.options.paymentMethods?.records || []).map(method => ({
                code: method.id,
                label: method.name,
                data: method
            }));
        },
        purchasePaymentPayload() {
            const selected = this.purchaseForm.payments || [];

            if(selected.length === 0) return [];

            const amount = Number((this.purchaseTotal / selected.length).toFixed(2));
            let remainder = Number((this.purchaseTotal - (amount * selected.length)).toFixed(2));

            return selected.map((method, index) => {
                const lineAmount = index === 0 ? amount + remainder : amount;

                return {
                    payment_method_id: method.code,
                    amount: lineAmount,
                    reference: null,
                    note: null
                };
            });
        }
    }
};
</script>
