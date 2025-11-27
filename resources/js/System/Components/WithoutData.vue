<template>
    <div v-if="['image'].includes(type)" class="d-flex flex-column justify-content-center align-items-center">
        <img :src="image" class="fluid-image" width="23%"/>
        <h5>
            <span class="fs-5 fw-bold px-5" v-text="text"></span>
        </h5>
    </div>
    <template v-else-if="['text'].includes(type)">
        <span v-text="text"></span>
    </template>
</template>

<script>
import * as Constants  from "../Helpers/Constants.js";

export default {
    name: "WithoutData",
    emits: [],
    props: {
        type: {
            type: String,
            required: false,
            default: "image"
        },
        text: {
            type: String,
            required: false,
            default: Constants.generalConfig.messages.withoutResults
        }
    },
    computed: {
        image() {

            return `${Constants.requestRoute}/System/assets/img/utils/without_data/2.png`;

        }

    },
    methods: {
        //
    }
};
</script>
