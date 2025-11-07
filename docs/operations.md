# Operations Runbook

## Runtime services

- **Queue workers**: `php artisan horizon` supervises redis-backed queues (`default`, `exports`, `notifications`, `uploads`). Autoscale worker counts based on queue length.
- **Scheduler**: `php artisan schedule:work` triggers recurring jobs for low-stock checks, digest emails, export cleanup, and audit log pruning.
- **Broadcasting**: Soketi running with TLS termination behind nginx. Sanctum tokens mapped to private channels `private-user.{id}` and sector feeds `presence-sector.{sectorId}`.

## Monitoring & alerting

- Laravel Horizon metrics + notifications on failed jobs.
- Sentry DSN configured for server and client (via Inertia plugin).
- Health endpoint `/healthz` returns service status, DB migrations, Redis ping, MinIO bucket accessibility.
- Prometheus exporter (e.g., Laravel Prometheus exporter package) recommended for queue depth, request latency, cache hit ratio.

## Backup strategy

- Nightly logical dumps of `core_db` and `punchlist` stored in encrypted MinIO bucket `abrm-backups` with 30-day retention.
- MinIO versioning enabled on uploads bucket for point-in-time recovery.
- Application configuration stored in Git + secrets manager (e.g., AWS Secrets Manager) outside repository.

## Deployment workflow

1. Push to `main` triggers CI pipeline (GitHub Actions).
2. CI runs:
   - `composer install --no-dev`
   - `npm ci && npm run build`
   - `php artisan test`
   - `php artisan lint` (Larastan + Pint)
3. Artifact built into OCI image, pushed to registry.
4. CD pipeline runs migrations, flips Horizon supervisor, and reloads nginx via zero-downtime deployment (e.g., Laravel Vapor or Kubernetes).

## Incident response

- On failed queue jobs: inspect Horizon dashboard, replay after fixing root cause.
- On MinIO outage: service worker queues uploads offline; manual replay once storage available.
- On Redis failure: fallback to database queue driver temporarily and disable realtime features until restored.

## Security maintenance

- Rotate VAPID keys annually; store rotated keys in secrets manager and update `.env`.
- Enforce quarterly password resets for privileged roles via scheduled job.
- Audit log tampering detection: nightly job verifies hash chain across `audit_logs` table.
