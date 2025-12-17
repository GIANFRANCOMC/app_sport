<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="nav-align-top">
        <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link waves-effect justify-content-start" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-general" aria-controls="navs-pills-general" aria-selected="false" tabindex="-1">
                    <span class="d-flfex align-items-center fw-semibold">
                        <span>🏢</span>
                        <span class="ms-1">1. Información general</span>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link waves-effect justify-content-start" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-contacts" aria-controls="navs-pills-contacts" aria-selected="false" tabindex="-1">
                    <span class="d-flex align-items-center fw-semibold">
                        <span>☎️</span>
                        <span class="ms-1">2. Información de contacto y redes</span>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link waves-effect justify-content-start" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-branding" aria-controls="navs-pills-branding" aria-selected="false" tabindex="-1">
                    <span class="d-flfex align-items-center fw-semibold">
                        <span>🎨</span>
                        <span class="ms-2">3. Identidad visual</span>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link waves-effect justify-content-start" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-share" aria-controls="navs-pills-share" aria-selected="false" tabindex="-1">
                    <span class="d-flex align-items-center fw-semibold">
                        <span>🔗</span>
                        <span class="ms-1">4. Accesos compartido</span>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation" v-if="false">
                <button type="button" class="nav-link waves-effect justify-content-start" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-configuration" aria-controls="navs-pills-configuration" aria-selected="false" tabindex="-1">
                    <span class="d-flex align-items-center fw-semibold">
                        <span>⚙️</span>
                        <span class="ms-1">5. Parámetros</span>
                    </span>
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade" id="navs-pills-general" role="tabpanel">
                <div class="row g-3">
                    <InputSlot
                        hasDiv
                        title="Tipo de documento"
                        isRequired
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.identity_document_type"
                        xl="4"
                        lg="4">
                        <template v-slot:input>
                            <v-select
                                v-model="forms.entity.createUpdate.data.identity_document_type"
                                :options="identityDocumentTypes"
                                @close="tooltips({show: true, time: 500})"
                                :clearable="false"
                                :searchable="false"/>
                        </template>
                    </InputSlot>
                    <InputText
                        v-model="forms.entity.createUpdate.data.document_number"
                        hasDiv
                        title="Número de documento"
                        isRequired
                        maxlength="15"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.document_number"
                        xl="4"
                        lg="4">
                        <template v-slot:inputGroupAppend>
                            <template v-if="['ruc'].includes(forms.entity.createUpdate.data.identity_document_type?.data.code)">
                                <button :class="['btn waves-effect btn-primary']" type="button" @click="searchDocumentNumber({consult: forms.entity.createUpdate})" data-bs-toggle="tooltip" data-bs-placement="top" title="Buscar N° documento">
                                    <i class="fa fa-search"></i>
                                </button>
                            </template>
                        </template>
                    </InputText>
                    <InputText
                        v-model="forms.entity.createUpdate.data.legal_name"
                        hasDiv
                        title="Nombre legal"
                        isRequired
                        maxlength="100"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.legal_name"
                        xl="4"
                        lg="4"/>
                    <InputText
                        v-model="forms.entity.createUpdate.data.commercial_name"
                        hasDiv
                        title="Nombre comercial"
                        isRequired
                        maxlength="100"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.commercial_name"
                        xl="4"
                        lg="4"/>
                    <InputText
                        v-model="forms.entity.createUpdate.data.address"
                        hasDiv
                        title="Dirección"
                        maxlength="100"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.address"
                        xl="8"
                        lg="8"/>
                </div>
            </div>
            <div class="tab-pane fade" id="navs-pills-contacts" role="tabpanel">
                <div class="row g-3">
                    <InputText
                        v-model="forms.entity.createUpdate.data.telephone"
                        hasDiv
                        title="Teléfono"
                        maxlength="40"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.telephone"
                        xl="6"
                        lg="6"/>
                    <InputText
                        v-model="forms.entity.createUpdate.data.email"
                        hasDiv
                        title="Correo electrónico"
                        maxlength="100"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.email"
                        xl="6"
                        lg="6"/>
                    <InputText
                        v-model="forms.entity.createUpdate.data.facebook"
                        hasDiv
                        title="Facebook"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.facebook"
                        xl="6"
                        lg="6">
                        <template v-slot:inputGroupAppend>
                            <a :href="forms.entity.createUpdate.data.facebook" target="_blank" class="btn btn-label-info waves-effect" v-if="isValidUrl({url: forms.entity.createUpdate.data.facebook})">
                                <i class="fa fa-globe"></i>
                                <span class="ms-2">Visitar</span>
                            </a>
                        </template>
                    </InputText>
                    <InputText
                        v-model="forms.entity.createUpdate.data.instagram"
                        hasDiv
                        title="Instagram"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.instagram"
                        xl="6"
                        lg="6">
                        <template v-slot:inputGroupAppend>
                            <a :href="forms.entity.createUpdate.data.instagram" target="_blank" class="btn btn-label-danger waves-effect" v-if="isValidUrl({url: forms.entity.createUpdate.data.instagram})">
                                <i class="fa fa-globe"></i>
                                <span class="ms-2">Visitar</span>
                            </a>
                        </template>
                    </InputText>
                    <InputText
                        v-model="forms.entity.createUpdate.data.whatsapp"
                        hasDiv
                        title="Whatsapp"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.whatsapp"
                        xl="6"
                        lg="6">
                        <template v-slot:inputGroupAppend>
                            <a :href="forms.entity.createUpdate.data.whatsapp" target="_blank" class="btn btn-label-success waves-effect" v-if="isValidUrl({url: forms.entity.createUpdate.data.whatsapp})">
                                <i class="fa fa-globe"></i>
                                <span class="ms-2">Visitar</span>
                            </a>
                        </template>
                    </InputText>
                </div>
            </div>
            <div class="tab-pane fade" id="navs-pills-branding" role="tabpanel">
                <div class="row g-3">
                    <InputText
                        v-model="forms.entity.createUpdate.data.tagline"
                        hasDiv
                        title="Slogan"
                        maxlength="200"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.tagline"
                        xl="12"
                        lg="12"/>
                    <InputText
                        v-model="forms.entity.createUpdate.data.description"
                        hasDiv
                        title="Descripción general"
                        maxlength="300"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.description"
                        xl="12"
                        lg="12"/>
                </div>
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
                                    <img v-if="isDefined({value: forms.entity.createUpdate.data.logomark})" :src="getAsset(forms.entity.createUpdate.data.logomark, {type: 'storage'})" width="150px" height="150px" class="img-fluid"/>
                                    <template v-else>
                                        <img :src="getAsset(config.essential.ownerApp.assets.img.logomark, {type: 'none', back: 1})" width="150px" height="150px" class="img-fluid"/>
                                        <div class="alert alert-warning w-100 py-1 mb-0 text-nowrap">Es referencial</div>
                                    </template>
                                </td>
                                <td class="text-start">
                                    <InputSlot
                                        :hasDiv="false"
                                        title="Isotipo">
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
                                        <span class="ms-2 small">Tamaño máximo: {{ config.forms.inputs.maxSize / 1024 }} MB (PNG, JPG, JPEG).</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>
                                    <img v-if="isDefined({value: forms.entity.createUpdate.data.logotype})" :src="getAsset(forms.entity.createUpdate.data.logotype, {type: 'storage'})" width="150px" height="150px" class="img-fluid"/>
                                    <template v-else>
                                        <img :src="getAsset(config.essential.ownerApp.assets.img.logotype, {type: 'none', back: 1})" width="150px" height="150px" class="img-fluid"/>
                                        <div class="alert alert-warning w-100 py-1 mb-0 text-nowrap">Es referencial</div>
                                    </template>
                                </td>
                                <td class="text-start">
                                    <InputSlot
                                        :hasDiv="false"
                                        title="Logotipo">
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
                                        <span class="ms-2 small">Tamaño máximo: {{ config.forms.inputs.maxSize / 1024 }} MB (PNG, JPG, JPEG).</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>
                                    <img v-if="isDefined({value: forms.entity.createUpdate.data.combinationmark})" :src="getAsset(forms.entity.createUpdate.data.combinationmark, {type: 'storage'})" width="150px" height="150px" class="img-fluid"/>
                                    <template v-else>
                                        <img :src="getAsset(config.essential.ownerApp.assets.img.combinationmark, {type: 'none', back: 1})" width="150px" height="150px" class="img-fluid"/>
                                        <div class="alert alert-warning w-100 py-1 mb-0 text-nowrap">Es referencial</div>
                                    </template>
                                </td>
                                <td class="text-start">
                                    <InputSlot
                                        :hasDiv="false"
                                        title="Marca combinada">
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
                                        <span class="ms-2 small">Tamaño máximo: {{ config.forms.inputs.maxSize / 1024 }} MB (PNG, JPG, JPEG).</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>
                                    <img v-if="isDefined({value: forms.entity.createUpdate.data.login_image})" :src="getAsset(forms.entity.createUpdate.data.login_image, {type: 'storage'})" width="150px" height="150px" class="img-fluid"/>
                                    <template v-else>
                                        <img :src="getAsset(config.essential.ownerApp.assets.img.login_image, {type: 'none', back: 1})" width="150px" height="150px" class="img-fluid"/>
                                        <div class="alert alert-warning w-100 py-1 mb-0 text-nowrap">Es referencial</div>
                                    </template>
                                </td>
                                <td class="text-start">
                                    <InputSlot
                                        :hasDiv="false"
                                        title="Imagen de login">
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
                                        <span class="ms-2 small">Tamaño máximo: {{ config.forms.inputs.maxSize / 1024 }} MB (PNG, JPG, JPEG).</span>
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
                        xl="6"
                        lg="6">
                        <template v-slot:input>
                            <MyDashboardCompany/>
                        </template>
                    </InputSlot>
                    <InputSlot
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
                        v-model="forms.entity.createUpdate.data.slug"
                        hasDiv
                        title="Acceso MI WEB"
                        isRequired
                        :disabled="true"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.slug"
                        xl="6"
                        lg="6"/>
                    <InputText
                        v-model="forms.entity.createUpdate.data.token_api_misc"
                        hasDiv
                        title="Token API - Misc"
                        hasTextBottom
                        :textBottomInfo="forms.entity.createUpdate.errors?.token_api_misc"
                        xl="6"
                        lg="6"/>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3" v-if="isUpdate">
        <div class="row g-3">
            <div class="d-flex flex-row-reverse">
                <button type="button" class="btn waves-effect btn-primary" @click="createUpdateEntity()">
                    <i class="fa fa-save"></i>
                    <span class="ms-2">Guardar información</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts    from "@System/Helpers/Alerts.js";
