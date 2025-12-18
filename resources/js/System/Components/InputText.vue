<template>
    <InputBase
        input-type="text"
        v-bind="$attrs"
        :model-value="modelValue"
        @update:modelValue="$emit('update:modelValue', $event)"
        @enterKeyPressed="$emit('enterKeyPressed')"
        @input="$emit('input', $event)"
        @change="$emit('change', $event)">
        <template v-for="(_, slot) in $slots" #[slot]="scope">
            <slot :name="slot" v-bind="scope || {}"></slot>
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
        }
    }
};
</script>
