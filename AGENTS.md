# Development Server Coordination

- Before starting a development server, check whether the user already has the
  application running. If a user-run instance is available, reuse it for live
  verification instead of starting another process or choosing alternate ports.
- Treat development servers started by the user as user-owned. Do not stop,
  restart, reload, rebuild through, or otherwise disrupt them without first
  telling the user why the lifecycle action is needed and receiving explicit
  confirmation.
- This confirmation requirement also applies when a task appears to require a
  server restart after configuration changes, database seeding, cache clearing,
  or any other process that could interrupt the user's active workflow.
- When an existing server cannot be reused, report the reason and ask for
  confirmation before starting a separate instance.

# Architecture & Security Contracts

- `ARCHITECTURE.md` and `SECURITY.md` are the authoritative contracts for the
  intent-led campaign system. They are **not auto-loaded** — read them before
  any work on the domain (T-02 and later) and treat their rules as binding.
- `ARCHITECTURE.md` fixes the entity/ownership map, the LLM-vs-deterministic
  boundary, angle-field routing, the model response schema, and the
  validation/fail-closed policy.
- `SECURITY.md` fixes the threat model and the **Required** controls (org-scoped
  policies, consent/suppression enforcement, provider-key handling, rate limits).
- If a change would contradict either document, update the document first and
  flag the change rather than silently diverging.
- `ARCHITECTURE.md` is finalised for T-01 (no provisional sections remain); treat all sections as binding when implementing T-02 and later.
- **Page routing contract:** Before creating or changing a backoffice, landing, or public-facing
  page, read `ARCHITECTURE.md` section 14. Every browser-facing URL must have a Laravel web route and
  an appropriate Blade shell with the correct middleware. Keep simple/static pages in Laravel +
  Blade; use Vue Router only for an extensive workflow inside that shell. Never create a
  Vue-only URL, client-side deep link, or router fallback that Laravel cannot capture on direct
  navigation and refresh. Reconstruct route state from URL parameters and authorized API data,
  and keep JSON endpoints in `routes/api.php` rather than serving views from API routes.

# Issue Tracking

- See [docs/KANBAN.md](docs/KANBAN.md) for the current Kanban board and ticket workflow.
- Use the kanban skills to manipulate the KANBAN board. Also adhere the worktree approach when modifying the tickets, so it can be parallel against current work.
- User may occasionally refer the ticket number in the prompt or the task description. If so, you eagerly check the ticket spec.

# Plan Artifacts

- Store plans that are written as files, including Markdown, text, JSON, or
  other metadata formats, under `docs/plans/`.
- For a substantial plan with supporting structure or multiple artifacts,
  create a dedicated subdirectory within `docs/plans/`.

# Testing Scope

- Limit unit tests primarily to self-contained, pure-function utilities with
  minimal dependence on Laravel or Vue framework behavior.
- Unit tests means unit. Do not test the framework. Do not create a test that imposes a series of flows.
- Avoid adding unit tests for framework-coupled Laravel or Vue files unless the
  behavior cannot be covered effectively at another test level.
- For frontend work, skip Vue "page" and "container" components unit testing for now
  as those components may tightly couple with each other and prone to break periodically.
  Simple Vue components that can be mounted without too requiring lots of mocks/stubs can be unit tested.
- Do not eagerly write feature tests. Ask the user if they want to. Also ask what range of scope they want to cover.
