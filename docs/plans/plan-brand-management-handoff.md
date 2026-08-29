# Plan: Brand Management Handoff

> Status: **T-05 scoped Brand Settings complete**. Compliance language, runtime sign-off
> resolution, and deterministic evidence administration remain deferred by product decision.

## Current State

T-03 provides organization-scoped authentication. Each user has one `organization_id`,
JWTs carry that organization context, and the JWT middleware rejects stale or mismatched
organization claims. The four seeded organizations are useful tenant/account boundaries;
they are not marketing brands.

The scoped T-05 implementation now provides an organization-owned `Brand` model, brand
configuration, a brand API, policy authorization, and a backoffice management UI. It stores
visual identity, four named writing preferences, preferred/avoided language, logo storage, and
a versioned Markdown-backed prompt profile.

There is not yet an Angle relationship or runtime deterministic evidence registry. Those are
the next domain decisions for T-06 and later generation work.
The current dashboard shows the signed-in user's organization and intentionally has no
organization switcher.

## Target State

Keep `Organization` and `Brand` as separate concepts:

```text
Organization
  hasMany Users
  hasMany Brands

Brand
  belongsTo Organization
  hasMany Angles

Optional enrichment:
  Brand
    hasMany ApprovedClaims (when a relational evidence registry is useful)
```

A brand is an organization-owned marketing identity. It stores visual identity, voice,
reading level, sign-off, required language, banned phrases, and compliance constraints. Angles
and future campaigns belong to a brand. Historical records must retain their brand relationship
and must not depend on changing organization/user records. The campaign system requires a
deterministic, brand-scoped evidence source for factual health content, but does not require a
specific persistence model; a relational `ApprovedClaim` registry is optional enrichment.

Do not convert the four existing organizations into brands automatically. Seed two
contrasting brands under `Heymo Org` so the current seeded administrator can review both
without cross-organization switching. Keep the other three organizations as business
accounts for the T-03 access-boundary demonstration.

## Decisions and Boundaries

- No platform-wide organization switching is part of this work.
- `admin@heymo.test` remains an admin for `Heymo Org`, not a global administrator.
- Brand management is available only to users with the backend-enforced
  `backoffice.manage` capability.
- Every brand query and mutation is scoped through the authenticated user's organization.
- Members may be allowed to view data later, but must not create, edit, or archive brands or
  optional evidence records unless an explicit capability is added.
- Static landing pages remain hand-authored. Brand/angle data supplies ownership and
  campaign context; it must not become a runtime page builder.
- Do not add Vue component or feature tests in this slice. Keep unit tests for pure utilities
  only, matching the current testing decision and `AGENTS.md`.

## Affected Files

| File                                                     | Change Type   | Dependencies                                                                                |
| -------------------------------------------------------- | ------------- | ------------------------------------------------------------------------------------------- |
| `database/migrations/*_create_brands_table.php`          | create        | `organizations` migration                                                                   |
| `database/migrations/*_create_approved_claims_table.php` | optional      | only if relational evidence enrichment is selected                                          |
| `database/migrations/*_create_angles_table.php`          | create        | brands migration; optional evidence enrichment if selected                                  |
| `database/migrations/*_create_landing_pages_table.php`   | create        | angles migration; keep static page identifiers                                              |
| `app/Models/Brand.php`                                   | create        | Organization relationship                                                                   |
| `app/Models/ApprovedClaim.php`                           | optional      | relational evidence enrichment only                                                         |
| `app/Models/Angle.php`                                   | create        | Brand and deterministic proof references                                                    |
| `app/Models/LandingPage.php`                             | create        | Angle relationship                                                                          |
| `app/Models/Organization.php`                            | modify        | `hasMany(Brand::class)`                                                                     |
| `app/Models/User.php`                                    | modify        | organization-scoped authorization helpers if needed                                         |
| `database/factories/*`                                   | create/modify | deterministic seed support and utility fixtures                                             |
| `database/seeders/DatabaseSeeder.php`                    | modify        | seed organizations, two brands, optional evidence, angles, and mappings in dependency order |
| `app/Http/Controllers/*` and `app/Http/Requests/*`       | create        | authenticated brand and optional evidence endpoints                                         |
| `routes/api.php`                                         | modify        | protected, organization-scoped routes                                                       |
| `resources/js/pages/Backoffice/*`                        | create/modify | brand list/forms and dashboard navigation                                                   |
| `resources/js/lib/*`                                     | modify        | typed brand API calls only if existing helper surface is insufficient                       |
| `README.md`                                              | modify        | brand model, seeded demo, reset command, and safety decisions                               |
| `tests/Unit/*`                                           | create/modify | pure utility behavior only                                                                  |

## Execution Plan

### Phase 1: Lock the domain contract

- [ ] Define the minimum Brand fields: organization owner, name, slug, active/archive state,
      logo reference, structured visual tokens, voice, reading level, sign-off, required phrases,
      banned phrases, and compliance language.
- [ ] If relational evidence enrichment is selected, define optional evidence fields such as
      brand owner, exact text, source/reference, conditions or disclosure, status, and archive
      metadata. Do not make this table a prerequisite for the core product loop.
- [ ] Define the full Angle fields from the PRD and the selected deterministic proof source;
      a relational approved-evidence relation is optional.
- [ ] Define a stable landing identifier to angle mapping without composing page markup from
      database records.
- [ ] Decide which fields are generation inputs versus editorial/audit documentation and
      record the decision in the README or a design note.
