<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Filters -->
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

    <!-- Records -->
    <div class="list-section mb-1 mb-md-1 table-responsive">
        <table class="table table-hover">
            <thead class="align-middle bg-secondary text-center">
                <tr>
                    <th class="text-white" style="width: 20%;">CÓDIGO INTERNO</th>
                    <th class="text-white" style="width: 25%;" v-text="MODULE.config.pageTitleSingular"></th>
                    <th class="text-white" style="width: 10%;">DURACIÓN</th>
                    <th class="text-white" style="width: 20%;">PRECIO DE VENTA</th>
                    <th class="text-white" style="width: 5%;"></th>
                    <th class="text-white" style="width: 10%;">ESTADO</th>
                    <th class="text-white" style="width: 10%;">ACCIONES</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0 bg-white">
                <tr v-if="entityList.extras.loading">
                    <td colspan="99">
                        <Loader/>
                    </td>
                </tr>
                <template v-else-if="entityList.records.total > 0">
                    <tr v-for="record in entityList.records.data" :key="record.id">
                        <td class="text-center">
                            <span v-text="record.internal_code" class="fw-semibold d-block"></span>
                        </td>
                        <td>
                            <span v-text="record.name" class="fw-semibold d-block"></span>
                            <small v-if="record.description" v-text="record.description" class="text-muted"></small>
                            <small class="text-muted d-block">
                                Límite diario: {{ record.attendance_limit_per_day || "Sin límite" }} - Comisión: {{ commissionLabel(record) }}
                            </small>
                        </td>
                        <td class="text-center">
                            <span v-text="record.formatted_duration" class="fw-semibold text-lowercase"></span>
                        </td>
                        <td class="text-center">
                            <span class="fw-semibold d-block">
                                <span v-text="`${record.currency?.sign} ${separatorNumber(record.price)}`"></span>
                            </span>
                            <div v-if="isDefined(record.min_price) || isDefined(record.max_price)" class="d-flex flex-column mt-1">
                                <small v-if="isDefined(record.min_price)" class="text-muted" v-text="`Min: ${record.currency?.sign} ${separatorNumber(record.min_price)}`"></small>
                                <small v-if="isDefined(record.max_price)" class="text-muted" v-text="`Max: ${record.currency?.sign} ${separatorNumber(record.max_price)}`"></small>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <i :class="['fa fa-globe cursor-pointer', record.see_my_web ? 'text-success' : 'text-light']" data-bs-toggle="tooltip" data-bs-placement="top" :title="record.see_my_web ? 'Visible en mi página' : 'No visible en mi página'"></i>
                                <i :class="['fa-solid fa-dollar-sign cursor-pointer', record.see_my_web_price ? 'text-success' : 'text-light']" data-bs-toggle="tooltip" data-bs-placement="top" :title="record.see_my_web_price ? 'Precio visible' : 'Precio no visible'"></i>
                            </div>
                        </td>
                        <td class="text-center">
                            <StatusBadge class="flex-shrink-none" :status="record.status" :formatted-status="record.formatted_status"/>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs btn-warning waves-effect" @click="openModal(record)">
                                <span v-text="MODULE.texts.actions.edit"></span>
                            </button>
                        </td>
                    </tr>
                </template>
                <tr v-else>
                    <td colspan="99">
                        <WithoutData type="image"/>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav v-if="!entityList.extras.loading && entityList.records.total > 0" class="d-flex justify-content-center">
        <Paginator :links="entityList.records.links" @clickPage="listEntity"/>
    </nav>

    <!-- Modal: Create/Update -->
    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.default.id" data-bs-backdrop="static" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="modalTitles.createUpdate[isUpdate ? 'update' : 'store']"></h5>
                    <button type="button" class="btn-header-modal" data-bs-dismiss="modal">
                        <i class="fa fa-times icon-close-modal"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveEntity">
                        <div class="row g-3">
                            <InputText
                                v-model="forms[entity].createUpdate.data.internal_code"
                                hasDiv
                                :title="MODULE.texts.form.internalCode"
                                :titleClass="[config.forms.classes.title]"
                                isRequired
                                :maxlength="internalCodeEditableMaxlength"
                                :showCharCounter="false"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.internal_code"
                                xl="4"
                                lg="4">
                                <template v-if="internalCodePrefixLabel" v-slot:inputGroupPrepend>
                                    <span class="input-group-text br-internal-code-prefix" v-text="internalCodePrefixLabel"></span>
                                </template>
                                <template v-slot:inputGroupAppend v-if="!isUpdate">
                                    <button type="button" :class="['btn waves-effect', isUpdate ? 'btn-warning' : 'btn-primary']" @click="generateCodeAction" data-bs-toggle="tooltip" data-bs-placement="top" :title="MODULE.texts.form.generateCodeTooltip">
                                        <i class="fa fa-rotate"></i>
                                    </button>
                                </template>
                            </InputText>
                            <InputText
                                v-model="forms[entity].createUpdate.data.name"
                                hasDiv
                                :title="MODULE.texts.form.name"
                                :titleClass="[config.forms.classes.title]"
                                isRequired
                                maxlength="50"
                                showCharCounter
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.name"
                                xl="8"
                                lg="8"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.description"
                                hasDiv
                                :title="MODULE.texts.form.description"
                                :titleClass="[config.forms.classes.title]"
                                maxlength="100"
                                showCharCounter
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.description"
                                xl="6"
                                lg="6"/>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.duration_value"
                                hasDiv
                                :title="MODULE.texts.form.durationValue"
                                :titleClass="[config.forms.classes.title]"
                                isRequired
                                :decimals="0"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.duration_value"
                                xl="3"
                                lg="3"/>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.durationType"
                                :titleClass="[config.forms.classes.title]"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.duration_type"
                                xl="3"
                                lg="3">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.duration_type"
                                        :options="durationTypes"
                                        :class="config.forms.classes.select2"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.price"
                                hasDiv
                                :title="MODULE.texts.form.price"
                                :titleClass="[config.forms.classes.title]"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.price"
                                xl="4"
                                lg="4">
                                <template v-slot:inputGroupPrepend>
                                    <span class="input-group-text text-muted">
                                        <span v-text="forms[entity].createUpdate.data.currency?.data?.sign"></span>
                                    </span>
                                </template>
                            </InputNumber>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.min_price"
                                hasDiv
                                :title="MODULE.texts.form.minPrice"
                                :titleClass="[config.forms.classes.title]"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.min_price"
                                xl="4"
                                lg="4"
                                md="6"
                                sm="6">
                                <template v-slot:inputGroupPrepend>
                                    <span class="input-group-text text-muted">
                                        <span v-text="forms[entity].createUpdate.data.currency?.data?.sign"></span>
                                    </span>
                                </template>
                            </InputNumber>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.max_price"
                                hasDiv
                                :title="MODULE.texts.form.maxPrice"
                                :titleClass="[config.forms.classes.title]"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.max_price"
                                xl="4"
                                lg="4"
                                md="6"
                                sm="6">
                                <template v-slot:inputGroupPrepend>
                                    <span class="input-group-text text-muted">
                                        <span v-text="forms[entity].createUpdate.data.currency?.data?.sign"></span>
                                    </span>
                                </template>
                            </InputNumber>
                            <div class="form-group col-xl-4 col-lg-4 col-md-12 col-sm-12">
                                <label class="form-label fw-bold colon-at-end fs-6">Precio incluye IGV</label>
                                <div class="br-entity-publication-settings br-tax-inclusion-control">
                                    <label class="br-entity-switch" for="subscription_price_includes_tax">
                                        <input
                                            id="subscription_price_includes_tax"
                                            v-model="forms[entity].createUpdate.data.price_includes_tax"
                                            class="form-check-input"
                                            type="checkbox"
                                            role="switch">
                                        <span>
                                            <strong>Incluye IGV</strong>
                                            <small>Si está activo, el precio de venta ya contiene el impuesto y no incrementará el total al vender.</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.attendance_limit_per_day"
                                hasDiv
                                :title="MODULE.texts.form.attendanceLimit"
                                :titleClass="[config.forms.classes.title]"
                                :decimals="0"
                                :minValue="1"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.attendance_limit_per_day"
                                xl="4"
                                lg="4"/>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.commissionType"
                                :titleClass="[config.forms.classes.title]"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.commission_type"
                                xl="4"
                                lg="4">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.commission_type"
                                        :options="commissionTypeOptions"
                                        :class="config.forms.classes.select2"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                            <InputNumber
                                v-model="forms[entity].createUpdate.data.commission_value"
                                hasDiv
                                :title="MODULE.texts.form.commissionValue"
                                :titleClass="[config.forms.classes.title]"
                                :disabled="forms[entity].createUpdate.data.commission_type?.code === 'none'"
                                :minValue="0"
                                :maxValue="forms[entity].createUpdate.data.commission_type?.code === 'percentage' ? 100 : undefined"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.commission_value"
                                xl="4"
                                lg="4"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.benefits_text"
                                hasDiv
                                :title="MODULE.texts.form.benefits"
                                :titleClass="[config.forms.classes.title]"
                                maxlength="500"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.benefits"
                                xl="6"
                                lg="6"/>
                            <InputText
                                v-model="forms[entity].createUpdate.data.restrictions_text"
                                hasDiv
                                :title="MODULE.texts.form.restrictions"
                                :titleClass="[config.forms.classes.title]"
                                maxlength="500"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.restrictions"
                                xl="6"
                                lg="6"/>
                            <InputSlot
                                v-if="false"
                                hasDiv
                                :title="MODULE.texts.form.currency"
                                :titleClass="[config.forms.classes.title]"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.currency_id"
                                xl="3"
                                lg="3">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.currency"
                                        :options="currencies"
                                        :class="config.forms.classes.select2"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.categories"
                                :titleClass="[config.forms.classes.title]"
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.categories"
                                xl="12"
                                lg="12">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.categories"
                                        :options="categories"
                                        :class="config.forms.classes.select2"
                                        :clearable="true"
                                        :searchable="true"
                                        :multiple="true"/>
                                </template>
                            </InputSlot>
                            <InputSlot
                                hasDiv
                                :title="MODULE.texts.form.status"
                                :titleClass="[config.forms.classes.title]"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.errors?.status"
                                xl="4"
                                lg="4">
                                <template v-slot:input>
                                    <v-select
                                        v-model="forms[entity].createUpdate.data.status"
                                        :options="statuses"
                                        :class="config.forms.classes.select2"
                                        :clearable="false"
                                        :searchable="false"/>
                                </template>
                            </InputSlot>
                            <InputSlot
                                v-if="false"
                                hasDiv
                                :title="MODULE.texts.form.visibility"
                                :titleClass="[config.forms.classes.title]"
                                :isInputGroup="false"
                                :divInputClass="['d-flex flex-wrap justify-content-center align-items-end gap-4 pt-2']"
                                xl="8"
                                lg="8">
                                <template v-slot:input>
                                    <div class="form-check form-switch d-flex align-items-start">
                                        <input class="form-check-input" type="checkbox" role="switch" id="see_my_web" v-model="forms[entity].createUpdate.data.see_my_web"/>
                                        <label class="form-check-label d-flex align-items-center gap-1 ms-2 cursor-pointer mb-0" for="see_my_web">
                                            <i :class="['fa', forms[entity].createUpdate.data.see_my_web ? 'fa-globe text-success' : 'fa-globe text-muted']"></i>
                                            <span v-text="MODULE.texts.form.seeMyWeb"></span>
                                        </label>
                                    </div>
                                    <div class="form-check form-switch d-flex align-items-start" :class="{'opacity-50': !forms[entity].createUpdate.data.see_my_web}">
                                        <input class="form-check-input" type="checkbox" role="switch" id="see_my_web_price" v-model="forms[entity].createUpdate.data.see_my_web_price" :disabled="!forms[entity].createUpdate.data.see_my_web"/>
                                        <label class="form-check-label d-flex align-items-center gap-1 ms-2 mb-0" :class="[forms[entity].createUpdate.data.see_my_web ? 'cursor-pointer' : 'cursor-not-allowed']" for="see_my_web_price">
                                            <i :class="['fa-solid', forms[entity].createUpdate.data.see_my_web_price && forms[entity].createUpdate.data.see_my_web ? 'fa-dollar-sign text-success' : 'fa-dollar-sign text-muted']"></i>
                                            <span v-text="MODULE.texts.form.seeMyWebPrice"></span>
                                        </label>
                                    </div>
                                </template>
                            </InputSlot>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary waves-effect"
                        data-bs-dismiss="modal"
                        v-text="MODULE.texts.modal.close">
                    </button>
                    <button
                        type="button"
                        :class="['btn waves-effect', isUpdate ? 'btn-warning' : 'btn-primary']"
                        @click="saveEntity"
                        :disabled="isSaving">
                        <i class="fa fa-save"></i>
                        <span class="ms-2" v-text="MODULE.texts.modal.save"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts from "@System/Helpers/Alerts.js";
