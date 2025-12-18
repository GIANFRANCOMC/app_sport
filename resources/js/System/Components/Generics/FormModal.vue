<template>
    <div
        class="modal fade"
        :id="modalId"
        data-bs-backdrop="static"
        tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" :class="modalSize" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="title"></h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <slot name="body"></slot>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary waves-effect"
                        data-bs-dismiss="modal">
                        {{ closeButtonText }}
                    </button>
                    <slot name="footer">
                        <button
                            type="button"
                            :class="['btn', 'waves-effect', submitButtonClass]"
                            @click="$emit('submit')">
                            <i :class="submitButtonIcon"></i>
                            <span class="ms-2">{{ submitButtonText }}</span>
                        </button>
                    </slot>
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
        modalSize: {
            type: String,
            default: ""
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
            default: "btn-primary"
        },
        submitButtonIcon: {
            type: String,
            default: "fa fa-save"
        }
    }
};
</script>

