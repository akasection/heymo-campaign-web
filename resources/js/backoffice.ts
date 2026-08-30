import { createApp } from "vue";
import { RouterView } from "vue-router";
import router from "./router";

createApp(RouterView).use(router).mount("#app");
