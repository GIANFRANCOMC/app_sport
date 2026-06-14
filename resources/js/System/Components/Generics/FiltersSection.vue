<template>
    <section class="br-filter-bar">
        <div class="row align-items-end g-2">
            <InputSlot
                hasDiv
                :title="filterByTitle"
                :titleClass="titleClass"
                xl="3"
                lg="3">
                <template v-slot:input>
                    <v-select
                        :model-value="filterByValue"
                        @update:model-value="$emit('update:filterByValue', $event)"
                        :options="filterByOptions"
                        :class="selectClass"
                        :clearable="false"
                        :searchable="false"
                        :disabled="loading"
                        append-to-body>
                        <template #selected-option="option">
                            <span
                                class="br-select-selected-text"
                                :title="getOptionLabel(option)">
                                {{ getOptionLabel(option) }}
                            </span>
                        </template>
                        <template #option="option">
                            <span
                                class="br-select-option-text"
                                :title="getOptionLabel(option)">
                                {{ getOptionLabel(option) }}
                            </span>
                        </template>
                        <template #no-options>
                            <SelectNoOptions/>
                        </template>
                    </v-select>
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
                xl="5"
                lg="5"/>
            <InputSlot
                hasDiv
                :isInputGroup="false"
                :divInputClass="['br-filter-bar__actions']"
                xl="4"
                lg="4">
                <template v-slot:input>
                    <button
                        type="button"
                        class="br-btn br-btn-sm br-btn-action-search waves-effect"
                        @click="$emit('search')"
                        :disabled="loading">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <span v-text="searchButtonText"></span>
                    </button>
                    <button
                        v-if="showAddButton"
                        type="button"
                        class="br-btn br-btn-sm br-btn-action-open-create waves-effect"
                        @click="$emit('add')"
                        :disabled="loading">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        <span v-text="addButtonText"></span>
                    </button>
                    <button
                        v-if="showDownloadButton"
                        type="button"
                        :class="[
                            'br-btn',
                            'br-btn-sm',
                            'br-btn-action-export',
                            'waves-effect',
                            {'br-btn-action-export--desktop-icon': downloadIconOnlyOnDesktop}
                        ]"
                        @click="$emit('download')"
                        :disabled="loading || downloading"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        :title="downloadButtonTooltip"
                        :aria-label="downloadButtonTooltip">
                        <i class="fa-solid fa-file-excel" aria-hidden="true"></i>
                        <span class="br-btn-action-export__label" v-text="downloadButtonText"></span>
                    </button>
                </template>
            </InputSlot>
        </div>
    </section>
</template>

<script>
import SelectNoOptions from "@System/Components/Generics/SelectNoOptions.vue";

export default {
    name: "FiltersSection",
    components: {
        SelectNoOptions
    },
    emits: ["search", "add", "download", "update:filterByValue", "update:filterWordValue"],
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
        showDownloadButton: {
            type: Boolean,
            required: false,
            default: false
        },
        downloadButtonText: {
            type: String,
            required: false,
            default: "Descargar Excel"
        },
        downloading: {
            type: Boolean,
            required: false,
            default: false
        },
        downloadIconOnlyOnDesktop: {
            type: Boolean,
            required: false,
            default: false
        },
        downloadButtonTooltip: {
            type: String,
            required: false,
            default: "Descargar Excel"
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
    },
    methods: {
        getOptionLabel(option) {

            return option?.label ?? option?.name ?? option?.value ?? "";

        }
    }
};
</script>

<style scoped>
</style>

