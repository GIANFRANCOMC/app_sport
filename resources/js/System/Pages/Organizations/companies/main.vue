<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="nav-align-top">
        <ul class="nav nav-tabs nav-fill" role="tablist">
            <li v-for="tab in tabItems" :key="tab.id" class="nav-item" role="presentation">
                <button type="button" class="nav-link waves-effect justify-content-start" role="tab" data-bs-toggle="tab" :data-bs-target="`#navs-pills-${tab.id}`" :aria-controls="`navs-pills-${tab.id}`" aria-selected="false" tabindex="-1">
                    <span class="d-flex align-items-center fw-semibold">
                        <i :class="['fa', tab.icon, 'me-2']"></i>
                        <span v-text="MODULE.texts.tabs[tab.textKey]"></span>
                    </span>
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade" id="navs-pills-general" role="tabpanel">
                <div class="row g-3">
                    <InputSlot
                        hasDiv
                        :title="MODULE.texts.form.identityDocumentType"
                        :titleClass="[config.forms.classes.title]"
                        isRequired
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.identity_document_type_id"
                        xl="4"
                        lg="4">
                        <template v-slot:input>
                            <v-select
                                v-model="forms[entity].createUpdate.data.identity_document_type"
                                :options="identityDocumentTypes"
                                :class="config.forms.classes.select2"
                                @close="tooltips({show: true, time: 500})"
                                :clearable="false"
                                :searchable="false"/>
                        </template>
                    </InputSlot>
                    <InputText
                        v-model="forms[entity].createUpdate.data.document_number"
                        hasDiv
                        :title="MODULE.texts.form.documentNumber"
                        :titleClass="[config.forms.classes.title]"
                        isRequired
                        :maxlength="documentNumberMaxLength"
                        showCharCounter
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.document_number"
                        xl="4"
                        lg="4">
                        <template v-slot:inputGroupAppend>
                            <template v-if="isDocumentTypeSearchable">
                                <button :class="['btn waves-effect btn-primary']" type="button" @click="searchDocumentNumber" data-bs-toggle="tooltip" data-bs-placement="top" :title="MODULE.texts.form.searchDocumentTooltip">
                                    <i class="fa fa-search"></i>
                                </button>
                            </template>
                        </template>
                    </InputText>
                    <InputText
                        v-model="forms[entity].createUpdate.data.legal_name"
                        hasDiv
                        :title="MODULE.texts.form.legalName"
                        :titleClass="[config.forms.classes.title]"
                        isRequired
                        maxlength="100"
                        showCharCounter
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.legal_name"
                        xl="4"
                        lg="4"/>
                    <InputText
                        v-model="forms[entity].createUpdate.data.commercial_name"
                        hasDiv
                        :title="MODULE.texts.form.commercialName"
                        :titleClass="[config.forms.classes.title]"
                        isRequired
                        maxlength="100"
                        showCharCounter
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.commercial_name"
                        xl="4"
                        lg="4"/>
                    <InputText
                        v-model="forms[entity].createUpdate.data.address"
                        hasDiv
                        :title="MODULE.texts.form.address"
                        :titleClass="[config.forms.classes.title]"
                        maxlength="100"
                        showCharCounter
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.address"
                        xl="8"
                        lg="8"/>
                    <InputText
                        v-model="forms[entity].createUpdate.data.tagline"
                        hasDiv
                        :title="MODULE.texts.form.tagline"
                        :titleClass="[config.forms.classes.title]"
                        maxlength="200"
                        showCharCounter
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.tagline"
                        xl="12"
                        lg="12"/>
                    <InputText
                        v-if="false"
                        v-model="forms[entity].createUpdate.data.description"
                        hasDiv
                        :title="MODULE.texts.form.description"
                        :titleClass="[config.forms.classes.title]"
                        maxlength="300"
                        showCharCounter
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.description"
                        xl="12"
                        lg="12"/>
                </div>
            </div>
            <div class="tab-pane fade" id="navs-pills-contacts" role="tabpanel">
                <div class="row g-3">
                    <InputText
                        v-model="forms[entity].createUpdate.data.telephone"
                        hasDiv
                        :title="MODULE.texts.form.telephone"
                        :titleClass="[config.forms.classes.title]"
                        maxlength="40"
                        showCharCounter
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.telephone"
                        xl="6"
                        lg="6"/>
                    <InputText
                        v-model="forms[entity].createUpdate.data.email"
                        hasDiv
                        :title="MODULE.texts.form.email"
                        :titleClass="[config.forms.classes.title]"
                        maxlength="100"
                        showCharCounter
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.email"
                        xl="6"
                        lg="6"/>
                    <InputText
                        v-model="forms[entity].createUpdate.data.facebook"
                        hasDiv
                        :title="MODULE.texts.form.facebook"
                        :titleClass="[config.forms.classes.title]"
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.facebook"
                        xl="6"
                        lg="6">
                        <template v-slot:inputGroupAppend>
                            <a :href="forms[entity].createUpdate.data.facebook" target="_blank" class="btn btn-label-info waves-effect" v-if="isValidUrl({url: forms[entity].createUpdate.data.facebook})">
                                <i class="fa fa-globe"></i>
                                <span class="ms-2">Visitar</span>
                            </a>
                        </template>
                    </InputText>
                    <InputText
                        v-model="forms[entity].createUpdate.data.instagram"
                        hasDiv
                        :title="MODULE.texts.form.instagram"
                        :titleClass="[config.forms.classes.title]"
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.instagram"
                        xl="6"
                        lg="6">
                        <template v-slot:inputGroupAppend>
                            <a :href="forms[entity].createUpdate.data.instagram" target="_blank" class="btn btn-label-danger waves-effect" v-if="isValidUrl({url: forms[entity].createUpdate.data.instagram})">
                                <i class="fa fa-globe"></i>
                                <span class="ms-2">Visitar</span>
                            </a>
                        </template>
                    </InputText>
                    <InputText
                        v-model="forms[entity].createUpdate.data.whatsapp"
                        hasDiv
                        :title="MODULE.texts.form.whatsapp"
                        :titleClass="[config.forms.classes.title]"
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.whatsapp"
                        xl="6"
                        lg="6">
                        <template v-slot:inputGroupAppend>
                            <a :href="forms[entity].createUpdate.data.whatsapp" target="_blank" class="btn btn-label-success waves-effect" v-if="isValidUrl({url: forms[entity].createUpdate.data.whatsapp})">
                                <i class="fa fa-globe"></i>
                                <span class="ms-2">Visitar</span>
                            </a>
                        </template>
                    </InputText>
                </div>
            </div>
            <div class="tab-pane fade" id="navs-pills-branding" role="tabpanel">
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr class="text-center align-middle">
                                <th class="bg-secondary text-white fw-semibold" style="width: 40%;">IMAGEN</th>
                                <th class="bg-secondary text-white fw-semibold" style="width: 60%;">NOMBRE DEL RECURSO</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0 bg-white">
                            <tr class="text-center">
                                <td>
                                    <img v-if="isDefined(forms[entity].createUpdate.data.logomark)" :src="getAsset(forms[entity].createUpdate.data.logomark, {type: 'storage'})" width="150px" height="150px" class="img-fluid"/>
                                    <template v-else>
                                        <img :src="getAsset(config.essential.ownerApp.assets.img.logomark, {type: 'none', back: 1})" width="150px" height="150px" class="img-fluid"/>
                                        <div class="alert alert-warning w-100 py-1 mb-0 text-nowrap">Es referencial</div>
                                    </template>
                                </td>
                                <td class="text-start">
                                    <InputSlot
                                        :hasDiv="false"
                                        :title="MODULE.texts.form.logomark"
                                        :titleClass="[config.forms.classes.title]">
                                        <template v-slot:input>
                                            <input type="file" class="form-control" id="logomarkFileId" accept="image/png, image/jpg, image/jpeg"/>
                                        </template>
                                    </InputSlot>
                                    <div class="d-block mt-1 text-nowrap">
                                        <i class="fa fa-info-circle text-info"></i>
                                        <span class="ms-2 small">Nota: Ícono o símbolo gráfico que representa la marca sin texto.</span>
                                    </div>
                                    <div class="d-block mt-1 text-nowrap">
                                        <i class="fa fa-warning text-warning"></i>
                                        <span class="ms-2 small">Tamaño máximo: {{ maxFileSizeMB }} MB (PNG, JPG, JPEG).</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>
                                    <img v-if="isDefined(forms[entity].createUpdate.data.logotype)" :src="getAsset(forms[entity].createUpdate.data.logotype, {type: 'storage'})" width="150px" height="150px" class="img-fluid"/>
                                    <template v-else>
                                        <img :src="getAsset(config.essential.ownerApp.assets.img.logotype, {type: 'none', back: 1})" width="150px" height="150px" class="img-fluid"/>
                                        <div class="alert alert-warning w-100 py-1 mb-0 text-nowrap">Es referencial</div>
                                    </template>
                                </td>
                                <td class="text-start">
                                    <InputSlot
                                        :hasDiv="false"
                                        :title="MODULE.texts.form.logotype"
                                        :titleClass="[config.forms.classes.title]">
                                        <template v-slot:input>
                                            <input type="file" class="form-control" id="logotypeFileId" accept="image/png, image/jpg, image/jpeg"/>
                                        </template>
                                    </InputSlot>
                                    <div class="d-block mt-1 text-nowrap">
                                        <i class="fa fa-info-circle text-info"></i>
                                        <span class="ms-2 small">Nota: Versión textual del nombre de la marca. Debe contener solo letras, sin símbolos.</span>
                                    </div>
                                    <div class="d-block mt-1 text-nowrap">
                                        <i class="fa fa-warning text-warning"></i>
                                        <span class="ms-2 small">Tamaño máximo: {{ maxFileSizeMB }} MB (PNG, JPG, JPEG).</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>
                                    <img v-if="isDefined(forms[entity].createUpdate.data.combinationmark)" :src="getAsset(forms[entity].createUpdate.data.combinationmark, {type: 'storage'})" width="150px" height="150px" class="img-fluid"/>
                                    <template v-else>
                                        <img :src="getAsset(config.essential.ownerApp.assets.img.combinationmark, {type: 'none', back: 1})" width="150px" height="150px" class="img-fluid"/>
                                        <div class="alert alert-warning w-100 py-1 mb-0 text-nowrap">Es referencial</div>
                                    </template>
                                </td>
                                <td class="text-start">
                                    <InputSlot
                                        :hasDiv="false"
                                        :title="MODULE.texts.form.combinationmark"
                                        :titleClass="[config.forms.classes.title]">
                                        <template v-slot:input>
                                            <input type="file" class="form-control" id="combinationmarkFileId" accept="image/png, image/jpg, image/jpeg"/>
                                        </template>
                                    </InputSlot>
                                    <div class="d-block mt-1 text-nowrap">
                                        <i class="fa fa-info-circle text-info"></i>
                                        <span class="ms-2 small">Nota: Combinación del logotipo y el isotipo en una sola unidad visual.</span>
                                    </div>
                                    <div class="d-block mt-1 text-nowrap">
                                        <i class="fa fa-warning text-warning"></i>
                                        <span class="ms-2 small">Tamaño máximo: {{ maxFileSizeMB }} MB (PNG, JPG, JPEG).</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>
                                    <img v-if="isDefined(forms[entity].createUpdate.data.login_image)" :src="getAsset(forms[entity].createUpdate.data.login_image, {type: 'storage'})" width="150px" height="150px" class="img-fluid"/>
                                    <template v-else>
                                        <img :src="getAsset(config.essential.ownerApp.assets.img.login_image, {type: 'none', back: 1})" width="150px" height="150px" class="img-fluid"/>
                                        <div class="alert alert-warning w-100 py-1 mb-0 text-nowrap">Es referencial</div>
                                    </template>
                                </td>
                                <td class="text-start">
                                    <InputSlot
                                        :hasDiv="false"
                                        :title="MODULE.texts.form.loginImage"
                                        :titleClass="[config.forms.classes.title]">
                                        <template v-slot:input>
                                            <input type="file" class="form-control" id="loginImageFileId" accept="image/png, image/jpg, image/jpeg"/>
                                        </template>
                                    </InputSlot>
                                    <div class="d-block mt-1">
                                        <i class="fa fa-info-circle text-info"></i>
                                        <span class="ms-2 small">Nota: Imagen que se muestra en la pantalla de inicio de sesión. Generalmente puede ser un logo, una ilustración institucional o una imagen decorativa que represente a la entidad.</span>
                                    </div>
                                    <div class="d-block mt-1 text-nowrap">
                                        <i class="fa fa-warning text-warning"></i>
                                        <span class="ms-2 small">Tamaño máximo: {{ maxFileSizeMB }} MB (PNG, JPG, JPEG).</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="navs-pills-share" role="tabpanel">
                <div class="row g-3">
                    <InputSlot
                        hasDiv
                        :isInputGroup="false"
                        :divInputClass="['d-flex justify-content-center']"
                        xl="12"
                        lg="12">
                        <template v-slot:input>
                            <MyDashboardCompany/>
                        </template>
                    </InputSlot>
                    <InputSlot
                        v-if="false"
                        hasDiv
                        :isInputGroup="false"
                        :divInputClass="['d-flex justify-content-center']"
                        xl="6"
                        lg="6">
                        <template v-slot:input>
                            <MyWebCompany/>
                        </template>
                    </InputSlot>
                </div>
            </div>
            <div class="tab-pane fade" id="navs-pills-configuration" role="tabpanel" v-if="false">
                <div class="row g-3">
                    <InputText
                        v-model="forms[entity].createUpdate.data.slug"
                        hasDiv
                        :title="MODULE.texts.form.slug"
                        :titleClass="[config.forms.classes.title]"
                        isRequired
                        :disabled="true"
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.slug"
                        xl="6"
                        lg="6"/>
                    <InputText
                        v-model="forms[entity].createUpdate.data.token_api_misc"
                        hasDiv
                        :title="MODULE.texts.form.tokenApiMisc"
                        :titleClass="[config.forms.classes.title]"
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.token_api_misc"
                        xl="6"
                        lg="6"/>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3" v-if="hasRecord">
        <div class="row g-3">
            <div class="d-flex flex-row-reverse">
                <button type="button" class="btn waves-effect btn-primary" @click="saveEntity" :disabled="isSaving">
                    <i class="fa fa-save"></i>
                    <span class="ms-2" v-text="MODULE.texts.actions.save"></span>
                </button>
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

