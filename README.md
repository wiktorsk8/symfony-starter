# Symfony URL Shortener Starter

A developer-friendly Symfony 7.3 starter that demonstrates a clean, modular architecture for building a URL shortener service. It ships with Docker (FrankenPHP + Caddy) and PostgreSQL for an easy local development experience, leverages CQRS, a simple in-memory Command Bus via Symfony Messenger, and Doctrine ORM for persistence.

<p align="center">
  <a href="https://www.php.net/releases/8.3/en.php"><img alt="PHP" src="https://img.shields.io/badge/PHP-%E2%89%A5%208.3-777bb3?logo=php&logoColor=white"></a>
  <a href="https://symfony.com/"><img alt="Symfony" src="https://img.shields.io/badge/Symfony-7.3-000000?logo=symfony&logoColor=white"></a>
  <a href="https://www.postgresql.org/"><img alt="PostgreSQL" src="https://img.shields.io/badge/PostgreSQL-16-336791?logo=postgresql&logoColor=white"></a>
  <a href="https://frankenphp.dev/"><img alt="FrankenPHP" src="https://img.shields.io/badge/FrankenPHP-runtime-blue"></a>
  <a href="LICENSE"><img alt="License" src="https://img.shields.io/badge/license-MIT-green.svg"></a>
</p>

---

## Contents
- Overview
- Architecture at a Glance
- Tech Stack
- Quick Start (Docker)
- Configuration & Environment
- Database & Migrations
- Running the App
- API Reference (with curl)
- Postman Collection
- Local Dev (Without Docker)
- Project Structure
- Troubleshooting
- Deployment Notes
- License

---

## Overview
This starter is a compact yet production-minded reference for building HTTP APIs with Symfony 7.3.
- Create short links from long URLs.
- Resolve short slugs and redirect to the original URL.
- Query stored short links.

It focuses on separation of concerns using CQRS (Commands/Queries split), application services, domain-oriented modules, and infrastructure adapters.


## Architecture at a Glance

```
┌────────────────────────────────────────────────────────────────┐
│                       HTTP (Symfony)                           │
│  Routes/Controllers → Requests DTOs → Commands/Queries         │
└───────────────┬───────────────────────────────┬────────────────┘
                │                               │
          Commands (write)                 Queries (read)
                │                               │
       Symfony Messenger Bus                 Query Services
                │                               │
          Handlers (App)                      Doctrine ORM
                │                               │
         Domain/Entities                   Repositories/DTOs
                │                               │
               Cache                         PostgreSQL
```

### Key Flow: Create Short Link
```mermaid
sequenceDiagram
  autonumber
  participant C as Client
  participant API as Symfony Controller
  participant Bus as CommandBus (Messenger)
  participant H as CreateShortLinkHandler
  participant DB as PostgreSQL (Doctrine)
  participant Cache as Cache Listener

  C->>API: POST /api/short_links { url }
  API->>Bus: dispatch(CreateShortLink)
  Bus->>H: handle(command)
  H->>DB: persist ShortLink{id, slug, url}
  H-->>API: ok (id)
  API-->>C: 200 { id }
  H->>Cache: ShortLinkCreated event → cache entry
```

### Key Flow: Resolve Short Link
```mermaid
sequenceDiagram
  autonumber
  participant C as Client
  participant API as Symfony Controller
  participant Q as GetUrlQuery
  participant DB as PostgreSQL (Doctrine)

  C->>API: GET /{slug}
  API->>Q: execute(slug)
  Q->>DB: find URL by slug
  DB-->>Q: url | null
  Q-->>API: url | null
  API-->>C: 302 Redirect to url | 404
```


## Tech Stack
- Symfony 7.3 (Framework, Messenger, Cache)
- PHP ≥ 8.3 (FrankenPHP Runtime)
- Doctrine ORM 3 + Migrations + DBAL
- PostgreSQL 16
- Docker Compose for local dev


## Quick Start (Docker)
Prerequisites:
- Docker Desktop (or Engine) + Docker Compose

Steps:
1) Clone and configure environment
```
git clone <this-repo-url> symfony-starter
cd symfony-starter
cp .env.example .env  # if you maintain one; otherwise define the variables below in your shell
```

2) Provide database credentials via environment (examples)
```
# .env (or your shell / compose .env file)
DB_HOST=postgres
DB_PORT=5432
DB_NAME=app
DB_USER=app
DB_PASSWORD=!ChangeMe!
HTTP_PORT=8080
HTTPS_PORT=8443
HTTP3_PORT=8443
SERVER_NAME=localhost
```

3) Start services
```
docker compose up -d --build
```

4) Install PHP deps inside the container
```
docker compose exec php composer install
```

5) Run migrations
```
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

6) Open the app
- HTTP: http://localhost:${HTTP_PORT:-80}
- HTTPS: https://localhost:${HTTPS_PORT:-443}

Note: The project uses FrankenPHP + Caddy; the container exposes HTTP/HTTPS/HTTP3. For local API testing, HTTP is usually sufficient.


## Configuration & Environment
Relevant compose files:
- `compose.yaml` – base services (php/frankenphp, postgres)
- `compose.override.yaml` – dev overrides (bind mounts, Xdebug toggle)

Important env vars consumed by Docker/Symfony:
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` – PostgreSQL connection
- `SERVER_NAME`, `HTTP_PORT`, `HTTPS_PORT`, `HTTP3_PORT` – Caddy/FrankenPHP networking
- `APP_ENV` – Symfony environment (`dev` by default in override)
- `XDEBUG_MODE` – enable/disable Xdebug in dev image

