# Heymo Intent-Led Campaign Assignment

## Backlog

### T-02 Create the campaign domain and immutable audit schema

- tags: [backend, database, audit, core]
- priority: high
- workload: Extreme
- steps:
  - [ ] Add migrations, models, factories, and relationships.
  - [ ] Add status enums or constrained values and useful database indexes.
  - [ ] Capture immutable consent, generation, validation, and send-event records.

  ```md
  **Dependencies:** T-01 Define the campaign contract and safety policy.

  Create the minimum domain: Brand, Angle, LandingPage/landing identifier, Visitor,
  IntentResponse, ConsentRecord, Campaign, CampaignMessage, GenerationAttempt, and
  DeliveryEvent/Suppression. Model campaign messages by sequence position and channel so email
  audit data is queryable without parsing blobs. Enforce the no-unapproved-claims rule through
  a deterministic, brand-scoped evidence-source boundary. A relational `ApprovedClaim` registry
  is optional enrichment when it adds useful fact selection, conditions, versioning, or audit
  queryability; it is not a required product entity.

  **Done when:** one visitor can be traced from landing page and angle through answers,
  consent, campaign, generated message, policy outcome, and send status. A consent withdrawal
  and a conversion/suppression state are durable and cannot be bypassed by a queued send.
  ```

### T-04 Configure provider-neutral LLM and mail integration

- tags: [backend, llm, mail, queue, configuration]
- priority: high
- workload: Hard
- steps:
  - [ ] Add documented environment settings for OpenAI-compatible base URL, key, model, timeout, and retry limits.
  - [ ] Implement a small HTTP client boundary for Together AI and Fireworks AI.
  - [ ] Configure a development mail driver and the queued worker workflow.

  ```md
  **Dependencies:** T-01 Define the campaign contract and safety policy.

  The selected provider and model must be configuration values, not source-code constants.
  Use Laravel's HTTP client/Guzzle at the integration boundary; do not introduce a large SDK
  solely for an OpenAI-compatible endpoint. Mailtrap/Mailpit is acceptable for local delivery,
  provided the README names the driver and queue-worker command.

  **Done when:** a configuration check can report a clear non-secret error for a missing key,
  timeout, rate limit, or unexpected provider response, and a job can be retried safely.
  ```

### T-06 Build authenticated angle management

- tags: [backend, frontend, admin, angle]
- priority: high
- workload: Hard
- steps:
  - [ ] Create, edit, archive, and list angles scoped to a brand.
  - [ ] Capture all PRD angle fields and deterministic proof references; support optional evidence enrichment.
  - [ ] Validate that promise, proof, offer, and next step are present and truthful.

  ```md
  **Dependencies:** T-02 Create the campaign domain and immutable audit schema; T-03 Add
  authenticated admin access and authorisation; T-05 Build brand management settings.

  Implement the PRD fields: audience, trigger moment, primary job, tension, desired outcome,
  single promise, proof, objection, offer, tone, and next step. Treat angle data as campaign
  strategy and landing-page reference; it is not a runtime page-builder.

  **Done when:** an admin can see which brand owns every angle, configure deterministic proof,
  optionally select brand-scoped evidence enrichment, and archive an angle without breaking
  historical campaign audit records.
  ```

### T-07 Build three hand-authored, message-matched landing pages

- tags: [frontend, landing-pages, vue, blade]
- priority: high
- workload: Hard
- steps:
  - [ ] Build one static page each for fatigue/low energy, athletic performance, and family-history concern.
  - [ ] Apply the owning brand's visual identity without dynamically composing pages from angle records.
  - [ ] Add a clear entry point to the capture quiz and preserve source-page context.

  ```md
  **Dependencies:** T-05 Build brand management settings; T-06 Build
  authenticated angle management.

  These pages are deliberately hand-written Blade templates/Vue components. Each must make its
  angle unmistakable before the visitor sees a generic product catalogue. The stored landing
  identifier maps the page to a seeded angle for capture and reporting.

  **Done when:** all three URLs load directly, present distinct angle-led copy, and retain their
  landing identifier and angle context when the quiz opens.
  ```

### T-08 Implement intent capture, explicit consent, and suppression handling

