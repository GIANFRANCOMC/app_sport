<template>
    <section class="filters-section mb-4 mb-md-4">
        <div class="row align-items-end g-3">
            <InputSlot
                hasDiv
                :title="filterByTitle"
                :titleClass="titleClass"
                xl="3"
                lg="4">
                <template v-slot:input>
                    <v-select
                        :model-value="filterByValue"
                        @update:model-value="$emit('update:filterByValue', $event)"
                        :options="filterByOptions"
                        :class="selectClass"
                        :clearable="false"
                        :searchable="false"
                        :disabled="loading"/>
                </template>
            </InputSlot>
            <InputText
                :model-value="filterWordValue"
                @update:model-value="$emit('update:filterWordValue', $event)"
                @enterKeyPressed="$emit('search')"
                hasDiv
                :title="searchTitle"
                :titleClass="titleClass"
                :placeholder="searchPlaceholder"
                :disabled="loading"
                xl="4"
                lg="4"/>
            <InputSlot
                hasDiv
                :isInputGroup="false"
                :divInputClass="['d-flex flex-wrap justify-content-start gap-2 gap-md-3']"
                xl="5"
                lg="4">
                <template v-slot:input>
                    <button
                        type="button"
                        class="btn btn-info-1 waves-effect"
                        @click="$emit('search')"
                        :disabled="loading">
                        <i class="fa fa-search"></i>
                        <span class="ms-2" v-text="searchButtonText"></span>
                    </button>
                    <button
                        v-if="showAddButton"
                        type="button"
                        class="btn btn-primary waves-effect"
                        @click="$emit('add')"
                        :disabled="loading">
                        <i class="fa fa-plus"></i>
                        <span class="ms-2" v-text="addButtonText"></span>
                    </button>
                </template>
            </InputSlot>
        </div>
    </section>
</template>

<script>
export default {
    name: "FiltersSection",
    emits: ["search", "add", "update:filterByValue", "update:filterWordValue"],
    props: {
        filterByValue: {
            type: Object,
            required: true
        },
        filterWordValue: {
            type: String,
            required: true,
            default: ""
        },
        filterByOptions: {
            type: Array,
            required: true,
            default: []
        },
        searchPlaceholder: {
            type: String,
            required: false,
            default: "Buscar..."
        },
        loading: {
            type: Boolean,
            required: false,
            default: false
        },
        filterByTitle: {
            type: String,
            required: false,
            default: "Filtrar por"
        },
        searchTitle: {
            type: String,
            required: false,
            default: "Búsqueda"
        },
        searchButtonText: {
            type: String,
            required: false,
            default: "Buscar"
        },
        addButtonText: {
            type: String,
            required: false,
            default: "Agregar"
        },
        showAddButton: {
            type: Boolean,
            required: false,
            default: true
        },
        titleClass: {
            type: Array,
            required: false,
            default: () => []
        },
        selectClass: {
            type: String,
            required: false,
            default: "form-select form-select-sm"
        }
    }
};
</script>

<style scoped>
</style>