import { initCrudModule } from "@System/Helpers/ModuleFactory.js";
import * as Forms from "@System/Helpers/Forms.js";
import * as Requests from "@System/Helpers/Requests.js";
import * as Utils from "@System/Helpers/Utils.js";
import InternalCodePrefixMixin from "@System/Mixins/InternalCodePrefixMixin.js";

const MODULE_CONFIG = {
    entity: "subscriptions",
    menuId: "menu-items-subscriptions",
    pageTitle: "Membresías",
    pageTitleSingular: "Membresía",
    internalCodeEntity: "subscription",
    breadcrumbParent: "Catálogo comercial",
    perPage: 10
};

const FORM_FIELDS = {
    internal_code: "",
    name: "",
    description: "",
    duration_value: "",
    duration_type: null,
    price: "",
    price_includes_tax: true,
    min_price: "",
    max_price: "",
    attendance_limit_per_day: "",
    commission_type: null,
    commission_value: "",
    benefits_text: "",
    restrictions_text: "",
    currency: null,
    categories: [],
    see_my_web: false,
    see_my_web_price: false,
    status: null
};

const FORM_FIELD_CONFIG = {
    internal_code: {trim: true},
    name: {trim: true},
    description: {normalize: true},
    duration_value: {toNumber: true, minValue: 1},
    duration_type: {getCode: true},
    price: {toNumber: true, minValue: 0},
    price_includes_tax: {toBoolean: true},
    min_price: {toNumber: true, minValue: 0},
    max_price: {toNumber: true, minValue: 0},
    attendance_limit_per_day: {toNumber: true, minValue: 1},
    commission_type: {getCode: true},
    commission_value: {toNumber: true, minValue: 0},
    currency: {mapToField: "currency_id"},
    categories: {getArray: {mapTo: "category_id"}},
    see_my_web: {toBoolean: true},
    see_my_web_price: {toBoolean: true},
    status: {getCode: true}
};

