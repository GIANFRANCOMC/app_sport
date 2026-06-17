import { createApp } from "vue";

import App from "./main.vue";
import VueSelect from "vue-select";
import "vue-select/dist/vue-select.css";

import Breadcrumb from "@System/Components/Breadcrumb.vue";
import InputNumber from "@System/Components/InputNumber.vue";
import InputSlot from "@System/Components/InputSlot.vue";
import InputText from "@System/Components/InputText.vue";
import Paginator from "@System/Components/Paginator.vue";
import Loader from "@System/Components/Loader.vue";
import WithoutData from "@System/Components/WithoutData.vue";
import StatusBadge from "@System/Components/Generics/StatusBadge.vue";

createApp(App)
.component("v-select", VueSelect)
.component("Breadcrumb", Breadcrumb)
.component("InputNumber", InputNumber)
.component("InputSlot", InputSlot)
.component("InputText", InputText)
.component("Paginator", Paginator)
.component("Loader", Loader)
.component("WithoutData", WithoutData)
.component("StatusBadge", StatusBadge)
.mount("#app");
