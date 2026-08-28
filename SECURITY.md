# Security — Intent-Led Campaign System

> Status: **Draft v1** — companion to `ARCHITECTURE.md`. This documents the security
> posture, the threat model, and the controls that must hold for a regulated DTC
> health-marketing system. Sections marked **Required** are implementation mandates for the
> tickets that follow.

---

## 1. Scope & threat model

The system processes **sensitive personal data** in **regulated territory** (US DTC blood
testing): demographics (age, sex), a stated health concern, email consent, generated
marketing email, and provider API keys.

**Assets to protect**

| Asset                                  | Sensitivity                    |
| -------------------------------------- | ------------------------------ |
| Visitor PII (email, age, sex)          | High                           |
| Stated health concern / intent answers | High                           |
| Consent & suppression records          | High (legal)                   |
| Approved claims & brand constraints    | Medium (business + compliance) |
| Generated email bodies                 | High                           |
| Admin accounts & sessions              | High                           |
| LLM provider keys                      | Critical                       |

**Threat actors**

1. **Unauthenticated web users** — probing capture/admin endpoints, rate-limit abuse.
2. **Malicious visitors** — prompt injection through free-text quiz fields.
3. **Compromised admin session** — leaked JWT/cookie, escalated access.
4. **Third-party LLM provider** — data exposure of prompt payloads.

---

## 2. Authentication (implemented)

### Passwordless OTP login (`PasswordlessLoginService`, `LoginChallenge`)

- Six-digit codes, **hashed at rest** (`Hash::make`); plaintext never persisted.
- Single-use (`consumed_at`), expiry (`expires_minutes`, default 10), attempt cap
  (`max_attempts`, default 5) with lockout, and **invalidation of prior challenges on
  resend**.
- Per-email **and** per-IP rate limits (`max_requests_per_hour`, default 5) with a resend
  cooldown (default 60s).
- **Neutral responses** on unknown email / rate limit — no account enumeration.
- Code delivery is logged only under `OTP_LOG_CODES=true` **and** `APP_ENV=local`; the
  email is the primary audit surface.

### Session guard (admin dashboard)

- `web` guard (`config/auth.php`) drives the Blade admin area; `routes/web.php` applies
  `auth` middleware to the dashboard and `guest` to login.

### JWT (API)

- HS256-signed JWT issued via `JwtService`; claims include `sub`, `sid`, `jti`, `role`,
  `org_id`, `iss`, `aud`, `iat`, `nbf`, `exp`.
- Each token is backed by a server-side `AuthSession` row, so tokens are **revocable**
  (`revoked_at`) and have a bounded lifetime (`ttl_minutes`, default 120).
- `AuthenticateJwt` re-validates the session is active **and** that `org_id` + `role` in
  the claims still match the user on every request.

> **Hardening (Required):** `config/jwt.php` falls back to `APP_KEY` when `JWT_SECRET` is
> unset. Set a **dedicated `JWT_SECRET`** in production; do not ship with the fallback.

---

## 3. Authorization (implemented + to extend)

- Roles are `admin` / `member` (`User::ROLE_*`); capabilities are derived in
  `User::capabilities()` (`backoffice.view` for members, `backoffice.manage` +
  `organizations.view` for admins).
- Admin operations must remain behind the session guard; the public capture API must never
  expose admin mutation.

**Required for the domain (T-02 / T-05 / T-06):**

- Register Eloquent **policies** in `AuthServiceProvider` for `Brand`, `ApprovedClaim`,
  `Angle`, `Visitor`, and `Campaign`.
- Enforce **organization scoping** on every query: a user may only read/mutate entities
  owned by their own `organization_id`. Cross-org reads are a release blocker.

---

## 4. Secrets & configuration management

- All secrets (`APP_KEY`, `JWT_SECRET`, mail credentials, LLM provider keys) are
  **environment variables**, never source-code constants.
- LLM provider key(s) introduced in T-04 must be: env-only, server-side only, never
  serialized to the browser, never logged, and surfaced only as **non-secret** errors
  (e.g. "missing key" without the key).
- `Handler::dontFlash` already excludes `password`, `current_password`,
  `password_confirmation`; extend it to any new secret-bearing fields.

