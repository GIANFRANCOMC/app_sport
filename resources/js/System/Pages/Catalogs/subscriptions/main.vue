<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="row align-items-end g-3 mb-3 mb-md-4">
        <InputSlot
            hasDiv
            title="Filtrar por"
            :titleClass="[config.forms.classes.title]"
            xl="3"
            lg="4">
            <template v-slot:input>
                <v-select
                    v-model="lists.entity.filters.filter_by"
                    :options="filterByOptions"
                    :class="config.forms.classes.select2"
                    :clearable="false"
                    :searchable="false"/>
            </template>
        </InputSlot>
        <InputText
            v-model="lists.entity.filters.word"
            @enterKeyPressed="listEntity({})"
            hasDiv
            title="Búsqueda"
            :titleClass="[config.forms.classes.title]"
            :placeholder="'Buscar por ' + (lists.entity.filters.filter_by?.label || '...').toLowerCase()"
            xl="4"
            lg="4"/>
        <InputSlot
            hasDiv
            :isInputGroup="false"
            :divInputClass="['d-flex flex-wrap justify-content-start gap-2 gap-md-3']"
            xl="5"
            lg="4">
            <template v-slot:input>
                <button type="button" class="btn btn-info-1 waves-effect" @click="listEntity({})" :disabled="lists.entity.extras.loading">
                    <i class="fa fa-search"></i>
                    <span class="ms-2">Buscar</span>
                </button>
                <button type="button" class="btn btn-primary waves-effect" @click="modalCreateUpdateEntity({})" :disabled="lists.entity.extras.loading">
                    <i class="fa fa-plus"></i>
                    <span class="ms-2">Agregar</span>
                </button>
            </template>
        </InputSlot>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr class="text-center align-middle">
                    <th class="bg-secondary text-white fw-semibold" style="width: 20%;">CÓDIGO INTERNO</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 20%;">NOMBRE</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 15%;">DURACIÓN</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 15%;">PRECIO DE VENTA</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 5%;"></th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 10%;">ESTADO</th>
                    <th class="bg-secondary text-white fw-semibold" style="width: 15%;">ACCIONES</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0 bg-white">
                <template v-if="lists.entity.extras.loading">
                    <tr class="text-center">
                        <td colspan="99" class="py-4">
                            <Loader/>
                        </td>
                    </tr>
                </template>
                <template v-else>
                    <template v-if="lists.entity.records.total > 0">
                        <tr v-for="record in lists.entity.records.data" :key="record.id" class="text-center">
                            <td v-text="record.internal_code" class="fw-bold"></td>
                            <td v-text="record.name" class="text-start"></td>
                            <td>
                                <span v-text="record.formatted_duration" class="badge bg-label-primary fw-bold"></span>
                            </td>
                            <td>
                                <span v-text="record.currency?.sign"></span>
                                <span v-text="separatorNumber(record.price)" class="ms-2"></span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center gap-3 border border-light rounded px-2 py-1">
                                    <i :class="['fa fa-globe cursor-pointer', record.see_my_web ? 'text-success' : 'text-light']" data-bs-toggle="tooltip" data-bs-placement="top" :title="[record.see_my_web ? 'Visible en mi página' : 'No visible en mi página']"></i>
                                    <i v-if="record.see_my_web && record.see_my_web_price" :class="['fa-solid fa-dollar-sign cursor-pointer', record.see_my_web_price ? 'text-success' : 'text-light']" data-bs-toggle="tooltip" data-bs-placement="top" :title="[record.see_my_web_price ? 'Precio visible' : 'Precio no visible']"></i>
                                </div>
                            </td>
                            <td>
                                <span :class="['badge', 'fw-semibold', 'text-capitalize', { 'bg-label-success': ['active'].includes(record.status), 'bg-label-danger': ['inactive'].includes(record.status) }]" v-text="record.formatted_status"></span>
                            </td>
                            <td>
                                <InputSlot
                                    hasDiv
                                    :isInputGroup="false"
                                    :divInputClass="['d-flex flex-wrap justify-content-center gap-2 gap-md-1']"
                                    xl="12"
                                    lg="12">
                                    <template v-slot:input>
                                        <button type="button" class="btn btn-sm btn-warning waves-effect" @click="modalCreateUpdateEntity({record})">
                                            <i class="fa fa-pencil"></i>
                                            <span class="ms-2">Editar</span>
                                        </button>
                                    </template>
                                </InputSlot>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="text-center" colspan="99">
                                <WithoutData type="image"/>
                            </td>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center" v-if="!lists.entity.extras.loading && lists.entity.records?.total > 0">
        <Paginator :links="lists.entity.records.links" @clickPage="listEntity"/>
    </div>

    <!-- Modals -->
    <div class="modal fade" :id="forms.entity.createUpdate.extras.modals.default.id" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="forms.entity.createUpdate.extras.modals.default.titles[isDefined({value: forms.entity.createUpdate.data?.id}) ? 'update' : 'store']"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <InputText
                            v-model="forms.entity.createUpdate.data.internal_code"
                            hasDiv
                            title="Código interno"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.internal_code"
                            xl="5"
                            lg="5">
                            <template v-slot:inputGroupAppend>
                                <button type="button" :class="['btn waves-effect', isDefined({value: forms.entity.createUpdate.data?.id}) ? 'btn-warning' : 'btn-primary']" @click="setGenerateCode({length: 7})" data-bs-toggle="tooltip" data-bs-placement="top" title="Generar aleatoriamente">
                                    <i class="fa fa-rotate"></i>
                                </button>
                            </template>
                        </InputText>
                        <InputText
                            v-model="forms.entity.createUpdate.data.name"
                            hasDiv
                            title="Nombre"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.name"
                            xl="7"
                            lg="7"/>
                        <InputText
                            v-model="forms.entity.createUpdate.data.description"
                            hasDiv
                            title="Descripción"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.description"
                            xl="12"
                            lg="12"/>
                        <InputSlot
                            hasDiv
                            title="Tipo"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.duration_type"
                            xl="6"
                            lg="6">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.duration_type"
                                    :options="durationTypes"
                                    :clearable="false"
                                    :searchable="false"/>
                            </template>
                        </InputSlot>
                        <InputNumber
                            v-model="forms.entity.createUpdate.data.duration_value"
                            hasDiv
                            title="Duración"
                            isRequired
                            :decimals="0"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.duration_value"
                            xl="6"
                            lg="6">
                            <template v-slot:inputGroupAppend>
                                <button v-if="isDefined({value: getLabelDurationType({record: forms.entity.createUpdate.data})})" type="button" :class="['btn waves-effect', isDefined({value: forms.entity.createUpdate.data?.id}) ? 'btn-warning' : 'btn-primary']">
                                    <span v-text="getLabelDurationType({record: forms.entity.createUpdate.data})"></span>
                                </button>
                            </template>
                        </InputNumber>
                        <InputNumber
                            v-model="forms.entity.createUpdate.data.price"
                            hasDiv
                            title="Precio de venta"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.price"
                            xl="4"
                            lg="4"/>
                        <InputNumber
                            v-model="forms.entity.createUpdate.data.min_price"
                            hasDiv
                            title="Precio mínimo"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.min_price"
                            xl="4"
                            lg="4"
                            md="6"
                            sm="6"/>
                        <InputNumber
                            v-model="forms.entity.createUpdate.data.max_price"
                            hasDiv
                            title="Precio máximo"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.max_price"
                            xl="4"
                            lg="4"
                            md="6"
                            sm="6"/>
                        <InputSlot
                            v-if="false"
                            hasDiv
                            title="Moneda"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.currency"
                            xl="6"
                            lg="6">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.currency"
                                    :options="currencies"
                                    :clearable="false"
                                    :searchable="false"/>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            title="Categorías"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.categories"
                            xl="12"
                            lg="12">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.categories"
                                    :options="categories"
                                    :clearable="true"
                                    :searchable="true"
                                    :multiple="true"/>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            title="Estado"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.status"
                            xl="6"
                            lg="6">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.status"
                                    :options="statuses"
                                    :clearable="false"
                                    :searchable="false"/>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            :isInputGroup="false"
                            :divInputClass="['d-flex flex-wrap justify-content-end align-items-end h-100']"
                            xl="6"
                            lg="6">
                            <template v-slot:input>
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" v-model="forms.entity.createUpdate.data.see_my_web"/>
                                    <span class="ms-2">Visualizar en mi página</span>
                                </label>
                                <label class="form-check-label" v-if="forms.entity.createUpdate.data.see_my_web">
                                    <input class="form-check-input" type="checkbox" v-model="forms.entity.createUpdate.data.see_my_web_price"/>
                                    <span class="ms-2">Visualizar precio</span>
                                </label>
                            </template>
                        </InputSlot>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" :class="['btn waves-effect', isDefined({value: forms.entity.createUpdate.data?.id}) ? 'btn-warning' : 'btn-primary']" @click="createUpdateEntity()">
                        <i class="fa fa-save"></i>
                        <span class="ms-2">Guardar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts    from "../../Helpers/Alerts.js";
