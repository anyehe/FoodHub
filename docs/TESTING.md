# FoodHub End-to-End Test Plan

## 1. Backend health
Open `http://127.0.0.1:8000/api/v1/health` and confirm `status=ok`.

## 2. Browser customer flow
Open `http://127.0.0.1:8000/app`.

Demo customer:
- Email: `customer@foodhub.test`
- Password: `password`

The browser portal signs in against Sanctum, loads the live catalog, creates a cart, reads the seeded branch/address, and creates a delivery order.

## 3. Operations flow
Open `/dashboard`.

Demo staff:
- `admin@foodhub.test` / `password`
- `kitchen@foodhub.test` / `password`
- `manager@foodhub.test` / `password`
- `rider@foodhub.test` / `password`

The dashboard uses the real API token and reports current branch statistics.

## 4. Automated end-to-end test
From `backend`:

```bash
php artisan test
```

`tests/Feature/EndToEndOrderTest.php` verifies:

customer -> order -> kitchen status transitions -> rider assignment -> rider acceptance -> pickup -> location update -> delivery.

## 5. Flutter Android
Start Laravel at `127.0.0.1:8000`. For an Android emulator, the host is `10.0.2.2`, already configured by default.

```bash
cd flutter_app
flutter create --platforms=android .
flutter pub get
flutter run --dart-define=FOODHUB_API=http://10.0.2.2:8000/api/v1
```

For a physical Android device, replace the API address with the PC's LAN IP, for example:

```bash
flutter run --dart-define=FOODHUB_API=http://192.168.1.10:8000/api/v1
```

Make sure Windows Firewall permits PHP's port 8000.

## 6. XAMPP Apache
For Apache, create a virtual host whose `DocumentRoot` points to:

`backend/public`

Do not point Apache at the Laravel project root.

The easiest local-development path is still `php artisan serve`.

## 7. Payment testing
Cash is fully represented in the demo flow. GCash, card and PayPal are modeled as payment methods but require provider credentials/webhook configuration before real-money processing is enabled.

### Apache VirtualHost example

Add to `xampp/apache/conf/extra/httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName foodhub.local
    DocumentRoot "C:/xampp/htdocs/FoodHub-Advanced/backend/public"
    <Directory "C:/xampp/htdocs/FoodHub-Advanced/backend/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add `127.0.0.1 foodhub.local` to Windows `hosts`, restart Apache, then use `http://foodhub.local`.

## 8. Phase 2 browser operations

- `/admin` with `admin@foodhub.test` / `password`
- `/kitchen` with `kitchen@foodhub.test` / `password`
- `/rider` with `rider@foodhub.test` / `password`

After installing dependencies, run:

```bash
php artisan migrate
php artisan test
```

Do not use `migrate:fresh --seed` on an existing database unless you intentionally want to erase and recreate all FoodHub data.