const VALIDATION_RULES = {
    internal_code: {required: true},
    name: {required: true},
    description: {required: false},
    duration_value: {required: true, number: true, min: 1},
    duration_type: {required: true},
    price: {required: true, number: true, min: 0},
    price_includes_tax: {required: false},
    min_price: {required: false, number: true, min: 0},
    max_price: {required: false, number: true, min: 0},
    attendance_limit_per_day: {required: false, number: true, min: 1},
    commission_type: {required: false},
    commission_value: {required: false, number: true, min: 0},
    currency: {required: true},
    categories: {required: false},
    see_my_web: {required: false},
    see_my_web_price: {required: false},
    status: {required: true}
};

const ERROR_LABELS = {
    internal_code: "Código interno",
    name: "Nombre",
    description: "Descripción",
    duration_value: "Duración (valor)",
    duration_type: "Duración (tipo)",
    price: "Precio de venta",
    price_includes_tax: "Precio incluye IGV",
    min_price: "Precio mínimo",
    max_price: "Precio máximo",
    attendance_limit_per_day: "Límite diario",
    commission_type: "Tipo de comisión",
    commission_value: "Valor de comisión",
    benefits: "Beneficios",
    restrictions: "Restricciones",
    currency: "Moneda",
    categories: "Categorías",
    see_my_web: "Visualizar en mi página",
    see_my_web_price: "Visualizar precio",
    status: "Estado"
};