const MODULE_CONFIG = {
    entity: "companies",
    menuId: "menu-configuration-my_company",
    pageTitle: "Mi empresa",
    pageTitleSingular: "Mi empresa",
    breadcrumbParent: "Configuración"
};

const FORM_FIELDS = {
    id: null,
    slug: "",
    identity_document_type: null,
    document_number: "",
    legal_name: "",
    commercial_name: "",
    tagline: "",
    description: "",
    address: "",
    telephone: "",
    email: "",
    token_api_misc: "",
    logomark: "",
    logotype: "",
    combinationmark: "",
    login_image: "",
    facebook: "",
    instagram: "",
    whatsapp: "",
    status: null
};

const FORM_FIELD_CONFIG = {
    identity_document_type: {mapToField: "identity_document_type_id"},
    document_number: {trim: true},
    legal_name: {trim: true},
    commercial_name: {trim: true},
    tagline: {trim: true, normalize: true},
    description: {trim: true, normalize: true},
    address: {trim: true, normalize: true},
    telephone: {trim: true, normalize: true},
    email: {trim: true, normalize: true},
    facebook: {trim: true, normalize: true},
    instagram: {trim: true, normalize: true},
    whatsapp: {trim: true, normalize: true},
    status: {getCode: true}
};

const TAB_ITEMS = [
    {id: "general", icon: "fa-building", textKey: "general", visible: true},
    {id: "contacts", icon: "fa-phone", textKey: "contacts", visible: true},
    {id: "branding", icon: "fa-palette", textKey: "branding", visible: true},
    {id: "share", icon: "fa-link", textKey: "share", visible: true},
    {id: "configuration", icon: "fa-cog", textKey: "configuration", visible: false}
];

