## Plan: Heymo Campaign Operations Dashboard

Replace the Vue starter screen with a responsive, typed local-data campaign operations dashboard. Encode the reference's clinical Heymo visual language as Tailwind v4 semantic utilities, then reuse a deliberately small set of Vue components for navigation, panels, metrics, statuses, and hexagonal validation controls. This phase is a front-end prototype only: it establishes a usable dashboard shell and interactions without Laravel entities or APIs.

**Steps**

1. **Establish the Heymo visual foundation**: extend `/home/akasection/git/heymo-campaign-web/resources/css/app.css` with Tailwind v4 `@theme` tokens for the Heymo deep navy, blood-red action color, cool gray workspace, panel borders, and positive/warning states. Add a base font stack, page background treatment, and a reusable `hex-tile` utility using `clip-path`, with an accessible rectangular fallback for browsers that do not support clipping. Keep existing Tailwind source declarations intact.
2. **Add the icon dependency**: add `@phosphor-icons/vue` to `/home/akasection/git/heymo-campaign-web/package.json` through pnpm so navigation, controls, metric tiles, and status labels use familiar icons instead of bespoke SVG paths. The temporary brand mark remains an inline Phosphor droplet icon beside the `Heymo!` text wordmark.
3. **Create the Backoffice entry view and data contract**: add `/home/akasection/git/heymo-campaign-web/resources/views/Backoffice/dashboard.blade.php` as the Laravel view that mounts the Vue Backoffice application and loads the existing Vite entries. Add `/home/akasection/git/heymo-campaign-web/resources/js/pages/Backoffice/dashboard.ts` with explicit TypeScript interfaces and representative static data for navigation, campaign stats, map locations, processed samples, campaign progress, alerts, priority states, and validation actions. Keep all visible demo data in this Backoffice page module so it can later be replaced with a Backoffice API source without presentation rewrites.
4. **Build reusable Backoffice presentation primitives**: create `/home/akasection/git/heymo-campaign-web/resources/js/components/Backoffice/SidebarNav.vue`, `Panel.vue`, `StatTile.vue`, `StatusBadge.vue`, and `HexValidationTile.vue`. The sidebar owns active item state and compact/mobile rendering; `Panel` standardizes the white surface, border, shadow, padding, and compact title treatments; the remaining components encapsulate repeated metric, priority, and hexagon patterns. All components receive semantic props rather than hard-coded dashboard copy.
5. **Compose the responsive Backoffice dashboard**: create `/home/akasection/git/heymo-campaign-web/resources/js/pages/Backoffice/Dashboard.vue` as the operational screen, then update `/home/akasection/git/heymo-campaign-web/resources/js/App.vue` only to render the Backoffice page at the application mount. Use a navy persistent sidebar on desktop and a slide-in/simplified mobile navigation control; a compact header with the current dashboard title, notification action, and profile affordance; and an adaptive content grid. The desktop layout includes an active pickup locations map-style panel with plotted sample markers, three metric cards, a samples table, a compact trend chart, campaign progress, a spider/radar-style campaign health visualization, recent alerts, priorities, and the hexagonal validation flow from the reference.
6. **Make the prototype feel operational**: implement focused local interactions only: nav item selection, a mobile sidebar toggle, alert dismissal/read state, and validation-tile selection/status change. Build charts and map plots from accessible HTML/CSS/SVG-free primitives where possible (CSS grid/positioned markers for the map; semantic tables/lists plus restrained bars/lines for trends), avoiding a chart dependency for static demo data. Ensure controls have accessible labels, keyboard operation, and visible focus states.
7. **Apply responsive and visual finishing**: verify the shell at mobile, tablet, and desktop widths. Preserve information hierarchy by changing dense table content to horizontally scrollable or stacked rows at small widths, retaining a minimum touch target for tool buttons, avoiding text collisions, and maintaining the reference's clipped geometric motif without turning the workspace into a card wall.
8. **Validate**: run the JavaScript/TypeScript lint and formatting checks, then produce a production Vite build. Start the Laravel/Vite development servers and inspect the dashboard in desktop and mobile browser viewports, confirming the mounted app, responsive navigation, all interactions, and absence of horizontal layout breakage.

**Relevant files**

- `/home/akasection/git/heymo-campaign-web/resources/css/app.css` — Tailwind v4 `@theme` color/shape tokens, base page styling, and the hexagonal utility.
- `/home/akasection/git/heymo-campaign-web/package.json` — add `@phosphor-icons/vue`; lockfile updates are expected.
- `/home/akasection/git/heymo-campaign-web/resources/views/Backoffice/dashboard.blade.php` — Laravel Backoffice view and Vue application mount.
- `/home/akasection/git/heymo-campaign-web/resources/js/pages/Backoffice/dashboard.ts` — typed static dashboard data and the future API-replacement boundary.
- `/home/akasection/git/heymo-campaign-web/resources/js/pages/Backoffice/Dashboard.vue` — dashboard composition, local interaction state, and responsive layout grid.
- `/home/akasection/git/heymo-campaign-web/resources/js/components/Backoffice/SidebarNav.vue` — desktop/mobile navigation with active state.
- `/home/akasection/git/heymo-campaign-web/resources/js/components/Backoffice/Panel.vue` — consistent white dashboard panel surface.
- `/home/akasection/git/heymo-campaign-web/resources/js/components/Backoffice/StatTile.vue` — reusable metric presentation.
- `/home/akasection/git/heymo-campaign-web/resources/js/components/Backoffice/StatusBadge.vue` — semantic campaign/sample/alert status treatment.
- `/home/akasection/git/heymo-campaign-web/resources/js/components/Backoffice/HexValidationTile.vue` — reusable clipped validation state control.
- `/home/akasection/git/heymo-campaign-web/resources/js/App.vue` — Vue mount-level Backoffice page rendering.

**Verification**

1. Run `pnpm lint` to check the new Vue/TypeScript code.
2. Run `pnpm format:check` to confirm repository formatting compliance; apply the repository formatter and rerun only if this check identifies touched-file formatting changes.
3. Run `pnpm build` to verify Vite, Tailwind v4 class extraction, Vue compilation, and the new Phosphor icon import paths.
4. Run `pnpm dev:all`, load `http://localhost:8000`, and manually check desktop plus narrow mobile layouts. Confirm all tiles render, no content overlaps or creates unintended horizontal overflow, and local nav/alert/validation interactions work by mouse and keyboard.

**Decisions**

- Included: admin dashboard only, campaign-operations content, typed static demo data, Tailwind v4 semantic tokens, a small reusable Vue component set, Phosphor icons, responsive behavior, and lightweight local interactions.
- Excluded: the landing page and email layouts in the reference, Laravel migrations/models/controllers/API endpoints, authentication/authorization, final logo/illustration assets, persistent state, routing, and real geospatial/chart libraries.
- The visual direction is light, clinical, and operational: deep navy structure, blood-red actions/status emphasis, cool neutral surfaces, restrained shadows, and hexagonal validation geometry.
- The API boundary is the typed Backoffice dashboard data module. Wiring a Laravel API later should replace that module's data source rather than reshape the UI components.
