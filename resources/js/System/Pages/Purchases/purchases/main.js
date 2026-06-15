import {createApp} from "vue";
import VueSelect from "vue-select";
import "vue-select/dist/vue-select.css";

import App from "./main.vue";
import Breadcrumb from "@System/Components/Breadcrumb.vue";
import InputNumber from "@System/Components/InputNumber.vue";
import InputText from "@System/Components/InputText.vue";
import Loader from "@System/Components/Loader.vue";
import Paginator from "@System/Components/Paginator.vue";
import WithoutData from "@System/Components/WithoutData.vue";

createApp(App)
    .component("v-select", VueSelect)
    .component("Breadcrumb", Breadcrumb)
    .component("InputNumber", InputNumber)
    .component("InputText", InputText)
    .component("Loader", Loader)
    .component("Paginator", Paginator)
    .component("WithoutData", WithoutData)
    .mount("#app");