const TEXTS = {
    form: {
        identityDocumentType: "Tipo de documento",
        documentNumber: "Número de documento",
        legalName: "Nombre legal",
        commercialName: "Nombre comercial",
        address: "Dirección",
        telephone: "Teléfono",
        email: "Correo electrónico",
        facebook: "Facebook",
        instagram: "Instagram",
        whatsapp: "Whatsapp",
        tagline: "Slogan",
        description: "Descripción general",
        logomark: "Isotipo",
        logotype: "Logotipo",
        combinationmark: "Marca combinada",
        loginImage: "Imagen de login",
        searchDocumentTooltip: "Buscar N° documento",
        slug: "Acceso MI WEB",
        tokenApiMisc: "Token API - Misc"
    },
    tabs: {
        general: "1. Información general",
        contacts: "2. Información de contacto y redes",
        branding: "3. Identidad visual",
        share: "4. Accesos compartidos",
        configuration: "5. Parámetros"
    },
    actions: {
        save: "Guardar información"
    }
};

const MODULE = {
    config: MODULE_CONFIG,
    formFields: FORM_FIELDS,
    formFieldConfig: FORM_FIELD_CONFIG,
    texts: TEXTS,
    tabItems: TAB_ITEMS
};

export default {
    name: "CompaniesMain",
    data() {

        const crudModule = initCrudModule({
            entity: MODULE.config.entity,
            menuId: MODULE.config.menuId,
            pageTitle: MODULE.config.pageTitle,
            pageTitleSingular: MODULE.config.pageTitleSingular
        });

        crudModule.forms[MODULE.config.entity].createUpdate.data = Forms.initFormData(MODULE.formFields);

        return {
            ...crudModule,
            MODULE: MODULE,
            isInitialized: false,
            isSaving: false
        };

    },
    mounted: async function() {

        Utils.navbarItem("menu-parent-configuration", {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});

        Alerts.swals({type: "initParams"});

        const initParams = await this.initParams();

        this.isInitialized = true;

        if(initParams) {

            const initOthers = await this.initOthers();

            if(initOthers) {

                Alerts.swals({show: false});

            }

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

                this.options.company               = response.data.config.company;
                this.options.identityDocumentTypes = response.data.config.identityDocumentTypes;
                this.options.statuses              = response.data.config.statuses;

            }

            return Requests.valid({result: response});

        },
        async initOthers() {

            return new Promise(resolve => {

                const company = (this.options.company?.records ?? []).length > 0 ? this.options.company.records[0] : null;

                if(company) {

                    this.populateFormFromCompany(company);

                }

                this.showFirstTab();
                this.tooltips({show: true, time: 500});

                resolve(true);

            });

        },
        populateFormFromCompany(company) {

            const identityDocumentType = this.identityDocumentTypes.find(e => e.code === company?.identity_document_type_id);
            const status               = this.statuses.find(e => e.code === company?.status);
            const formData             = this.forms[this.entity].createUpdate.data;

            formData.id                     = company?.id;
            formData.slug                   = company?.slug;
            formData.identity_document_type = identityDocumentType;
            formData.document_number        = company?.document_number;
            formData.legal_name             = company?.legal_name;
            formData.commercial_name        = company?.commercial_name;
            formData.tagline                = company?.tagline;
            formData.description            = company?.description;
            formData.address                = company?.address;
            formData.telephone              = company?.telephone;
            formData.email                  = company?.email;
            formData.token_api_misc         = company?.token_api_misc;
            formData.logomark               = company?.logomark;
            formData.logotype               = company?.logotype;
            formData.combinationmark        = company?.combinationmark;
            formData.login_image            = company?.login_image;
            formData.facebook               = company?.facebook;
            formData.instagram              = company?.instagram;
            formData.whatsapp               = company?.whatsapp;
            formData.status                 = status;

        },
        showFirstTab() {

            const tabTrigger = document.querySelector(`[data-bs-target="#navs-pills-general"]`);

            if(tabTrigger) {

                const tab = new bootstrap.Tab(tabTrigger);
                tab.show();

            }

        },
        // Forms
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
                        messages: Utils.getErrors({errors: validation}),
                        keys: [{column: "section", label: "Sección"}, {column: "msg", label: "Mensaje"}],
                        msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`,
                        width: 800
                    });

                    this.isSaving = false;
                    return;

                }

                const preparedData  = Forms.prepareFormData(formData, this.MODULE.formFieldConfig);
                const id            = preparedData.id;
                const isUpdate      = this.isDefined(id);
                const requestMethod = isUpdate ? "patch" : "post";
                const route         = this.routeActions[isUpdate ? "update" : "store"];
                const result        = await Requests[requestMethod]({route, data: preparedData, id});

                if(Requests.valid({result})) {

                    this.updateFormImages(result.data.company);
                    Alerts.generateAlert({type: "success", msgContent: result.data.msg});

                    Forms.clearFormData(entityForms.data, this.MODULE.formFields);

                }else {

                    Forms.handleFormResponseErrors({result, formErrorsObject: entityForms.errors, config: this.config});

                }

            }catch(error) {

                Alerts.generateAlert({type: "error", messages: [error], msgContent: this.config.messages.catchError});

            }finally {

                this.isSaving = false;

            }

        },
        prepareFormData(formData, formFieldConfig) {

            const preparedData  = Forms.prepareFormData(formData, formFieldConfig);

            // Append file inputs
            this.appendFileToFormData(preparedData, "logomarkFileId", "logomark");
            this.appendFileToFormData(preparedData, "logotypeFileId", "logotype");
            this.appendFileToFormData(preparedData, "combinationmarkFileId", "combinationmark");
            this.appendFileToFormData(preparedData, "loginImageFileId", "login_image");

            return preparedData;

        },
        updateFormImages(company) {

            const formData = this.forms[this.entity].createUpdate.data;

            formData.logomark        = company?.logomark;
            formData.logotype        = company?.logotype;
            formData.combinationmark = company?.combinationmark;
            formData.login_image     = company?.login_image;

        },
        clearForm() {

            const fileInputIds = ["logomarkFileId", "logotypeFileId", "combinationmarkFileId", "loginImageFileId"];

            fileInputIds.forEach(id => {

                const element = document.getElementById(id);

                if(element) {

                    element.value = "";

                }

            });

        },
        validateFormData(formData) {

            const result = {
                bool: true,
                identity_document_type: [],
                document_number: [],
                legal_name: [],
                commercial_name: [],
                logomark: [],
                logotype: [],
                combinationmark: [],
                login_image: []
            };

            const sections = {
                general: {label: "Información general"},
                contacts: {label: "Información de contacto y redes"},
                branding: {label: "Identidad visual"}
            };

            // Validate required fields
            this.validateRequiredField(result, formData?.identity_document_type, "identity_document_type", sections.general.label, getPrefix("Tipo de documento"));
            this.validateRequiredField(result, formData?.document_number, "document_number", sections.general.label, getPrefix("Número de documento"));
            this.validateRequiredField(result, formData?.legal_name, "legal_name", sections.general.label, getPrefix("Nombre legal"));
            this.validateRequiredField(result, formData?.commercial_name, "commercial_name", sections.general.label, getPrefix("Nombre comercial"));

            // Validate image files
            this.validateImageFile(result, "logomarkFileId", "logomark", sections.branding.label, getPrefix("Isotipo"));
            this.validateImageFile(result, "logotypeFileId", "logotype", sections.branding.label, getPrefix("Logotipo"));
            this.validateImageFile(result, "combinationmarkFileId", "combinationmark", sections.branding.label, getPrefix("Marca combinada"));
            this.validateImageFile(result, "loginImageFileId", "login_image", sections.branding.label, getPrefix("Imagen de login"));

            return result;

        },
        validateRequiredField(result, value, fieldName, section, prefix) {

            if(!this.isDefined(value)) {

                result[fieldName].push({section: section, msg: `${prefix}${this.config.forms.errors.labels.required}`});

                result.bool = false;

            }

        },
        validateImageFile(result, elementId, fieldName, section, prefix) {

            const fileElement = document.getElementById(elementId);

            if(!fileElement?.files?.length) {

                return;

            }

            const file = fileElement.files[0];
            const allowedExtensions = ["png", "jpg", "jpeg"];
            const fileExtension = file.name.split(".").pop().toLowerCase();
            const maxSize = this.config.forms.inputs.maxSize * 1024;

            if(file.size > maxSize) {

                result[fieldName].push({
                    section: section,
                    msg: `${prefix}${this.config.forms.errors.functions.maxSize.numeric(this.config.forms.inputs.maxSize / 1024)}`
                });

                result.bool = false;

            }else if(!allowedExtensions.includes(fileExtension)) {

                result[fieldName].push({
                    section: section,
                    msg: `${prefix}${this.config.forms.errors.labels.not_valid_extension}`
                });

                result.bool = false;

            }

        },
        async searchDocumentNumber() {

            const entityForms          = this.forms[this.entity].createUpdate;
            const documentNumber      = entityForms.data.document_number;
            const identityDocumentType = entityForms.data.identity_document_type;

            if(!this.isDefined(documentNumber)) {

                Alerts.generateAlert({msgContent: "Debe ingresar el número de documento para realizar la búsqueda."});
                return;

            }

            if(!this.isDefined(identityDocumentType)) {

                Alerts.generateAlert({msgContent: "Debe seleccionar el tipo de documento."});
                return;

            }

            Alerts.swals({});

            const route    = Requests.config({entity: "helpers", type: "searchDocumentNumber"});
            const formJson = {document_number: documentNumber, type: identityDocumentType.data?.code};
            const response = await Requests.get({route, data: formJson});

            if(Requests.valid({result: response})) {

                const data = response.data.data;

                entityForms.data.legal_name      = data?.legal_name ?? "";
                entityForms.data.commercial_name = data?.commercial_name ?? "";
                entityForms.data.address         = data?.address ?? "";

                Alerts.toastrs({type: "success", subtitle: response?.data?.msg});

            }else {

                Alerts.toastrs({type: "error", subtitle: response?.data?.msg});

            }

            Alerts.swals({show: false});
            Alerts.tooltips({show: false});

        },
        // Utils
        isDefined(value) {

            return Utils.isDefined({value});

        },
        isValidUrl({url}) {

            return Utils.isValidUrl({url});

        },
        getAsset(path, {type, back}) {

            return Utils.getAsset(path, {type, back});

        },
        tooltips({show = true, time = 10}) {

            Alerts.tooltips({show, time});

        }
    },
    computed: {
        entity() {

            return this.MODULE.config.entity;

        },
        routeActions() {

            return this.config.entity.routes;

        },
        breadcrumbTitles() {

            return [
                {title: this.MODULE.config.breadcrumbParent},
                this.config.entity.page
            ];

        },
        identityDocumentTypes() {

            return (this.options?.identityDocumentTypes?.records ?? []).map(e => ({code: e.id, label: e.name, data: e}));

        },
        statuses() {

            return (this.options?.statuses ?? []).map(e => ({code: e.code, label: e.label}));

        },
        hasRecord() {

            return this.isDefined(this.forms[this.entity].createUpdate.data?.id);

        },
        isDocumentTypeSearchable() {

            const documentType = this.forms[this.entity].createUpdate.data.identity_document_type?.data;

            return documentType?.is_searchable === true || documentType?.is_searchable === 1;

        },
        documentNumberMinLength() {

            const documentType = this.forms[this.entity].createUpdate.data.identity_document_type?.data;

            if(documentType?.min_length) {

                return parseInt(documentType.min_length);

            }

            return 1;

        },
        documentNumberMaxLength() {

            const documentType = this.forms[this.entity].createUpdate.data.identity_document_type?.data;

            if(documentType?.max_length) {

                return parseInt(documentType.max_length);

            }

            return 15;

        },
        maxFileSizeMB() {

            return this.config.forms.inputs.maxSize / 1024;

        },
        tabItems() {

            return MODULE.tabItems.filter(tab => tab.visible);

        }
    },
    watch: {
        "forms.companies.createUpdate.data.identity_document_type": {
            handler(newValue) {

                if(this.isDefined(newValue)) {

                    const maxLength    = this.documentNumberMaxLength;
                    const currentValue = this.forms[this.entity].createUpdate.data.document_number?.toString() || "";

                    if(currentValue.length > maxLength) {

                        this.forms[this.entity].createUpdate.data.document_number = currentValue.substring(0, maxLength);

                    }

                }

            },
            immediate: false
        }
    }
};
</script>

<style scoped>
</style>
