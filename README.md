# FoodHub Advanced

FoodHub is an independently implemented Laravel + Bootstrap + MySQL + Flutter food-commerce platform. It is designed around the public feature category of modern food-delivery platforms but does not copy proprietary eFood source code, branding, assets or exact UI.

## Technology

- Laravel 12
- PHP 8.2+
- MySQL 8 / MariaDB compatible
- Laravel Sanctum
- Bootstrap 5.3
- Flutter/Dart mobile application
- REST API `/api/v1`
- XAMPP-compatible local deployment

## Core workflow

Customer -> catalog -> branch -> cart -> quote -> checkout -> order -> kitchen -> ready -> rider assignment -> rider pickup -> live location -> delivered -> review.

## Demo accounts

All demo accounts use password `password`:

- `admin@foodhub.test`
- `customer@foodhub.test`
- `rider@foodhub.test`
- `kitchen@foodhub.test`
- `manager@foodhub.test`
- `waiter@foodhub.test`

## Install on XAMPP

1. Install PHP 8.2+ and Composer.
2. Start MySQL in XAMPP.
3. Create database `foodhub` in phpMyAdmin or run `database/create_database.sql`.
4. Copy `backend/.env.example` to `backend/.env`.
5. Set MySQL credentials in `.env`.
6. Run `scripts/setup-xampp.bat` from Windows, or manually. The setup script uses non-destructive migrations and does not automatically reseed an existing database. For a new/demo database, run `php artisan db:seed --force` once after migrations. Use `migrate:fresh --seed` only when you intentionally want to destroy and recreate the database:

```bash
cd backend
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

For an existing FoodHub database, use `php artisan migrate` rather than `migrate:fresh`; `migrate:fresh` intentionally deletes the existing schema and data.

Then open:

- `http://127.0.0.1:8000/` customer landing page
- `http://127.0.0.1:8000/app` browser customer portal
- `http://127.0.0.1:8000/dashboard` operations dashboard
- `http://127.0.0.1:8000/admin` admin control center
- `http://127.0.0.1:8000/kitchen` kitchen display
- `http://127.0.0.1:8000/rider` rider console
- `http://127.0.0.1:8000/api/v1/health` API health check


### Composer dependency bootstrap (important)

This source package intentionally does **not** contain a fabricated `composer.lock`. The lock file must be generated from the exact `composer.json` dependency graph by Composer. On the first successful dependency resolution, Composer writes `composer.lock`; subsequent installs use that lock file for reproducible versions.

If `composer.lock` is missing, use:

```powershell
cd backend
composer validate --no-check-lock
composer update --no-install --prefer-dist --no-interaction
composer install --prefer-dist --no-interaction
```

Or run the Windows helper:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/bootstrap-composer.ps1
```

If Composer reports a timeout connecting to `repo.packagist.org`, this is a network/DNS/firewall/proxy problem rather than a Laravel application error. The dependency resolver cannot create a valid lock file until Composer can reach its package repository or a configured mirror. Do not create a hand-written or empty `composer.lock`.

Once the first resolution succeeds, keep `backend/composer.lock` with the project and use `composer install` for later setups.

## Flutter

The Flutter source is in `flutter_app/`. Because Flutter generates platform folders according to the installed Flutter SDK, run:

```bash
cd flutter_app
flutter create --platforms=android .
flutter pub get
flutter run --dart-define=FOODHUB_API=http://10.0.2.2:8000/api/v1
```

The included `scripts/setup-flutter.bat` automates those steps.

## What is implemented

- Customer authentication
- Sanctum tokens
- Catalog and search
- Branches
- Categories
- Product options
- Branch pricing/availability
- Customer addresses
- Cart/checkout through API and browser portal
- Delivery/pickup/dine-in order types
- Scheduled order field
- Coupon validation
- Tax and delivery pricing
- Payment records
- Staff order workflow
- Rider assignment
- Rider acceptance/pickup/delivery
- Rider location updates
- Reviews
- Branch inventory records
- Dine-in tables and QR tokens
- Admin dashboard API
- Admin catalog/branch/user/inventory/table/coupon endpoints
- Bootstrap responsive web UI
- Flutter customer application
- Automated end-to-end order test

## Integrations requiring credentials

Production GCash/card/PayPal processing, Firebase push notifications, Google Maps, SMS/email providers and real WebSocket broadcasting require provider credentials. The data model and service boundaries are prepared so these can be enabled without rewriting the order engine.

See `docs/TESTING.md`, `docs/ARCHITECTURE.md`, `docs/FEATURE_MATRIX.md`, and `docs/PHASE2_CHANGELOG.md`.

## Dashboard authentication fix

The operations dashboard uses Laravel Sanctum through the `/api/v1/auth/login` endpoint. The dashboard page itself is intentionally not protected by Laravel's session `auth` middleware because there is no separate web `/login` route. Staff authorization is performed by the API `auth:sanctum` + `staff` middleware chain.

The current build includes:
- `app/Http/Controllers/Controller.php` base controller
- JSON 401/403 responses from `StaffMiddleware`
- dashboard token storage using the dedicated `localStorage` key `fh_staff_token`
- browser customer token storage using the dedicated `localStorage` key `fh_customer_token`
- automatic hiding/showing of the login panel
- dashboard sign-out
- explicit handling for expired sessions and staff authorization failures

## Simulation status

See `docs/SIMULATION_REPORT.md` for the source-level simulation and fixes performed before handoff.

## Final platform surfaces

Additional final-phase routes:

- `http://127.0.0.1:8000/reports` reporting portal
- `http://127.0.0.1:8000/digital-twin` Digital Twin simulation portal
- `http://127.0.0.1:8000/qr/{table-token}` QR dine-in entry

