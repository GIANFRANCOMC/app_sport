import {createApp} from "vue";
import App from "./main.vue";
import Breadcrumb from "@System/Components/Breadcrumb.vue";

createApp(App)
    .component("Breadcrumb", Breadcrumb)
    .mount("#app");
