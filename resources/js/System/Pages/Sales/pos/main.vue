<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <section class="br-pos">
        <main class="br-pos__catalog">

            <Loader v-if="loading"/>
            <template v-else>
                <nav class="br-pos-categories" aria-label="Categorías">
                    <button
                        type="button"
                        class="br-pos-category"
                        :class="{'is-active': selectedCategoryId === null}"
                        @click="selectedCategoryId = null">
                        <span>
                            <strong>Todos</strong>
                            <small>{{ items.length }} disponibles</small>
                        </span>
                    </button>
                    <button
                        v-for="category in visibleCategories"
                        :key="category.id"
                        type="button"
                        class="br-pos-category"
                        :class="{'is-active': selectedCategoryId === category.id}"
                        @click="selectedCategoryId = category.id">
                        <span>
                            <strong>{{ category.name }}</strong>
                            <small>{{ countByCategory(category.id) }} disponibles</small>
                        </span>
                    </button>
                </nav>
                <div class="br-pos-search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input
                        v-model.trim="search"
                        type="search"
                        class="form-control"
                        placeholder="Buscar producto, código o marca"
                        aria-label="Buscar producto">
                </div>

                <section class="br-pos-products" aria-label="Productos">
                    <article
                        v-for="item in filteredItems"
                        :key="item.id"
                        class="br-pos-product"
                        :style="itemCardStyle(item)">
                        <div class="br-pos-product__body">
                            <span class="br-pos-product__image" :style="itemImageStyle(item)" aria-hidden="true">
                                <i :class="itemIcon(item)"></i>
                            </span>
                            <span class="br-pos-product__info">
                                <strong>{{ item.name }}</strong>
                                <small v-if="item.brand?.name" class="br-pos-product__brand">
                                    <span>Marca</span>
                                    <b>{{ item.brand.name }}</b>
                                </small>
                                <small v-else class="br-pos-product__description">
                                    {{ item.description || 'Producto disponible' }}
                                </small>
                                <span v-if="item.internal_code || item.barcode" class="br-pos-product__codes">
                                    <span v-if="item.internal_code" class="is-internal">
                                        <b>Int.</b>
                                        <em>{{ item.internal_code }}</em>
                                    </span>
                                    <span v-if="item.barcode" class="is-barcode">
                                        <b>Bar.</b>
                                        <em>{{ item.barcode }}</em>
                                    </span>
                                </span>
                            </span>
                        </div>
                        <footer class="br-pos-product__footer">
                            <strong>{{ currencySign(item) }} {{ separatorNumber(item.price) }}</strong>
                            <div class="br-pos-product__actions">
                                <button
                                    type="button"
                                    class="br-pos-detail"
                                    data-bs-toggle="tooltip"
                                    title="Ver detalle"
                                    @click="openItemDetail(item)"
                                    aria-label="Ver detalle del catálogo">
                                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                </button>
                                <button
                                    v-if="quantityInCart(item.id)"
                                    type="button"
                                    class="br-pos-remove br-btn-danger"
                                    @click="decreaseItem(item.id)"
                                    aria-label="Quitar del detalle">
                                    <i class="fa-solid fa-minus" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="br-pos-add" @click="addItem(item)" aria-label="Agregar al detalle">
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                    <span v-if="quantityInCart(item.id)">{{ quantityInCart(item.id) }}</span>
                                </button>
                            </div>
                        </footer>
                    </article>
                </section>
                <WithoutData v-if="!filteredItems.length"/>
            </template>
        </main>

        <aside class="br-pos-ticket" aria-label="Resumen de venta">
            <section class="br-pos-ticket__config">
                <div v-if="!hasOpenCashSessions" class="br-pos-alert br-pos-alert--danger mb-0">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <span>Abre una caja para registrar ventas POS.</span>
                </div>
                <div v-else-if="hasSingleCashSession" class="br-pos-cash-static">
                    <span>Caja activa</span>
                    <strong>{{ selectedCashSession?.register?.name || 'Caja' }}</strong>
                    <small>{{ selectedCashSession?.branch?.name || 'Sucursal no definida' }}</small>
                </div>
                <label v-else>
                    <span>Caja</span>
                    <v-select
                        v-model="selectedCashSession"
                        :options="cashSessionOptions"
                        class="bg-white"
                        :clearable="true"
                        :searchable="false"
                        placeholder="Seleccione caja abierta"
                        @option:selected="syncFromCashSession"/>
                </label>
                <label v-if="hasOpenCashSessions && showWarehouseInput">
                    <span>Almacén</span>
                    <v-select
                        v-model="selectedWarehouse"
                        :options="warehouseOptions"
                        class="bg-white"
                        :clearable="false"
                        :searchable="false"
                        placeholder="Seleccione almacén"/>
                </label>
                <label v-if="hasOpenCashSessions">
                    <span class="br-pos-field-label">
                        <span>Cliente</span>
                        <AddCustomer
                            triggerLabel="Agregar"
                            :options="customerQuickCreateOptions"
                            @postAction="addCustomerPostAction"/>
                    </span>
                    <v-select
                        v-model="selectedCustomer"
                        :options="customerOptions"
                        class="bg-white"
                        :clearable="false"
                        :searchable="true"
                        placeholder="Seleccione cliente"/>
                </label>
            </section>

            <section class="br-pos-total-box" aria-label="Total de venta">
                <div class="br-pos-ticket__totals">
                    <div>
                        <span>Subtotal</span>
                        <strong>S/ {{ separatorNumber(subtotal) }}</strong>
                    </div>
                    <div v-for="tax in taxSummary" :key="tax.tax_id">
                        <span>{{ tax.name }} {{ separatorNumber(tax.rate) }}%</span>
                        <strong>S/ {{ separatorNumber(tax.amount) }}</strong>
                    </div>
                    <div v-if="!taxSummary.length">
                        <span>Impuestos</span>
                        <strong>S/ 0.00</strong>
                    </div>
                </div>

                <header class="br-pos-ticket__header">
                    <div>
                        <p>Total</p>
                        <h2>S/ {{ separatorNumber(total) }}</h2>
                        <span>{{ totalQuantity }} ítems agregados</span>
                    </div>
                    <button
                        v-if="hasOpenCashSessions"
                        type="button"
                        class="br-icon-action br-btn-danger"
                        data-bs-toggle="tooltip"
                        title="Limpiar venta"
                        :disabled="!cart.length"
                        @click="clearCart">
                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                    </button>
                </header>
            </section>

            <section class="br-pos-ticket__items">
                <article v-for="line in cart" :key="line.item.id" class="br-pos-ticket-item">
                    <div>
                        <strong>{{ line.item.name }}</strong>
                        <small><b>{{ line.quantity }}</b> x <b>{{ currencySign(line.item) }} {{ separatorNumber(line.price) }}</b></small>
                    </div>
                    <div class="br-pos-ticket-item__side">
                        <strong>{{ currencySign(line.item) }} {{ separatorNumber(lineTotal(line)) }}</strong>
                        <div class="br-pos-ticket-item__qty">
                            <button type="button" class="is-danger br-btn-danger" @click="decreaseItem(line.item.id)">-</button>
                            <span>{{ line.quantity }}</span>
                            <button type="button" @click="addItem(line.item)">+</button>
                        </div>
                    </div>
                </article>
            </section>

            <button
                v-if="hasOpenCashSessions && canSubmit"
                type="button"
                class="br-btn br-btn-success br-pos-ticket__pay"
                :disabled="saving || !canSubmit"
                @click="openSaleConfirmation">
                <i class="fa-solid fa-cash-register" aria-hidden="true"></i>
                <span>Revisar venta</span>
            </button>
        </aside>
    </section>

    <div
        class="modal fade br-entity-modal br-pos-detail-modal"
        id="brPosItemDetailModal"
        data-bs-backdrop="static"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="brPosItemDetailModalTitle">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header">
                    <div>
                        <p class="br-entity-modal__eyebrow mb-1">Catálogo comercial</p>
                        <h2 id="brPosItemDetailModalTitle" class="modal-title br-entity-modal__title">
                            {{ selectedItemDetail?.name || 'Detalle' }}
                        </h2>
                    </div>
                    <button type="button" class="br-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="modal-body br-entity-modal__body br-pos-detail-modal__body">
                    <div class="br-pos-detail-modal__hero" :style="itemCardStyle(selectedItemDetail || {})">
                        <span class="br-pos-product__image" :style="itemImageStyle(selectedItemDetail || {})" aria-hidden="true">
                            <i :class="itemIcon(selectedItemDetail || {})"></i>
                        </span>
                        <div>
                            <strong>{{ itemTypeLabel(selectedItemDetail) }}</strong>
                            <span>{{ currencySign(selectedItemDetail) }} {{ separatorNumber(selectedItemDetail?.price) }}</span>
                        </div>
                    </div>

                    <dl class="br-pos-detail-modal__list">
                        <div>
                            <dt>Marca</dt>
                            <dd>{{ selectedItemDetail?.brand?.name || 'Sin marca' }}</dd>
                        </div>
                        <div>
                            <dt>Descripción</dt>
                            <dd>{{ selectedItemDetail?.description || 'Sin descripción registrada' }}</dd>
                        </div>
                        <div>
                            <dt>Categorías</dt>
                            <dd>{{ formattedItemCategories(selectedItemDetail) }}</dd>
                        </div>
                        <div>
                            <dt>Código interno</dt>
                            <dd>{{ selectedItemDetail?.internal_code || 'No registrado' }}</dd>
                        </div>
                        <div>
                            <dt>Código de barras</dt>
                            <dd>{{ selectedItemDetail?.barcode || 'No registrado' }}</dd>
                        </div>
                        <div>
                            <dt>Incluye IGV</dt>
                            <dd>{{ boolLabel(includesTax(selectedItemDetail)) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="modal-footer br-entity-modal__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="br-btn br-btn-primary" @click="addItem(selectedItemDetail)" data-bs-dismiss="modal">
                        <span>Agregar al detalle</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        class="modal fade br-entity-modal br-pos-sale-modal"
        id="brPosSaleConfirmationModal"
        data-bs-backdrop="static"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="brPosSaleConfirmationModalTitle">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header">
                    <div>
                        <p class="br-entity-modal__eyebrow mb-1">Venta POS</p>
                        <h2 id="brPosSaleConfirmationModalTitle" class="modal-title br-entity-modal__title">
                            Confirmar venta
                        </h2>
                    </div>
                    <button type="button" class="br-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="modal-body br-entity-modal__body br-pos-sale-modal__body">
                    <section class="br-pos-sale-summary" aria-label="Resumen de importes">
                        <div>
                            <span>Subtotal</span>
                            <strong>S/ {{ separatorNumber(subtotal) }}</strong>
                        </div>
                        <div v-for="tax in taxSummary" :key="`summary-${tax.tax_id}`">
                            <span>{{ tax.name }} {{ separatorNumber(tax.rate) }}%</span>
                            <strong>S/ {{ separatorNumber(tax.amount) }}</strong>
                        </div>
                        <div v-if="!taxSummary.length">
                            <span>Impuestos</span>
                            <strong>S/ 0.00</strong>
                        </div>
                        <div class="is-total">
                            <span>Total</span>
                            <strong>S/ {{ separatorNumber(total) }}</strong>
                        </div>
                    </section>

                    <section class="br-pos-sale-payments" aria-label="Pagos de la venta">
                        <header>
                            <div>
                                <strong>Métodos de pago</strong>
                                <small>{{ showPaymentEditor ? 'Ajusta los importes antes de confirmar si el pago es mixto.' : 'Revisa cómo se registrará el pago de esta venta.' }}</small>
                            </div>
                            <button type="button" class="br-pos-payments__add" @click="showPaymentEditor = !showPaymentEditor">
                                <i class="fa-solid" :class="showPaymentEditor ? 'fa-eye-slash' : 'fa-pen-to-square'" aria-hidden="true"></i>
                                <span>{{ showPaymentEditor ? 'Ocultar edición' : 'Cambiar método de pago' }}</span>
                            </button>
                        </header>

                        <div v-if="!showPaymentEditor" class="br-pos-payments-summary">
                            <div v-for="payment in paymentSummary" :key="`confirm-summary-${payment.uid}`">
                                <span>{{ payment.label }}</span>
                                <strong>S/ {{ separatorNumber(payment.amount) }}</strong>
                            </div>
                        </div>

                        <div v-else class="br-pos-payments-editor">
                            <article v-for="(payment, index) in payments" :key="`confirm-${payment.uid}`" class="br-pos-payment">
                                <v-select
                                    v-model="payment.method"
                                    :options="paymentMethodOptions"
                                    class="bg-white"
                                    :clearable="false"
                                    :searchable="true"
                                    placeholder="Método"/>
                                <InputNumber
                                    v-model="payment.amount"
                                    title=""
                                    hasDiv
                                    :minValue="0"/>
                                <button
                                    type="button"
                                    class="br-pos-payment__remove br-btn-danger"
                                    :disabled="payments.length === 1"
                                    @click="removePayment(index)"
                                    aria-label="Quitar método de pago">
                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                </button>
                            </article>

                            <button type="button" class="br-pos-payments__add br-pos-payments__add-row" @click="addPayment(false)">
                                <i class="fa-solid fa-circle-plus" aria-hidden="true"></i>
                                <span>Agregar método</span>
                            </button>
                        </div>

                        <p v-if="paymentDifference !== 0" class="br-pos-payments__difference">
                            Falta cuadrar S/ {{ separatorNumber(Math.abs(paymentDifference)) }}
                        </p>
                    </section>
                </div>

                <div class="modal-footer br-entity-modal__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Volver</button>
                    <button
                        type="button"
                        class="br-btn br-btn-success"
                        :disabled="saving || !canSubmit"
                        @click="confirmSale">
                        <span>{{ saving ? 'Confirmando venta' : 'Confirmar venta' }}</span>
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
        return {
            config: Requests.config({entity: "sales"}),
            loading: false,
            saving: false,
            search: "",
            showPaymentEditor: false,
            selectedCategoryId: null,
            branches: [],
            warehouses: [],
            customers: [],
            customerConfig: {},
            items: [],
            categories: [],
            currencies: [],
            taxes: [],
            paymentMethods: [],
            cashSessions: [],
            selectedBranch: null,
            selectedSerie: null,
            selectedWarehouse: null,
            selectedCustomer: null,
            selectedPaymentMethod: null,
            selectedCashSession: null,
            selectedItemDetail: null,
            payments: [],
            cart: []
        };
    },
    computed: {
        breadcrumbTitles() {
            return [{title: "Ventas"}, {title: "Venta POS", active: true}];
        },
        branchOptions() {
            return this.branches.map(branch => ({...branch, label: branch.name}));
        },
        warehouseOptions() {
            const branchId = this.selectedBranch?.id;
            return this.warehouses
                .filter(warehouse => !branchId || warehouse.branch_id === branchId)
                .map(warehouse => ({...warehouse, label: `${warehouse.branch?.name || 'Sucursal'} - ${warehouse.name}`}));
        },
        customerOptions() {
            return this.customers.map(customer => ({...customer, label: customer.name}));
        },
        customerQuickCreateOptions() {
            return {
                identityDocumentTypes: this.customerConfig?.identityDocumentTypes || [],
                genders: this.customerConfig?.genders || [],
                statuses: this.customerConfig?.statuses || []
            };
        },
        paymentMethodOptions() {
            return this.paymentMethods.map(method => ({...method, label: method.name}));
        },
        cashSessionOptions() {
            return this.cashSessions
                .map(session => ({
                    ...session,
                    label: `${session.register?.name || 'Caja'} - ${session.branch?.name || 'Sucursal'}`
                }));
        },
        hasOpenCashSessions() {
            return this.cashSessionOptions.length > 0;
        },
        hasSingleCashSession() {
            return this.cashSessionOptions.length === 1;
        },
        showWarehouseInput() {
            return this.warehouseOptions.length > 1;
        },
        visibleCategories() {
            return this.categories.filter(category => this.countByCategory(category.id) > 0);
        },
        filteredItems() {
            const text = this.search.toLowerCase();

            return this.items.filter(item => {
                const matchesCategory = this.selectedCategoryId === null || this.itemCategoryIds(item).includes(this.selectedCategoryId);
                const matchesSearch = !text || [
                    item.name,
                    item.internal_code,
                    item.barcode,
                    item.brand?.name,
                    item.description
                ].some(value => String(value || "").toLowerCase().includes(text));

                return matchesCategory && matchesSearch;
            });
        },
        totalQuantity() {
            return this.cart.reduce((total, line) => total + Number(line.quantity || 0), 0);
        },
        subtotal() {
            return this.cart.reduce((total, line) => total + this.lineTotal(line), 0);
        },
        taxableSubtotal() {
            return this.cart.reduce((total, line) => {
                return total + (this.includesTax(line.item) ? 0 : this.lineTotal(line));
            }, 0);
        },
        taxSummary() {
            return this.taxes.map(tax => {
                const rate = Number(tax.rate || tax.percentage || 0);
                const amount = this.taxableSubtotal * (rate / 100);

                return {
                    tax_id: tax.id,
                    name: tax.name,
                    rate,
                    amount
                };
            }).filter(tax => tax.amount > 0);
        },
        totalTax() {
            return this.taxSummary.reduce((total, tax) => total + tax.amount, 0);
        },
        total() {
            return this.subtotal + this.totalTax;
        },
        paymentsTotal() {
            return this.payments.reduce((total, payment) => total + Number(payment.amount || 0), 0);
        },
        paymentSummary() {
            return this.payments
                .filter(payment => payment.method)
                .map(payment => ({
                    uid: payment.uid,
                    label: payment.method?.label || payment.method?.name || "Método de pago",
                    amount: Number(payment.amount || 0)
                }));
        },
        paymentDifference() {
            return Number((this.total - this.paymentsTotal).toFixed(2));
        },
        canSubmit() {
            return this.cart.length > 0
                && this.hasOpenCashSessions
                && this.selectedBranch
                && this.selectedSerie
                && this.selectedWarehouse
                && this.selectedCustomer
                && this.selectedCashSession
                && this.payments.length > 0
                && this.payments.every(payment => payment.method && Number(payment.amount || 0) > 0)
                && this.paymentDifference === 0;
        }
    },
    watch: {
        total() {
            this.syncDefaultPaymentAmount();
        }
    },
    mounted() {
        Utils.navbarItem("menu-parent-sales", {addClass: "open"});
        Utils.navbarItem("menu-sales-pos", {addClass: "active"});
        this.initParams();
    },
    methods: {
        async initParams() {
            this.loading = true;
            const result = await Requests.get({route: this.config.routes.initParams});
            this.loading = false;

            if(!Requests.valid({result})) {
                Alerts.toastrs({type: "error", subtitle: "No fue posible cargar el POS."});
                return;
            }

            const data = result.data.config;
            this.branches = data.branches?.records || [];
            this.warehouses = data.warehouses?.records || [];
            this.customerConfig = data.customers || {};
            this.customers = data.customers?.records || [];
            this.items = data.items?.records || [];
            this.categories = data.categories?.records || [];
            this.currencies = data.currencies?.records || [];
            this.taxes = data.taxes?.records || [];
            this.paymentMethods = data.paymentMethods?.records || [];
            this.cashSessions = data.cashSessions?.records || [];

            this.selectedBranch = this.branchOptions[0] || null;
            this.selectedCustomer = this.customerOptions[0] || null;
            this.selectedPaymentMethod = this.paymentMethodOptions[0] || null;
            this.selectedCashSession = this.cashSessionOptions[0] || null;
            this.syncFromCashSession();
            this.resetPayments();
            Alerts.tooltips({});
        },
        syncBranchDependents() {
            this.selectedSerie = this.selectedBranch?.series?.[0] || null;
            this.selectedWarehouse = this.warehouseOptions[0] || null;
            this.selectedCashSession = this.cashSessionOptions[0] || null;
        },
        syncFromCashSession() {
            const branchId = this.selectedCashSession?.branch_id;

            this.selectedBranch = this.branchOptions.find(branch => branch.id === branchId) || this.selectedBranch || this.branchOptions[0] || null;
            this.selectedSerie = this.selectedBranch?.series?.[0] || null;
            this.selectedWarehouse = this.warehouseOptions[0] || null;
        },
        defaultPaymentMethod() {
            return this.paymentMethodOptions.find(method => {
                const name = String(method.name || method.label || "").toLowerCase();
                const code = String(method.code || "").toLowerCase();

                return method.is_default || code.includes("cash") || name.includes("efectivo");
            }) || this.paymentMethodOptions[0] || null;
        },
        resetPayments() {
            this.payments = [{
                uid: Date.now(),
                method: this.defaultPaymentMethod(),
                amount: Number(this.total.toFixed(2))
            }];
            this.showPaymentEditor = false;
        },
        syncDefaultPaymentAmount() {
            if(this.payments.length !== 1) return;

            this.payments[0].amount = Number(this.total.toFixed(2));
        },
        addPayment(openEditor = true) {
            if(openEditor) {
                this.showPaymentEditor = true;
            }

            this.payments.push({
                uid: Date.now() + this.payments.length,
                method: this.paymentMethodOptions.find(method => !this.payments.some(payment => payment.method?.id === method.id)) || this.paymentMethodOptions[0] || null,
                amount: Math.max(Number(this.paymentDifference.toFixed(2)), 0)
            });
        },
        removePayment(index) {
            if(this.payments.length === 1) return;

            this.payments.splice(index, 1);
            this.syncDefaultPaymentAmount();
        },
        itemCategoryIds(item) {
            return (item.category_items || item.categoryItems || [])
                .map(row => row.category_id || row.category?.id)
                .filter(Boolean);
        },
        countByCategory(categoryId) {
            return this.items.filter(item => this.itemCategoryIds(item).includes(categoryId)).length;
        },
        addItem(item) {
            const line = this.cart.find(row => row.item.id === item.id);

            if(line) {
                line.quantity = Number(line.quantity) + 1;
                return;
            }

            this.cart.push({
                item,
                quantity: 1,
                price: Number(item.price || 0)
            });
        },
        decreaseItem(itemId) {
            const line = this.cart.find(row => row.item.id === itemId);
            if(!line) return;

            line.quantity = Number(line.quantity) - 1;

            if(line.quantity <= 0) {
                this.cart = this.cart.filter(row => row.item.id !== itemId);
            }
        },
        quantityInCart(itemId) {
            return this.cart.find(row => row.item.id === itemId)?.quantity || 0;
        },
        clearCart() {
            this.cart = [];
            this.resetPayments();
        },
        lineTotal(line) {
            return Number(line.quantity || 0) * Number(line.price || 0);
        },
        currencySign(item = null) {
            return item?.currency?.sign || this.currencies[0]?.sign || "S/";
        },
        separatorNumber(value) {
            return Number(value || 0).toLocaleString("es-PE", {minimumFractionDigits: 2, maximumFractionDigits: 2});
        },
        today() {
            return new Date().toISOString().slice(0, 10);
        },
        includesTax(item) {
            return [true, 1, "1", "true"].includes(item?.price_includes_tax);
        },
        itemIcon(item) {
            if(item?.type === "service") return "fa-solid fa-dumbbell";
            if(item?.type === "subscription") return "fa-solid fa-id-card";

            return "fa-solid fa-box-open";
        },
        itemImageStyle(item) {
            if(item?.type === "service") {
                return {
                    background: "color-mix(in srgb, #fef3c7 78%, #ffffff)",
                    color: "#b45309"
                };
            }

            if(item?.type === "subscription") {
                return {
                    background: "color-mix(in srgb, var(--br-success-soft) 74%, #ffffff)",
                    color: "var(--br-success-hover)"
                };
            }

            return {
                background: "color-mix(in srgb, var(--br-primary-soft) 78%, #ffffff)",
                color: "var(--br-primary-active)"
            };
        },
        itemCardStyle(item) {
            if(item?.type === "service") {
                return {
                    "--br-pos-item-accent": "#b45309",
                    "--br-pos-item-hover": "color-mix(in srgb, #fef3c7 68%, #ffffff)"
                };
            }

            if(item?.type === "subscription") {
                return {
                    "--br-pos-item-accent": "var(--br-success-hover)",
                    "--br-pos-item-hover": "color-mix(in srgb, var(--br-success-soft) 68%, #ffffff)"
                };
            }

            return {
                "--br-pos-item-accent": "var(--br-primary)",
                "--br-pos-item-hover": "color-mix(in srgb, var(--br-primary-soft) 68%, #ffffff)"
            };
        },
        openItemDetail(item) {
            this.selectedItemDetail = item;
            Alerts.modals({type: "show", id: "brPosItemDetailModal"});
        },
        itemTypeLabel(item) {
            if(item?.type === "service") return "Servicio";
            if(item?.type === "subscription") return "Membresía";

            return "Producto";
        },
        formattedItemCategories(item) {
            const categories = (item?.category_items || item?.categoryItems || [])
                .map(row => row.category?.name)
                .filter(Boolean);

            return categories.length ? categories.join(", ") : "Sin categorías";
        },
        boolLabel(value) {
            return value ? "Sí" : "No";
        },
        openSaleConfirmation() {
            if(!this.canSubmit) {
                Alerts.toastrs({type: "warning", subtitle: "Completa caja, cliente, pagos y productos. Los pagos deben cuadrar con el total."});
                return;
            }

            this.showPaymentEditor = false;
            Alerts.modals({type: "show", id: "brPosSaleConfirmationModal"});
        },
        confirmSale() {
            if(!this.canSubmit) {
                Alerts.toastrs({type: "warning", subtitle: "Revisa los métodos de pago. El total pagado debe cuadrar con la venta."});
                return;
            }

            Alerts.modals({type: "hide", id: "brPosSaleConfirmationModal"});
            this.submitSale();
        },
        addCustomerPostAction({response = null}) {
            if(!Requests.valid({result: response}) || !response?.data?.customer) return;

            const customer = response.data.customer;
            const exists = this.customers.some(record => record.id === customer.id);

            if(!exists) {
                this.customers.push(customer);
            }

            this.selectedCustomer = {...customer, label: customer.name};
        },
        buildPayload() {
            return {
                branch_id: this.selectedBranch?.id,
                serie_id: this.selectedSerie?.id,
                warehouse_id: this.selectedWarehouse?.id,
                cash_session_id: this.selectedCashSession?.id,
                holder_id: this.selectedCustomer?.id,
                currency_id: this.currencies[0]?.id || this.cart[0]?.item?.currency_id,
                issue_date: this.today(),
                observation: "Venta POS",
                details: this.cart.map(line => ({
                    item_id: line.item.id,
                    type: line.item.type,
                    currency_id: line.item.currency_id,
                    name: line.item.name,
                    quantity: line.quantity,
                    price: line.price,
                    price_includes_tax: this.includesTax(line.item),
                    observation: null
                })),
                taxes: this.taxSummary.map(tax => ({
                    tax_id: tax.tax_id,
                    rate: tax.rate,
                    amount: Number(tax.amount.toFixed(2))
                })),
                payments: this.payments.map(payment => ({
                    payment_method_id: payment.method?.id,
                    amount: Number(Number(payment.amount || 0).toFixed(2)),
                    reference: "POS",
                    note: null
                }))
            };
        },
        async submitSale() {
            if(!this.canSubmit) {
                Alerts.toastrs({type: "warning", subtitle: "Completa caja, cliente, pagos y productos. Los pagos deben cuadrar con el total."});
                return;
            }

            this.saving = true;
            Alerts.loading?.({message: "Generando venta"});

            const result = await Requests.post({
                route: this.config.routes.store,
                data: this.buildPayload()
            });

            this.saving = false;
            Alerts.close?.();
            window.Swal?.close?.();

            if(!Requests.valid({result})) {
                Alerts.toastrs({type: "error", subtitle: result.data?.msg || "No fue posible generar la venta."});
                return;
            }

            Alerts.toastrs({type: "success", subtitle: result.data?.msg || "Venta generada correctamente."});
            this.clearCart();
        }
    }
};
</script>
