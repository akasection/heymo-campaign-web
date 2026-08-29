# Heymo! Campaign Webapp

Campaign management web application for Heymo!.

## Stack

- **Backend**: Laravel 9 on PHP 8.1 (installed via mise + vfox-php)
- **Frontend**: Vue 3 + Vite (TypeScript, Tailwind CSS v4 + daisyUI 5), built with pnpm
- **Database**: PostgreSQL 18 (Docker — `_dev/docker-compose.yaml`)
- **Cache / Queue / Session**: Valkey 8 (Docker — `_dev/docker-compose.yaml`), accessed through Laravel's Redis layer (predis client)

## Prerequisites

- [mise](https://mise.jdx.dev/) with the `version-fox/vfox-php` plugin — installs PHP 8.1 **and Composer** (bundled into the PHP prefix, auto-activated on PATH by mise; no separate Composer install).
- [Node.js](https://nodejs.org/) 24 (pinned in `mise.toml`) — **pnpm** is provided by Corepack (auto-activated from the `packageManager` field in `package.json`; no separate pnpm install).
- [Docker Engine](https://docs.docker.com/engine/install/) + Docker Compose plugin.

## Installation

### 1. System dependencies (PHP build libraries)

Pick the script for your OS — these install the libraries needed to compile PHP and its extensions (xml, openssl, intl, gd, sqlite, **pdo_pgsql**, etc.):

```bash
# Ubuntu / Debian
sudo bash _dev/deps-ubuntu.sh

# macOS (Homebrew)
bash _dev/deps-mac.sh

# Windows (PowerShell, as Administrator)
powershell -ExecutionPolicy Bypass -File _dev/deps-win.ps1
```

<details>
<summary><strong>PostgreSQL driver</strong> — enable <code>pdo_pgsql</code> (only needed if the app can't connect to Postgres)</summary>

`vfox-php` does **not** compile `pdo_pgsql` by default, and the mise built-in `php` plugin is a **different** build than the pinned `vfox:version-fox/vfox-php` — so `mise install php@8.1` builds the wrong PHP and will fail (missing OpenSSL). To enable `pdo_pgsql` without rebuilding the working PHP, build it as a shared extension against the installed PHP (needs `libpq-dev`, already installed by the deps script):

```bash
PHP_PREFIX=~/.local/share/mise/installs/vfox-version-fox-vfox-php/8.1.34
PHP_SRC=~/.local/share/mise/downloads/vfox-version-fox-vfox-php-8.1.34/php-8.1.34.tar.gz
EXT_DIR="$PHP_PREFIX/lib/php/extensions/no-debug-non-zts-20210902"

cd /tmp && rm -rf pdo_pgsql_build && mkdir pdo_pgsql_build && cd pdo_pgsql_build
tar xzf "$PHP_SRC" && cd php-8.1.34/ext/pdo_pgsql
phpize
./configure --with-pdo-pgsql=/usr --with-php-config="$PHP_PREFIX/bin/php-config"
make -j"$(nproc)"
cp modules/pdo_pgsql.so "$EXT_DIR/"
echo 'extension=pdo_pgsql.so' > "$PHP_PREFIX/conf.d/pdo_pgsql.ini"
php -m | grep pdo_pgsql   # should list pdo_pgsql
```

> **Prefer a full PHP rebuild instead?** Note that setting `PHP_CONFIGURE_OPTIONS` **replaces** the plugin's default Linux configure flags (`--with-openssl --with-curl --with-zlib --with-readline --with-gettext --with-xsl --with-gmp --with-sodium`), so you must include them or the build loses OpenSSL/curl and can't fetch Composer. The correct rebuild uses the **vfox** tool name:
>
> ```bash
> PHP_CONFIGURE_OPTIONS='--with-openssl --with-curl --with-zlib --with-readline --with-gettext --with-xsl --with-gmp --with-sodium --with-pdo-pgsql=/usr' \
>   mise install vfox:version-fox/vfox-php@8.1
> ```
>
> On macOS the plugin adds Homebrew include paths the same way, so append `--with-pdo-pgsql` there too instead of relying on `PHP_CONFIGURE_OPTIONS` alone.

</details>

### 2. Start the data and mail services (PostgreSQL 18 + Valkey + Mailpit)

```bash
docker compose -f _dev/docker-compose.yaml up -d
```

This starts:

| Service    | Image                    | Host port       | Purpose                                   |
| ---------- | ------------------------ | --------------- | ----------------------------------------- |
| `postgres` | `postgres:18-alpine`     | `5434`          | PostgreSQL 18 database                    |
| `valkey`   | `valkey/valkey:8-alpine` | `6380`          | Cache, queue, sessions (Redis-compatible) |
| `mailpit`  | `axllent/mailpit:latest` | `1025` / `8025` | Development SMTP / captured inbox         |

> The host ports are deliberately **5434 / 6380 / 1025 / 8025** so they don't
> clash with native PostgreSQL, Valkey/Redis, or mail tooling. You can override
> them with `POSTGRES_PORT`, `VALKEY_PORT`, `MAILPIT_SMTP_PORT`, and
> `MAILPIT_WEB_PORT` env vars.
>
> Both services use named volumes (`postgres-data`, `valkey-data`) so data
> survives container restarts.

### 3. Install PHP and JS dependencies

```bash
composer install
pnpm install
```

### 4. Environment configuration

```bash
cp .env.example .env
php artisan key:generate
```

The `.env.example` is already wired for this stack:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5434
DB_DATABASE=heymo
DB_USERNAME=heymo
DB_PASSWORD=secret

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6380
```

### 5. Run migrations and seed the reviewer accounts

```bash
php artisan migrate --seed
php artisan storage:link
```

The second command exposes uploaded brand logos through `public/storage` when using the local
filesystem disk. It is safe to run again if the link already exists.

## Authentication

The `/admin` dashboard uses Laravel's `web` session guard. Sign-in is passwordless: open
<http://localhost:8000/login>, enter one of the provisioned email addresses below, and enter
the six-digit code from Mailpit. The captured email is available at
<http://localhost:8025>. Codes are formatted as `xxx-yyy`, expire after ten minutes, are
single-use, and are rate-limited.

### Seeded reviewer accounts

| Email                | Name          | Organisation    | Role   |
| -------------------- | ------------- | --------------- | ------ |
| `admin@heymo.test`   | Mara Ellis    | Heymo Org       | Admin  |
| `ops@lexical.test`   | Noah Williams | Lexical Labs    | Member |
| `review@eje.test`    | Samira Patel  | EJE Science     | Member |
| `team@xohealth.test` | Jonah Brooks  | XO Health Group | Member |

There are no seeded passwords. Every login request creates a new code and sends a custom HTML
email through Mailpit. With `OTP_LOG_CODES=true` and `APP_ENV=local`, the formatted code is also
written to stderr, so it is visible in the terminal running `php artisan serve` or `pnpm
dev:all`. Keep `OTP_LOG_CODES=false` outside local development.

Administrators receive the `backoffice.manage` capability. Members can open the dashboard but
do not receive management controls. The frontend keeps its short-lived JWT in
`sessionStorage`; the server also maintains the browser session. `GET /api/user` requires a
Bearer JWT, `POST /api/auth/token` renews one for an authenticated web session, and
`POST /api/auth/logout` revokes the active JWT and ends the browser session.

### Brand management

Administrators can open **Brands** from the backoffice navigation. Each profile stores its
organization-owned identity separately from the static landing pages: primary and secondary
colors, a logo, curated heading/body fonts, and four named writing preferences (tone, flow,
tense, and reading level). Preferred and avoided terms are normalized before they are persisted.

The backoffice uses daisyUI for shared controls such as buttons, inputs, cards, tabs, badges,
alerts, tables, progress indicators, and loading states. Heymo color tokens and the bespoke
hexagon/data-visual elements remain project-owned. This keeps the component language reusable
without flattening the existing visual identity.

The saved prompt blueprint is derived from those structured settings and carries a version. It
is a preview of the writing instructions that future campaign generation will receive, not an
editable free-form prompt and not a source of health claims. The long-form guidance for each
voice preset lives in server-only Markdown files under
`resources/llm/config/brands/<property>-<level>.md`; `config/brands.php` keeps only the preset
metadata and file mapping. Required compliance and sign-off blocks are added in the later
copy-controls slice before LLM generation is enabled. A relational approved-evidence registry,
such as `ApprovedClaim`, is optional enrichment rather than a required product entity.

The seeded administrator can compare two contrasting profiles under `heymo-org`:

| Brand           | Palette               | Writing direction                     |
| --------------- | --------------------- | ------------------------------------- |
| Lexical Labs    | `#2E5BFF` + `#00B8A9` | Informal, narrative, relaxed, simpler |
| XO Health Group | `#163A4A` + `#7393A8` | Formal, descriptive, serious, complex |

For a clean local reviewer reset, run:

```bash
php artisan migrate:fresh --seed
```

This removes and recreates the configured development database.

### JWT settings

`JWT_ALGORITHM` is restricted to `HS256`. For local use, an empty `JWT_SECRET` falls back to
`APP_KEY`; set a separate long random `JWT_SECRET` in any shared or non-local environment.
`JWT_ISSUER`, `JWT_AUDIENCE`, and `JWT_TTL_MINUTES` control the token claims and lifetime.

## First Run

Start both the Vite dev server **and** the Laravel server in one command:

```bash
pnpm dev:all        # or: composer dev (same thing)
```

This runs Vite (hot reload) and `php artisan serve` together via `concurrently`, with color-coded `[vite]` / `[php]` output. Ctrl+C stops both.

Prefer separate terminals? Use them individually:

```bash
# Terminal 1 — frontend dev server (Vite, hot reload)
pnpm dev

# Terminal 2 — backend server
pnpm serve          # == php artisan serve
```

Then open <http://localhost:8000>.

If the database doesn't exist yet (first run after a fresh volume), create it:

```bash
docker exec -it heymo-postgres createdb -U heymo heymo
```

Useful commands:

```bash
# Shut down the data services (data is kept in the named volumes)
docker compose -f _dev/docker-compose.yaml down

# Wipe the volumes (destructive — removes all data)
docker compose -f _dev/docker-compose.yaml down -v

# Run the queue worker (required for QUEUE_CONNECTION=redis)
php artisan queue:work
```

## Contribute

### Developers

- PHP code style & linting:
  - `composer lint` — checks without modifying: TLint (Laravel best practices) then Pint `--test` (formatting).
  - `composer format` — auto-fixes formatting with [Laravel Pint](https://laravel.com/docs/9.x/pint) (`pint.json`, `laravel` preset).
  - TLint (`vendor/bin/tlint lint`) is a linter only; Pint owns formatting, so don't also run `tlint format` (two formatters would fight).
- JS/TS (Oxc — configs in `oxlint.config.ts` / `oxfmt.config.ts`):
  - `pnpm lint` — oxlint.
  - `pnpm format` — oxfmt (formats in place).
  - `pnpm format:check` — oxfmt `--check` (CI-safe, modifies nothing).
- Tests: PHPUnit (`vendor/bin/phpunit`).
- Keep `.env` out of version control; update `.env.example` when adding configuration.
- When changing the DB, always add a migration (`php artisan make:migration`) rather than editing existing ones.
- Keep [docs/KANBAN.md](docs/KANBAN.md) current as tickets move between columns. Periodically commit and merge those board-only updates directly to `main`; do not include them in an individual task's pull request.

### AI Agents

- Read `AGENTS.md` first before contributing.
- This project uses **PostgreSQL 18** (Docker, host port `5434`) and **Valkey** (Docker, host port `6380`) — both Redis and PHP Redis config map to Valkey; the Redis client is `predis` (pure PHP, no phpredis extension needed).
- The PHP runtime is managed by mise + vfox-php; `pdo_pgsql` is **not** compiled in by default and must be enabled at PHP build time (see Prerequisites/Installation).
- DB credentials in the compose file and `.env.example` default to user `heymo` / password `secret` / database `heymo`. Override via `POSTGRES_*` env vars and `.env` respectively.
- Frontend assets are built with Vite (`pnpm dev` / `pnpm build`); do not commit compiled assets unless required.