const FILTER_OPTIONS = [
    {code: "all", label: "Todos los filtros"},
    {code: "internal_code", label: "Código interno"},
    {code: "name", label: "Nombre"},
    {code: "description", label: "Descripción"},
    {code: "price", label: "Precio de venta"}
];

const TEXTS = {
    filters: {
        filterBy: "Filtrar por",
        search: "Búsqueda"
    },
    actions: {
        search: "Buscar",
        add: "Agregar",
        edit: "Editar"
    },
    form: {
        internalCode: "Código interno",
        name: "Nombre",
        description: "Descripción",
        durationValue: "Duración (valor)",
        durationType: "Duración (tipo)",
        price: "Precio de venta",
        minPrice: "Precio mínimo",
        maxPrice: "Precio máximo",
        attendanceLimit: "Límite diario de asistencias",
        commissionType: "Tipo de comisión",
        commissionValue: "Valor de comisión",
        benefits: "Beneficios (separados por coma)",
        restrictions: "Restricciones (separadas por coma)",
        currency: "Moneda",
        categories: "Categorías",
        status: "Estado",
        visibility: "Visibilidad web",
        seeMyWeb: "Visualizar en mi página",
        seeMyWebPrice: "Visualizar precio",
        generateCodeTooltip: "Generar aleatoriamente"
    },
    modal: {
        close: "Cerrar",
        save: "Guardar"
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

export default {
    name: "SubscriptionsMain",
    mixins: [InternalCodePrefixMixin],
    data() {

        const crudModule = initCrudModule({
            entity: MODULE.config.entity,
            menuId: MODULE.config.menuId,
            pageTitle: MODULE.config.pageTitle,
            pageTitleSingular: MODULE.config.pageTitleSingular
        });

        crudModule.lists[MODULE.config.entity].filters.filter_by = MODULE.filterOptions[0];
        crudModule.forms[MODULE.config.entity].createUpdate.data = Forms.initFormData(MODULE.formFields);

        return {
            ...crudModule,
            MODULE: MODULE,
            isInitialized: false,
            isSaving: false
        };

    },
    mounted: async function() {

        Utils.navbarItem("menu-parent-items", {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});

        Alerts.swals({type: "initParams"});

        const initParams = await this.initParams();

        this.isInitialized = true;

        if(initParams) {

            Alerts.swals({show: false});
            this.listEntity({});

        }

    },
    methods: {
        async initParams() {

            const response = await Requests.get({
                route: this.routeActions.initParams,
                data: {page: "main"},
                showAlert: true
            });

            if(response?.data?.config) {

                this.options.categories    = response.data.config.categories;
                this.options.currencies    = response.data.config.currencies;
                this.options.durationTypes = response.data.config.durationTypes;
                this.options.statuses      = response.data.config.statuses;
                this.options.internal_code_prefixes = response.data.config.internal_code_prefixes ?? {};

            }

            return Requests.valid({result: response});

        },
        // List
        async listEntity(params = null) {

            const entityList   = this.lists[this.entity];
            const emptyRecords = {total: 0, data: [], links: []};
            const filters      = Utils.cloneJson(entityList.filters);
            const filterData   = {per_page: this.MODULE.config.perPage, filter_by: filters.filter_by?.code, word: filters.word};

            entityList.extras.loading = true;

            try {

                const url = this.isDefined(params) && typeof params === "object" ? params.url : params;

                let requestUrl  = url || entityList.extras.route;
                let requestData = {};

                if(this.isDefined(url)) {

                    const urlObj = new URL(url, window.location.origin);

                    Object.entries(filterData).forEach(([key, value]) => {

                        if(this.isDefined(value) && !urlObj.searchParams.has(key)) urlObj.searchParams.set(key, value);

                    });

                    requestUrl = `${urlObj.pathname}${urlObj.search}`;

                }else {

                    requestData = filterData;

                }

                const response = await Requests.get({route: requestUrl, data: requestData, showAlert: true});

                entityList.records = response?.data ?? emptyRecords;

            }catch(error) {

                entityList.records = emptyRecords;

            }finally {

                entityList.extras.loading = false;

            }

        },
        handleSearch() {

            this.listEntity({});

        },
        // Forms
        openModal(record = null) {

            const entityForms = this.forms[this.entity].createUpdate;

            entityForms.errors = {};
            Forms.clearFormData(entityForms.data, this.MODULE.formFields);

            if(this.isDefined(record)) {

                // Map record data to form
                const currencyOption     = this.currencies.find(e => e.code === record?.currency_id),
                      categoryItems      = (record?.category_items ?? []).map(e => e?.category_id),
                      durationTypeOption = this.durationTypes.find(e => e.code === record?.duration_type),
                      statusOption       = this.statuses.find(e => e.code === record?.status);

                entityForms.data.id               = record.id;
                entityForms.data.internal_code    = this.stripInternalCodePrefix(record.internal_code);
                entityForms.data.name             = record.name;
                entityForms.data.description      = record.description;
                entityForms.data.duration_value   = record.duration_value;
                entityForms.data.duration_type    = durationTypeOption;
                entityForms.data.price            = record.price;
                entityForms.data.price_includes_tax = Boolean(record.price_includes_tax ?? true);
                entityForms.data.min_price        = record.min_price;
                entityForms.data.max_price        = record.max_price;
                entityForms.data.attendance_limit_per_day = record.attendance_limit_per_day;
                entityForms.data.commission_type  = this.commissionTypeOptions.find(e => e.code === (record.commission_type || "none")) ?? this.commissionTypeOptions[0];
                entityForms.data.commission_value = record.commission_value;
                entityForms.data.benefits_text    = this.arrayToText(record.benefits);
                entityForms.data.restrictions_text = this.arrayToText(record.restrictions);
                entityForms.data.currency         = currencyOption;
                entityForms.data.categories       = this.categories.filter(e => categoryItems.includes(e.code));
                entityForms.data.see_my_web       = Boolean(record.see_my_web ?? false);
                entityForms.data.see_my_web_price = Boolean(record.see_my_web_price ?? false);
                entityForms.data.status           = statusOption;

            }else {

                // Set defaults for new record
                entityForms.data.internal_code = this.generateCode({length: 7});
                entityForms.data.duration_value = 1;
                entityForms.data.duration_type  = this.durationTypes.length > 0 ? this.durationTypes[0] : null;
                entityForms.data.currency       = this.currencies.length > 0 ? this.currencies[0] : null;
                entityForms.data.price_includes_tax = true;
                entityForms.data.commission_type = this.commissionTypeOptions[0];
                entityForms.data.commission_value = "";
                entityForms.data.status         = this.statuses.length > 0 ? this.statuses[0] : null;

            }

            Alerts.modals({type: "show", id: entityForms.extras.modals.default.id});
            Alerts.tooltips({show: true, time: 500});

        },
        generateCodeAction() {

            this.forms[this.entity].createUpdate.data.internal_code = this.generateCode({length: 7});

            Alerts.toastrs({type: "success", subtitle: "Código interno generado correctamente."});
            Alerts.tooltips({show: false});

        },
        async saveEntity() {

            if(this.isSaving) return;

            const entityForms = this.forms[this.entity].createUpdate;

            Alerts.swals({});

            entityForms.errors = {};
            this.isSaving = true;

            try {

                const formData   = Utils.cloneJson(entityForms.data);
                const validation = this.validateFormData(formData);

                if(!validation.bool) {

                    Alerts.generateAlert({
                        type: "error",
                        messages: Forms.getDescriptiveErrors(validation.errors, this.MODULE.errorLabels),
                        msgContent: this.config.messages.errorValidate
                    });
                    this.isSaving = false;
                    return;

                }

                const preparedData  = Forms.prepareFormData(formData, this.MODULE.formFieldConfig);
                preparedData.benefits = this.textToArray(formData.benefits_text);
                preparedData.restrictions = this.textToArray(formData.restrictions_text);
                delete preparedData.benefits_text;
                delete preparedData.restrictions_text;
                const id            = preparedData.id;
                const isUpdate      = this.isDefined(id);
                const requestMethod = isUpdate ? "patch" : "post";
                const route         = this.routeActions[isUpdate ? "update" : "store"];
                const result        = await Requests[requestMethod]({route, data: preparedData, id});

                if(Requests.valid({result})) {

                    Alerts.modals({type: "hide", id: entityForms.extras.modals.default.id});
                    Alerts.generateAlert({type: "success", msgContent: result.data.msg});

                    Forms.clearFormData(entityForms.data, this.MODULE.formFields);

                    const entityList  = this.entityList;
                    const currentPage = entityList?.records?.current_page ?? 1;

                    this.listEntity({url: `${entityList?.extras?.route || ""}?page=${currentPage}`});

                }else {

                    Forms.handleFormResponseErrors({
                        result,
                        formErrorsObject: entityForms.errors,
                        config: this.config,
                        errorLabels: this.MODULE.errorLabels
                    });

                }

            }catch(error) {

                Alerts.generateAlert({type: "error", messages: [error], msgContent: this.config.messages.catchError});

            }finally {

                this.isSaving = false;

            }

        },
        validateFormData(formData) {

            const result = Forms.validateFormData(formData, this.validationRules, {isDescriptive: true, errorLabels: this.MODULE.errorLabels});

            // Custom validation for price ranges (only if price is valid)
            if(!result.errors.price) {

                const minPrice = parseFloat(formData.min_price) || 0;
                const maxPrice = parseFloat(formData.max_price) || 0;
                const price    = parseFloat(formData.price) || 0;

                if(minPrice > 0 && maxPrice > 0) {

                    if(maxPrice < minPrice) {

                        if(!result.errors.max_price) result.errors.max_price = [];

                        result.errors.max_price.push(`${this.MODULE.errorLabels.max_price}: Debe ser mayor o igual al precio mínimo`);
                        result.bool = false;

                    }else if(price < minPrice || price > maxPrice) {

                        if(!result.errors.price) result.errors.price = [];

                        result.errors.price.push(`${this.MODULE.errorLabels.price}: Debe estar entre ${minPrice} y ${maxPrice}`);
                        result.bool = false;

                    }

                }else if(minPrice > 0 && price < minPrice) {

                    if(!result.errors.price) result.errors.price = [];

                    result.errors.price.push(`${this.MODULE.errorLabels.price}: Debe ser mayor o igual al precio mínimo`);
                    result.bool = false;

                }else if(maxPrice > 0 && price > maxPrice) {

                    if(!result.errors.price) result.errors.price = [];

                    result.errors.price.push(`${this.MODULE.errorLabels.price}: Debe ser menor o igual al precio máximo`);
                    result.bool = false;

                }

            }

            return result;

        },
        // Others
        isDefined(value) {

            return Utils.isDefined({value});

        },
        generateCode({length}) {

            return Utils.generateCode({length});

        },
        separatorNumber(value) {

            return Utils.separatorNumber(value);

        },
        textToArray(value) {

            return String(value ?? "")
                .split(",")
                .map(item => item.trim())
                .filter(Boolean);

        },
        arrayToText(value) {

            return Array.isArray(value) ? value.filter(Boolean).join(", ") : "";

        },
        commissionLabel(record) {

            const type = record?.commission_type || "none";
            const value = Number(record?.commission_value || 0);

            if(type === "percentage") return `${this.separatorNumber(value)}%`;
            if(type === "fixed") return `${record?.currency?.sign || ""} ${this.separatorNumber(value)}`.trim();

            return "Sin comisión";

        },
    },
    computed: {
        entity() {

            return this.MODULE.config.entity;

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

            return (this.options?.categories?.records ?? []).map(e => ({code: e.id, label: e.name, data: e}));

        },
        currencies() {

            return (this.options?.currencies?.records ?? []).map(e => ({code: e.id, label: e.plural_name, data: e}));

        },
        durationTypes() {

            return (this.options?.durationTypes ?? []).map(e => ({code: e.code, label: e.label, plural: e.plural}));

        },
        statuses() {

            return (this.options?.statuses ?? []).map(e => ({code: e.code, label: e.label}));

        },
        commissionTypeOptions() {

            return [
                {code: "none", label: "Sin comisión"},
                {code: "percentage", label: "Porcentaje"},
                {code: "fixed", label: "Monto fijo por unidad"}
            ];

        },
        isUpdate() {

            return this.isDefined(this.forms[this.entity].createUpdate.data.id);

        },
        modalTitles() {

            return {
                createUpdate: this.forms[this.entity].createUpdate.extras.modals.default.titles
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

            if(!filterBy) return "Buscar...";

            return `Buscar por ${(filterBy.label || "...").toLowerCase()}`;

        },
        validationRules() {

            const rules = Utils.cloneJson(this.MODULE.validationRules);

            return rules;

        }
    }
};
</script>

<style scoped>
</style>