- **Verify:** review foreign-key ownership, uniqueness rules, archive behavior, and the
  organization scope before writing migrations.

### Phase 2: Build the T-02 persistence foundation

- [ ] Add migrations in dependency order with foreign keys, useful indexes, organization-plus
      slug uniqueness, explicit statuses, and nullable archive timestamps where history must remain.
- [ ] Use PostgreSQL JSON/JSONB-compatible fields for visual tokens and phrase lists only where
      the data is genuinely structured; keep core angle fields queryable as columns and add a
      relational evidence registry only when its queryability is worth the extra domain surface.
- [ ] Add Eloquent relationships, casts, factories, and optional evidence-selection relations
      only when that enrichment is selected.
- [ ] Preserve historical brand/angle references when a record is archived; do not cascade-delete
      campaign evidence.
- **Verify:** run migrations in a disposable test database and execute focused pure-utility
  checks before adding the UI.

### Phase 3: Add backend management and authorization

- [ ] Add authenticated brand list, create, update, and archive endpoints.
- [ ] Add optional evidence management endpoints only if the relational enrichment is selected;
      the core API must expose the deterministic evidence-source boundary without assuming a
      relational evidence table.
- [ ] Add angle management endpoints after brand ownership is available. Validate proof against
      the configured deterministic evidence source when one is selected.
- [ ] Enforce `auth.jwt` plus the backend `backoffice.manage` capability on mutations; never rely
      on hidden frontend controls.
- [ ] Scope every query by `$request->user()->organization_id` and reject cross-organization IDs.
- [ ] Return archived records only through an explicit review/history path.
- **Verify:** manually exercise unauthenticated access, member mutation denial,
  cross-organization access denial, valid admin CRUD, and archive preservation. Keep new
  automated coverage limited to pure utilities in this slice.

### Phase 4: Add the admin UI

- [ ] Add a brand management entry from the existing Backoffice navigation.
- [ ] Build a compact list/detail/edit flow for brand identity and copy constraints.
- [ ] Surface deterministic evidence/proof within the owning brand context; a relational
      approved-claim editor is optional enrichment.
- [ ] Show the current organization clearly and do not add an organization switcher.
- [ ] Keep controls keyboard accessible and consistent with the existing Heymo visual language.
- [ ] Use typed API responses and the existing `apiFetch` authentication helper.
- **Verify:** run targeted frontend lint/format checks and a production Vite build; manually
  confirm the seeded admin can see both brands while a member cannot mutate them.

### Phase 5: Make the seed deterministic and reviewer-ready

- [ ] Keep the four existing organizations and four accounts unchanged.
- [ ] Seed two genuinely different brands under `Heymo Org`, for example a measured clinical
      identity and a warmer active-lifestyle identity.
- [ ] Make the brands differ in colors, typography tokens, voice, reading level, sign-off,
      required language, and banned phrases, not only in name or logo.
- [ ] Optionally seed deterministic evidence entries with conditions and disclosures, then seed
      three angles and static landing identifiers after their migrations exist.
- [ ] Use `updateOrCreate` or equivalent stable keys so normal reseeding is deterministic.
- [ ] Add authored sample records later through T-12 so reviewers can inspect the demo without
      an LLM key or remote provider call.
- **Verify:** on an isolated/disposable database, run exactly `php artisan migrate:fresh --seed`
  and confirm it completes unattended and produces both brands plus the core demo records.
  Do not run the destructive command against the active development database without explicit
  approval or an isolated database target.

### Phase 6: Document and hand off

- [ ] Explain Organization versus Brand and explicitly state that organization switching is out
      of scope.
- [ ] Document the two seeded brands, their contrasting constraints, and the admin review path.
- [ ] Document which fields feed generation, which deterministic evidence source is allowed,
      and how archive/scoping protects audit history. Name `ApprovedClaim` only as optional
      relational enrichment.
- [ ] Update the end-to-end README journey when visitor/campaign tickets are implemented.
- [ ] Leave T-03 marked Done; update ticket status only when the corresponding future ticket is
      actually delivered.
- **Final verification:** `vendor/bin/phpunit`, `composer lint`, `pnpm lint`, `pnpm build`, and
  `git diff --check`.

## Rollback Plan

1. If the schema work fails, roll back only the new brand/domain migrations and preserve the
   existing T-03 migrations and seeded users.
2. If an API or UI change breaks authentication, verify the existing T-03 auth feature tests
   first and remove only the new routes/components; do not alter JWT/session behavior to make
   brand tests pass.
3. If seed data fails, fix dependency order or stable keys in the new seed section and validate
   on an isolated database before touching the current development database.
4. If a migration must be revised after data exists, create a forward migration or reset an
   isolated demo database; do not destroy a shared database as a shortcut.

## Risks

- Treating organizations as brands would make tenancy and marketing identity inseparable and
  would prevent one organization from owning multiple brands.
- Frontend-only capability checks could expose cross-organization or member mutations; all
  management authorization must be enforced server-side.
- JSON-only storage for important evidence or angle fields may make audit/reporting queries
  difficult; choose relational evidence enrichment when queryability is worth the added domain
  surface, but do not treat it as mandatory.
- Seeding brands in external organizations would make them invisible to the current Heymo
  admin because organization switching is intentionally out of scope.
- Running `migrate:fresh --seed` against the active local database can erase current data; use
  an isolated target for PRD verification.
