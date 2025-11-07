# ABRM-Managment

> **Status: Implementation blocked**
>
> This repository is intended to host a Laravel 11 + Inertia/Vue 3 progressive web application for the ABRM management platform. The current environment does not permit outbound HTTP requests, which prevents installing the Laravel framework and required Composer dependencies. The codebase therefore contains only the implementation notes and delivery plan prepared while offline. Once network access is restored, follow the steps below to bootstrap the full stack.

## Project overview

The application will provide sector-based task and inventory management with offline-capable notes, MinIO object storage integration, Sanctum-powered APIs, and a Workbox-driven progressive web app shell. Two database connections are required: `core_db` (authentication, roles, audit logs) and `punchlist` (operational data). MinIO buckets `abrm-uploads` and `abrm-exports` will store rich-media artifacts and generated documents.

## Local development bootstrap (requires outbound internet access)

1. **Install PHP 8.3+, Composer 2.6+, Node 20+, and Docker Desktop.**
2. **Install the Laravel skeleton** once networking is available:
   ```bash
   composer create-project laravel/laravel="^11.0" .
   ```
3. **Require project dependencies**:
   ```bash
   composer require laravel/sanctum spatie/laravel-permission predis/predis laravel/horizon barryvdh/laravel-dompdf simplesoftwareio/simple-qrcode league/flysystem-aws-s3-v3 guzzlehttp/guzzle laravel/pail
   npm install --save-dev laravel-vite-plugin @inertiajs/vue3 @headlessui/vue @heroicons/vue tailwindcss postcss autoprefixer @tailwindcss/forms @vueuse/core workbox-window workbox-background-sync workbox-broadcast-update workbox-precaching workbox-routing workbox-strategies workbox-expiration @vitejs/plugin-vue axios dayjs qs zxcvbn
   ```
4. **Publish Sanctum and permission configs**, set up Horizon, and scaffold Inertia with Tailwind.
5. **Apply the SQL schema** contained in `123456.sql` to both `core_db` and `punchlist` with the provided data.
6. **Copy `config.php` from the legacy application** into `config/legacy.php` (see `docs/legacy-config.md`).
7. **Configure environment variables** as documented below.
8. **Run the Docker stack** described in `docker-compose.yml` to launch MySQL (two schemas), Redis, and MinIO.
9. **Execute database migrations and seeders** (`php artisan migrate --database=core_db`, `php artisan migrate --database=punchlist`, `php artisan db:seed`).
10. **Run tests and compile assets**: `php artisan test`, `npm run build`, `npm run lint`.

## Environment variables

Create a `.env` file based on the template below:

```
APP_NAME=ABRM-Managment
APP_ENV=local
APP_DEBUG=false
APP_URL=https://abrm.localhost
FRONTEND_URLS=https://abrm.localhost,https://staging.abrm.local
ASSET_URL=${APP_URL}

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_CORE_HOST=mysql
DB_CORE_PORT=3306
DB_CORE_DATABASE=core_db
DB_CORE_USERNAME=abrm
DB_CORE_PASSWORD=secret

DB_PUNCHLIST_HOST=mysql
DB_PUNCHLIST_PORT=3306
DB_PUNCHLIST_DATABASE=punchlist
DB_PUNCHLIST_USERNAME=abrm
DB_PUNCHLIST_PASSWORD=secret

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

BROADCAST_DRIVER=pusher
PUSHER_APP_ID=local
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_HOST=soketi
PUSHER_PORT=6001
PUSHER_SCHEME=http

FILESYSTEM_DISK=minio
MINIO_ENDPOINT=http://minio:9000
MINIO_USE_PATH_STYLE=true
MINIO_KEY=abrm
MINIO_SECRET=abrmsecret
MINIO_REGION=us-east-1
MINIO_BUCKET_UPLOADS=abrm-uploads
MINIO_BUCKET_EXPORTS=abrm-exports

SANCTUM_STATEFUL_DOMAINS=abrm.localhost
SESSION_DOMAIN=.abrm.localhost

PUSH_VAPID_PUBLIC=
PUSH_VAPID_PRIVATE=
MAIL_MAILER=log

HORIZON_PREFIX=abrm
```

## Planned directories & modules

| Module | Description |
| --- | --- |
| `app/Domain/Users` | User aggregates, profile updates, 2FA + password history services. |
| `app/Domain/Inventory` | Inventory items, batches, movement transactions, valuation jobs, export builders. |
| `app/Domain/Tasks` | Building and room task workflows, photo upload orchestration, QR export generation. |
| `app/Domain/Notes` | Rich-text notes, attachments, background sync queue reconciliation. |
| `app/Domain/Sectors` | Sector management, scoped dashboards, access control policies. |
| `app/Domain/Notifications` | Web push subscription handling, preference center, digest scheduling. |
| `app/Support/Audit` | Audit logging utilities writing to `core_db` with contextual metadata. |

The `resources/js` tree will provide Vue 3 single-file components for dashboards, entity CRUD modals, background sync workers, and the Workbox-powered service worker. TailwindCSS will be configured via `tailwind.config.js` with custom themes for light/dark modes.

## Offline prep work

Although the framework cannot be installed yet, the following preparation steps are complete:

- A detailed infrastructure and deployment plan is recorded in `docs/architecture.md`.
- Legacy configuration mapping guidance lives in `docs/legacy-config.md`.
- Queue, caching, and monitoring strategy outlines are provided in `docs/operations.md`.

Once network access is restored, continue with the bootstrap checklist above to deliver the production-ready implementation.
