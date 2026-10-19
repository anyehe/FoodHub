# FoodHub Release Gate

## Completed in the pre-release source build

- Laravel 12 / PHP 8.2+ project structure
- Customer, staff, branch, kitchen, waiter, rider and admin flows
- Order lifecycle and pricing rules
- Payment webhook HMAC verification and duplicate-safe processing
- Branch isolation for reports and Digital Twin
- Review ownership validation
- Audit logging for mutating staff/admin API requests
- Authentication throttling
- Database-aware health endpoint
- Audit-log retention command
- Static PHP syntax validation
- API route/controller consistency audit
- Deterministic platform simulation
- ZIP integrity validation
- Flutter source hardening: no demo credentials in login fields; API timeout added

## Mandatory target-environment gate

The build environment used to prepare this package does not contain Composer/vendor dependencies, MySQL, or the Flutter/Dart SDK. Therefore the following are **not claimed as completed here**:

1. `composer install` and Laravel package discovery
2. Real MySQL migrations
3. Full PHPUnit execution
4. Real HTTP/API integration testing against MySQL
5. Flutter `pub get`, analyzer, APK/AAB build, or device testing
6. Firebase, maps, payment-provider, email/SMS, or WebSocket live integrations
7. Production load testing

## Required XAMPP verification

```text
cd C:\xampp\htdocs\FoodHub\backend
composer install
php artisan optimize:clear
php artisan migrate
php artisan test
php artisan route:list
php artisan foodhub:audit-prune
php artisan serve
```

Then test `/api/v1/health`, authentication, catalog, checkout, customer order lifecycle, payment webhook signatures, staff permissions, rider assignment/delivery, notifications, admin audit logs, reports, Digital Twin, and branch isolation.

For Flutter:

```text
cd C:\xampp\htdocs\FoodHub\flutter_app
flutter pub get
flutter analyze
flutter build apk --debug
```

Use a disposable test database for destructive/integration tests. Do not run `migrate:fresh` against a database containing real data.