Additional API groups:

- `/api/v1/payments/*`
- `/api/v1/notifications/*`
- `/api/v1/reports/*`
- `/api/v1/pos/*`
- `/api/v1/digital-twin/*`
- `/api/v1/rider/availability`
- `/api/v1/rider/assignments/{id}/locations`

## Final-phase deployment notes

1. Run `composer install` in `backend` after extracting a fresh project copy.
2. Preserve the existing `.env` when upgrading an already-configured FoodHub installation.
3. Run `php artisan migrate`; do not use `migrate:fresh` unless a complete database reset is intentional.
4. Set `FOODHUB_WEBHOOK_SECRET` before exposing the payment webhook publicly.
5. Real GCash/card/PayPal provider credentials are required for actual payment capture. The local system records payment state and provides a webhook abstraction but does not pretend to be a live payment gateway.
6. Generate Flutter platform folders on the target development machine with `flutter create --platforms=android .`, then run `flutter pub get`.
7. For Android emulator API access, use `http://10.0.2.2:8000/api/v1`; for a physical device, use the LAN address of the computer running Laravel.

## Project documentation

For a complete explanation of the architecture, database, workflows, authentication, customer ordering, kitchen/rider operations, payments, notifications, POS, QR dine-in, Digital Twin, Flutter structure, setup process, testing, troubleshooting, production hardening and development methodology, read:

- `docs/PROJECT_DOCUMENTATION.md` — complete project documentation and build guide.
- `docs/BUILD_WORKFLOW.md` — step-by-step architecture and request workflows.
- `docs/ARCHITECTURE_FINAL.md` — concise final architecture reference.
- `docs/FEATURE_MATRIX.md` — feature implementation status.
- `docs/TESTING.md` — testing and deployment checks.
- `docs/UI_UX_PHASE.md` — UI/UX phase notes.

## Security & Integration Hardening
See [docs/SECURITY_HARDENING.md](docs/SECURITY_HARDENING.md).

## Pre-release validation

Before distributing a build, run:

```bash
php scripts/audit-project.php
php scripts/simulate-platform.php
```

The audit checks API/controller consistency, security-sensitive configuration, branch isolation, order validation, and PHP syntax. The simulation exercises deterministic order, pricing, webhook, GPS, role, and input-boundary scenarios.

## Phase 1 runtime procedure

After extracting the project into XAMPP, install Composer dependencies and run the real Laravel test suite. Use `scripts/phase1-runtime-check.bat` for the non-destructive runtime gate. After every source change, also run `php scripts/audit-project.php` and `php scripts/simulate-platform.php`.

## Phase 2 — Real integrations

The current build adds provider-ready integration boundaries for PayPal, GCash, Firebase Cloud Messaging, Google Maps, SMS, email and Laravel Reverb realtime broadcasting. External credentials remain environment-only and are not bundled. See `docs/PHASE2_REAL_INTEGRATIONS.md` for setup and acceptance order.

Validation gate for this build:

```bat
php scripts/audit-project.php
php scripts/simulate-platform.php
php scripts/verify-project.php
```

The deterministic simulation currently covers 49 checks. Provider transactions and Flutter compilation still require verification on the developer machine with real credentials/configuration.

## Phase 4 — Advanced Operations

The current build adds the Phase 4 advanced-operations layer:
- expanded Digital Twin snapshot, simulation and scenario comparison
- analytics and operational reporting
- FoodHub Wallet with transactional ledger and wallet checkout/refunds
- loyalty points, tiers, redemption and admin adjustments
- first-class promotion campaigns
- expanded audit/reporting views
- Flutter wallet and promotion surfaces

See `docs/PHASE4_ADVANCED_OPERATIONS.md` for the workflow, endpoints, business rules and acceptance procedure.


## Phase 5 — Production
The Phase 5 production layer adds defensive HTTP security headers, optional HTTPS enforcement, detailed health monitoring, MySQL backup/retention commands, backup verification, load-smoke testing and a final acceptance gate. See `docs/PHASE5_PRODUCTION.md`.

- Role-separated web portals and rider mobile-only operations: `docs/PORTAL_SEPARATION.md`


## Composer network recovery

The first installation requires Composer to resolve dependencies and create `backend/composer.lock`. The bootstrap script now tests an IPv4 HTTPS connection first and sets `COMPOSER_IPRESOLVE=4` automatically when IPv4 works. If Packagist is still unreachable, the script prints DNS and proxy diagnostics.

Run from the project root:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\bootstrap-composer.ps1
```

If it reports that Packagist cannot be reached, also run:

```powershell
composer diagnose
Resolve-DnsName repo.packagist.org
curl.exe -4 -I https://repo.packagist.org/packages.json
Test-NetConnection repo.packagist.org -Port 443
```

Do not create a fake `composer.lock`; it must be generated by Composer after dependency metadata is available.
