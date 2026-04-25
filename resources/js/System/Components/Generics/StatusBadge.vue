<template>
    <span :class="badgeClasses" v-text="displayStatus"></span>
</template>

<script>
import { getStatusLabelClasses } from "@System/Helpers/ModuleConstants.js";

export default {
    name: "StatusBadge",
    props: {
        /** Código de estado (p. ej. active, pending). Si es null, se usa variante secondary. */
        status: {
            type: String,
            default: null
        },
        /** Texto mostrado; si no se envía, se muestra `status`. */
        formattedStatus: {
            type: String,
            default: null
        },
        /** Mapa estado → modificador (success, danger, …) que se fusiona con el mapa global. */
        customVariants: {
            type: Object,
            default: () => ({})
        }
    },
    computed: {
        badgeClasses() {
            return getStatusLabelClasses(this.status, this.customVariants);
        },
        displayStatus() {
            return this.formattedStatus || this.status;
        }
    }
};
</script>

