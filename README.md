# ABRM-Managment (Vanilla PHP Edition)

This repository contains a production-focused baseline for ABRM-Managment implemented with **pure PHP 8.3, PDO, and vanilla JS/CSS**—no external frameworks or package managers required.

## Features

- Custom front controller, router, and middleware pipeline
- Secure authentication with Argon2id passwords and hardened session controls (no OTP required)
- RBAC helpers backed by the `core_db` role & permission tables
- Dual-database access: `core_db` for identity/audit, `punchlist` for operational data
- Futuristic glass-and-neon UI with modals, context menus, and dark-mode toggle
- Inventory, Tasks, Notes, Users, and Dashboard screens
- JSON:API-style responses and health check endpoint
- MinIO presigned upload support via handcrafted SigV4 implementation
- Offline-ready PWA shell with service worker caching

## Requirements

- PHP 8.3 with extensions: `pdo_mysql`, `openssl`, `zip`, `gd`
- MySQL-compatible databases seeded from `123456.sql`
- MinIO (or S3-compatible) object storage
- Web server configured to serve the `public/` directory as document root

## Setup

1. Copy `.env.example` to `.env` (or export the variables in your environment).
2. Update credentials for `core_db`, `punchlist`, and MinIO.
3. Import `123456.sql` into both databases (core data + operational data) to mirror production schema.
4. Configure your web server (Nginx/Apache/Caddy) to point to `public/index.php`.
5. Ensure HTTPS is enabled to respect secure cookies and HSTS headers.
6. (Optional) Provide custom PWA icons by replacing the inline data URIs in `public/manifest.webmanifest` if you want branded artwork.

## Development

- The application auto-loads classes via `vendor_stub.php`.
- `app/Bootstrap.php` wires configuration, sessions, headers, CSRF, and rate limiting.
- Routes are registered in `public/index.php`.
- Views live under `views/` and share the glassmorphism layout in `views/layout.php`.
- Assets are served directly from `public/assets/`.

## Testing Health

Visit `/healthz` to verify:

```json
{
  "app": "ok",
  "db": {"core": true, "punchlist": true},
  "minio": true
}
```

## MinIO Presigned Uploads

Use `POST /photos/presign` with `filename` and `content_type` (and CSRF token) to receive a presigned PUT URL. Requires the `photos.upload` permission.

## Notes

- Additional modules (exports, movement workflows, background sync queue processing) are scaffolded for future enhancement.
- Replace the bundled placeholder QR generator (`lib/phpqrcode.php`) with a full implementation if higher fidelity codes are required.
