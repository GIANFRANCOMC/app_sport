<template>
    <a href="javascript:void(0)" @click="modalCreateUpdateEntity({})" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar cliente">
        <i class="fa fa-plus-circle"></i>
    </a>

    <!-- Modals -->
    <div class="modal fade" :id="forms.entity.createUpdate.extras.modals.default.id" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="forms.entity.createUpdate.extras.modals.default.titles['store']"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <InputSlot
                            hasDiv
                            title="Tipo de documento"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.identity_document_type"
                            xl="6"
                            lg="6">
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
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.document_number"
                            xl="6"
                            lg="6">
                            <template v-slot:inputGroupAppend>
                                <template v-if="[2, 4].includes(forms.entity.createUpdate.data.identity_document_type?.code)">
                                    <button :class="['btn waves-effect', 'btn-primary']" type="button" @click="searchDocumentNumber({consult: forms.entity.createUpdate})" data-bs-toggle="tooltip" data-bs-placement="top" title="Buscar documento">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </template>
                            </template>
                        </InputText>
                        <InputText
                            v-model="forms.entity.createUpdate.data.name"
                            hasDiv
                            title="Nombre"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.name"
                            xl="12"
                            lg="12"/>
                        <InputText
                            v-model="forms.entity.createUpdate.data.email"
                            hasDiv
                            title="Correo electrónico"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.email"
                            xl="6"
                            lg="6"/>
                        <InputText
                            v-model="forms.entity.createUpdate.data.phone_number"
                            hasDiv
                            title="Celular"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.phone_number"
                            xl="6"
                            lg="6"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" :class="['btn waves-effect', 'btn-primary']" @click="createUpdateEntity()">
                        <i class="fa fa-save"></i>
                        <span class="ms-2">Guardar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Register Biometric Fingerprint -->
    <div class="modal fade" :id="forms.entity.registerBiometric.extras.modals.default.id" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold">Registrar huella biométrica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="fa fa-info-circle me-2"></i>
                        <span>Para registrar la huella, primero debe registrar la huella en el dispositivo biométrico físico. El sistema asociará el ID del usuario del dispositivo con el cliente.</span>
                    </div>
                    <div class="row g-3">
                        <InputSlot
                            hasDiv
                            title="Cliente"
                            :isInputGroup="false"
                            xl="12"
                            lg="12">
                            <template v-slot:input>
                                <span v-text="forms.entity.registerBiometric.data.customerName" class="fw-semibold"></span>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            title="Dispositivo biométrico"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.registerBiometric.errors?.biometric_device"
                            xl="12"
                            lg="12">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.registerBiometric.data.biometric_device"
                                    :options="biometricDevices"
                                    :class="config.forms.classes.select2"
                                    :clearable="false"
                                    :searchable="true"
                                    placeholder="Seleccione un dispositivo ..."
                                    label="label"
                                    @close="tooltips({show: true, time: 500})"/>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            title="ID de usuario en el dispositivo"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.registerBiometric.errors?.device_user_id"
                            xl="6"
                            lg="6">
                            <template v-slot:input>
                                <input
                                    v-model="forms.entity.registerBiometric.data.device_user_id"
                                    type="number"
                                    class="form-control"
                                    placeholder="Ingrese el ID del usuario"
                                    min="1"/>
                            </template>
                            <template v-slot:defaultAppend>
                                <small class="mt-1 text-muted fw-semibold ms-2">Se asignará automáticamente si está vacío</small>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            title="Índice del dedo"
                            hasTextBottom
                            :textBottomInfo="forms.entity.registerBiometric.errors?.finger_index"
                            xl="6"
                            lg="6">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.registerBiometric.data.finger_index"
                                    :options="fingerIndexes"
                                    :class="config.forms.classes.select2"
                                    :clearable="false"
                                    :searchable="false"/>
                            </template>
                        </InputSlot>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" @click="skipBiometricRegistration()">Omitir</button>
                    <button type="button" class="btn btn-primary waves-effect" @click="registerBiometricFingerprint()">
                        <i class="fa fa-fingerprint"></i>
                        <span class="ms-2">Registrar huella</span>
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
    name: "AddCustomer",
    emits: ["postAction"],
    props: {
        options: {
            type: Object,
            required: false,
            default: {}
        }
    },
    data() {
        return {
            forms: {
                entity: {
                    createUpdate: {
                        extras: {
                            modals: {
                                default: {
                                    id: Utils.uuid(),
                                    titles: {
                                        store: "Agregar cliente"
                                    }
                                }
                            }
                        },
                        data: {
                            id: null,
                            identity_document_type: null,
                            document_number: "",
                            name: "",
                            email: "",
                            phone_number: "",
                            status: null
                        },
                        errors: {}
                    },
                    registerBiometric: {
                        extras: {
                            modals: {
                                default: {
                                    id: Utils.uuid()
                                }
                            }
                        },
                        data: {
                            customerId: null,
                            customerName: "",
                            biometric_device: null,
                            device_user_id: "",
                            finger_index: null
                        },
                        errors: {}
                    }
                }
            },
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "customers"})
                }
            }
        };
    },
    methods: {
        // Forms
        modalCreateUpdateEntity({record = null}) {

            const functionName = "modalCreateUpdateEntity";

            // Alerts.swals({});
            this.clearForm({functionName});
            this.formErrors({functionName, type: "clear"});

            this.forms.entity.createUpdate.data.status = this.statuses[0];

            // Alerts.swals({show: false});
            Alerts.modals({type: "show", id: this.forms.entity.createUpdate.extras.modals.default.id});
            Alerts.tooltips({show: false});

        },
        async createUpdateEntity() {

            const functionName = "createUpdateEntity";

            Alerts.swals({});
            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms.entity.createUpdate.data);

            const validateForm = this.validateForm({functionName, form});

            if(validateForm?.bool) {

                form.identity_document_type_id = form?.identity_document_type?.code;
                form.status = form?.status?.code;

                delete form.identity_document_type;

                let createUpdate = await Requests.post({route: this.config.entity.routes.store, data: form});

                if(Requests.valid({result: createUpdate})) {

                    Alerts.modals({type: "hide", id: this.forms.entity.createUpdate.extras.modals.default.id});
                    Alerts.toastrs({type: "success", subtitle: createUpdate?.data?.msg});
                    Alerts.swals({show: false});

                    this.clearForm({functionName});
                    
                    // Si hay dispositivos biométricos disponibles, ofrecer registrar huella
                    const customerId = createUpdate?.data?.data?.id;
                    if(customerId && (this.biometricDevices?.length ?? 0) > 0) {
                        
                        this.modalRegisterBiometric({customerId, customerName: createUpdate?.data?.data?.name});

                    } else {

                        this.handlePostAction({response: createUpdate});

                    }

                }else {

                    this.formErrors({functionName, type: "set", errors: createUpdate?.errors ?? []});
                    Alerts.toastrs({type: "error", subtitle: createUpdate?.data?.msg});
                    Alerts.swals({show: false});

                    this.handlePostAction({response: createUpdate});

                }

            }else {

                this.formErrors({functionName, type: "set", errors: validateForm});
                Alerts.toastrs({type: "error", subtitle: this.config.messages.errorValidate});
                Alerts.swals({show: false});

                this.handlePostAction({response: validateForm});

            }

        },
        // Forms utils
        clearForm({functionName}) {

            switch(functionName) {
                case "modalCreateUpdateEntity":
                case "createUpdateEntity":
                    this.forms.entity.createUpdate.data.id                     = null;
                    this.forms.entity.createUpdate.data.identity_document_type = null;
                    this.forms.entity.createUpdate.data.document_number        = "";
                    this.forms.entity.createUpdate.data.name                   = "";
                    this.forms.entity.createUpdate.data.email                  = "";
                    this.forms.entity.createUpdate.data.phone_number           = "";
                    this.forms.entity.createUpdate.data.status                 = null;
                    break;

                case "modalRegisterBiometric":
                case "registerBiometricFingerprint":
                    this.forms.entity.registerBiometric.data.customerId        = null;
                    this.forms.entity.registerBiometric.data.customerName      = "";
                    this.forms.entity.registerBiometric.data.biometric_device  = null;
                    this.forms.entity.registerBiometric.data.device_user_id    = "";
                    this.forms.entity.registerBiometric.data.finger_index      = null;
                    break;
            }

        },
        formErrors({functionName, type = "clear", errors = []}) {

            if(["modalCreateUpdateEntity", "createUpdateEntity"].includes(functionName)) {

                this.forms.entity.createUpdate.errors = ["set"].includes(type) ? errors : [];

            } else if(["modalRegisterBiometric", "registerBiometricFingerprint"].includes(functionName)) {

                this.forms.entity.registerBiometric.errors = ["set"].includes(type) ? errors : [];

            }

        },
        validateForm({functionName, form = null, extras = null}) {

            let result = {
                bool: true
            };

            if(["createUpdateEntity"].includes(functionName)) {

                result.identity_document_type = [];
                result.document_number        = [];
                result.name                   = [];
                result.email                  = [];
                result.phone_number           = [];
                result.status                 = [];

                if(!this.isDefined({value: form?.identity_document_type})) {

                    result.identity_document_type.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.document_number})) {

                    result.document_number.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.name})) {

                    result.name.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

                // if(!this.isDefined({value: form?.email})) {

                    // result.email.push(this.config.forms.errors.labels.required);
                    // result.bool = false;

                // }

                // if(!this.isDefined({value: form?.phone_number})) {

                    // result.phone_number.push(this.config.forms.errors.labels.required);
                    // result.bool = false;

                // }

                if(!this.isDefined({value: form?.status})) {

                    result.status.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

            }

            return result;

        },
        async searchDocumentNumber({consult}) {

            let route = Requests.config({entity: "helpers", type: "searchDocumentNumber"});
            const formJson = {document_number: consult.data.document_number, type: consult.data.identity_document_type?.code};

            Alerts.swals({});

            let searchDocumentNumber = await Requests.get({route: route, data: formJson});

            if(Requests.valid({result: searchDocumentNumber})) {

                const data = searchDocumentNumber.data.data;

                if(consult.data.identity_document_type?.code == 2) {

                    this.forms.entity.createUpdate.data.name = `${data?.first_name} ${data?.last_name} ${data?.second_last_name}`;

                }else if(consult.data.identity_document_type?.code == 4) {

                    this.forms.entity.createUpdate.data.name = `${data?.legal_name}`;

                }

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
        tooltips({show = true, time = 10}) {

            Alerts.tooltips({show, time});

        },
        // Biometric
        modalRegisterBiometric({customerId, customerName}) {

            const functionName = "modalRegisterBiometric";

            this.clearForm({functionName});
            this.formErrors({functionName, type: "clear"});

            this.forms.entity.registerBiometric.data.customerId = customerId;
            this.forms.entity.registerBiometric.data.customerName = customerName;
            this.forms.entity.registerBiometric.data.finger_index = this.fingerIndexes[0];

            Alerts.modals({type: "show", id: this.forms.entity.registerBiometric.extras.modals.default.id});
            Alerts.tooltips({show: false});

        },
        async registerBiometricFingerprint() {

            const functionName = "registerBiometricFingerprint";

            Alerts.swals({});
            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms.entity.registerBiometric.data);

            const validateForm = this.validateBiometricForm({form});

            if(validateForm?.bool) {

                const data = {
                    biometric_device_id: form.biometric_device?.code,
                    device_user_id: form.device_user_id || null,
                    finger_index: form.finger_index?.code || 0
                };

                let route = Requests.config({entity: "customers", type: "registerBiometricFingerprint"});
                route = route.replace("{id}", form.customerId);

                let registerFingerprint = await Requests.post({route: route, data: data});

                if(Requests.valid({result: registerFingerprint})) {

                    Alerts.modals({type: "hide", id: this.forms.entity.registerBiometric.extras.modals.default.id});
                    Alerts.toastrs({type: "success", subtitle: registerFingerprint?.data?.msg || "Huella registrada exitosamente"});
                    Alerts.swals({show: false});

                    this.clearForm({functionName});
                    this.handlePostAction({response: registerFingerprint});

                } else {

                    this.formErrors({functionName, type: "set", errors: registerFingerprint?.errors ?? []});
                    Alerts.toastrs({type: "error", subtitle: registerFingerprint?.data?.msg || "Error al registrar la huella"});
                    Alerts.swals({show: false});

                }

            } else {

                this.formErrors({functionName, type: "set", errors: validateForm});
                Alerts.toastrs({type: "error", subtitle: this.config.messages.errorValidate});
                Alerts.swals({show: false});

            }

        },
        skipBiometricRegistration() {

            Alerts.modals({type: "hide", id: this.forms.entity.registerBiometric.extras.modals.default.id});
            this.clearForm({functionName: "modalRegisterBiometric"});
            this.handlePostAction({response: {bool: true}});

        },
        validateBiometricForm({form}) {

            let result = {
                bool: true
            };

            result.biometric_device = [];
            result.device_user_id = [];
            result.finger_index = [];

            if(!this.isDefined({value: form?.biometric_device})) {

                result.biometric_device.push(this.config.forms.errors.labels.required);
                result.bool = false;

            }

            return result;

        },
        // Emits
        handlePostAction({response}) {

            this.$emit("postAction", {response});

        }
    },
    computed: {
        identityDocumentTypes: function() {

            return (this.options?.identityDocumentTypes?.records ?? []).map(e => ({code: e.id, label: e.name}));

        },
        statuses: function() {

            return [
                {"code": "active", "label": "Activo"}
            ];

        },
        biometricDevices: function() {

            return (this.options?.biometricDevices?.records ?? []).map(e => ({
                code: e.id,
                label: `${e.name} (${e.branch?.name ?? ""}) - ${e.ip_address}`,
                data: e
            }));

        },
        fingerIndexes: function() {

            return [
                {"code": 0, "label": "Pulgar derecho"},
                {"code": 1, "label": "Índice derecho"},
                {"code": 2, "label": "Medio derecho"},
                {"code": 3, "label": "Anular derecho"},
                {"code": 4, "label": "Meñique derecho"},
                {"code": 5, "label": "Pulgar izquierdo"},
                {"code": 6, "label": "Índice izquierdo"},
                {"code": 7, "label": "Medio izquierdo"},
                {"code": 8, "label": "Anular izquierdo"},
                {"code": 9, "label": "Meñique izquierdo"}
            ];

        }
    },
};
</script>
