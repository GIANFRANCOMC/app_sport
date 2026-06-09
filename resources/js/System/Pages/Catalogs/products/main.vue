<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <main class="br-entity">
        <FiltersSection
            :filter-by-value="filterByValue"
            @update:filterByValue="filterByValue = $event"
            :filter-word-value="filterWordValue"
            @update:filterWordValue="filterWordValue = $event"
            :filter-by-options="filterByOptions"
            :search-placeholder="searchPlaceholder"
            :loading="entityList.extras.loading"
            :filter-by-title="MODULE.texts.filters.filterBy"
            :search-title="MODULE.texts.filters.search"
            :search-button-text="MODULE.texts.actions.search"
            :add-button-text="MODULE.texts.actions.add"
            :show-add-button="true"
            :title-class="[config.forms.classes.title]"
            :select-class="config.forms.classes.select2"
            @search="handleSearch"
            @add="openModal()"/>

        <section class="br-entity-list" :aria-label="MODULE.texts.table.ariaLabel">
            <div class="table-responsive">
                <table class="table br-entity-table mb-0">
                    <colgroup>
                        <col class="br-entity-table__col-product">
                        <col class="br-entity-table__col-identification">
                        <col class="br-entity-table__col-price">
                        <col class="br-entity-table__col-inventory">
                        <col class="br-entity-table__col-status">
                        <col class="br-entity-table__col-actions">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Identificación</th>
                            <th class="text-end">Precio</th>
                            <th>Inventario</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">
                                <span class="visually-hidden">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="entityList.extras.loading">
                            <td colspan="6" class="py-4">
                                <Loader/>
                            </td>
                        </tr>
                        <template v-else-if="entityList.records.total > 0">
                            <tr v-for="record in entityList.records.data" :key="record.id">
                                <td>
                                    <span class="br-entity-table__name" v-text="record.name"></span>
                                    <span v-if="record.description" class="br-entity-table__description" v-text="record.description"></span>
                                    <span v-if="record.category_items?.length" class="br-entity-table__meta">
                                        {{ record.category_items.length }} {{ record.category_items.length === 1 ? "categoría" : "categorías" }}
                                    </span>
                                    <span v-if="record.brand" class="br-entity-table__meta">
                                        Marca: {{ record.brand.name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="br-entity-identifiers">
                                        <div class="br-entity-identifier">
                                            <span class="br-entity-identifier__label">Código interno</span>
                                            <span class="br-entity-identifier__value">
                                                <span class="br-entity-code" v-text="record.internal_code"></span>
                                                <CopyButton
                                                    :value="record.internal_code"
                                                    label="Código interno"/>
                                            </span>
                                        </div>
                                        <div class="br-entity-identifier">
                                            <span class="br-entity-identifier__label">Código de barras</span>
                                            <span class="br-entity-identifier__value">
                                                <span class="br-entity-barcode" v-text="record.barcode"></span>
                                                <CopyButton
                                                    :value="record.barcode"
                                                    label="Código de barras"/>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="br-entity-prices">
                                        <span class="br-entity-price-row is-sale">
                                            <span>Venta</span>
                                            <strong>{{ record.currency?.sign }} {{ separatorNumber(record.price) }}</strong>
                                        </span>
                                        <span
                                            v-if="isDefined(record.min_price)"
                                            class="br-entity-price-row is-minimum">
                                            <span>Mín.</span>
                                            <strong>{{ record.currency?.sign }} {{ separatorNumber(record.min_price) }}</strong>
                                        </span>
                                        <span
                                            v-if="isDefined(record.max_price)"
                                            class="br-entity-price-row is-maximum">
                                            <span>Máx.</span>
                                            <strong>{{ record.currency?.sign }} {{ separatorNumber(record.max_price) }}</strong>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="br-entity-stock">
                                        {{ separatorNumber(stockSummary(record).total) }} unidades
                                    </span>
                                    <span
                                        v-if="stockSummary(record).alerts > 0"
                                        class="br-entity-stock-alert">
                                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                        <span>
                                            {{ stockSummary(record).alerts }}
                                            {{ stockSummary(record).alerts === 1 ? "almacén con stock bajo" : "almacenes con stock bajo" }}
                                        </span>
                                    </span>
                                    <span v-else class="br-entity-stock-healthy">
                                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                        <span>
                                            Stock saludable en
                                            {{ stockSummary(record).warehouses }}
                                            {{ stockSummary(record).warehouses === 1 ? "almacén" : "almacenes" }}
                                        </span>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <StatusBadge
                                        class="flex-shrink-none"
                                        :status="record.status"
                                        :formatted-status="record.formatted_status"/>
                                </td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="br-icon-action br-icon-action-edit"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        :title="MODULE.texts.actions.edit"
                                        :aria-label="`${MODULE.texts.actions.edit} ${record.name}`"
                                        @click="openModal(record)">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="6" class="py-4">
                                <WithoutData type="image"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <nav
            v-if="!entityList.extras.loading && entityList.records.total > 0"
            class="d-flex justify-content-center mt-3"
            aria-label="Paginación de productos">
            <Paginator :links="entityList.records.links" @clickPage="listEntity"/>
        </nav>
    </main>

    <div
        class="modal fade br-entity-modal"
        :id="productForm.extras.modals.default.id"
        data-bs-backdrop="static"
        tabindex="-1"
        role="dialog"
        aria-modal="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header">
                    <div>
                        <p class="br-entity-modal__eyebrow mb-1">Catálogo comercial</p>
                        <h2 class="modal-title br-entity-modal__title">
                            {{ modalTitles.createUpdate[isUpdate ? "update" : "store"] }}
                        </h2>
                    </div>
                    <button
                        type="button"
                        class="br-modal-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="modal-body br-entity-modal__body">
                    <nav class="br-entity-tabs" aria-label="Secciones del formulario">
                        <button
                            v-for="(tab, index) in formTabs"
                            :key="tab.id"
                            type="button"
                            :class="['br-entity-tab', {'is-active': activeFormTab === tab.id}]"
                            :aria-selected="activeFormTab === tab.id"
                            :aria-controls="`product-tab-${tab.id}`"
                            role="tab"
                            @click="activeFormTab = tab.id">
                            <span class="br-entity-tab__step" v-text="index + 1"></span>
                            <span class="br-entity-tab__content">
                                <strong v-text="tab.label"></strong>
                                <small v-text="tab.description"></small>
                            </span>
                            <span v-if="tabHasErrors(tab.id)" class="br-entity-tab__error" aria-label="Contiene errores">
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                            </span>
                        </button>
                    </nav>

                    <form @submit.prevent="saveEntity">
                        <section
                            v-show="activeFormTab === 'general'"
                            id="product-tab-general"
                            class="br-entity-form-section"
                            role="tabpanel">
                            <div class="row g-3">
                                <InputText
                                    v-model="productForm.data.name"
                                    hasDiv
                                    :title="MODULE.texts.form.name"
                                    :titleClass="[config.forms.classes.title]"
                                    isRequired
                                    maxlength="50"
                                    showCharCounter
                                    hasTextBottom
                                    :textBottomInfo="productForm.errors?.name"
                                    xl="4"
                                    lg="4"/>

                                <InputText
                                    v-model="productForm.data.internal_code"
                                    hasDiv
                                    :title="MODULE.texts.form.internalCode"
                                    :titleClass="[config.forms.classes.title]"
                                    isRequired
                                    maxlength="50"
                                    :showCharCounter="false"
                                    hasTextBottom
                                    :textBottomInfo="productForm.errors?.internal_code"
                                    xl="4"
                                    lg="4">
                                    <template v-slot:defaultAppend>
                                        <button
                                            type="button"
                                            class="br-field-help"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            :title="MODULE.texts.form.internalCodeHelp"
                                            aria-label="¿Para qué sirve el código interno?">
                                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                        </button>
                                    </template>
                                    <template v-if="!isUpdate" v-slot:inputGroupAppend>
                                        <button
                                            type="button"
                                            class="br-input-action"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            :title="MODULE.texts.form.generateInternalCodeTooltip"
                                            :aria-label="MODULE.texts.form.generateInternalCodeTooltip"
                                            @click="generateInternalCode($event)">
                                            <i class="fa-solid fa-rotate" aria-hidden="true"></i>
                                        </button>
                                    </template>
                                </InputText>

                                <InputText
                                    v-model="productForm.data.barcode"
                                    hasDiv
                                    :title="MODULE.texts.form.barcode"
                                    :titleClass="[config.forms.classes.title]"
                                    isRequired
                                    maxlength="13"
                                    :showCharCounter="false"
                                    hasTextBottom
                                    :textBottomInfo="productForm.errors?.barcode"
                                    xl="4"
                                    lg="4">
                                    <template v-slot:defaultAppend>
                                        <button
                                            type="button"
                                            class="br-field-help"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            :title="MODULE.texts.form.barcodeHelp"
                                            aria-label="¿Para qué sirve el código de barras?">
                                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                        </button>
                                    </template>
                                    <template v-slot:inputGroupAppend>
                                        <button
                                            type="button"
                                            class="br-input-action"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            :title="MODULE.texts.form.generateBarcodeTooltip"
                                            :aria-label="MODULE.texts.form.generateBarcodeTooltip"
                                            @click="generateBarcode($event)">
                                            <i class="fa-solid fa-rotate" aria-hidden="true"></i>
                                        </button>
                                    </template>
                                </InputText>

                                <InputNumber
                                    v-model="productForm.data.price"
                                    hasDiv
                                    :title="MODULE.texts.form.price"
                                    :titleClass="[config.forms.classes.title]"
                                    isRequired
                                    hasTextBottom
                                    :textBottomInfo="productForm.errors?.price"
                                    xl="4"
                                    lg="4">
                                    <template v-slot:inputGroupPrepend>
                                        <span class="input-group-text br-currency-prefix">
                                            <span class="br-currency-prefix__symbol" v-text="currencySign"></span>
                                        </span>
                                    </template>
                                </InputNumber>

                                <InputNumber
                                    v-model="productForm.data.min_price"
                                    hasDiv
                                    :title="MODULE.texts.form.minPrice"
                                    :titleClass="[config.forms.classes.title]"
                                    hasTextBottom
                                    :textBottomInfo="productForm.errors?.min_price"
                                    xl="4"
                                    lg="4">
                                    <template v-slot:inputGroupPrepend>
                                        <span class="input-group-text br-currency-prefix">
                                            <span class="br-currency-prefix__symbol" v-text="currencySign"></span>
                                        </span>
                                    </template>
                                </InputNumber>

                                <InputNumber
                                    v-model="productForm.data.max_price"
                                    hasDiv
                                    :title="MODULE.texts.form.maxPrice"
                                    :titleClass="[config.forms.classes.title]"
                                    hasTextBottom
                                    :textBottomInfo="productForm.errors?.max_price"
                                    xl="4"
                                    lg="4">
                                    <template v-slot:inputGroupPrepend>
                                        <span class="input-group-text br-currency-prefix">
                                            <span class="br-currency-prefix__symbol" v-text="currencySign"></span>
                                        </span>
                                    </template>
                                </InputNumber>

                                <InputSlot
                                    hasDiv
                                    :title="MODULE.texts.form.brand"
                                    :titleClass="[config.forms.classes.title]"
                                    hasTextBottom
                                    :textBottomInfo="productForm.errors?.brand_id"
                                    xl="4"
                                    lg="4">
                                    <template v-slot:input>
                                        <v-select
                                            v-model="productForm.data.brand"
                                            :options="brands"
                                            :class="config.forms.classes.select2"
                                            :clearable="true"
                                            :searchable="true"
                                            append-to-body>
                                            <template #selected-option="option">
                                                <span
                                                    class="br-select-selected-text"
                                                    :title="getSelectOptionLabel(option)"
                                                    v-text="getSelectOptionLabel(option)">
                                                </span>
                                            </template>
                                            <template #option="option">
                                                <span
                                                    class="br-select-option-text"
                                                    :title="getSelectOptionLabel(option)"
                                                    v-text="getSelectOptionLabel(option)">
                                                </span>
                                            </template>
                                        </v-select>
                                    </template>
                                </InputSlot>

                                <InputSlot
                                    hasDiv
                                    :title="MODULE.texts.form.status"
                                    :titleClass="[config.forms.classes.title]"
                                    isRequired
                                    hasTextBottom
                                    :textBottomInfo="productForm.errors?.status"
                                    xl="4"
                                    lg="4">
                                    <template v-slot:input>
                                        <v-select
                                            v-model="productForm.data.status"
                                            :options="statuses"
                                            :class="config.forms.classes.select2"
                                            :clearable="false"
                                            :searchable="false"
                                            append-to-body>
                                            <template #selected-option="option">
                                                <span
                                                    class="br-select-selected-text"
                                                    :title="getSelectOptionLabel(option)"
                                                    v-text="getSelectOptionLabel(option)">
                                                </span>
                                            </template>
                                            <template #option="option">
                                                <span
                                                    class="br-select-option-text"
                                                    :title="getSelectOptionLabel(option)"
                                                    v-text="getSelectOptionLabel(option)">
                                                </span>
                                            </template>
                                        </v-select>
                                    </template>
                                </InputSlot>
                            </div>
                        </section>

                        <section
                            v-show="activeFormTab === 'commercial'"
                            id="product-tab-commercial"
                            class="br-entity-form-section"
                            role="tabpanel">
                            <div class="row g-3">
                                <InputSlot
                                    hasDiv
                                    :title="MODULE.texts.form.categories"
                                    :titleClass="[config.forms.classes.title]"
                                    hasTextBottom
                                    :textBottomInfo="productForm.errors?.categories"
                                    xl="12"
                                    lg="12">
                                    <template v-slot:input>
                                        <v-select
                                            v-model="productForm.data.categories"
                                            :options="categories"
                                            :class="config.forms.classes.select2"
                                            :clearable="true"
                                            :searchable="true"
                                            :multiple="true"
                                            append-to-body>
                                            <template #selected-option="option">
                                                <span
                                                    class="br-select-selected-text"
                                                    :title="getSelectOptionLabel(option)"
                                                    v-text="getSelectOptionLabel(option)">
                                                </span>
                                            </template>
                                            <template #option="option">
                                                <span
                                                    class="br-select-option-text"
                                                    :title="getSelectOptionLabel(option)"
                                                    v-text="getSelectOptionLabel(option)">
                                                </span>
                                            </template>
                                        </v-select>
                                    </template>
                                </InputSlot>

                                <InputText
                                    v-model="productForm.data.description"
                                    hasDiv
                                    :title="MODULE.texts.form.commercialDescription"
                                    :titleClass="[config.forms.classes.title]"
                                    maxlength="100"
                                    showCharCounter
                                    hasTextBottom
                                    :textBottomInfo="productForm.errors?.description"
                                    xl="12"
                                    lg="12"/>

                                <div class="col-12">
                                    <div class="br-entity-publication-settings">
                                        <label class="br-entity-switch" for="see_my_web">
                                            <input
                                                id="see_my_web"
                                                v-model="productForm.data.see_my_web"
                                                class="form-check-input"
                                                type="checkbox"
                                                role="switch"
                                                @change="syncPublicationSettings">
                                            <span>
                                                <strong>Publicar producto</strong>
                                                <small>Permite mostrarlo en el futuro catálogo web o PDF.</small>
                                            </span>
                                        </label>

                                        <label
                                            class="br-entity-switch"
                                            :class="{'is-disabled': !productForm.data.see_my_web}"
                                            for="see_my_web_price">
                                            <input
                                                id="see_my_web_price"
                                                v-model="productForm.data.see_my_web_price"
                                                class="form-check-input"
                                                type="checkbox"
                                                role="switch"
                                                :disabled="!productForm.data.see_my_web">
                                            <span>
                                                <strong>Mostrar precio</strong>
                                                <small>Se mostrará junto al producto solo cuando la publicación esté activa.</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section
                            v-show="activeFormTab === 'inventory'"
                            id="product-tab-inventory"
                            class="br-entity-form-section mb-0"
                            role="tabpanel">
                            <p class="br-entity-inventory-note">
                                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                <span v-if="isUpdate">
                                    Ajusta el mínimo de alerta. El stock actual se modifica desde Gestión de stock.
                                </span>
                                <span v-else>
                                    Registra el stock de apertura y el mínimo necesario para cada almacén.
                                </span>
                            </p>

                            <div v-if="productForm.errors?.inventory" class="alert alert-danger py-2" v-text="firstError(productForm.errors.inventory)"></div>

                            <div v-if="productForm.data.inventory.length" class="br-entity-inventory">
                                <div class="br-entity-inventory__head" aria-hidden="true">
                                    <span>Almacén</span>
                                    <span>{{ isUpdate ? "Stock actual" : "Stock inicial" }}</span>
                                    <span>Stock mínimo</span>
                                </div>

                                <div
                                    v-for="(inventory, index) in productForm.data.inventory"
                                    :key="inventory.warehouse_id"
                                    class="br-entity-inventory__row">
                                    <div class="br-entity-inventory__warehouse">
                                        <strong v-text="inventory.branch_name"></strong>
                                        <span v-text="inventory.warehouse_name"></span>
                                        <small
                                            :class="['br-entity-inventory-status', inventoryStockStatus(inventory).className]">
                                            <i :class="inventoryStockStatus(inventory).icon" aria-hidden="true"></i>
                                            <span v-text="inventoryStockStatus(inventory).text"></span>
                                        </small>
                                    </div>

                                    <div class="br-entity-inventory__field">
                                        <InputNumber
                                            v-model="inventory.initial_stock"
                                            :title="isUpdate ? 'Stock actual' : 'Stock inicial'"
                                            :titleClass="['br-entity-inventory__mobile-label']"
                                            :minValue="0"
                                            :decimals="2"
                                            :disabled="isUpdate"
                                            hasTextBottom
                                            :textBottomInfo="inventoryFieldErrors(index, 'initial_stock')"/>
                                    </div>

                                    <div class="br-entity-inventory__field">
                                        <InputNumber
                                            v-model="inventory.minimum_stock"
                                            title="Stock mínimo"
                                            :titleClass="['br-entity-inventory__mobile-label']"
                                            :minValue="0"
                                            :decimals="2"
                                            hasTextBottom
                                            :textBottomInfo="inventoryFieldErrors(index, 'minimum_stock')"/>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="br-entity-inventory-empty">
                                <i class="fa-solid fa-warehouse" aria-hidden="true"></i>
                                <span>No hay almacenes activos. Crea una sucursal con almacén antes de registrar productos.</span>
                            </div>
                        </section>
                    </form>
                </div>

                <div class="modal-footer br-entity-modal__footer">
                    <button
                        type="button"
                        class="br-btn br-btn-cancel"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button
                        type="button"
                        :class="['br-btn', isUpdate ? 'br-btn-action-update' : 'br-btn-action-create']"
                        :disabled="isSaving || productForm.data.inventory.length === 0"
                        @click="saveEntity">
                        <span v-text="submitButtonText"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts from "@System/Helpers/Alerts.js";
import {initCrudModule} from "@System/Helpers/ModuleFactory.js";
import * as Forms from "@System/Helpers/Forms.js";
import * as Requests from "@System/Helpers/Requests.js";
import * as Utils from "@System/Helpers/Utils.js";

const MODULE_CONFIG = {
    entity: "products",
    menuId: "menu-items-products",
    pageTitle: "Productos",
    pageTitleSingular: "Producto",
    breadcrumbParent: "Catálogo comercial",
    perPage: 10
};

const FORM_TABS = [
    {
        id: "general",
        label: "Datos y precio",
        description: "Identidad y rango de precios",
        fields: ["internal_code", "barcode", "name", "price", "min_price", "max_price", "currency", "currency_id", "brand", "brand_id", "status"]
    },
    {
        id: "commercial",
        label: "Configuración comercial",
        description: "Información adicional y publicación",
        fields: ["categories", "description", "see_my_web", "see_my_web_price"]
    },
    {
        id: "inventory",
        label: "Inventario",
        description: "Stock por almacén",
        fields: ["inventory"]
    }
];

const FORM_FIELDS = {
    internal_code: "",
    barcode: "",
    name: "",
    description: "",
    price: "",
    min_price: "",
    max_price: "",
    currency: null,
    categories: [],
    brand: null,
    see_my_web: true,
    see_my_web_price: false,
    inventory: [],
    status: null
};

const FORM_FIELD_CONFIG = {
    internal_code: {trim: true},
    barcode: {trim: true},
    name: {trim: true},
    description: {normalize: true},
    price: {toNumber: true, minValue: 0},
    min_price: {toNumber: true, minValue: 0},
    max_price: {toNumber: true, minValue: 0},
    currency: {mapToField: "currency_id"},
    categories: {getArray: {mapTo: "category_id"}},
    brand: {mapToField: "brand_id"},
    see_my_web: {toBoolean: true},
    see_my_web_price: {toBoolean: true},
    status: {getCode: true}
};

const VALIDATION_RULES = {
    internal_code: {required: true},
    barcode: {required: true},
    name: {required: true},
    description: {required: false},
    price: {required: true, number: true, min: 0},
    min_price: {required: false, number: true, min: 0},
    max_price: {required: false, number: true, min: 0},
    currency: {required: true},
    categories: {required: false},
    brand: {required: false},
    see_my_web: {required: false},
    see_my_web_price: {required: false},
    inventory: {required: true},
    status: {required: true}
};

const ERROR_LABELS = {
    internal_code: "Código interno",
    barcode: "Código de barras",
    name: "Nombre",
    description: "Descripción comercial adicional",
    price: "Precio de venta",
    min_price: "Precio mínimo",
    max_price: "Precio máximo",
    currency: "Moneda",
    categories: "Categorías",
    brand: "Marca",
    inventory: "Inventario por almacén",
    status: "Estado"
};

const FILTER_OPTIONS = [
    {code: "all", label: "Todos los filtros"},
    {code: "internal_code", label: "Código interno"},
    {code: "barcode", label: "Código de barras"},
    {code: "name", label: "Nombre"},
    {code: "brand", label: "Marca"},
    {code: "description", label: "Descripción comercial adicional"},
    {code: "price", label: "Precio de venta"}
];

const TEXTS = {
    filters: {
        filterBy: "Filtrar por",
        search: "Búsqueda"
    },
    actions: {
        search: "Buscar",
        add: "Agregar producto",
        edit: "Editar producto"
    },
    table: {
        ariaLabel: "Listado de productos"
    },
    form: {
        internalCode: "Código interno",
        barcode: "Código de barras",
        name: "Nombre",
        commercialDescription: "Descripción comercial adicional",
        price: "Precio de venta",
        minPrice: "Precio mínimo",
        maxPrice: "Precio máximo",
        categories: "Categorías",
        brand: "Marca",
        status: "Estado",
        internalCodeHelp: "Identificador privado que la empresa utiliza para ordenar, buscar y controlar internamente el producto.",
        barcodeHelp: "Código de barras en formato EAN-13 que puede imprimirse en la etiqueta del producto y ser leído por clientes o escáneres.",
        generateInternalCodeTooltip: "Generar un nuevo código interno para uso exclusivo de la empresa",
        generateBarcodeTooltip: "Generar un nuevo código de barras EAN-13 para imprimir en la etiqueta"
    },
    modal: {
        store: "Agregar producto",
        update: "Editar producto",
        storing: "Agregando",
        updating: "Editando"
    }
};

const MODULE = {
    config: MODULE_CONFIG,
    formFields: FORM_FIELDS,
    formFieldConfig: FORM_FIELD_CONFIG,
    validationRules: VALIDATION_RULES,
    errorLabels: ERROR_LABELS,
    texts: TEXTS,
    filterOptions: FILTER_OPTIONS
};

function calculateEan13CheckDigit(twelveDigits) {

    const sum = twelveDigits
        .split("")
        .reduce((total, digit, index) => total + Number(digit) * (index % 2 === 0 ? 1 : 3), 0);

    return String((10 - (sum % 10)) % 10);

}

function generateEan13() {

    const randomValue = window.crypto?.getRandomValues
        ? (() => {

            const values = new Uint32Array(1);
            window.crypto.getRandomValues(values);

            return values[0];

        })()
        : Math.floor(Math.random() * 1000000000);

    const body = `200${String(randomValue % 1000000000).padStart(9, "0")}`;

    return `${body}${calculateEan13CheckDigit(body)}`;

}

function isValidEan13(value) {

    const barcode = String(value ?? "");

    return /^\d{13}$/.test(barcode) && barcode[12] === calculateEan13CheckDigit(barcode.slice(0, 12));

}

export default {
    name: "ProductsMain",
    data() {

        const crudModule = initCrudModule({
            entity: MODULE.config.entity,
            menuId: MODULE.config.menuId,
            pageTitle: MODULE.config.pageTitle,
            pageTitleSingular: MODULE.config.pageTitleSingular
        });

        crudModule.lists[MODULE.config.entity].filters.filter_by = MODULE.filterOptions[0];
        crudModule.forms[MODULE.config.entity].createUpdate.data =
            Forms.initFormData(Utils.cloneJson(MODULE.formFields));

        return {
            ...crudModule,
            MODULE,
            activeFormTab: FORM_TABS[0].id,
            isSaving: false
        };

    },
    async mounted() {

        Utils.navbarItem("menu-parent-items", {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});

        Alerts.swals({type: "initParams"});

        const initParams = await this.initParams();

        if(initParams) {

            Alerts.swals({show: false});
            await this.listEntity({});

        }

        document.getElementById(this.productForm.extras.modals.default.id)?.addEventListener("hidden.bs.modal", this.resetProductForm);

    },
    beforeUnmount() {

        Alerts.tooltips({show: false});
        document.getElementById(this.productForm.extras.modals.default.id)?.removeEventListener("hidden.bs.modal", this.resetProductForm);

    },
    methods: {
        getSelectOptionLabel(option) {

            return option?.label ?? option?.name ?? option?.value ?? "";

        },
        async initParams() {

            const response = await Requests.get({
                route: this.routeActions.initParams,
                data: {page: "main"},
                showAlert: true
            });

            if(response?.data?.config) {

                this.options.brands     = response.data.config.brands;
                this.options.categories = response.data.config.categories;
                this.options.currencies = response.data.config.currencies;
                this.options.statuses   = response.data.config.statuses;
                this.options.warehouses = response.data.config.warehouses;

            }

            return Requests.valid({result: response});

        },
        async listEntity(params = null) {

            const emptyRecords = {total: 0, data: [], links: []};
            const filters = Utils.cloneJson(this.entityList.filters);
            const filterData = {
                per_page: this.MODULE.config.perPage,
                filter_by: filters.filter_by?.code,
                word: filters.word
            };

            this.entityList.extras.loading = true;

            try {

                const url = this.isDefined(params) && typeof params === "object" ? params.url : params;
                let requestUrl = url || this.entityList.extras.route;
                let requestData = {};

                if(this.isDefined(url)) {

                    const urlObject = new URL(url, window.location.origin);

                    Object.entries(filterData).forEach(([key, value]) => {

                        if(this.isDefined(value) && !urlObject.searchParams.has(key)) urlObject.searchParams.set(key, value);

                    });

                    requestUrl = `${urlObject.pathname}${urlObject.search}`;

                }else {

                    requestData = filterData;

                }

                const response = await Requests.get({
                    route: requestUrl,
                    data: requestData,
                    showAlert: true
                });

                this.entityList.records = response?.data ?? emptyRecords;

            }catch(error) {

                this.entityList.records = emptyRecords;

            }finally {

                this.entityList.extras.loading = false;
                this.$nextTick(() => Alerts.tooltips({}));

            }

        },
        handleSearch() {

            this.listEntity({});

        },
        openModal(record = null) {

            Alerts.tooltips({show: false});
            this.resetProductForm();

            if(this.isDefined(record)) {

                const categoryIds = (record.category_items ?? []).map(category => category.category_id);

                Object.assign(this.productForm.data, {
                    id: record.id,
                    internal_code: record.internal_code,
                    barcode: record.barcode,
                    name: record.name,
                    description: record.description,
                    price: record.price,
                    min_price: record.min_price,
                    max_price: record.max_price,
                    currency: this.currencies.find(currency => currency.code === record.currency_id) ?? null,
                    categories: this.categories.filter(category => categoryIds.includes(category.code)),
                    brand: this.resolveBrandOption(record),
                    see_my_web: Boolean(record.see_my_web),
                    see_my_web_price: Boolean(record.see_my_web && record.see_my_web_price),
                    inventory: this.buildInventory(record),
                    status: this.statuses.find(status => status.code === record.status) ?? null
                });

            }else {

                Object.assign(this.productForm.data, {
                    internal_code: this.generateRandomCode(7),
                    barcode: generateEan13(),
                    currency: this.currencies[0] ?? null,
                    categories: [],
                    brand: null,
                    see_my_web: true,
                    see_my_web_price: false,
                    inventory: this.buildInventory(),
                    status: this.statuses[0] ?? null
                });

            }

            Alerts.modals({type: "show", id: this.productForm.extras.modals.default.id});
            this.$nextTick(() => Alerts.tooltips({time: 350}));

        },
        resetProductForm() {

            const form = this.forms[this.entity].createUpdate;

            form.data = Forms.initFormData(Utils.cloneJson(this.MODULE.formFields));
            form.errors = {};
            this.activeFormTab = FORM_TABS[0].id;

        },
        buildInventory(record = null) {

            const warehouseItems = record?.warehouse_items ?? [];

            return this.warehouses.map(warehouse => {

                const warehouseItem = warehouseItems.find(item => Number(item.warehouse_id) === Number(warehouse.id));

                return {
                    warehouse_id: Number(warehouse.id),
                    branch_name: warehouse.branch?.name ?? "Sucursal",
                    warehouse_name: warehouse.name,
                    initial_stock: Number(warehouseItem?.quantity ?? 0),
                    minimum_stock: Number(warehouseItem?.minimum_stock ?? 0)
                };

            });

        },
        resolveBrandOption(record) {

            const activeBrand = this.brands.find(brand => Number(brand.code) === Number(record?.brand_id));

            if(activeBrand) return activeBrand;

            if(!record?.brand) return null;

            return {
                code: record.brand.id,
                label: `${record.brand.name}${record.brand.status === "inactive" ? " (Inactiva)" : ""}`,
                data: record.brand
            };

        },
        generateInternalCode(event) {

            this.productForm.data.internal_code = this.generateRandomCode(7);
            Alerts.dismissTooltip(event?.currentTarget);

        },
        generateBarcode(event) {

            this.productForm.data.barcode = generateEan13();
            Alerts.dismissTooltip(event?.currentTarget);

        },
        syncPublicationSettings() {

            if(!this.productForm.data.see_my_web) this.productForm.data.see_my_web_price = false;

        },
        async saveEntity() {

            if(this.isSaving) return;

            Alerts.swals({});
            this.productForm.errors = {};
            this.isSaving = true;

            try {

                const formData = Utils.cloneJson(this.productForm.data);
                const validation = this.validateFormData(formData);

                if(!validation.bool) {

                    this.productForm.errors = validation.errors;
                    this.focusFirstTabWithErrors(validation.errors);
                    Alerts.generateAlert({messages: Utils.getErrors({errors: validation.errors}), msgContent: this.config.messages.errorValidate});
                    return;

                }

                const preparedData = Forms.prepareFormData(formData, this.MODULE.formFieldConfig);
                preparedData.inventory = preparedData.inventory.map(inventory => ({
                    warehouse_id: Number(inventory.warehouse_id),
                    initial_stock: Number(inventory.initial_stock ?? 0),
                    minimum_stock: Number(inventory.minimum_stock ?? 0)
                }));

                if(!preparedData.see_my_web) preparedData.see_my_web_price = false;

                const id = preparedData.id;
                const isUpdate = this.isDefined(id);
                const requestMethod = isUpdate ? "patch" : "post";
                const route = this.routeActions[isUpdate ? "update" : "store"];
                const result = await Requests[requestMethod]({route, data: preparedData, id});

                if(Requests.valid({result})) {

                    Alerts.modals({type: "hide", id: this.productForm.extras.modals.default.id});
                    Alerts.generateAlert({type: "success", msgContent: result.data.msg});

                    const currentPage = this.entityList?.records?.current_page ?? 1;
                    await this.listEntity({url: `${this.entityList?.extras?.route || ""}?page=${currentPage}`});

                }else {

                    Forms.handleFormResponseErrors({result, formErrorsObject: this.productForm.errors, config: this.config});

                }

            }catch(error) {

                Alerts.generateAlert({type: "error", messages: [error], msgContent: this.config.messages.catchError});

            }finally {

                this.isSaving = false;

            }

        },
        validateFormData(formData) {

            const result = Forms.validateFormData(formData, this.validationRules, {isDescriptive: true, errorLabels: this.MODULE.errorLabels});

            if(!isValidEan13(formData.barcode)) {

                result.errors.barcode = ["Código de barras: Ingresa un código válido o genera uno automáticamente."];
                result.bool = false;

            }

            if(!Array.isArray(formData.inventory) || formData.inventory.length === 0) {

                result.errors.inventory = ["Inventario por almacén: Se requiere al menos un almacén activo."];
                result.bool = false;

            }else {

                formData.inventory.forEach((inventory, index) => {

                    ["initial_stock", "minimum_stock"].forEach(field => {

                        const value = Number(inventory[field]);

                        if(!Number.isFinite(value) || value < 0) {

                            result.errors[`inventory.${index}.${field}`] = [
                                `${field === "initial_stock" ? "Stock inicial" : "Stock mínimo"}: Debe ser mayor o igual a 0.`
                            ];
                            result.bool = false;

                        }

                    });

                });

            }

            if(!result.errors.price) {

                const minPrice = parseFloat(formData.min_price) || 0;
                const maxPrice = parseFloat(formData.max_price) || 0;
                const price    = parseFloat(formData.price) || 0;

                if(minPrice > 0 && maxPrice > 0 && maxPrice < minPrice) {

                    result.errors.max_price = ["Precio máximo: Debe ser mayor o igual al precio mínimo."];
                    result.bool = false;

                }else if(minPrice > 0 && price < minPrice) {

                    result.errors.price = ["Precio de venta: Debe ser mayor o igual al precio mínimo."];
                    result.bool = false;

                }else if(maxPrice > 0 && price > maxPrice) {

                    result.errors.price = ["Precio de venta: Debe ser menor o igual al precio máximo."];
                    result.bool = false;

                }

            }

            return result;

        },
        inventoryFieldErrors(index, field) {

            const error = this.productForm.errors?.[`inventory.${index}.${field}`];

            return Array.isArray(error) ? error : (error ? [error] : []);

        },
        tabHasErrors(tabId) {

            const tab = FORM_TABS.find(item => item.id === tabId);
            const errorFields = Object.keys(this.productForm.errors ?? {});

            return tab?.fields.some(field => errorFields.some(errorField => errorField === field || errorField.startsWith(`${field}.`))) ?? false;

        },
        focusFirstTabWithErrors(errors) {

            const errorFields = Object.keys(errors ?? {});
            const tab = FORM_TABS.find(item =>
                item.fields.some(field =>
                    errorFields.some(errorField => errorField === field || errorField.startsWith(`${field}.`))
                )
            );

            this.activeFormTab = tab?.id ?? FORM_TABS[0].id;

        },
        firstError(error) {

            return Array.isArray(error) ? error[0] : error ?? "";

        },
        stockSummary(record) {

            const warehouseItems = record?.warehouse_items ?? [];

            return {
                total: warehouseItems.reduce((total, item) => total + Number(item.quantity ?? 0), 0),
                alerts: warehouseItems.filter(item => Number(item.quantity ?? 0) <= Number(item.minimum_stock ?? 0)).length,
                warehouses: warehouseItems.length
            };

        },
        inventoryStockStatus(inventory) {

            const currentStock = Number(inventory?.initial_stock ?? 0);
            const minimumStock = Number(inventory?.minimum_stock ?? 0);
            const isLowStock = currentStock <= minimumStock;

            if(isLowStock) {

                return {
                    className: "is-alert",
                    icon: "fa-solid fa-triangle-exclamation",
                    text: "Stock bajo o en el mínimo"
                };

            }

            return {
                className: "is-healthy",
                icon: "fa-solid fa-circle-check",
                text: "Inventario saludable"
            };

        },
        isDefined(value) {

            return Utils.isDefined({value});

        },
        generateRandomCode(length) {

            return Utils.generateCode({length});

        },
        separatorNumber(value) {

            return Utils.separatorNumber(value);

        }
    },
    computed: {
        entity() {

            return this.MODULE.config.entity;

        },
        productForm() {

            return this.forms[this.entity].createUpdate;

        },
        formTabs() {

            return FORM_TABS;

        },
        routeActions() {

            return this.config.entity.routes;

        },
        entityList() {

            return this.lists[this.entity];

        },
        breadcrumbTitles() {

            return [
                {title: this.MODULE.config.breadcrumbParent},
                this.config.entity.page
            ];

        },
        categories() {

            return (this.options?.categories?.records ?? []).map(category => ({
                code: category.id,
                label: category.name,
                data: category
            }));

        },
        brands() {

            return (this.options?.brands?.records ?? []).map(brand => ({
                code: brand.id,
                label: brand.name,
                data: brand
            }));

        },
        currencies() {

            return (this.options?.currencies?.records ?? []).map(currency => ({
                code: currency.id,
                label: currency.plural_name,
                data: currency
            }));

        },
        statuses() {

            return (this.options?.statuses ?? []).map(status => ({
                code: status.code,
                label: status.label
            }));

        },
        submitButtonText() {

            if(this.isSaving) return this.MODULE.texts.modal[this.isUpdate ? "updating" : "storing"];

            return this.MODULE.texts.modal[this.isUpdate ? "update" : "store"];

        },
        warehouses() {

            return this.options?.warehouses?.records ?? [];

        },
        currencySign() {

            return this.productForm.data.currency?.data?.sign ?? "";

        },
        isUpdate() {

            return this.isDefined(this.productForm.data.id);

        },
        modalTitles() {

            return {
                createUpdate: this.productForm.extras.modals.default.titles
            };

        },
        filterByOptions() {

            return this.MODULE.filterOptions;

        },
        filterByValue: {
            get() {

                return this.entityList.filters?.filter_by || this.MODULE.filterOptions[0];

            },
            set(value) {

                this.entityList.filters.filter_by = value;

            }
        },
        filterWordValue: {
            get() {

                return this.entityList.filters.word || "";

            },
            set(value) {

                this.entityList.filters.word = value;

            }
        },
        searchPlaceholder() {

            const filterBy = this.entityList.filters.filter_by;

            return filterBy ? `Buscar por ${(filterBy.label || "...").toLowerCase()}` : "Buscar productos";

        },
        validationRules() {

            return Utils.cloneJson(this.MODULE.validationRules);

        }
    }
};
</script>
