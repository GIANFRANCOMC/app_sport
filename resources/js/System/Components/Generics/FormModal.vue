<template>
    <div
        :class="['modal', 'fade', 'br-entity-modal', 'br-modal-standard', 'br-modal-shell', modalClass]"
        :id="modalId"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
        tabindex="-1"
        :aria-labelledby="titleId"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" :class="[modalSize, dialogClass]" role="document">
            <div class="modal-content">
                <div class="modal-header br-entity-modal__header br-modal-shell__header">
                    <div class="br-modal-shell__heading">
                        <p
                            v-if="eyebrow"
                            class="br-entity-modal__eyebrow mb-1"
                            v-text="eyebrow"></p>
                        <h2
                            :id="titleId"
                            class="modal-title br-entity-modal__title"
                            v-text="title"></h2>
                        <small
                            v-if="subtitle"
                            class="br-modal-shell__subtitle"
                            v-text="subtitle"></small>
                        <slot name="header"></slot>
                    </div>
                    <button
                        type="button"
                        class="br-modal-close br-modal-shell__close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body" :class="bodyClass">
                    <slot name="body">
                        <slot></slot>
                    </slot>
                </div>
                <div v-if="showFooter" class="modal-footer br-entity-modal__footer br-modal-shell__footer" :class="footerClass">
                    <div class="br-modal-shell__footer-close br-modal-shell__footer-left">
                        <slot name="footerClose">
                            <button
                                type="button"
                                class="br-btn br-btn-cancel waves-effect"
                                data-bs-dismiss="modal">
                                {{ closeButtonText }}
                            </button>
                        </slot>
                    </div>
                    <div class="br-modal-shell__footer-actions br-modal-shell__footer-right">
                        <slot name="footer">
                            <button
                                v-if="showSubmit"
                                type="button"
                                :class="['br-btn', 'waves-effect', submitButtonClass]"
                                :disabled="submitDisabled"
                                @click="$emit('submit')">
                                <i v-if="submitButtonIcon" :class="submitButtonIcon" aria-hidden="true"></i>
                                <span>{{ submitButtonText }}</span>
                            </button>
                        </slot>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "FormModal",
    emits: ["submit"],
    props: {
        modalId: {
            type: String,
            required: true
        },
        title: {
            type: String,
            required: true
        },
        eyebrow: {
            type: String,
            default: ""
        },
        subtitle: {
            type: String,
            default: ""
        },
        modalSize: {
            type: String,
            default: ""
        },
        modalClass: {
            type: [String, Array, Object],
            default: ""
        },
        dialogClass: {
            type: [String, Array, Object],
            default: ""
        },
        bodyClass: {
            type: [String, Array, Object],
            default: ""
        },
        footerClass: {
            type: [String, Array, Object],
            default: ""
        },
        showFooter: {
            type: Boolean,
            default: true
        },
        showSubmit: {
            type: Boolean,
            default: true
        },
        submitDisabled: {
            type: Boolean,
            default: false
        },
        closeButtonText: {
            type: String,
            default: "Cerrar"
        },
        submitButtonText: {
            type: String,
            default: "Guardar"
        },
        submitButtonClass: {
            type: String,
            default: "br-btn-primary"
        },
        submitButtonIcon: {
            type: String,
            default: "fa-solid fa-floppy-disk"
        }
    },
    computed: {
        titleId() {
            return `${this.modalId}-title`;
        }
    }
};
</script>

