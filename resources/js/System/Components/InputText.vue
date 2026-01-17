<template>
    <InputBase
        input-type="text"
        v-bind="$attrs"
        :model-value="modelValue"
        :maxlength="effectiveMaxlength"
        @update:modelValue="handleUpdate"
        @enterKeyPressed="$emit('enterKeyPressed')"
        @input="$emit('input', $event)"
        @change="$emit('change', $event)">
        <template v-for="(_, slot) in $slots" #[slot]="scope">
            <slot :name="slot" v-bind="scope || {}"></slot>
        </template>
        <template v-if="shouldShowCharCounter" #inputGroupAppend>
            <span class="input-group-text character-counter" :class="{'text-danger': isNearLimit, 'text-secondary': !isNearLimit}">
                {{ currentLength }}/{{ maxlengthValue }}
            </span>
        </template>
    </InputBase>
</template>

<script>
import InputBase from "./InputBase.vue";

export default {
    name: "InputText",
    components: { InputBase },
    inheritAttrs: false,
    emits: ["enterKeyPressed", "update:modelValue", "input", "change"],
    props: {
        modelValue: {
            type: [String, Number],
            default: ""
        },
        maxlength: {
            type: [String, Number],
            default: null
        },
        showCharCounter: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        currentLength() {

            const value = this.modelValue?.toString() || "";

            return value.length;

        },
        maxlengthValue() {

            const maxlengthAttr = this.maxlength || this.$attrs.maxlength;

            return maxlengthAttr ? parseInt(maxlengthAttr) : null;

        },
        effectiveMaxlength() {

            return this.maxlengthValue || this.$attrs.maxlength || undefined;

        },
        hasMaxlength() {

            return this.maxlengthValue !== null && this.maxlengthValue > 0;

        },
        shouldShowCharCounter() {

            return this.showCharCounter && this.hasMaxlength;

        },
        isNearLimit() {

            if(!this.hasMaxlength) return false;

            const percentage = (this.currentLength / this.maxlengthValue) * 100;

            return percentage >= 90;

        }
    },
    methods: {
        handleUpdate(value) {

            if(this.hasMaxlength && value.length > this.maxlengthValue) {

                value = value.substring(0, this.maxlengthValue);

            }

            this.$emit("update:modelValue", value);

        }
    }
};
</script>

<style scoped>
.character-counter {
    font-size: 0.65rem;
    padding: 0.25rem 0.35rem;
    border-left: none;
    background-color: rgba(0, 0, 0, 0.02);
    min-width: 38px;
    text-align: right;
    opacity: 0.85;
    font-weight: 500;
    line-height: 1.2;
}

.character-counter:hover {
    opacity: 1;
    background-color: rgba(0, 0, 0, 0.04);
}
</style>
