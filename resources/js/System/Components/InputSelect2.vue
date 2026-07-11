<template>
    <template v-if="hasDiv">
        <div :class="[...divClass, divSizeClass]">
            <slot name="default"></slot>
            <label v-if="!!title" v-text="title" :class="[...titleClass]"></label>
            <label v-if="isRequired" v-text="requiredLabel" :class="[...requiredClass]"></label>
            <div :class="['input-group', 'br-form-control-group', {'is-invalid': hasError}]">
                <slot name="inputGroupPrepend"></slot>
                <select
                    :id="id"
                    :class="[...inputClass]"
                    :disabled="disabled"
                    @change="handleChange"
                    :multiple="multiple">
                    <option v-if="hasDefaultOption" :value="defaultOptionValue" v-text="defaultOptionLabel"></option>
                    <option v-for="(option, index) in options" :value="option.code" v-text="option.label" :key="index" :extras="option?.extras"></option>
                </select>
                <slot name="inputGroupAppend"></slot>
            </div>
            <div v-if="hasTextBottom">
                <small v-if="textBottomType === 'first'" :class="[...textBottomClass, 'br-form-error']" v-text="textBottom"></small>
            </div>
        </div>
    </template>
    <template v-else>
        <slot name="default"></slot>
        <label v-if="!!title" v-text="title" :class="[...titleClass]"></label>
        <label v-if="isRequired" v-text="requiredLabel" :class="[...requiredClass]"></label>
        <div :class="['input-group', 'br-form-control-group', {'is-invalid': hasError}]">
            <slot name="inputGroupPrepend"></slot>
            <select
                :id="id"
                :class="[...inputClass]"
                :disabled="disabled"
                @change="handleChange"
                :multiple="multiple">
                <option v-if="hasDefaultOption" :value="defaultOptionValue" v-text="defaultOptionLabel"></option>
                <option v-for="(option, index) in options" :value="option.code" v-text="option.label" :key="index" :extras="option?.extras"></option>
            </select>
            <slot name="inputGroupAppend"></slot>
        </div>
        <div v-if="hasTextBottom">
            <small v-if="textBottomType === 'first'" :class="[...textBottomClass, 'br-form-error']" v-text="textBottom"></small>
        </div>
    </template>
</template>

<script>
import { generalConfig } from "../Helpers/Constants.js";

export default {
    name: "InputSelect2",
    emits: [],
    props: {
        id: {
            type: [String],
            required: true
        },
        // Options
        hasDefaultOption:{
            type: Boolean,
            required: false,
            default: true
        },
        defaultOptionLabel: {
            type: String,
            required: false,
            default: "Seleccione"
        },
        defaultOptionValue: {
            type: String,
            required: false,
            default: ""
        },
        options: {
            type: Array,
            required: false,
            default: []
        },
        multiple: {
            type: Boolean,
            required: false,
            default: false
        },
        // Title
        hasDiv: {
            type: Boolean,
            required: false,
            default: false
        },
        divClass: {
            type: Array,
            required: false,
            default: []
        },
        // Div - Title
        title: {
            type: String,
            required: false,
            default: ""
        },
        titleClass: {
            type: Array,
            required: false,
            default: ["form-label", "colon-at-end"]
        },
        // Aspect
        isRequired: {
            type: Boolean,
            required: false,
            default: false
        },
        requiredLabel: {
            type: String,
            required: false,
            default: generalConfig.forms.inputs.required
        },
        requiredClass: {
            type: Array,
            required: false,
            default: ["text-danger", "ms-1", "fw-bold"]
        },
        inputClass:{
            type: Array,
            required: false,
            default: ["select2", "form-select"]
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        },
        // Text Bottom
        hasTextBottom: {
            type: Boolean,
            required: false,
            default: false
        },
        textBottomType: {
            type: String,
            required: false,
            default: "first"
        },
        textBottomClass: {
            type: Array,
            required: false,
            default: [generalConfig.forms.errors.styles.default]
        },
        textBottomInfo: {
            type: Array,
            required: false,
            default: []
        },
        // Sizes
        xl: {
            type: [String, Number],
            required: false,
            default: "12"
        },
        lg: {
            type: [String, Number],
            required: false,
            default: "12"
        },
        md: {
            type: [String, Number],
            required: false,
            default: "12"
        },
        sm: {
            type: [String, Number],
            required: false,
            default: "12"
        }
    },
    computed: {
        hasError() {

            return Boolean(this.textBottom);

        },
        textBottom() {

            try {

                return this.textBottomInfo.length > 0 ? this.textBottomInfo[0] : "";

            }catch(e) {

                return e;

            }

        },
        divSizeClass() {

            return `form-group col-xl-${this.xl} col-lg-${this.lg} col-md-${this.md} col-sm-${this.sm}`;

        }
    },
    mounted: function() {

        let $this = $(`#${this.id}`);

        $this
        .wrap(`<div class="position-relative w-100"></div>`)
        .select2({
            placeholder: `Seleccione`,
            language: this.select2Language(),
            dropdownParent: $this.parent()
        });

    },
    watch: {
        options: function(newValue, oldValue) {

            let $this = $(`#${this.id}`);

            // $this.val("");
            $this.select2("destroy");

            $this
            .wrap(`<div class="position-relative w-100"></div>`)
            .select2({
                placeholder: `Seleccione`,
                language: this.select2Language(),
                dropdownParent: $this.parent()
            });

        }
    },
    methods: {
        select2Language() {
            return {
                noResults: () => "Sin opciones disponibles",
                searching: () => "Buscando...",
                inputTooShort: () => "Escribe para buscar"
            };
        }
    }
};
</script>
