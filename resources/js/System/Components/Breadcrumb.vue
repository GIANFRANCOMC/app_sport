<template>
    <nav
        v-if="visibleItems.length"
        class="br-breadcrumb"
        aria-label="Ubicación actual">
        <ol class="br-breadcrumb__list">
            <li class="br-breadcrumb__home" aria-hidden="true">
                <i class="fa-solid fa-house"></i>
            </li>
            <li
                v-for="(item, index) in visibleItems"
                :key="`${item.title}-${index}`"
                :class="['br-breadcrumb__item', {'is-current': item.active || index === visibleItems.length - 1}]"
                :aria-current="item.active || index === visibleItems.length - 1 ? 'page' : undefined">
                <i class="fa-solid fa-chevron-right br-breadcrumb__separator" aria-hidden="true"></i>
                <span>{{ item.title }}</span>
            </li>
        </ol>
    </nav>
</template>

<script>
export default {
    name: "Breadcrumb",
    emits: [],
    props: {
        list: {
            type: Array,
            required: false,
            default: () => []
        }
    },
    computed: {
        visibleItems() {

            return this.list.filter(item => item?.title);

        }
    },
    methods: {
        //
    }
};
</script>