- tags: [backend, frontend, capture, consent, safety]
- priority: high
- workload: Extreme
- steps:
  - [ ] Build the short quiz/pop-up and public submission endpoint.
  - [ ] Validate and persist age, sex, sub-interest, trigger, concern, page/angle, timestamp, email, and consent.
  - [ ] Provide unsubscribe/suppression handling that prevents future sends.

  ```md
  **Dependencies:** T-02 Create the campaign domain and immutable audit schema; T-06 Build
  authenticated angle management; T-07 Build three hand-authored, message-matched landing
  pages.

  Each captured field must affect campaign tone, emphasis, or presentation. Record affirmative
  email consent separately from the profile with source, timestamp, policy/version, and the
  exact submitted address. No campaign generation or email send may proceed without active
  consent. Do not collect clinical information beyond the stated concern.

  **Done when:** invalid submissions cannot create sends, the confirmation path gives a clear
  pending/generated state, and a withdrawal or conversion excludes the visitor from queued and
  future delivery.
  ```

### T-09 Generate and validate the three-email personalised welcome sequence

- tags: [backend, llm, campaign, safety, core]
- priority: high
- workload: Extreme
- steps:
  - [ ] Build the prompt payload and structured-response parser.
  - [ ] Generate promise, mechanism, and angle-specific-objection email beats.
  - [ ] Apply policy validation, repair retry, deterministic composition, and audit persistence.
  - [ ] Apply demographic presentation profiles without demographic clinical inferences.

  ```md
  **Dependencies:** T-01 Define the campaign contract and safety policy; T-02 Create the
  campaign domain and immutable audit schema; T-04 Configure provider-neutral LLM and mail
  integration; T-05 Build brand management settings; T-06 Build
  authenticated angle management; T-08 Implement intent capture, explicit consent, and
  suppression handling.

  The sequence is fixed: (1) deliver the promise and restate the angle in the visitor's
  language, (2) explain the approved mechanism plainly, (3) address the recorded
  angle-specific objection. Personalisation must use the visitor's stated sub-interest,
  trigger, and concern directly; age and sex select the presentation treatment only.

  **Done when:** two same-angle profiles with different demographics or answers produce
  materially different body copy, emphasis, and email look while retaining the exact same
  deterministic evidence set where their clinical context is the same. Every rejected or successful
  attempt records prompt version, provider/model, raw structured response, policy result, and
  final composed output.
  ```

### T-10 Queue, send, and observe campaign email delivery

- tags: [backend, mail, queue, reliability]
- priority: high
- workload: Hard
- steps:
  - [ ] Create a queued send flow with scheduled sequence positions and idempotency.
  - [ ] Re-check active consent and suppression immediately before each send.
  - [ ] Record attempted, sent, skipped, and failed delivery events with actionable errors.

  ```md
  **Dependencies:** T-02 Create the campaign domain and immutable audit schema; T-04 Configure
  provider-neutral LLM and mail integration; T-08 Implement intent capture, explicit consent,
  and suppression handling; T-09 Generate and validate the three-email personalised welcome
  sequence.

  Do not couple provider latency to the capture request. Jobs must safely handle retries,
  timeouts, rate limits, and duplicate dispatch. The mail driver needs to support local review
  in development, and no message should be marked sent unless the driver accepted it.

  **Done when:** the three messages are observable in Mailpit/Mailtrap or the configured driver,
  failures remain auditable, and a suppressed visitor's queued messages are skipped.
  ```

### T-12 Seed a complete, deterministic reviewer demo

- tags: [backend, database, seeding, demo]
- priority: high
- workload: Hard
- steps:
  - [ ] Seed an administrator, two contrasting brands, three angles, optional evidence enrichment, and landing mappings.
  - [ ] Seed diverse consented, suppressed, and failed-generation visitor histories.
  - [ ] Seed authored/validated sample campaigns so review does not need an API key.
  - [ ] Verify a clean reset and reseed from an empty database.

  ```md
  **Dependencies:** T-02 Create the campaign domain and immutable audit schema; T-03 Add
  authenticated admin access and authorisation; T-05 Build brand management settings;
  T-06 Build authenticated angle management; T-09 Generate and validate the
  three-email personalised welcome sequence; T-10 Queue, send, and observe campaign email
  delivery.

  The seeded brands must differ in both visual treatment and copy voice. The visitors should
  include same-angle comparisons where age, sex, and answers visibly change the emails. Seed
  realistic persisted sample outputs rather than calling a paid/remote LLM during seeding.

  **Done when:** `php artisan migrate:fresh --seed` succeeds unattended and the reviewer can log
  in immediately to inspect all required scenarios.
  ```

### T-13 Document setup, one end-to-end journey, and design decisions

