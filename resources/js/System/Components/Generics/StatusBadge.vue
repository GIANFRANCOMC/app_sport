<template>
    <span :class="badgeClasses" v-text="formattedStatus"></span>
</template>

<script>
import { STATUS_BADGE_VARIANTS, CSS_CLASSES } from "@System/Helpers/ModuleConstants.js";

export default {
    name: "StatusBadge",
    props: {
        status: {
            type: String,
            required: true
        },
        formattedStatus: {
            type: String,
            default: null
        },
        customVariants: {
            type: Object,
            default: () => ({})
        }
    },
    computed: {
        badgeClasses() {
            const variants = { ...STATUS_BADGE_VARIANTS, ...this.customVariants };
            return [
                ...CSS_CLASSES.BADGE_BASE,
                variants[this.status] || "bg-label-secondary"
            ];
        },
        displayStatus() {
            return this.formattedStatus || this.status;
        }
    }
};
</script>