Symfony Messenger is configured with the default bus `command.bus` and transaction middleware in `config/packages/messenger.yaml`.


## Database & Migrations
- ORM: Doctrine 3
- Migrations are stored under `migrations/`

Common commands:
```
# generate a new migration from entity changes
bin/console doctrine:migrations:diff

# run migrations
bin/console doctrine:migrations:migrate

# validate mapping
bin/console doctrine:schema:validate
```
When running in Docker, prefix with `docker compose exec php`.


## Running the App
- Dev (Docker):
```
docker compose up -d
```
- Tail logs:
```
docker compose logs -f php postgres
```
- Stop:
```
docker compose down
```


## API Reference
Base URL examples:
- Local (HTTP): `http://localhost:8080`
- Local (HTTPS): `https://localhost:8443`

### Create a short link
- Method: POST
- Path: `/api/short_links`
- Body (JSON):
```
{
  "url": "https://example.com/some/very/long/path?with=params"
}
```
- Success Response: `200 OK`
```
{
  "id": "<uuid>"
}
```
- Error Response: `500` with `{ "message": "..." }`

Curl:
```
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"url":"https://example.com"}' \
  http://localhost:8080/api/short_links
```

### Get short link info
- Method: GET
- Path: `/api/short_links/{shortLinkId}`
- Success Response: `200 OK`
```
{
  "id": "<uuid>",
  "slug": "<slug>",
  "url": "https://example.com"
}
```
- Not Found: `404` with `{ "message": "Not found" }`

Curl:
```
curl http://localhost:8080/api/short_links/<uuid>
```

### Redirect by slug
- Method: GET
- Path: `/{slug}`
- Success: `302` redirect to the original URL
- Not Found: `404` with `{ "message": "Not found" }`

Curl (follow redirects):
```
curl -I http://localhost:8080/<slug>
# or
curl -L http://localhost:8080/<slug>
```


## Postman Collection
A ready-to-use collection is provided:
- `postman/url_shortener.postman_collection.json`

Import it into Postman and adjust the host/port if needed.


## Local Dev (Without Docker)
Prerequisites:
- PHP ≥ 8.3 with extensions: `pdo_pgsql`, `intl`, `zip`, `opcache`, `apcu` (recommended)
- Composer
- PostgreSQL 16

Steps:
```
composer install
# copy and adjust env
echo "
APP_ENV=dev
DATABASE_URL=postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8
" > .env.local

# create database (if needed)
bin/console doctrine:database:create --if-not-exists

# run migrations
bin/console doctrine:migrations:migrate --no-interaction

# run PHP dev server (alternatives: Symfony CLI or Caddy)
php -S 127.0.0.1:8080 -t public
```
Open http://127.0.0.1:8080


## Project Structure
```
src/
├─ Kernel.php
├─ Shared/
│  ├─ Application/
│  │  └─ Command/          # Command, CommandHandler, CommandBus contracts
│  ├─ DTO/                 # Base DTO
│  └─ Infrastructure/
│     └─ Symfony/
│        └─ Messenger/     # CommandBus (Messenger adapter)
└─ ShortLink/
   ├─ Application/
   │  ├─ Command/          # CreateShortLink & Handler
   │  ├─ Queries/          # GetShortLink, GetUrlQuery, DTOs
   │  ├─ Events/           # ShortLinkCreated event
   │  └─ Exceptions/
   ├─ Infrastructure/
   │  ├─ Doctrine/         # Entity, Repository, Queries
   │  ├─ Cache/            # Cache repository & listener
   │  └─ Symfony/
   │     ├─ Controller/    # HTTP endpoints
   │     └─ Request/       # HTTP → Command mapping
   └─ ...
```
Design notes:
- CQRS: commands mutate state; queries read state and return DTOs.
- Symfony Messenger provides middleware like `doctrine_transaction` to wrap command handling in DB transactions (see `config/packages/messenger.yaml`).
- Caching is plugged via listeners to react to domain/application events.


## Troubleshooting
- Ports already in use
  - Change `HTTP_PORT`, `HTTPS_PORT` in your environment or stop conflicting services.
- Database connection errors
  - Ensure `DB_HOST/PORT/USER/PASSWORD/NAME` match the running `postgres` service.
  - From container: `docker compose exec postgres psql -U $DB_USER -d $DB_NAME -h localhost`.
- Composer memory issues
  - Try `COMPOSER_MEMORY_LIMIT=-1 composer install` inside the container.
- Migrations not found / schema issues
  - Validate with `bin/console doctrine:schema:validate`.
- Windows/WSL volume performance
  - The dev override mounts the whole project; consider excluding `vendor` or using WSL2.


## Deployment Notes
- Use the `frankenphp_prod` image target defined in `Dockerfile`.
- Build a production image:
```
docker build --target frankenphp_prod -t your-org/shortener:prod .
```
- Provide production-safe env vars and secrets (don’t commit them).
- Ensure DB migrations run during your deployment pipeline.


## License
This project is released under the MIT License. See [LICENSE](LICENSE).
