import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "./pages/Backoffice/Dashboard.vue";

const router = createRouter({
  history: createWebHistory("/admin/"),
  routes: [
    {
      path: "/",
      name: "admin.dashboard",
      component: Dashboard,
      meta: { navigationId: "dashboard" },
    },
    {
      path: "/brands",
      name: "admin.brands",
      component: Dashboard,
      meta: { navigationId: "brands" },
    },
    {
      path: "/brands/:id(\\d+)",
      name: "admin.brand",
      component: Dashboard,
      meta: { navigationId: "brands" },
    },
    {
      path: "/angles/:brandId(\\d+)?",
      name: "admin.angles",
      component: Dashboard,
      meta: { navigationId: "angles" },
    },
    {
      path: "/angles/:brandId(\\d+)/:angleId(\\d+)",
      name: "admin.angle",
      component: Dashboard,
      meta: { navigationId: "angles" },
    },
    {
      path: "/campaigns",
      name: "admin.campaigns",
      component: Dashboard,
      meta: { navigationId: "campaigns" },
    },
    {
      path: "/audit",
      name: "admin.audit",
      component: Dashboard,
      meta: { navigationId: "audit" },
    },
    {
      path: "/audit/:id(\\d+)",
      name: "admin.audit.detail",
      component: Dashboard,
      meta: { navigationId: "audit" },
    },
    {
      path: "/samples",
      name: "admin.samples",
      component: Dashboard,
      meta: { navigationId: "samples" },
    },
    {
      path: "/participants",
      name: "admin.participants",
      component: Dashboard,
      meta: { navigationId: "participants" },
    },
    {
      path: "/locations",
      name: "admin.locations",
      component: Dashboard,
      meta: { navigationId: "locations" },
    },
    {
      path: "/reports",
      name: "admin.reports",
      component: Dashboard,
      meta: { navigationId: "reports" },
    },
  ],
});

export default router;
