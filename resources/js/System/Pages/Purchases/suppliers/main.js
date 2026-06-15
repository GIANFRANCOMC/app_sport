import {createApp} from "vue";

import App from "./main.vue";
import Breadcrumb from "@System/Components/Breadcrumb.vue";
import InputText from "@System/Components/InputText.vue";
import Loader from "@System/Components/Loader.vue";
import Paginator from "@System/Components/Paginator.vue";
import WithoutData from "@System/Components/WithoutData.vue";

createApp(App)
    .component("Breadcrumb", Breadcrumb)
    .component("InputText", InputText)
    .component("Loader", Loader)
    .component("Paginator", Paginator)
    .component("WithoutData", WithoutData)
    .mount("#app");
