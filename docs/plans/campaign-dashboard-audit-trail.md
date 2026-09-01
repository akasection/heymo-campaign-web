# Plan: Campaign Dashboard, Audit Trail & Deterministic Demo Seed (T-11 + T-12)

**TL;DR** — Build the real T-11 dashboard + audit trail on top of the existing domain. Add the missing capture metadata (UUID fingerprint, landing beacon, UTM/device/session-duration) and real open tracking (pixel + opened events), then expose two org-scoped APIs (`/api/dashboard`, `/api/audit`) and two Vue views (dashboard + paginated audit list with a detail drawer). Backfill the deterministic reviewer demo seed (T-12) so `migrate:fresh --seed` produces a fully inspectable MVP/pilot dataset.

## Context (verified)

- No analytics backend exists: no metrics endpoints, no pagination, no `ReportsController`. The dashboard is hardcoded mock data in `resources/js/pages/Backoffice/dashboard.ts`.
- `DeliveryEvent` statuses: `attempted/sent/skipped/failed` only. No open/click tracking.
- `visitors` / `intent_responses` have no fingerprint / UTM / device / session columns. Consent matches `Visitor` via `(brand_id, email)` (unique composite).
- Domain data available: `campaigns`, `campaign_messages`, `generation_attempts`, `delivery_events`, `consent_records`, `suppressions`, `intent_responses`, `angles` (angle → brand → organization).
- Auth: `web` guard (Blade) + `auth.jwt` (API). Org scoping via `Brand::forOrganization`. Policies exist only for `Brand` + `Angle`. `apiFetch` wrapper; no Pinia (plain composables); daisyUI v5 (Tailwind v4 CSS-first); `Panel` / `StatTile` / `StatusBadge` components; no table/pagination component.
- Seeder: `DatabaseSeeder` seeds 1 org `heymo-org` + 4 users + 2 brands (`lexical-labs`, `xo-health-group`) + `AngleSeeder` (4 angles each). Zero visitors/campaigns/consent.

## User decisions (Q&A)

- Open rate: implement **real open tracking now** (1×1 pixel endpoint + opened events).
- Landed tracking: **yes** — landing beacon + `landing_events` table + UUID fingerprint (full funnel landed → consented → generated → sent).
- Success rate: **sent ÷ generated campaigns**, plus a small funnel showing each step's rate.
- Audit trail: **paginated list + per-visitor detail** (full evidence chain).
- Demo seed (T-12): **yes** — deterministic reviewer demo seed (no LLM) so the dashboard + audit are verifiable and the reviewer can log in and inspect all scenarios.

## Design decisions

- No extra table library: daisyUI `table` + a small custom `Pagination` component; server-side sort/filter. (Reject `@tanstack/vue-table` to stay minimal.)
- Device detection: `ua-parser-js`, best-effort with `navigator.userAgentData`.
- Store attribution (`utm_*` + referrer) and device as JSON columns (`attribution`, `device`) on `landing_events` and `intent_responses`; `fingerprint` + `session_duration_seconds` as plain columns.
- Open events: new `engagement_events` table (`open` | `click`) — do not overload `DeliveryEvent` (delivery = attempts vs engagement = opens). Track first-open only; store `user_agent`, not IP (PII minimisation per `SECURITY.md`).
- Audit detail: slide-over drawer on the list; deep-link via `?intent={id}` query param (reconstruct from URL per `ARCHITECTURE.md` §14) — no extra Laravel shell route.
- Metrics scope by organization via the brand chain; add `VisitorPolicy` + `CampaignPolicy` (org-scoped `view`) to satisfy `SECURITY.md` §3; list endpoints also scope queries.

## Phases

### Phase A — Capture metadata + landing fingerprinting

