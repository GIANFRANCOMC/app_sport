<template>
    <div
        v-if="type === 'default'"
        class="br-feedback-state br-loader"
        role="status"
        aria-live="polite"
        :aria-label="label">
        <span class="br-loader__indicator" aria-hidden="true">
            <span></span>
        </span>
        <div v-if="showLabel" class="br-feedback-state__content">
            <strong class="br-feedback-state__title" v-text="label"></strong>
            <small v-if="hint" class="br-feedback-state__description" v-text="hint"></small>
        </div>
    </div>
</template>

<script>
export default {
    name: "Loader",
    emits: [],
    props: {
        type: {
            type: String,
            required: false,
            default: "default"
        },
        label: {
            type: String,
            required: false,
            default: "Cargando información"
        },
        showLabel: {
            type: Boolean,
            required: false,
            default: true
        },
        hint: {
            type: String,
            required: false,
            default: "Espera un momento."
        }
    }
};
</script>
