<template>
    <QuickCreateTrigger
        :mode="triggerMode"
        :text="triggerText"
        :title="resolvedTriggerTitle"
        :aria-label="resolvedTriggerTitle"
        :icon="triggerIcon"
        :custom-class="triggerClass"
        :disabled="disabled"
        @click="openDialog"/>

    <Teleport to="body">
        <dialog
            ref="dialog"
            :id="resolvedDialogId"
            class="br-quick-create-dialog"
            :aria-labelledby="`${resolvedDialogId}-title`"
            @cancel="handleCancel"
            @close="handleClose">
            <header class="br-quick-create-dialog__header">
                <div>
                    <p class="br-quick-create-dialog__eyebrow" v-text="eyebrow"></p>
                    <h2
                        :id="`${resolvedDialogId}-title`"
                        class="br-quick-create-dialog__title"
                        v-text="`Agregar ${singularLabel.toLowerCase()}`">
                    </h2>
                    <p class="br-quick-create-dialog__subtitle">
                        Se añadirá y seleccionará sin salir del producto.
                    </p>
                </div>

                <button
                    type="button"
                    class="br-modal-close"
                    aria-label="Cerrar"
                    @click="closeDialog">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </header>

            <form class="br-quick-create-dialog__form" @submit.prevent="saveEntity">
                <div v-if="generalError" class="br-quick-create-dialog__alert" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <span v-text="generalError"></span>
                </div>

                <div class="row g-3">
                    <InputText
                        ref="nameInput"
                        v-model="form.name"
                        hasDiv
                        title="Nombre"
                        :titleClass="labelClasses"
                        isRequired
                        :maxlength="nameMaxlength"
                        showCharCounter
                        hasTextBottom
                        :textBottomInfo="errors.name"
                        autocomplete="off"
                        xl="7"
                        lg="7"/>

                    <InputText
                        v-model="form.internal_code"
                        hasDiv
                        title="Código interno"
                        :titleClass="labelClasses"
                        isRequired
                        :maxlength="internalCodeMaxlength"
                        hasTextBottom
                        :textBottomInfo="errors.internal_code"
                        autocomplete="off"
                        xl="5"
                        lg="5">
                        <template #inputGroupAppend>
                            <button
                                type="button"
                                class="br-input-action"
                                :title="`Generar código interno para ${singularLabel.toLowerCase()}`"
                                :aria-label="`Generar código interno para ${singularLabel.toLowerCase()}`"
                                @click="generateInternalCode">
                                <i class="fa-solid fa-rotate" aria-hidden="true"></i>
                            </button>
                        </template>
                    </InputText>

                    <InputText
                        v-model="form.description"
                        hasDiv
                        title="Descripción"
                        :titleClass="labelClasses"
                        :maxlength="descriptionMaxlength"
                        showCharCounter
                        hasTextBottom
                        :textBottomInfo="errors.description"
                        autocomplete="off"
                        xl="12"
                        lg="12"/>
                </div>

                <p class="br-quick-create-dialog__note">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    <span>Se creará con estado activo.</span>
                </p>
            </form>

            <footer class="br-quick-create-dialog__footer">
                <button type="button" class="br-btn br-btn-cancel" @click="closeDialog">
                    Cancelar
                </button>
                <button
                    type="button"
                    class="br-btn br-btn-action-create"
                    :disabled="isSaving"
                    @click="saveEntity">
                    <span v-text="isSaving ? 'Agregando' : `Agregar ${singularLabel.toLowerCase()}`"></span>
                </button>
            </footer>
        </dialog>
    </Teleport>
</template>

<script>
import QuickCreateTrigger from "@System/Components/Generics/QuickCreateTrigger.vue";
import * as Forms from "@System/Helpers/Forms.js";
import * as Requests from "@System/Helpers/Requests.js";
import * as Utils from "@System/Helpers/Utils.js";

let quickCreateSequence = 0;

const FORM_DEFAULTS = {
    internal_code: "",
    name: "",
    description: "",
    status: "active"
};