import * as Constants from "../../Helpers/Constants.js";
import * as Requests  from "../../Helpers/Requests.js";
import * as Utils     from "../../Helpers/Utils.js";

export default {
    components: {
        //
    },
    mounted: async function() {

        Utils.navbarItem("menu-item-catalogs", {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});
        Alerts.swals({type: "initParams"});

        let initParams = await this.initParams({}),
            initOthers = await this.initOthers({});

        if(initParams && initOthers) {

            Alerts.swals({show: false});
            this.listEntity({});

        }

    },
    data() {
        return {
            lists: {
                entity: {
                    extras: {
                        loading: false,
                        route: Requests.config({entity: "subscriptions", type: "list"})
                    },
                    filters: {
                        filter_by: null,
                        word: ""
                    },
                    records: {
                        total: 0
                    }
                }
            },
            forms: {
                entity: {
                    createUpdate: {
                        extras: {
                            modals: {
                                default: {
                                    id: Utils.uuid(),
                                    titles: {
                                        store: "Agregar",
                                        update: "Editar"
                                    }
                                }
                            }
                        },
                        data: {
                            id: null,
                            internal_code: "",
                            name: "",
                            description: "",
                            duration_type: null,
                            duration_value: "",
                            price: "",
                            min_price: "",
                            max_price: "",
                            currency: null,
                            categories: [],
                            see_my_web: false,
                            see_my_web_price: false,
                            status: null
                        },
                        errors: {}
                    }
                }
            },
            options: {},
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "subscriptions"}),
                    page: {
                        title: "Membresías",
                        active: true,
                        menu: {
                            id: "menu-item-catalogs-subscriptions"
                        }
                    }
                }
            }
        };
    },
    methods: {
        // Init
        async initParams({}) {

            let initParams = await Requests.get({route: this.config.entity.routes.initParams, data: {page: "main"}, showAlert: true});

            this.options.categories    = initParams.data?.config?.categories;
            this.options.currencies    = initParams.data?.config?.currencies;
            this.options.subscriptions = initParams.data?.config?.subscriptions;

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                this.lists.entity.filters.filter_by = this.filterByOptions[0];

                resolve(true);

            });

        },
        // Entity forms
        async listEntity({url = null}) {

            let filters = Utils.cloneJson(this.lists.entity.filters);
            const filterJson = {filter_by: filters?.filter_by?.code, word: filters.word};

            this.lists.entity.extras.loading = true;
            this.lists.entity.records        = (await Requests.get({route: url || this.lists.entity.extras.route, data: filterJson}))?.data;
            this.lists.entity.extras.loading = false;

        },
        // Forms
        setGenerateCode({length, showAlert = true}) {

            this.forms.entity.createUpdate.data.internal_code = this.generateCode({length});

            if(showAlert) {

                Alerts.toastrs({type: "success", subtitle: "Código interno generado correctamente."});
                Alerts.tooltips({show: false});

            }

        },
        getLabelDurationType({record = null}) {

            if(this.isDefined({value: this.options?.subscriptions?.durationTypes}) && this.isDefined({value: record})) {

                let durationType = this.options.subscriptions.durationTypes.filter(e => e.code == record?.duration_type?.code);

                return parseFloat(record.duration_value) > 1 ? durationType[0]?.plural : durationType[0]?.label;

            }

            return null;

        },
        modalCreateUpdateEntity({record = null}) {

            const functionName = "modalCreateUpdateEntity";

            // Alerts.swals({});
            this.clearForm({functionName});
            this.formErrors({functionName, type: "clear"});

            if(this.isDefined({value: record})) {

                const categoryItems = (record?.category_items ?? []).map(e => e?.category_id);

                let categories   = this.categories.filter(e => categoryItems.includes(e.code)),
                    currency     = this.currencies.filter(e => e.code === record?.currency_id)[0],
                    durationType = this.durationTypes.filter(e => e.code === record?.duration_type)[0],
                    status       = this.statuses.filter(e => e.code === record?.status)[0];

                this.forms.entity.createUpdate.data.id             = record?.id;
                this.forms.entity.createUpdate.data.internal_code  = record?.internal_code;
                this.forms.entity.createUpdate.data.name           = record?.name;
                this.forms.entity.createUpdate.data.description    = record?.description;
                this.forms.entity.createUpdate.data.duration_type  = durationType;
                this.forms.entity.createUpdate.data.duration_value = record?.duration_value;
                this.forms.entity.createUpdate.data.price          = record?.price;
                this.forms.entity.createUpdate.data.min_price      = record?.min_price ?? "";
                this.forms.entity.createUpdate.data.max_price      = record?.max_price ?? "";
                this.forms.entity.createUpdate.data.currency       = currency;
                this.forms.entity.createUpdate.data.categories     = categories;
                this.forms.entity.createUpdate.data.see_my_web     = Boolean(record?.see_my_web ?? false);
                this.forms.entity.createUpdate.data.see_my_web_price = Boolean(record?.see_my_web_price ?? false);
                this.forms.entity.createUpdate.data.status         = status;

            }else {

                this.setGenerateCode({length: 7, showAlert: false});
                this.forms.entity.createUpdate.data.currency = this.currencies[0];
                this.forms.entity.createUpdate.data.status   = this.statuses[0];

            }

            // Alerts.swals({show: false});
            Alerts.modals({type: "show", id: this.forms.entity.createUpdate.extras.modals.default.id});

        },
        async createUpdateEntity() {

            const functionName = "createUpdateEntity";

            Alerts.swals({});
            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms.entity.createUpdate.data);

            const validateForm = this.validateForm({functionName, form});

            if(validateForm?.bool) {

                form.currency_id   = form?.currency?.code;
                form.duration_type = form?.duration_type?.code;
                form.status        = form?.status?.code;

                delete form.currency;

                form.categories.forEach(category => {

                    category.category_id = category.code;

                    delete category.code;
                    delete category.label;

                });

                let createUpdate = await (this.isDefined({value: form.id}) ? Requests.patch({route: this.config.entity.routes.update, data: form, id: form.id}) :
                                                                             Requests.post({route: this.config.entity.routes.store, data: form}));

                if(Requests.valid({result: createUpdate})) {

                    Alerts.modals({type: "hide", id: this.forms.entity.createUpdate.extras.modals.default.id});
                    Alerts.toastrs({type: "success", subtitle: createUpdate?.data?.msg});
                    Alerts.swals({show: false});

                    this.clearForm({functionName});
                    this.listEntity({url: `${this.lists.entity.extras.route}?page=${this.lists.entity.records?.current_page ?? 1}`});

                }else {

                    this.formErrors({functionName, type: "set", errors: createUpdate?.errors ?? []});
                    Alerts.toastrs({type: "error", subtitle: createUpdate?.data?.msg});
                    Alerts.swals({show: false});

                }

            }else {

                this.formErrors({functionName, type: "set", errors: validateForm});
                Alerts.toastrs({type: "error", subtitle: this.config.messages.errorValidate});
                Alerts.swals({show: false});

            }

        },
        // Forms utils
        clearForm({functionName}) {

            switch(functionName) {
                case "modalCreateUpdateEntity":
                case "createUpdateEntity":
                    this.forms.entity.createUpdate.data.id             = null;
                    this.forms.entity.createUpdate.data.internal_code  = "";
                    this.forms.entity.createUpdate.data.name           = "";
                    this.forms.entity.createUpdate.data.description    = "";
                    this.forms.entity.createUpdate.data.duration_type  = null;
                    this.forms.entity.createUpdate.data.duration_value = "";
                    this.forms.entity.createUpdate.data.price          = "";
                    this.forms.entity.createUpdate.data.min_price      = "";
                    this.forms.entity.createUpdate.data.max_price      = "";
                    this.forms.entity.createUpdate.data.currency       = null;
                    this.forms.entity.createUpdate.data.categories    = [];
                    this.forms.entity.createUpdate.data.see_my_web     = false;
                    this.forms.entity.createUpdate.data.see_my_web_price = false;
                    this.forms.entity.createUpdate.data.status         = null;
                    break;
            }

        },
        formErrors({functionName, type = "clear", errors = []}) {

            if(["modalCreateUpdateEntity", "createUpdateEntity"].includes(functionName)) {

                this.forms.entity.createUpdate.errors = ["set"].includes(type) ? errors : [];

            }

        },
        validateForm({functionName, form = null, extras = null}) {

            let result = {
                bool: true
            };

            if(["createUpdateEntity"].includes(functionName)) {

                result.internal_code  = [];
                result.name           = [];
                result.description    = [];
                result.duration_type  = [];
                result.duration_value = [];
                result.price          = [];
                result.min_price      = [];
                result.max_price      = [];
                result.currency       = [];
                result.categories     = [];
                result.status         = [];

                if(!this.isDefined({value: form?.internal_code})) {

                    result.internal_code.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.name})) {

                    result.name.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.duration_type})) {

                    result.duration_type.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.duration_value})) {

                    result.duration_value.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }else if(!Utils.isNumber({value: form?.duration_value})) {

                    result.duration_value.push(this.config.forms.errors.labels.min_number_0);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.price})) {

                    result.price.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }else if(!Utils.isNumber({value: form?.price})) {

                    result.price.push(this.config.forms.errors.labels.min_number_0);
                    result.bool = false;

                }

                let isValidatedMinPrice = this.isDefined({value: form?.min_price}) && Number(form?.min_price) > 0,
                    isValidatedMaxPrice = this.isDefined({value: form?.max_price}) && Number(form?.max_price) > 0;

                if(isValidatedMinPrice && isValidatedMaxPrice) {

                    if(Number(form?.max_price) < Number(form?.min_price)) {

                        result.max_price.push(this.config.forms.errors.functions.min.numeric(form?.min_price));
                        result.bool = false;

                    }else if(Number(form?.price) < Number(form?.min_price) || Number(form?.price) > Number(form?.max_price)) {

                        result.price.push(this.config.forms.errors.functions.beetwen.numeric(form?.min_price, form?.max_price));
                        result.bool = false;

                    }

                }else if(isValidatedMinPrice) {

                    if(Number(form?.price) < Number(form?.min_price)) {

                        result.price.push(this.config.forms.errors.functions.min.numeric(form?.min_price));
                        result.bool = false;

                    }

                }else if(isValidatedMaxPrice) {

                    if(Number(form?.price) > Number(form?.max_price)) {

                        result.price.push(this.config.forms.errors.functions.max.numeric(form?.max_price));
                        result.bool = false;

                    }

                }

                if(!this.isDefined({value: form?.currency})) {

                    result.currency.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.status})) {

                    result.status.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

            }

            return result;

        },
        // Others
        isDefined({value}) {

            return Utils.isDefined({value});

        },
        generateCode({length}) {

            return Utils.generateCode({length});

        },
        separatorNumber(value) {

            return Utils.separatorNumber(value);

        }
    },
    computed: {
        breadcrumbTitles: function() {

            return [{title: "Catálogo comercial"}, this.config.entity.page];

        },
        filterByOptions: function() {

            return [
                {code: "all", label: "Todos"},
                {code: "internal_code", label: "Código interno"},
                {code: "name", label: "Nombre"},
                {code: "description", label: "Descripción"},
                {code: "price", label: "Precio de venta"}
            ];

        },
        categories: function() {

            return this.options?.categories?.records.map(e => ({code: e.id, label: e.name}));

        },
        currencies: function() {

            return this.options?.currencies?.records.map(e => ({code: e.id, label: e.plural_name}));

        },
        durationTypes: function() {

            return this.options?.subscriptions?.durationTypes.map(e => ({code: e.code, label: e.label}));

        },
        statuses: function() {

            return this.options?.subscriptions?.statuses.map(e => ({code: e.code, label: e.label}));

        }
    },
    watch: {
        "lists.entity.filters.filter_by": function(newValue, oldValue) {

            // this.listEntity({});

        }
    }
};
</script>
