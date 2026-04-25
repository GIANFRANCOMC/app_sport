<template>
    <nav
        v-if="shouldShowPaginator"
        class="br-pagination my-4"
        role="navigation"
        aria-label="Navegación de páginas">
        <ul class="pagination br-pagination__list d-flex flex-wrap justify-content-center align-items-center mb-0">
            <template v-for="(link, index) in displayLinks" :key="itemKey(link, index)">
                <li
                    class="page-item br-pagination__item"
                    :class="{ active: link.active, disabled: !link.url }">
                    <a
                        class="page-link br-pagination__link waves-effect"
                        href="javascript:void(0);"
                        :aria-current="link.active ? 'page' : undefined"
                        :aria-disabled="ariaDisabled(link) ? 'true' : undefined"
                        :tabindex="navigable(link) ? 0 : -1"
                        @click.prevent="onPageClick(link)"
                        v-html="link.label"></a>
                </li>
            </template>
        </ul>
    </nav>
</template>

<script>
export default {
    name: "Paginator",
    emits: ["clickPage"],
    props: {
        links: {
            type: Array,
            required: false,
            default: () => []
        }
    },
    watch: {
        links: {
            handler(list) {
                if(list != null && !Array.isArray(list)) {
                    console.warn("[Paginator] la prop \"links\" debe ser un array");
                }
            },
            immediate: true,
            deep: true
        }
    },
    computed: {
        displayLinks() {
            return this.buildWindowedLinks(this.links);
        },
        shouldShowPaginator() {
            if(!Array.isArray(this.links) || this.links.length === 0) return false;

            return this.displayLinks.some(link => link.url && !link.active);
        }
    },
    methods: {
        stripHtml(value) {
            return String(value).replace(/<[^>]+>/g, "").trim();
        },
        parsePageNumber(label) {
            if(label == null) return null;

            const text = this.stripHtml(label);

            if(text === "..." || text === "…") return null;

            const n = parseInt(text, 10);

            return Number.isFinite(n) ? n : null;
        },
        buildPageUrl(baseUrl, page) {
            if(!baseUrl) return null;

            try {
                const u = new URL(baseUrl, window.location.origin);

                u.searchParams.set("page", String(page));

                return `${u.pathname}${u.search}${u.hash}`;
            } catch {
                return baseUrl;
            }
        },
        buildWindowedLinks(raw) {
            if(!Array.isArray(raw) || raw.length === 0) return [];

            if(raw.length < 3) return raw;

            // Anterior/Siguiente aparte; solo páginas numéricas.
            const windowSize = 5;
            const prevLink = raw[0];
            const nextLink = raw[raw.length - 1];
            const middle = raw.slice(1, -1);
            const templateUrl = raw.find(item => item.url)?.url ?? null;

            if(!templateUrl) return raw;

            const pageMap = new Map();
            let lastPage = 1;

            for(const item of middle) {
                const n = this.parsePageNumber(item.label);

                if(n === null) continue;

                pageMap.set(n, item);
                lastPage = Math.max(lastPage, n);
            }

            if(pageMap.size === 0) return raw;

            const activeItem = middle.find(item => item.active);
            let current = activeItem ? this.parsePageNumber(activeItem.label) : null;

            if(current === null) current = pageMap.keys().next().value ?? 1;

            const half = Math.floor(windowSize / 2);
            let start = Math.max(1, Math.min(current - half, lastPage - windowSize + 1));
            let end = Math.min(lastPage, start + windowSize - 1);

            if(end - start < windowSize - 1) start = Math.max(1, end - windowSize + 1);

            const windowLinks = [];

            for(let p = start; p <= end; p++) {
                const existing = pageMap.get(p);

                if(existing) {
                    windowLinks.push({...existing, active: p === current});
                } else {
                    windowLinks.push({
                        url: this.buildPageUrl(templateUrl, p),
                        label: String(p),
                        active: p === current
                    });
                }
            }

            return [prevLink, ...windowLinks, nextLink];
        },
        itemKey(link, index) {
            const label = typeof link?.label === "string" ? this.stripHtml(link.label) : index;

            return `${index}-${link?.url ?? "no-url"}-${label}`;
        },
        navigable(link) {
            return !!(link?.url && !link.active);
        },
        ariaDisabled(link) {
            return !link?.active && !link?.url;
        },
        onPageClick(link) {
            if(!this.navigable(link)) return;

            this.$emit("clickPage", {url: link.url});
        }
    }
};
</script>