- tags: [documentation, readme, delivery]
- priority: high
- workload: Normal
- steps:
  - [x] Document local install, Docker services, env settings, queue worker, mail driver, and seed/reset command.
  - [ ] Publish seeded admin credentials and landing-page URLs.
  - [ ] Walk through one named visitor's inputs, campaign rationale, and all three resulting messages.
  - [ ] Explain the safety/constraint strategy and testing choices.

  ```md
  **Dependencies:** T-01 Define the campaign contract and safety policy; T-04 Configure
  provider-neutral LLM and mail integration; T-07 Build three hand-authored, message-matched
  landing pages; T-08 Implement intent capture, explicit consent, and suppression handling;
  T-09 Generate and validate the three-email personalised welcome sequence; T-10 Queue, send,
  and observe campaign email delivery; T-11 Build the admin audit trail and angle performance
  dashboard; T-12 Seed a complete, deterministic reviewer demo.

  The README is part of the review experience. It should make provider configuration and the
  zero-key seeded demo unambiguous, explain how intent flows to campaign generation, and call
  out the deliberate boundary between personalisation and clinical claims.

  **Done when:** a new reviewer can set up, seed, sign in, and inspect a complete journey using
  only the README.
  ```

### T-14 Add focused verification for release-blocking behaviour

- tags: [testing, safety, reliability]
- priority: high
- workload: Hard
- steps:
  - [ ] Test active-consent and suppression enforcement at generation and send time.
  - [ ] Test safety validator rejection and provider/malformed-output failure paths.
  - [ ] Test brand/visitor divergence and the fresh-seed command.

  ```md
  **Dependencies:** T-02 Create the campaign domain and immutable audit schema; T-08 Implement
  intent capture, explicit consent, and suppression handling; T-09 Generate and validate the
  three-email personalised welcome sequence; T-10 Queue, send, and observe campaign email
  delivery; T-12 Seed a complete, deterministic reviewer demo.

  Automated coverage is optional in the PRD, but these are the highest-value tests because they
  protect regulated-content and consent release blockers. Use a fake LLM client and mail driver
  to avoid network dependencies. Record any deliberately manual visual checks in the README.

  **Done when:** test names demonstrate the three decisive guarantees: no consent means no send,
  invalid copy means no send, and materially different inputs yield different safe campaigns.
  ```

### B-01 Add consented SMS as a persisted, non-delivery branch

- tags: [bonus, sms, consent, campaign]
- priority: medium
- workload: Hard

  ```md
  **Dependencies:** T-02 Create the campaign domain and immutable audit schema; T-08 Implement
  intent capture, explicit consent, and suppression handling; T-09 Generate and validate the
  three-email personalised welcome sequence; T-11 Build the admin audit trail and angle
  performance dashboard.

  Generate concise SMS for confirmation, a single relevant objection, or a truthful deadline.
  Persist rather than integrate a provider. Enforce separate SMS consent, quiet hours,
  frequency caps, and a simple opt-out state. Surface the rationale beside campaign emails.
  ```

### B-02 Add post-welcome engagement branching

- tags: [bonus, automation, analytics]
- priority: medium
- workload: Hard

  ```md
  **Dependencies:** T-02 Create the campaign domain and immutable audit schema; T-09 Generate
  and validate the three-email personalised welcome sequence; T-10 Queue, send, and observe
  campaign email delivery; T-11 Build the admin audit trail and angle performance dashboard.

  Model open/click/quiet events and select a bounded, pre-approved follow-up path. Keep the
  evidence of the branch decision visible in the visitor audit view.
  ```

### B-03 Add side-by-side angle performance comparison

- tags: [bonus, reporting, analytics]
- priority: low
- workload: Normal

  ```md
  **Dependencies:** T-11 Build the admin audit trail and angle performance dashboard.

  Extend the dashboard beyond core counts with an easy-to-scan comparison of capture and consent
  performance across angles. Avoid causal claims from the small demo data set.
  ```

## Todo

## In Progress

### T-11 Build the admin audit trail and angle performance dashboard

- tags: [frontend, backend, admin, reporting, audit]
- priority: high
- workload: Extreme
- steps:
  - [ ] Add aggregate angle metrics for captures, consent rate, generated campaigns, and sends.
  - [ ] Add visitor list and detail screens with lifecycle state.
  - [ ] Show the complete evidence chain and a readable explanation of generation choices.

  ```md
  **Dependencies:** T-02 Create the campaign domain and immutable audit schema; T-03 Add
  authenticated admin access and authorisation; T-05 Build brand management settings;
  T-06 Build authenticated angle management; T-08 Implement intent capture, explicit
  consent, and suppression handling; T-09 Generate and validate the three-email personalised
  welcome sequence; T-10 Queue, send, and observe campaign email delivery.

  A reviewer must be able to follow: landing page -> angle -> captured answers -> consent ->
  campaign inputs/presentation profile -> policy decision -> generated emails -> actual delivery
  events. Expose enough detail to audit the result without placing secrets or API keys in the UI.

  **Done when:** the aggregate view compares all three angles and a single visitor page clearly
  answers what was generated, why it was generated, and whether it was sent.
  ```