---

## 5. Data protection & PII

- **Minimisation** (PRD): collect only fields that change the experience. No clinical data
  beyond the stated concern.
- OTP codes are hashed; raw codes are never persisted.
- Consent is stored **separately** from the profile, with source, timestamp,
  policy/version, and the exact submitted address (see `ARCHITECTURE.md` §3).
- **Required (T-08):** define retention/deletion for withdrawn consent and suppressed
  visitors; suppression must be durable and cannot be bypassed by queued jobs.

---

## 6. LLM & prompt-injection security

The model receives structured prompt payloads assembled from visitor free-text
(`sub_interest`, `trigger`, `concern`), angle copy, brand constraints, and approved claims.

- **Treat visitor free-text as untrusted data**, never as instructions: delimit it clearly
  in the prompt and instruct the model that it is input, not directives.
- **The model never receives secrets**, admin-only data, or other visitors' data.
- **The model never triggers a send** — code composes, validates, and queues the email.
  Model output is inert until it passes the deterministic validator (fail-closed).
- **The validator is the backstop** for prompt-injection payloads that smuggle claims,
  urgency, or diagnosis language into the output (see `ARCHITECTURE.md` §8).
- Provider keys stay server-side; raw responses are persisted to `GenerationAttempt` for
  audit but must not include credentials.

---

## 7. Rate limiting & abuse controls

- **Implemented:** OTP per-email + per-IP hourly limits, resend cooldown, attempt lockout;
  `throttle:api` on the `api` middleware group.
- **Required (T-08):** rate-limit the public capture/submission endpoint (per IP and per
  email) to prevent spam submissions and mass campaign generation.
- **Required (T-10):** generation/send jobs must honour provider rate limits and retry
  safely without duplicate dispatch (idempotency keys).

---

## 8. Consent, suppression & audit integrity

- **No generation and no send without active consent** — consent is required before
  generation and **re-checked immediately before each send** (T-09/T-10).
- **Suppression blocks queued and future delivery**; a withdrawn or converted visitor is
  excluded, and this cannot be bypassed by a queued job.
- All audit records (`ConsentRecord`, `GenerationAttempt`, `DeliveryEvent`, `Suppression`)
  are **append-only and immutable**. A reviewer must be able to reconstruct the full chain
  (T-11), and audit data must never expose secrets or API keys.

---

## 9. Transport & session hardening

- Web routes sit behind `EncryptCookies` + `VerifyCsrfToken` + session start; the auth API
  endpoints run under the `web` middleware group, so they inherit CSRF and session
  protection.
- CORS is handled globally (`HandleCors`); restrict allowed origins to the known frontend.
- **Required for production:** HTTPS everywhere, `SESSION_SECURE_COOKIE=true`,
  `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax` (or `strict` where feasible), and
  `SESSION_DOMAIN` locked to the app domain.

---

## 10. Mail & delivery

- Local delivery uses Mailpit; the SMTP host is env-configured and no credentials are
  committed.
- OTP mail is an audit surface, not a secret store; the code is never embedded in logs or
  responses outside the guarded local flag.

---

## 11. Compliance checklist (PRD guardrails → control)

| Guardrail                                       | Security control                                                                                 |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| No unapproved health claims                     | Claims are DB records; model selects IDs only; deterministic validator rejects free-text claims. |
| No clinical inference from demographics         | Presentation profile computed in code; demographics never passed as clinical basis.              |
| No false urgency / scarcity / hidden conditions | Deterministic offer/next-step; validator scans model prose.                                      |
| Consent & suppression enforced                  | Consent gate + pre-send re-check + durable suppression.                                          |
| Subject lines accurate                          | Subject validated against body before send.                                                      |

---

## 12. Open items / hardening backlog

1. Set a dedicated `JWT_SECRET` (remove `APP_KEY` fallback) for production.
2. Register domain policies + org-scoping in `AuthServiceProvider` (T-02/T-05/T-06).
3. Rate-limit the capture endpoint (T-08).
4. Define PII retention/deletion for withdrawn consent (T-08).
5. Enforce provider-key handling: env-only, non-secret errors, no key in audit/logs (T-04).
6. Restrict CORS origins and lock session cookies for production.
