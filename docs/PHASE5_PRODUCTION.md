# FoodHub Phase 5 — Production

## Scope
Phase 5 hardens the completed application for production acceptance: security headers/HTTPS enforcement, database backup automation, backup verification, health monitoring, load-smoke testing, deployment guidance and final acceptance gates.

## Security
- `ProductionSecurityMiddleware` adds defensive response headers.
- `APP_FORCE_HTTPS=true` redirects insecure requests to HTTPS.
- Keep `APP_DEBUG=false` in production.
- Use strong, unique `APP_KEY` and `FOODHUB_WEBHOOK_SECRET`.
- Restrict database users to the permissions required by the application.
- Never commit `.env`, provider credentials, Firebase service-account JSON, or SQL backups.

## Backups
Run from `backend`: `php artisan foodhub:backup` and periodically `php artisan foodhub:backup-prune`. Backups are written under `BACKUP_DIR` and use `mysqldump --single-transaction --routines --triggers`. Verify the newest backup with `php ../scripts/backup-verify.php`. A real restore drill must be performed against a disposable database before production sign-off.

## Monitoring
- `/api/v1/health` is a lightweight liveness check.
- `/api/v1/health/detailed` checks database, cache and application key and reports request latency.
- Configure an external monitor to poll the detailed endpoint.
- Monitor Laravel logs, PHP-FPM/Apache, MySQL, disk usage, queue workers and failed jobs in production.

## Load smoke test
With the application running, configure `LOAD_TEST_BASE_URL`, `LOAD_TEST_REQUESTS` and `LOAD_TEST_CONCURRENCY`, then run `php scripts/load-test.php`. This is a smoke harness, not a substitute for a dedicated distributed load-testing platform.

## Deployment
1. Put the site in maintenance mode.
2. Back up the database.
3. Deploy immutable application code.
4. Run `composer install --no-dev --optimize-autoloader`.
5. Set production `.env` values and `APP_DEBUG=false`.
6. Run `php artisan migrate --force`.
7. Run `php artisan config:cache`, `route:cache`, and `view:cache`.
8. Restart queue workers/Reverb as applicable.
9. Run health and acceptance checks.
10. Exit maintenance mode.

## Restore drill
A backup is not production-ready until a restore has been demonstrated. Restore the SQL file into a disposable MySQL database, point a staging copy at it, run migrations/status checks and execute the Laravel test suite. Do not restore over the production database during a routine verification.

## Final acceptance
Run: `php scripts/audit-project.php`, `php scripts/simulate-platform.php`, `php scripts/verify-project.php`, `php scripts/final-acceptance.php`, `php artisan test`, `php artisan migrate:status`. Then execute the browser/mobile order lifecycle and provider-specific payment/push/map tests.
