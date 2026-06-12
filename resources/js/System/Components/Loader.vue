<template>
    <div
        v-if="type === 'default'"
        class="br-loader"
        role="status"
        aria-live="polite"
        :aria-label="label">
        <span class="br-loader__indicator" aria-hidden="true">
            <span></span>
        </span>
        <span v-if="showLabel" class="br-loader__label" v-text="label"></span>
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
        }
    }
};
</script>
