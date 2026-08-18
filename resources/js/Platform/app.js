import {createApp} from "vue";
import App from "./App.vue";

createApp(App, {config: window.PlatformConfig || {}}).mount("#platform-app");
