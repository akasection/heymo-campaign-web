# Heymo! Campaign Webapp

Campaign management web application for Heymo!.

## Stack

- **Backend**: Laravel 9 on PHP 8.1 (installed via mise + vfox-php)
- **Frontend**: Vue 3 + Vite (TypeScript, Tailwind CSS v4), built with pnpm
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

### 2. Start the data services (PostgreSQL 18 + Valkey)

```bash
docker compose -f _dev/docker-compose.yaml up -d
```

This starts:

| Service    | Image                    | Host port | Purpose                                   |
| ---------- | ------------------------ | --------- | ----------------------------------------- |
| `postgres` | `postgres:18-alpine`     | `5434`    | PostgreSQL 18 database                    |
| `valkey`   | `valkey/valkey:8-alpine` | `6380`    | Cache, queue, sessions (Redis-compatible) |

> The host ports are deliberately **5434 / 6380** so they don't clash with any
> native PostgreSQL or Valkey/Redis already running on 5432/5433/6379. You can
> override them with `POSTGRES_PORT` / `VALKEY_PORT` env vars.
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

### 5. Run migrations

```bash
php artisan migrate
```

## First Run

```bash
# Terminal 1 — frontend dev server (Vite, hot reload)
pnpm dev

# Terminal 2 — backend server
php artisan serve
# or: php artisan serve --port=8000
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
