import { createApp } from "vue";

import App from "./main.vue";
import VueSelect from "vue-select";
import "vue-select/dist/vue-select.css";

import Breadcrumb from "@System/Components/Breadcrumb.vue";
import InputNumber from "@System/Components/InputNumber.vue";
import InputText from "@System/Components/InputText.vue";
import Loader from "@System/Components/Loader.vue";
import WithoutData from "@System/Components/WithoutData.vue";
import AddCustomer from "@System/Components/Customers/AddCustomer.vue";

createApp(App)
.component("v-select", VueSelect)
.component("Breadcrumb", Breadcrumb)
.component("InputNumber", InputNumber)
.component("InputText", InputText)
.component("Loader", Loader)
.component("WithoutData", WithoutData)
.component("AddCustomer", AddCustomer)
.mount("#app");
