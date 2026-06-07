<template>
    <button
        ref="button"
        type="button"
        class="br-copy-button"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        :title="tooltipText"
        :aria-label="ariaLabelText"
        @click="copy">
        <i class="fa-solid fa-copy" aria-hidden="true"></i>
    </button>
</template>

<script>
export default {
    name: "CopyButton",
    props: {
        value: {
            type: [String, Number],
            required: false,
            default: ""
        },
        label: {
            type: String,
            required: false,
            default: "Valor"
        },
        tooltip: {
            type: String,
            required: false,
            default: null
        },
        copiedTooltip: {
            type: String,
            required: false,
            default: null
        },
        useLabelInTooltip: {
            type: Boolean,
            required: false,
            default: false
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    data() {

        return {
            statusText: null,
            resetTimeout: null
        };

    },
    computed: {
        cleanValue() {

            return String(this.value ?? "").trim();

        },
        defaultTooltip() {

            return this.useLabelInTooltip
                ? `Copiar ${this.label.toLowerCase()}`
                : "Copiar";

        },
        defaultCopiedTooltip() {

            return this.useLabelInTooltip
                ? `${this.label} copiado`
                : "Copiado";

        },
        tooltipText() {

            return this.statusText ?? this.tooltip ?? this.defaultTooltip;

        },
        ariaLabelText() {

            return `${this.defaultTooltip}${this.cleanValue ? ` ${this.cleanValue}` : ""}`;

        }
    },
    beforeUnmount() {

        window.clearTimeout(this.resetTimeout);
        this.disposeTooltip();

    },
    methods: {
        async copy() {

            if(this.disabled || !this.cleanValue) {

                this.showTooltip(`${this.label} sin valor`);
                return;

            }

            try {

                await this.writeClipboard(this.cleanValue);
                this.showTooltip(this.copiedTooltip ?? this.defaultCopiedTooltip);

            }catch(error) {

                this.showTooltip("No se pudo copiar");

            }

        },
        async writeClipboard(text) {

            if(navigator.clipboard?.writeText && window.isSecureContext) {

                await navigator.clipboard.writeText(text);
                return;

            }

            const textarea = document.createElement("textarea");

            textarea.value = text;
            textarea.setAttribute("readonly", "");
            textarea.style.position = "fixed";
            textarea.style.left = "-9999px";
            textarea.style.top = "0";
            document.body.appendChild(textarea);
            textarea.select();

            const copied = document.execCommand("copy");

            document.body.removeChild(textarea);

            if(!copied) throw new Error("Clipboard copy failed");

        },
        showTooltip(message) {

            this.statusText = message;

            this.$nextTick(() => {

                const instance = this.refreshTooltip();

                instance?.show();
                window.clearTimeout(this.resetTimeout);

                this.resetTimeout = window.setTimeout(() => {

                    this.statusText = null;
                    this.$nextTick(() => this.refreshTooltip());

                }, 1400);

            });

        },
        refreshTooltip() {

            const button = this.$refs.button;
            const Bootstrap = window.bootstrap;

            if(!button || !Bootstrap?.Tooltip) return null;

            const currentTitle = this.tooltipText;
            const existing = Bootstrap.Tooltip.getInstance(button);

            existing?.dispose();
            button.setAttribute("title", currentTitle);
            button.removeAttribute("data-bs-original-title");
            button.removeAttribute("aria-describedby");

            return new Bootstrap.Tooltip(button, {animation: false});

        },
        disposeTooltip() {

            const button = this.$refs.button;
            const Bootstrap = window.bootstrap;

            if(!button || !Bootstrap?.Tooltip) return;

            Bootstrap.Tooltip.getInstance(button)?.dispose();

        }
    }
};
</script>
