# ABRM-Managment Architecture

## High-level overview

- **Backend:** Laravel 11 PHP application deployed behind an HTTPS reverse proxy (nginx). Utilizes Sanctum for SPA authentication and spatie/laravel-permission for RBAC. Redis backs cache, queues, and Horizon. Observability provided via Sentry and Laravel Telescope (local only).
- **Frontend:** Inertia + Vue 3 SPA compiled with Vite. TailwindCSS powers design tokens, with HeadlessUI components and Heroicons. Context menus delivered through VueUse + HeadlessUI Combobox patterns.
- **Datastores:** MySQL-compatible server hosting two schemas: `core_db` and `punchlist`. The application leverages Laravel's multi-database connections with query logging restricted to local environments.
- **Object storage:** MinIO with buckets `abrm-uploads` and `abrm-exports`. Files are uploaded via presigned POST URLs and accessed via signed GET URLs with expiration.
- **PWA:** Workbox generated service worker providing precaching for shell assets, runtime caching for API calls with stale-while-revalidate, Background Sync for notes/tasks, and Push API integration using VAPID keys.

## Module breakdown

### Users & Authentication
- Sanctum SPA tokens with personal access token management UI.
- Password policies enforced via custom `PasswordHistory` and `PasswordStrength` validators (zxcvbn score >= 3).
- Session-based login using Argon2id-verifiable passwords stored in `core_db`.
- Audit logging for sign-ins, token issuance, profile edits.

### Roles & Permissions
- Predefined roles: Super Admin, Admin, Manager, Staff, Viewer.
- Permissions follow `module.action` naming (e.g., `inventory.view`, `tasks.update`).
- Sector scoping uses pivot table `sector_user` with policy checks to limit dataset queries.

### Inventory
- Tables (punchlist): `inventory_items`, `inventory_categories`, `inventory_units`, `inventory_batches`, `inventory_movements`, `inventory_movement_items`.
- Movement attachments stored in MinIO. Transfers trigger PDF paperwork (dompdf) queued to `exports` queue.
- Scheduled job recalculates low-stock alerts nightly, caching results for dashboard KPIs.

### Tasks
- `building_tasks` and `room_tasks` share polymorphic photos via `task_photos` referencing MinIO object keys.
- Export jobs (PDF/CSV/XLSX) executed asynchronously with progress stored in `exports` table and served via signed URLs.
- QR code exports produced with `simple-qrcode`, linking to upload portal for field staff photo submissions.

### Notes
- Rich text sanitized with HTML Purifier. Offline submissions stored in IndexedDB on the client; service worker replays queued mutations via Background Sync.
- Tags stored in `note_tags` and `tags` tables (punchlist). Attachments limited to 3 per note.
- Full audit trail recorded via `core_db.audit_logs`.

### Notifications
- In-app notification center with categories. Web push managed via `web_push_subscriptions` (core_db) referencing VAPID keys.
- Digest emails scheduled nightly, batched by user preference.
- Real-time events broadcast via Soketi (Pusher protocol) on Redis queue.

### Settings & Exports
- Settings stored in `core_db.settings` table with encrypted values where necessary.
- Export status updates emitted through SSE-compatible endpoint for progress UI.

## Infrastructure components

| Service | Purpose |
| --- | --- |
| nginx | Serves Laravel app, enforces HSTS, CSP, gzip/brotli. |
| php-fpm | Runs Laravel with OPCache, Horizon supervisor, and queue workers. |
| mysql | Dual schema database hosting. |
| redis | Cache/queue store. |
| soketi | WebSocket push. |
| minio | Object storage for uploads and exports. |

## Security controls

- CSRF protection on web routes, Sanctum token guard on API.
- HTTPS enforced, cookies flagged `Secure`, `SameSite=Lax`.
- Content Security Policy prohibits inline scripts; Vite injects nonces.
- File uploads validated for mime type and size; antivirus scanning hook reserved via queue.
- Rate limiting layered by user + IP via custom `RateLimiter` definitions.

## Deployment pipeline

1. Build step installs Composer & NPM dependencies, runs `npm run build` and `php artisan test`.
2. Artifacts (public/build, bootstrap/cache, vendor) packaged into container image.
3. Database migrations executed with `php artisan migrate --database=core_db` and `--database=punchlist`.
4. Horizon & queue workers deployed via Supervisor manifests.
5. Smoke tests hit `/healthz` endpoint verifying DB, Redis, MinIO connectivity.