export default {
    name: "QuickCreateCatalogEntity",
    components: {
        QuickCreateTrigger
    },
    emits: ["created", "postAction"],
    props: {
        entity: {
            type: String,
            required: true
        },
        resourceKey: {
            type: String,
            required: true
        },
        singularLabel: {
            type: String,
            required: true
        },
        eyebrow: {
            type: String,
            default: "Catálogo comercial"
        },
        triggerMode: {
            type: String,
            default: "link"
        },
        triggerText: {
            type: String,
            default: "Agregar"
        },
        triggerTitle: {
            type: String,
            default: ""
        },
        triggerIcon: {
            type: String,
            default: "fa-solid fa-plus"
        },
        triggerClass: {
            type: [String, Array, Object],
            default: ""
        },
        dialogId: {
            type: String,
            default: ""
        },
        nameMaxlength: {
            type: Number,
            default: 100
        },
        internalCodeMaxlength: {
            type: Number,
            default: 50
        },
        descriptionMaxlength: {
            type: Number,
            default: 250
        },
        internalCodePattern: {
            type: String,
            default: ""
        },
        internalCodePatternMessage: {
            type: String,
            default: "El código interno contiene caracteres no permitidos."
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    data() {

        quickCreateSequence += 1;

        return {
            instanceId: quickCreateSequence,
            form: Utils.cloneJson(FORM_DEFAULTS),
            errors: {},
            generalError: "",
            isSaving: false
        };

    },
    computed: {
        resolvedDialogId() {

            return this.dialogId || `br-quick-create-${this.entity}-${this.instanceId}`;

        },
        resolvedTriggerTitle() {

            return this.triggerTitle || `Agregar ${this.singularLabel.toLowerCase()}`;

        },
        labelClasses() {

            return ["form-label", "fw-bold", "colon-at-end", "fs-6"];

        },
        validationRules() {

            return {
                internal_code: {
                    required: true,
                    maxLength: this.internalCodeMaxlength,
                    custom: value => {

                        if(!this.internalCodePattern || !Utils.isDefined({value})) return "";

                        return new RegExp(this.internalCodePattern).test(String(value))
                            ? ""
                            : this.internalCodePatternMessage;

                    }
                },
                name: {
                    required: true,
                    maxLength: this.nameMaxlength
                },
                description: {
                    required: false,
                    maxLength: this.descriptionMaxlength
                }
            };

        }
    },
    beforeUnmount() {

        if(this.$refs.dialog?.open) {
            this.$refs.dialog.close();
        }

    },
    methods: {
        openDialog() {

            if(this.disabled || this.$refs.dialog?.open) return;

            this.resetForm();
            this.$refs.dialog?.showModal();

            this.$nextTick(() => {

                this.$refs.dialog?.querySelector("input")?.focus();

            });

        },
        closeDialog() {

            if(this.isSaving) return;

            if(this.$refs.dialog?.open) {
                this.$refs.dialog.close();
            }

        },
        handleCancel(event) {

            event.preventDefault();
            this.closeDialog();

        },
        handleClose() {

            this.errors = {};
            this.generalError = "";

        },
        resetForm() {

            this.form = {
                ...Utils.cloneJson(FORM_DEFAULTS),
                internal_code: Utils.generateCode({length: 7})
            };
            this.errors = {};
            this.generalError = "";

        },
        generateInternalCode(event) {

            this.form.internal_code = Utils.generateCode({length: 7});
            event?.currentTarget?.blur();

        },
        async saveEntity() {

            if(this.isSaving) return;

            this.errors = {};
            this.generalError = "";

            const validation = Forms.validateFormData(
                this.form,
                this.validationRules,
                {
                    isDescriptive: true,
                    errorLabels: {
                        internal_code: "Código interno",
                        name: "Nombre",
                        description: "Descripción"
                    }
                }
            );

            if(!validation.bool) {

                this.errors = validation.errors;
                return;

            }

            this.isSaving = true;

            try {

                const data = Forms.prepareFormData(
                    Utils.cloneJson(this.form),
                    {
                        internal_code: {trim: true},
                        name: {trim: true},
                        description: {normalize: true},
                        status: {trim: true}
                    }
                );
                const result = await Requests.post({
                    route: Requests.config({entity: this.entity, type: "store"}),
                    data
                });

                if(Requests.valid({result})) {

                    const record = result.data?.[this.resourceKey] ?? null;

                    this.$emit("created", {record, response: result});
                    this.$emit("postAction", {response: result});
                    this.$refs.dialog?.close();

                }else {

                    Forms.handleFormResponseErrors({
                        result,
                        formErrorsObject: this.errors,
                        showAlert: false
                    });

                    if(!Object.keys(this.errors).length) {
                        this.generalError = result?.data?.msg || "No fue posible completar el registro.";
                    }

                    this.$emit("postAction", {response: result});

                }

            }catch(error) {

                this.generalError = error?.message || "No fue posible completar el registro.";

            }finally {

                this.isSaving = false;

            }

        }
    }
};
</script>