1. Migration `2026_09_01_000019_create_landing_events_table`: `id`, `fingerprint` (string 36, indexed), `brand_id` FK nullable (nullOnDelete), `landing_identifier` (80), `angle_id` FK nullable, `attribution` (json nullable), `device` (json nullable), `landed_at`, timestamps; indexes `(fingerprint)`, `(brand_id, landed_at)`, `(angle_id, landed_at)`, `(landing_identifier, landed_at)`.
2. Migration `2026_09_01_000020_add_capture_metadata_to_intent_responses_table`: `fingerprint` (string 36 nullable indexed), `session_duration_seconds` (unsignedInteger nullable), `attribution` (json nullable), `device` (json nullable).
3. New `App\Models\LandingEvent` (casts: `attribution`/`device` → array, `landed_at` → datetime).
4. Extend `IntentResponse`: new fields in fillable + casts; add `campaigns()` HasMany (Campaign has `intent_response_id`).
5. `CaptureRequest`: optional `fingerprint` (uuid), `session_duration_seconds` (int 0..86400), `attribution` (array; whitelist `utm_source/utm_medium/utm_campaign/utm_term/utm_content/referrer`, string max 255), `device` (array; whitelist `type/os/browser`, string max 64). `prepareForValidation` trims strings.
6. `CaptureController::store`: persist `fingerprint` / `session_duration_seconds` / `attribution` / `device` on the `IntentResponse` create.
7. New `LandingEventController@store` (public, fire-and-forget): resolve brand + angle from `brand_id` + `landing_identifier` (angle nullable), create `LandingEvent`, return 202. Route `POST /api/landing`, middleware `web` + new `landing` limiter.
8. `RouteServiceProvider`: add `landing` limiter (per-IP, ~60/min).
9. Frontend: add `ua-parser-js`; new `resources/js/lib/tracking.ts` with `ensureFingerprint()` (`crypto.randomUUID`, `localStorage` "heymo.fingerprint"), `readAttribution()` (`URLSearchParams` utm_* + `document.referrer`; blank → direct), `detectDevice()` (`userAgentData` then `ua-parser-js`), `sendLandingBeacon()` (fetch keepalive, once per session via `sessionStorage` "heymo.landed"), `getSessionDurationSeconds()` (vs `sessionStorage` "heymo.landed_at").
10. Wire beacon in `resources/js/campaign.ts` (runs on landing and quiz; skip if `#quiz-app` already beaconed). `QuizPage.vue` reads fingerprint/attribution/device + session duration and includes them in the `/api/capture` payload. Set "heymo.landed_at" at first landing.

### Phase B — Open tracking (parallel with A)

1. Migration `2026_09_01_000021_add_open_tracking_to_campaign_messages_table`: `open_token` (string 36 unique nullable), `opened_at` (timestamp nullable).
2. Migration `2026_09_01_000022_create_engagement_events_table`: `id`, `campaign_message_id` FK cascadeOnDelete, `type` (16 `open`/`click`), `occurred_at`, `user_agent` (512 nullable), `metadata` (json nullable), timestamps; index `(campaign_message_id, type)`.
3. New `App\Models\EngagementEvent`; extend `CampaignMessage` (fillable `open_token`/`opened_at`; cast `opened_at`; `engagementEvents()` HasMany).
4. `CampaignGenerator::persistMessages`: set `open_token = (string) Str::uuid()` per message.
5. `CampaignMailComposer::compose`: pass `openTrackingUrl = route('open.track', $message->open_token)` to the view; `resources/views/mail/campaign.blade.php` adds a hidden 1×1 `<img>` pixel before `</body>`.
6. New `OpenTrackingController` (public): resolve message by `open_token`; if `opened_at` unset, set `opened_at = now()` + create `EngagementEvent(type=open, user_agent, occurred_at)`; return 1×1 transparent GIF (200, no-store). Route `GET /api/open/{openToken}`, middleware `web` + `open` limiter.
7. `RouteServiceProvider`: add `open` limiter (per-IP ~120/min).

### Phase C — Metrics service + dashboard API (depends on A + B)

