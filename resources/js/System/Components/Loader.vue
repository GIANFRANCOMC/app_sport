<template>
    <div v-if="['default'].includes(type)" class="d-flex flex-column justify-content-center align-items-center">
        <i class="fas fa-spinner fa-spin fa-3x my-3"></i>
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
        }
    },
    computed: {
        //
    },
    methods: {
        //
    }
};
</script>
