<template>
    <nav aria-label="Page navigation" class="my-3" v-if="shouldShowPaginator">
        <ul class="pagination d-flex flex-wrap">
            <template v-for="(link, key) in links" :key="key">
                <li :class="['page-item my-1', link.active ? 'active' : (link.url ? '' : 'disabled')]">
                    <a :class="['page-link waves-effect mx-1 px-3', link?.active ? 'disabled' : '']" href="javascript:void(0);" @click="handleClickPage({url: link.url})" v-html="link.label"></a>
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
            default: []
        }
    },
    computed: {
        shouldShowPaginator() {

            if(!this.links || this.links.length === 0) return false;

            const pagesWithUrl = this.links.filter(link => link.url && !link.active);

            return pagesWithUrl.length > 0;

        }
    },
    methods: {
        handleClickPage({url}) {

            this.$emit("clickPage", {url});

        }
    }
};
</script>