1. New `App\Services\CampaignMetricsService`: org + brand (+ optional date range) grouped `selectRaw` queries (no N+1): landings (count + distinct fingerprint), captures, consented visitors (distinct visitors with email consent), campaigns generated, active campaigns (status != failed), messages generated, messages sent (`sent_at` not null), opens (`opened_at` not null), open rate, funnel steps with per-step rate, per-angle rows. Attribution basis: `angle.brand_id` (angle is authoritative); consented attributed to angle via `intent_responses`.
2. New `DashboardController@index` (auth.jwt): optional `brand_id` (default first active brand in org); returns overall + funnel + angles (side-by-side). Org scope via `Brand::forOrganization`.
3. `routes/api.php`: `GET /api/dashboard` under `web` + `auth.jwt`.
4. Policies: new `VisitorPolicy` + `CampaignPolicy` (`view` via `brand.organization_id`), registered in `AuthServiceProvider`. Used by detail endpoints; list endpoints scope queries in the controller.

### Phase D — Audit trail API (depends on A + B; parallel with C)

1. New `App\Http\Resources\AuditRecordResource` (list row: visitor name/email, angle/brand, `captured_at`, attribution, device, session duration, campaign status, messages sent/opened counts) and detail shape with nested chain (landing events by fingerprint, consent records, suppressions, campaigns → messages → generation attempts / delivery events / engagement events).
2. New `AuditController`:
   - `index` — `IntentResponse::with(['visitor', 'angle.brand', 'campaigns'])` ordered by `captured_at` desc, org-scoped via `angle.brand_id` in org brand ids; filters `brand_id`, `angle_id`, `q` (email/name), `sort`; `paginate(per_page default 50, max 100)`.
   - `show` — full chain, org-scoped, `authorize('view')`.
   - Routes `GET /api/audit`, `GET /api/audit/{intentResponse}` under `web` + `auth.jwt`.

### Phase E — Frontend dashboard + audit views (depends on C + D)

1. `router.ts`: add `admin.audit` route (`/audit`, `navigationId: "audit"`); `web.php`: add `Route::view('admin/audit', 'Backoffice.dashboard')->name('admin.audit')` under `auth`.
2. `Dashboard.vue`: add nav item "Audit trail" (id `audit`); render `CampaignDashboard` when `activeNavigation === 'dashboard'`, `AuditTrail` when `'audit'`; remove mock dashboard usage.
3. New `resources/js/lib/dashboard.ts` + `lib/audit.ts` types; `composables/useDashboard.ts` + `useAuditTrail.ts` (mirror `useBrands` pattern).
4. New `pages/Backoffice/CampaignDashboard.vue`: brand selector, `StatTile` row (landed / captures / consented / generated / sent / opens / open rate), funnel bars, angle comparison table (reuse `Panel` / `StatusBadge`; pure-CSS bars like existing trend).
5. New `components/Backoffice/Pagination.vue` (daisyUI `join` buttons, per-page selector default 50).
6. New `pages/Backoffice/AuditTrail.vue`: filters (brand / angle / search), daisyUI table, pagination, row click opens `AuditDetailDrawer.vue` (evidence chain timeline) reading `?intent=` param.

### Phase F — Demo seed (T-12) + verification (depends on A + B; parallel with E)

