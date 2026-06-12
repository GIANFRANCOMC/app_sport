export default {
    computed: {
        internalCodeEntityKey() {

            return this.MODULE?.config?.internalCodeEntity
                ?? this.MODULE?.internalCodeEntity
                ?? "";

        },
        internalCodePrefixes() {

            return this.options?.internal_code_prefixes ?? {};

        },
        internalCodePrefixLabel() {

            const prefix = String(
                this.internalCodePrefixes[this.internalCodeEntityKey] ?? ""
            ).trim().replace(/-+$/, "");

            return prefix ? `${prefix}-` : "";

        },
        internalCodeEditableMaxlength() {

            return Math.max(1, 50 - this.internalCodePrefixLabel.length);

        }
    },
    methods: {
        stripInternalCodePrefix(value) {

            const code = String(value ?? "");
            const prefix = this.internalCodePrefixLabel;

            return prefix && code.toUpperCase().startsWith(prefix.toUpperCase())
                ? code.slice(prefix.length)
                : code;

        }
    }
};
