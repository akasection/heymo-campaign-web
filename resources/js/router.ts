import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "./pages/Backoffice/Dashboard.vue";

const router = createRouter({
  history: createWebHistory("/admin"),
  routes: [
    { path: "/", name: "admin.dashboard", component: Dashboard, meta: { navigationId: "dashboard" } },
    { path: "/brands", name: "admin.brands", component: Dashboard, meta: { navigationId: "brands" } },
    { path: "/brands/:id", name: "admin.brand", component: Dashboard, meta: { navigationId: "brands" } },
    { path: "/campaigns", name: "admin.campaigns", component: Dashboard, meta: { navigationId: "campaigns" } },
    { path: "/samples", name: "admin.samples", component: Dashboard, meta: { navigationId: "samples" } },
    { path: "/participants", name: "admin.participants", component: Dashboard, meta: { navigationId: "participants" } },
    { path: "/locations", name: "admin.locations", component: Dashboard, meta: { navigationId: "locations" } },
    { path: "/reports", name: "admin.reports", component: Dashboard, meta: { navigationId: "reports" } },
  ],
});

export default router;