1. New `Database\Seeders\DemoCampaignSeeder` (called from `DatabaseSeeder` after `AngleSeeder`): deterministic arrays (no LLM) — `landing_events` (incl. some landed-no-submit), visitors, `intent_responses` (fingerprint / attribution / device / session duration), `consent_records`, `suppressions` (incl. a suppressed visitor whose queued messages are skipped), campaigns (generated + one failed-generation), `campaign_messages` (sent/queued + `open_token`), `generation_attempts`, `delivery_events` (attempted/sent/skipped/failed), `engagement_events` (some opened). Include at least one same-angle pair (same brand + angle) whose differing age/sex/answers produce visibly different messages — the PRD's core personalisation test. This is the T-12 reviewer demo.
2. Backdate all seeded records to **before 2026-08-01** (e.g. late July 2026) by explicitly overriding `created_at` / `updated_at` / `captured_at` / `landed_at` / `consented_at` / `scheduled_at` / `sent_at` / `opened_at` etc., so seeded rows are visually distinguishable from live, man-made records.
3. Dev/demo only: `DemoCampaignSeeder::run()` returns early when `app()->environment('production')`. `DatabaseSeeder` calls it after `AngleSeeder`; the base seed (orgs / users / brands / angles) remains first-time-setup and runs everywhere, but demo campaign records never seed in production.
4. Verify `php artisan migrate:fresh --seed` completes unattended (and that a production-env run skips the demo records).

## Relevant files

- `routes/api.php`, `routes/web.php`, `app/Providers/RouteServiceProvider.php`
- `app/Http/Requests/CaptureRequest.php`, `app/Http/Controllers/CaptureController.php`
- New controllers: `LandingEventController`, `OpenTrackingController`, `DashboardController`, `AuditController`
- New service `app/Services/CampaignMetricsService.php`; modified `CampaignGenerator.php`, `CampaignMailComposer.php`
- New models `LandingEvent`, `EngagementEvent`; modified `IntentResponse`, `CampaignMessage`
- New policies `app/Policies/VisitorPolicy.php`, `app/Policies/CampaignPolicy.php`; modified `AuthServiceProvider.php`
- New resource `app/Http/Resources/AuditRecordResource.php`
- New migrations (4); new seeder `DemoCampaignSeeder.php`; modified `DatabaseSeeder.php`
- Frontend: `campaign.ts`, `router.ts`, `lib/tracking.ts`, `lib/dashboard.ts`, `lib/audit.ts`, `composables/useDashboard.ts`, `composables/useAuditTrail.ts`, `pages/Backoffice/{Dashboard,CampaignDashboard,AuditTrail}.vue`, `components/Backoffice/{Pagination,AuditDetailDrawer}.vue`, `QuizPage.vue`
- `resources/views/mail/campaign.blade.php`, `package.json` (`ua-parser-js`)

## Verification

1. `composer lint` / `pnpm lint`; `migrate:fresh --seed` completes clean.
2. Open a landing URL with `?utm_source=google&utm_medium=cpc`, submit the quiz → landing event + intent carry fingerprint/attribution/device/session-duration; email lands in Mailpit; opening it records an open event.
3. Dashboard shows overall metrics + funnel + angle comparison and updates after an open.
4. Audit list paginates at 50/page, filters work, row opens the detail drawer (landing → answers → consent → campaign → messages → delivery → opens).
5. Cross-org isolation: a member of another org sees only their own (or empty) data.
6. Deep link `/admin/audit?intent={id}` restores the right detail on refresh.
7. Seeded demo records all carry timestamps before 2026-08-01; a production-environment seed run skips `DemoCampaignSeeder` entirely (base seed still applies).

## Scope boundaries

- In: dashboard (brand + angle + funnel + open rate), audit list + detail, landing fingerprint/beacon, UTM/device/session-duration capture, open-tracking pixel, deterministic reviewer demo seed (T-12), daisyUI table + custom pagination.
- Out: click tracking (schema supports it, UI not built), SMS branch (B-01), post-welcome branching (B-02), feature tests (per `AGENTS.md`, ask first), client-side chart library, `@tanstack/vue-table`.

## Further considerations

1. Mailpit pixel: the `<img>` points at the app URL (`127.0.0.1:8000`) — verify it fires in Mailpit's web UI; if not, document a manual `curl` check.
2. Unit tests: only pure helpers (e.g. attribution/device sanitizer) per `AGENTS.md`; feature tests only if requested.
3. Attribution/device JSON vs columns — chose JSON for flexibility; if reviewers need SQL filtering by `utm_source`, switch to columns (future tweak).
