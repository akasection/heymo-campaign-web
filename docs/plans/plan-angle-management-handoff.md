# Plan: Angle Management Handoff

## Readiness

T-06 is ready to start as the next implementation slice.

T-05 now provides the reusable foundation:

- organization-owned `Brand` records with stable slugs and soft archive support;
- `Organization::brands()` and the `Brand::forOrganization()` query scope;
- admin capability and policy authorization patterns;
- the existing session-backed JWT API boundary;
- typed Brand resource/composable patterns in the backoffice; and
- a storage-neutral deterministic evidence contract.

T-02 remains in Backlog and owns the wider campaign schema. T-06 should add the Angle portion
without assuming that the remaining T-02 entities already exist. If another T-02 implementation
lands first, reconcile the Angle migration/model rather than defining the same tables twice.

The deferred compliance language and relational `ApprovedClaim` enrichment do not block the
Angle management UI. The Angle `proof` field must still be deterministic and admin-authored;
T-09 must not use it as permission for the model to invent facts.

## Scope

Build authenticated create, edit, archive, restore, and list flows for strategy records owned by
a Brand. An Angle is campaign strategy and landing-page context, not a runtime page builder or a
quiz-based matcher.

### Fields

Persist these PRD fields as queryable columns:

- `brand_id`
- `name` and stable, Brand-scoped `slug` for administration and future landing identifiers
- `audience`
- `trigger_moment`
- `primary_job`
- `tension`
- `desired_outcome`
- `single_promise`
- `proof`
- `objection`
- `offer`
- `tone`
- `next_step`
- timestamps and soft archive timestamp

For the first slice, `proof` is a deterministic Angle-owned block/reference entered by an
administrator. It is never model-authored. A later evidence registry can replace or supplement
that field with stable evidence IDs without changing the Angle ownership contract.

`tone` is an Angle-level strategy instruction and must remain distinct from the Brand's global
`tone_preset` and Tone/Tense sign-off matrix. The Brand controls the overall voice; the Angle
controls the campaign-specific emotional emphasis.

Use an allowlist in `config/angles.php` for values such as `reassuring`, `authoritative`, `warm`,
and `matter_of_fact`. Do not make the model infer or invent Angle tone from arbitrary prose.

## Domain and persistence

1. Add `Brand::angles()` and `Angle::brand()` relationships.
2. Store no duplicated `organization_id` on Angle. Resolve organization ownership through the
   owning Brand and scope Brand first in every request.
3. Add a unique constraint on `brand_id` plus `slug` and an index on `brand_id` plus archive
   state.
4. Use soft deletion so archived Angles remain available to historical campaign references.
5. Keep the proof and strategy fields as structured, bounded columns; do not store the entire
   Angle as an opaque JSON blob.
6. Generate the slug server-side on create and keep it stable when the Angle name changes.

## Authorization and API

Use nested, organization-scoped routes:

- `GET /api/brands/{brand}/angles`
- `POST /api/brands/{brand}/angles`
- `GET /api/brands/{brand}/angles/{angle}`
- `PUT /api/brands/{brand}/angles/{angle}`
- `DELETE /api/brands/{brand}/angles/{angle}`
- `POST /api/brands/{brand}/angles/{angle}/restore`

The controller must first resolve the Brand through the authenticated user's organization, then
resolve the Angle through that Brand. Do not rely on unscoped implicit model binding.

Add `AnglePolicy` with `viewAny`, `view`, `create`, `update`, `delete`, and `restore`. Every
mutation requires `backoffice.manage`; every policy path verifies that the Angle's Brand belongs
to the user's organization. Do not accept `organization_id` from the client.

Return a safe `AngleResource` containing the Brand summary, all strategy fields, archive state,
and deterministic proof data. Keep provider configuration and future evidence source internals
out of the browser payload.

## Validation

- Require every PRD field needed to form a coherent strategy. At minimum, `name`, `audience`,
  `trigger_moment`, `primary_job`, `tension`, `desired_outcome`, `single_promise`, `proof`,
  `objection`, `offer`, `tone`, and `next_step` must be non-empty.
- Validate `tone` with the shared options configuration.
- Trim text fields and normalize whitespace at the request boundary.
- Keep the proof text/reference deterministic and bounded; do not pass an admin field through as
  an instruction to the model.
- Add a pure `AngleOfferValidator` or equivalent helper for false urgency, fake scarcity,
  hidden conditions, and misleading-comparison language. Reject unsafe offer text rather than
  trying to repair it in the UI.
- Do not validate medical truth with an LLM. The later generation validator and deterministic
  evidence source remain responsible for the regulated boundary.

## Backoffice experience

Add an `Angles` entry to the existing backoffice navigation and reuse the Brand visual language.
The page should provide:

- a Brand filter or grouped Brand list;
- active and archived Angle views;
- compact cards/table rows showing the Angle name, Brand, tone, proof state, and archive state;
- a structured editor for all 11 PRD fields;
- a readable proof/offer/next-step review area;
- loading, empty, validation, and API error states; and
- archive/restore actions restricted to administrators.

Use `useAngles` for resource state and request sequencing, and a small `useAngleForm` for draft
normalization and validation. Reuse the existing daisyUI controls and `Panel` composition. Do not
add a page builder, runtime page composition, or a new state library.

The form should explain the distinction between Brand voice and Angle strategy through field
labels and layout, not through a second editable prompt. The Angle does not need to expose the
Tone/Tense sign-off matrix; that remains Brand-level generation groundwork.

## Landing-page boundary

T-06 stores strategy and stable slugs. It does not create landing pages or decide which Angle a
visitor receives. T-07 will hand-author static pages and map their stable identifiers to seeded
Angles. A newly created Angle may have no page until a developer authors one; that is expected.

## Verification

No feature tests are planned for this slice. Add only pure utility coverage for offer validation,
text normalization, and any deterministic prompt/presentation mapping. Verify the rest through:

- frontend lint and the existing TypeScript/Vite build;
- non-destructive migration/bootstrap checks;
- manual admin CRUD, archive/restore, invalid-offer, and Brand-scoping checks; and
- an authenticated request from a member or another organization's account to confirm denial.

Do not run `migrate:fresh --seed` against the active development database without explicit
approval or an isolated database target.

## Handoff checklist

- [x] Brand model, migration, organization relationship, policy, API, and UI exist.
- [x] Brand ownership is resolved through the authenticated organization.
- [x] Evidence/ApprovedClaim storage is optional and does not block Angle CRUD.
- [ ] Add Angle migration, model, factory, and Brand relationship.
- [ ] Add Angle options, request normalization, and validation helper.
- [ ] Add Angle policy, resource, controller, and nested routes.
- [ ] Add `useAngles`, `useAngleForm`, list/detail/editor UI, and navigation.
- [ ] Add deterministic Angle seed data in the reviewer-demo phase.
- [ ] Reconcile the implementation with T-02 before adding later campaign entities.
