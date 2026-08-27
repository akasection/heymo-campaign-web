import { createApp } from "vue";
import AdminLogin from "./pages/AdminLogin.vue";

const appElement = document.querySelector<HTMLElement>("#app");

createApp(AdminLogin, {
  showDemoAccounts: appElement?.dataset.showDemoAccounts === "true",
}).mount("#app");
