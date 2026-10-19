# FoodHub Phase 2 Changelog

## Added

- Provider-ready PayPal REST gateway with OAuth, order creation and capture.
- Configurable GCash checkout gateway boundary.
- Firebase Cloud Messaging HTTP v1 server adapter.
- Device-token persistence and registration API.
- Flutter Firebase Messaging initialization and token registration.
- Google Maps geocoding and directions integration boundary.
- Configurable SMS provider adapter.
- Laravel Reverb/broadcasting configuration.
- Authenticated private order broadcast channel.
- Realtime order update event emitted during order creation and status transitions.
- Email delivery for application/order notifications while preserving database notifications.
- Integration status service and integration setup documentation.

## Safety behavior

- External providers are disabled until credentials are configured.
- FCM failures do not fail order creation or status transitions.
- Payment gateway initiation does not report a successful payment unless the provider confirms it.
- No production credentials or Firebase native configuration files are bundled.
- GCash remains endpoint-configurable because the exact merchant API contract must come from the selected merchant integration.

## Required local setup

```bat
composer install
php artisan optimize:clear
php artisan migrate
php artisan test
```

Optional Reverb:

```bat
php artisan reverb:start
```

Flutter:

```bat
flutter pub get
flutterfire configure
flutter analyze
flutter build apk --debug
```

## Not claimed as verified in the build environment

The build environment does not contain the user's provider credentials, MySQL runtime, Composer vendor tree or Flutter SDK. Therefore provider transactions, FCM delivery, Reverb networking and Flutter compilation require verification on the user's machine.