## Review / QA

## Done

### T-05 Build brand management settings

- tags: [backend, frontend, admin, brand, safety]
- priority: high
- workload: Hard
- steps:
  - [x] Build authenticated brand list, create, edit, archive, and restore flows.
  - [x] Manage visual identity, voice, reading level, sign-off groundwork, preferred and avoided phrases, fonts, and logo.
  - [x] Derive and expose a versioned Markdown-backed brand prompt profile.

  ```md
  **Dependencies:** T-02 Create the campaign domain and immutable audit schema; T-03 Add
  authenticated admin access and authorisation.

  This closes the agreed brand-settings slice. An administrator can maintain materially
  different brand identities and writing profiles through the organization-scoped backoffice.
  The Tone/Tense sign-off matrix is documented as generation groundwork, while the runtime
  signature identity and resolver are deferred. Required compliance language and the deterministic
  evidence source are intentionally deferred until campaign-generation priorities require them.
  A relational `ApprovedClaim` registry remains optional enrichment and is not a product dependency.

  **Done when:** an admin can maintain two substantially different brand profiles, inspect the
  derived prompt profile, and make the settings available to future landing and campaign
  pipelines without exposing provider secrets.
  ```

### T-01 Define the campaign contract and safety policy

- tags: [architecture, safety, llm, prd]
- priority: high
- workload: Hard
- steps:
  - [x] Define the entity relationship map and ownership boundaries.
  - [x] Identify which angle fields feed generation and which remain editorial documentation.
  - [x] Specify the structured model response schema for all three email beats.
  - [x] Write the validation and retry/fail-closed policy for unsafe or malformed output.

  ```md
  **Why:** The PRD requires constraints to be enforced, rather than merely placed in an LLM prompt.

  **Generation inputs:** angle audience, trigger, primary job, tension, desired outcome,
  single promise, deterministic proof, objection, truthful offer, tone, and next step; visitor
  age range, sex, sub-interest, trigger, concern, page context, and consent; brand voice,
  reading level, sign-off, visual identity, required language, banned phrases, and a
  deterministic evidence source when factual content requires it.

  **Enforcement design:** provide factual evidence and disclosures through a deterministic,
  brand-scoped source. The source may be relational, configured, or block-based; the PRD does
  not prescribe its entity name. The model returns valid JSON that selects evidence IDs and
  writes only personalisation/transition fields. A server-side policy validator rejects missing
  required language, banned phrases, unsupported evidence references, prohibited
  diagnosis/cure/outcome language, demographic clinical inference, inaccurate subjects, and
  malformed structure. Re-prompt once with violations; if it remains invalid or the provider
  fails, persist a failed generation attempt and send nothing. The final email is composed from
  validated prose plus validated evidence and deterministic blocks, with the prompt recipe,
  policy result, evidence snapshot, and model metadata retained for audit.

  **Done when:** this decision is documented in the README or an architecture note, with one
  example showing exactly why two profiles receive different presentation without different
  clinical claims.
  ```

### D-01 Prepare local development infrastructure

- tags: [setup, infrastructure]
- priority: low
- workload: Hard
  ```md
  Laravel 9/PHP 8.1, Vue 3/Vite/Tailwind, PostgreSQL 18, Valkey 8, Docker Compose, pnpm, and
  project linting/formatting conventions are established. The current application remains the
  intentional starter scaffold for the assignment implementation.
  ```

### T-03 Add authenticated admin access and authorisation

- tags: [backend, frontend, auth, admin]
- priority: high
- workload: Normal
- defaultExpanded: false
- steps:
  - [x] Add a secure session-based admin login and logout flow.
  - [x] Protect all admin UI and admin API routes.
  - [x] Seed one documented administrator account.
  - [x] Seed three other business accounts.

  ```md
  **Dependencies:** T-02 Create the campaign domain and immutable audit schema.

  Use Laravel's web/session guard for the dashboard rather than exposing admin operations
  through the public capture API. Add a minimal role/ability boundary now, even if the demo
  seeds only administrators.

  **Done when:** unauthenticated users cannot read or mutate brands, angles, visitors, or
  campaigns, while the seeded administrator can access the dashboard.
  ```