import * as Constants from "@System/Helpers/Constants.js";
import * as Requests  from "@System/Helpers/Requests.js";
import * as Utils     from "@System/Helpers/Utils.js";

export default {
    components: {
        //
    },
    mounted: async function() {

        Utils.navbarItem("menu-parent-configuration", {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});
        Alerts.swals({type: "initParams"});

        let initParams = await this.initParams({}),
            initOthers = await this.initOthers({});

        if(initParams && initOthers) {

            Alerts.swals({show: false});

        }

    },
    data() {
        return {
            forms: {
                entity: {
                    createUpdate: {
                        data: {
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
                        },
                        errors: {}
                    }
                }
            },
            options: {},
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "companies"}),
                    page: {
                        title: "Mi empresa",
                        active: true,
                        menu: {
                            id: "menu-configuration-my_company"
                        }
                    }
                }
            }
        };
    },
    methods: {
        // ============================================
        // Initialization Methods
        // ============================================
        async initParams({}) {

            const initParams = await Requests.get({
                route: this.config.entity.routes.initParams,
                data: {page: "main"},
                showAlert: true
            });

            if(initParams?.data?.config) {

                this.options.companies             = initParams.data.config.companies;
                this.options.company               = initParams.data.config.company;
                this.options.identityDocumentTypes = initParams.data.config.identityDocumentTypes;

            }

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                const company = (this.options.company?.records ?? []).length > 0
                    ? this.options.company.records[0]
                    : null;

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

            const formData = this.forms.entity.createUpdate.data;

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

        // ============================================
        // Form Methods
        // ============================================
        async createUpdateEntity() {

            const functionName = "createUpdateEntity";

            Alerts.swals({});
            this.formErrors({functionName, type: "clear"});

            const form = Utils.cloneJson(this.forms.entity.createUpdate.data);
            const validateForm = this.validateForm({
                functionName,
                form,
                extras: {type: "descriptive"}
            });

            if(!validateForm?.bool) {

                Alerts.generateAlert({
                    messages: Utils.getErrors({errors: validateForm}),
                    keys: [
                        {column: "section", label: "Sección"},
                        {column: "msg", label: "Mensaje"}
                    ],
                    msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`,
                    width: 800
                });

                return;

            }

            const formData = this.prepareFormData(form);
            const response = await Requests.patch({
                route: this.config.entity.routes.update,
                formData: formData,
                id: form.id
            });

            if(Requests.valid({result: response})) {

                this.updateFormImages(response.data.company);
                Alerts.generateAlert({
                    type: "success",
                    msgContent: response?.data?.msg
                });

                this.clearForm({functionName});

            }else {

                this.formErrors({
                    functionName,
                    type: "set",
                    errors: response?.errors ?? []
                });

                Alerts.toastrs({
                    type: "error",
                    subtitle: response?.data?.msg
                });

                Alerts.swals({show: false});

            }

        },
        prepareFormData(form) {

            const formData = new FormData();

            // Prepare form fields
            form.identity_document_type_id = form?.identity_document_type?.code;
            form.status = form?.status?.code;

            delete form.identity_document_type;

            // Append form fields to FormData
            for(const key in form) {

                if(form.hasOwnProperty(key)) {

                    // Skip image fields that are not files
                    if(["logomark", "logotype", "combinationmark", "login_image"].includes(key) && !(form[key] instanceof File)) {

                        continue;

                    }

                    formData.append(key, form[key] ?? "");

                }

            }

            // Append file inputs
            this.appendFileToFormData(formData, "logomarkFileId", "logomark");
            this.appendFileToFormData(formData, "logotypeFileId", "logotype");
            this.appendFileToFormData(formData, "combinationmarkFileId", "combinationmark");
            this.appendFileToFormData(formData, "loginImageFileId", "login_image");

            return formData;

        },
        appendFileToFormData(formData, elementId, fieldName) {

            const fileElement = document.getElementById(elementId);

            if(fileElement?.files?.length > 0) {

                formData.append(fieldName, fileElement.files[0]);

            }

        },
        updateFormImages(company) {

            const formData = this.forms.entity.createUpdate.data;

            formData.logomark        = company?.logomark;
            formData.logotype        = company?.logotype;
            formData.combinationmark = company?.combinationmark;
            formData.login_image     = company?.login_image;

        },

        // ============================================
        // Form Utility Methods
        // ============================================
        clearForm({functionName}) {

            if(functionName === "createUpdateEntity") {

                const fileInputIds = [
                    "logomarkFileId",
                    "logotypeFileId",
                    "combinationmarkFileId",
                    "loginImageFileId"
                ];

                fileInputIds.forEach(id => {

                    const element = document.getElementById(id);

                    if(element) {

                        element.value = "";

                    }

                });

            }

        },
        formErrors({functionName, type = "clear", errors = []}) {

            if(functionName === "createUpdateEntity") {

                this.forms.entity.createUpdate.errors = type === "set" ? errors : [];

            }

        },
        validateForm({functionName, form = null, extras = null}) {

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

            if(functionName !== "createUpdateEntity") {

                return result;

            }

            const isDescriptive = extras?.type === "descriptive";
            const getPrefix = (label) => isDescriptive ? `${label}: ` : "";

            const sections = {
                general: {label: "Información general"},
                contacts: {label: "Información de contacto y redes"},
                branding: {label: "Identidad visual"}
            };

            // Validate required fields
            this.validateRequiredField(result, form?.identity_document_type, "identity_document_type", sections.general.label, getPrefix("Tipo de documento"));
            this.validateRequiredField(result, form?.document_number, "document_number", sections.general.label, getPrefix("Número de documento"));
            this.validateRequiredField(result, form?.legal_name, "legal_name", sections.general.label, getPrefix("Nombre legal"));
            this.validateRequiredField(result, form?.commercial_name, "commercial_name", sections.general.label, getPrefix("Nombre comercial"));

            // Validate image files
            this.validateImageFile(result, "logomarkFileId", "logomark", sections.branding.label, getPrefix("Isotipo"));
            this.validateImageFile(result, "logotypeFileId", "logotype", sections.branding.label, getPrefix("Logotipo"));
            this.validateImageFile(result, "combinationmarkFileId", "combinationmark", sections.branding.label, getPrefix("Marca combinada"));
            this.validateImageFile(result, "loginImageFileId", "login_image", sections.branding.label, getPrefix("Imagen de login"));

            return result;

        },
        validateRequiredField(result, value, fieldName, section, prefix) {

            if(!this.isDefined({value})) {

                result[fieldName].push({
                    section: section,
                    msg: `${prefix}${this.config.forms.errors.labels.required}`
                });

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

        // ============================================
        // Helper Methods
        // ============================================
        async searchDocumentNumber({consult}) {

            const formJson = {
                document_number: consult.data.document_number,
                type: consult.data.identity_document_type?.data.code
            };

            if(!this.isDefined({value: formJson.document_number})) {

                Alerts.generateAlert({
                    msgContent: "Debe ingresar el número de documento para realizar la búsqueda."
                });

                return;

            }

            Alerts.swals({});

            try {

                const route = Requests.config({entity: "helpers", type: "searchDocumentNumber"});
                const response = await Requests.get({route, data: formJson});

                if(Requests.valid({result: response})) {

                    const data = response.data.data;
                    const formData = this.forms.entity.createUpdate.data;

                    formData.legal_name      = data?.legal_name ?? "";
                    formData.commercial_name = data?.commercial_name ?? "";
                    formData.address         = data?.address ?? "";

                    Alerts.toastrs({
                        type: "success",
                        subtitle: response?.data?.msg
                    });

                }else {

                    Alerts.toastrs({
                        type: "error",
                        subtitle: response?.data?.msg
                    });

                }

            }finally {

                Alerts.swals({show: false});
                Alerts.tooltips({show: false});

            }

        },

        // ============================================
        // Utility Methods
        // ============================================
        isDefined({value}) {

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
        breadcrumbTitles: function() {

            return [{title: "Configuración"}, this.config.entity.page];

        },
        identityDocumentTypes: function() {

            return this.options?.identityDocumentTypes?.records.map(e => ({code: e.id, label: e.name, data: e}));

        },
        statuses: function() {

            return this.options?.companies?.statuses.map(e => ({code: e.code, label: e.label}));

        },
        isUpdate: function() {

            return this.isDefined({value: this.forms.entity.createUpdate.data?.id});

        }
    }
};
</script>
