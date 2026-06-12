<template>
    <template v-if="hasDiv">
        <div :class="[...divClass, divSizeClass]">
            <slot name="default"></slot>
            <label v-if="!!title" v-text="title" :class="[...titleClass]"></label>
            <label v-if="isRequired" v-text="requiredLabel" :class="[...requiredClass]"></label>
            <div :class="['input-group', 'br-form-control-group', {'is-invalid': hasError}]">
                <slot name="inputGroupPrepend"></slot>
                <select
                    :value="modelValue"
                    @input="updateValue($event.target.value)"
                    :class="[...inputClass]"
                    :disabled="disabled"
                    @change="handleChange">
                    <option v-if="hasDefaultOption" :value="defaultOptionValue" v-text="defaultOptionLabel"></option>
                    <option v-for="(option, index) in options" :value="option.code" v-text="option.label" :key="index"></option>
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
                :value="modelValue"
                @input="updateValue($event.target.value)"
                :class="[...inputClass]"
                :disabled="disabled"
                @change="handleChange">
                <option v-if="hasDefaultOption" :value="defaultOptionValue" v-text="defaultOptionLabel"></option>
                <option v-for="(option, index) in options" :value="option.code" v-text="option.label" :key="index"></option>
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
    name: "InputSelect",
    emits: ["changeEvent", "update:modelValue"],
    props: {
        modelValue: {
            type: [String, Number],
            default: ""
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
        // Div - Show
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
        // Input - Required
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
        // Input - Props
        inputClass:{
            type: Array,
            required: false,
            default: ["form-select"]
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
    methods: {
        updateValue(value) {

            this.$emit("update:modelValue", value);

        },
        handleChange() {

            this.$emit("changeEvent");

        }
    }
};
</script>
