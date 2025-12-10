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

        Utils.navbarItem("menu-item-configuration", {addClass: "open"});
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
                            id: "menu-item-configuration-companies"
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

            this.options.companies             = initParams.data?.config?.companies;
            this.options.company               = initParams.data?.config?.company;
            this.options.identityDocumentTypes = initParams.data?.config?.identityDocumentTypes;

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                const company = (this.options.company?.records ?? []).length > 0 ? this.options.company?.records[0] : null;

                let identityDocumentType = this.identityDocumentTypes.find(e => e.code === company?.identity_document_type_id),
                    status               = this.statuses.find(e => e.code === company?.status);

                this.forms.entity.createUpdate.data.id                     = company?.id;
                this.forms.entity.createUpdate.data.slug                   = company?.slug;
                this.forms.entity.createUpdate.data.identity_document_type = identityDocumentType;
                this.forms.entity.createUpdate.data.document_number        = company?.document_number;
                this.forms.entity.createUpdate.data.legal_name             = company?.legal_name;
                this.forms.entity.createUpdate.data.commercial_name        = company?.commercial_name;
                this.forms.entity.createUpdate.data.tagline                = company?.tagline;
                this.forms.entity.createUpdate.data.description            = company?.description;
                this.forms.entity.createUpdate.data.address                = company?.address;
                this.forms.entity.createUpdate.data.telephone              = company?.telephone;
                this.forms.entity.createUpdate.data.email                  = company?.email;
                this.forms.entity.createUpdate.data.token_api_misc         = company?.token_api_misc;
                this.forms.entity.createUpdate.data.logomark               = company?.logomark;
                this.forms.entity.createUpdate.data.logotype               = company?.logotype;
                this.forms.entity.createUpdate.data.combinationmark        = company?.combinationmark;
                this.forms.entity.createUpdate.data.login_image            = company?.login_image;
                this.forms.entity.createUpdate.data.facebook               = company?.facebook;
                this.forms.entity.createUpdate.data.instagram              = company?.instagram;
                this.forms.entity.createUpdate.data.whatsapp               = company?.whatsapp;
                this.forms.entity.createUpdate.data.status                 = status;

                const tabTrigger = document.querySelector(`[data-bs-target="#navs-pills-general"]`);
                const tab = new bootstrap.Tab(tabTrigger);
                tab.show();

                this.tooltips({show: true, time: 500});

                resolve(true);

            });

        },
        // Forms
        async createUpdateEntity() {

            const functionName = "createUpdateEntity";

            Alerts.swals({});
            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms.entity.createUpdate.data);

            const validateForm = this.validateForm({functionName, form, extras: {type: "descriptive"}});

            if(validateForm?.bool) {

                form.identity_document_type_id = form?.identity_document_type?.code;
                form.status = form?.status?.code;

                delete form.identity_document_type;

                let formData = new FormData();

                for(let key in form) {

                    if(form.hasOwnProperty(key)) {

                        if(["logomark", "logotype", "combinationmark", "login_image"].includes(key) && !(form[key] instanceof File)) continue;

                        formData.append(key, form[key] ?? "");

                    }

                }

                const logomarkFile        = document.getElementById("logomarkFileId");
                const logotypeFile        = document.getElementById("logotypeFileId");
                const combinationmarkFile = document.getElementById("combinationmarkFileId");
                const loginImageFile      = document.getElementById("loginImageFileId");

                if(logomarkFile && logomarkFile.files && logomarkFile.files.length > 0) {

                    formData.append("logomark", logomarkFile.files[0]);

                }

                if(logotypeFile && logotypeFile.files && logotypeFile.files.length > 0) {

                    formData.append("logotype", logotypeFile.files[0]);

                }

                if(combinationmarkFile && combinationmarkFile.files && combinationmarkFile.files.length > 0) {

                    formData.append("combinationmark", combinationmarkFile.files[0]);

                }

                if(loginImageFile && loginImageFile.files && loginImageFile.files.length > 0) {

                    formData.append("login_image", loginImageFile.files[0]);

                }

                let createUpdate = await Requests.patch({route: this.config.entity.routes.update, formData: formData, id: form.id});

                if(Requests.valid({result: createUpdate})) {

                    this.forms.entity.createUpdate.data.logomark        = createUpdate.data.company?.logomark;
                    this.forms.entity.createUpdate.data.logotype        = createUpdate.data.company?.logotype;
                    this.forms.entity.createUpdate.data.combinationmark = createUpdate.data.company?.combinationmark;
                    this.forms.entity.createUpdate.data.login_image     = createUpdate.data.company?.login_image;

                    // Alerts.toastrs({type: "success", subtitle: createUpdate?.data?.msg});
                    // Alerts.swals({show: false});
                    Alerts.generateAlert({type: "success", msgContent: createUpdate?.data?.msg});

                    this.clearForm({functionName});

                }else {

                    this.formErrors({functionName, type: "set", errors: createUpdate?.errors ?? []});
                    Alerts.toastrs({type: "error", subtitle: createUpdate?.data?.msg});
                    Alerts.swals({show: false});

                }

            }else {

                // this.formErrors({functioname, type: "set", errors: validateForm});
                // Alerts.toastrs({type: "error", subtitle: this.config.messages.errorValidate});
                //Alerts.swals({show: false});
                Alerts.generateAlert({messages: Utils.getErrors({errors: validateForm}), keys: [{"column": "section", "label" : "Sección"}, {"column": "msg", "label" : "Mensaje"}], msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`, width: 800});

            }

        },
        // Forms utils
        clearForm({functionName}) {

            switch(functionName) {
                case "modalCreateUpdateEntity":
                case "createUpdateEntity":
                    document.getElementById("logomarkFileId").value        = "";
                    document.getElementById("logotypeFileId").value        = "";
                    document.getElementById("combinationmarkFileId").value = "";
                    document.getElementById("loginImageFileId").value      = "";
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

                result.identity_document_type = [];
                result.document_number        = [];
                result.legal_name             = [];
                result.commercial_name        = [];
                result.logomark               = [];
                result.logotype               = [];
                result.combinationmark        = [];
                result.login_image            = [];

                const logomarkFile        = document.getElementById("logomarkFileId");
                const logotypeFile        = document.getElementById("logotypeFileId");
                const combinationmarkFile = document.getElementById("combinationmarkFileId");
                const loginImageFile      = document.getElementById("loginImageFileId");

                const isDescriptive = ["descriptive"].includes(extras?.type);

                let sections = {
                    general: {
                        label: "Información general"
                    },
                    contacts: {
                        label: "Información de contacto y redes"
                    },
                    branding: {
                        label: "Identidad visual"
                    }
                };

                if(!this.isDefined({value: form?.identity_document_type})) {

                    result.identity_document_type.push({section: sections.general.label, msg: `${isDescriptive ? "Tipo de documento:" : ""} ${this.config.forms.errors.labels.required}`});
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.document_number})) {

                    result.document_number.push({section: sections.general.label, msg: `${isDescriptive ? "Número de documento:" : ""} ${this.config.forms.errors.labels.required}`});
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.legal_name})) {

                    result.legal_name.push({section: sections.general.label, msg: `${isDescriptive ? "Nombre legal:" : ""} ${this.config.forms.errors.labels.required}`});
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.commercial_name})) {

                    result.commercial_name.push({section: sections.general.label, msg: `${isDescriptive ? "Nombre comercial:" : ""} ${this.config.forms.errors.labels.required}`});
                    result.bool = false;

                }

                const allowedExtensions = ["png", "jpg", "jpeg"];

                if(logomarkFile && logomarkFile.files && logomarkFile.files.length > 0) {

                    const fileExtension = logomarkFile.files[0].name.split(".").pop().toLowerCase();

                    if(logomarkFile.files[0].size > this.config.forms.inputs.maxSize * 1024) {

                        result.logomark.push({section: sections.branding.label, msg: `${isDescriptive ? "Isotipo:" : ""} ${this.config.forms.errors.functions.maxSize.numeric(this.config.forms.inputs.maxSize / 1024)}`});
                        result.bool = false;

                    }else if(!allowedExtensions.includes(fileExtension)) {

                        result.logomark.push({section: sections.branding.label, msg: `${isDescriptive ? "Isotipo:" : ""} ${this.config.forms.errors.labels.not_valid_extension}`});
                        result.bool = false;

                    }

                }

                if(logotypeFile && logotypeFile.files && logotypeFile.files.length > 0) {

                    const fileExtension = logotypeFile.files[0].name.split(".").pop().toLowerCase();

                    if(logotypeFile.files[0].size > this.config.forms.inputs.maxSize * 1024) {

                        result.logotype.push({section: sections.branding.label, msg: `${isDescriptive ? "Logotipo:" : ""} ${this.config.forms.errors.functions.maxSize.numeric(this.config.forms.inputs.maxSize / 1024)}`});
                        result.bool = false;

                    }else if(!allowedExtensions.includes(fileExtension)) {

                        result.logotype.push({section: sections.branding.label, msg: `${isDescriptive ? "Logotipo:" : ""} ${this.config.forms.errors.labels.not_valid_extension}`});
                        result.bool = false;

                    }

                }

                if(combinationmarkFile && combinationmarkFile.files && combinationmarkFile.files.length > 0) {

                    const fileExtension = combinationmarkFile.files[0].name.split(".").pop().toLowerCase();

                    if(combinationmarkFile.files[0].size > this.config.forms.inputs.maxSize * 1024) {

                        result.combinationmark.push({section: sections.branding.label, msg: `${isDescriptive ? "Marca combinada:" : ""} ${this.config.forms.errors.functions.maxSize.numeric(this.config.forms.inputs.maxSize / 1024)}`});
                        result.bool = false;

                    }else if(!allowedExtensions.includes(fileExtension)) {

                        result.combinationmark.push({section: sections.branding.label, msg: `${isDescriptive ? "Marca combinada:" : ""} ${this.config.forms.errors.labels.not_valid_extension}`});
                        result.bool = false;

                    }

                }

                if(loginImageFile && loginImageFile.files && loginImageFile.files.length > 0) {

                    const fileExtension = loginImageFile.files[0].name.split(".").pop().toLowerCase();

                    if(loginImageFile.files[0].size > this.config.forms.inputs.maxSize * 1024) {

                        result.login_image.push({section: sections.branding.label, msg: `${isDescriptive ? "Imagen de login:" : ""} ${this.config.forms.errors.functions.maxSize.numeric(this.config.forms.inputs.maxSize / 1024)}`});
                        result.bool = false;

                    }else if(!allowedExtensions.includes(fileExtension)) {

                        result.login_image.push({section: sections.branding.label, msg: `${isDescriptive ? "Imagen de login:" : ""} ${this.config.forms.errors.labels.not_valid_extension}`});
                        result.bool = false;

                    }

                }

            }

            return result;

        },
        async searchDocumentNumber({consult}) {

            let route = Requests.config({entity: "helpers", type: "searchDocumentNumber"});
            const formJson = {document_number: consult.data.document_number, type: consult.data.identity_document_type?.data.code};

            if(!this.isDefined({value: formJson.document_number})) {

                Alerts.generateAlert({msgContent: `Debe ingresar el número de documento para realizar la búsqueda.`});
                return;

            }

            Alerts.swals({});

            let searchDocumentNumber = await Requests.get({route: route, data: formJson});

            if(Requests.valid({result: searchDocumentNumber})) {

                const data = searchDocumentNumber.data.data;

                this.forms.entity.createUpdate.data.legal_name      = `${data?.legal_name}`;
                this.forms.entity.createUpdate.data.commercial_name = `${data?.commercial_name}`;
                this.forms.entity.createUpdate.data.address         = `${data?.address}`;

                Alerts.toastrs({type: "success", subtitle: searchDocumentNumber?.data?.msg});
                Alerts.swals({show: false});

            }else {

                Alerts.toastrs({type: "error", subtitle: searchDocumentNumber?.data?.msg});
                Alerts.swals({show: false});

            }

            Alerts.tooltips({show: false});

        },
        // Others
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
