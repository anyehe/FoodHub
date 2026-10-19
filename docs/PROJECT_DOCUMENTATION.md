# FoodHub — Complete Project Documentation

**Project:** FoodHub Advanced Food Delivery, Restaurant Operations, POS, Dine-in and Digital Twin Platform  
**Version:** UI Polished / Final platform foundation  
**Backend:** Laravel 12, PHP 8.2+  
**Database:** MySQL 8 / MariaDB-compatible  
**Web UI:** Blade + Bootstrap 5.3  
**Mobile:** Flutter / Dart  
**API:** REST under `/api/v1`  
**Local environment:** XAMPP on Windows

---

## 1. Project Overview

FoodHub is a full-stack food-commerce and restaurant-operations platform. It combines customer ordering, branch management, kitchen workflow, rider delivery, dine-in/QR ordering, POS entry points, reporting, notifications, and a Digital Twin simulation layer in one Laravel backend.

The project is intentionally an **independent implementation**. It is inspired by the public feature category of modern multi-branch food-delivery systems, but it does not copy proprietary source code, branding, assets, or exact UI from another product.

The architecture is designed so that the same Laravel API and MySQL database can serve:

- the customer-facing responsive website;
- the browser-based operations portals;
- the Flutter customer application;
- the Flutter rider workflow;
- future native or third-party clients.

### Main business flow

```text
Customer
   |
   v
Browse branches/categories/products
   |
   v
Select options -> Cart -> Price quote
   |
   v
Checkout
   |
   +--> Delivery
   +--> Pickup
   +--> Dine-in / QR
   |
   v
Order created
   |
   v
Kitchen confirmation
   |
   v
Preparing -> Ready
   |
   v
Rider assignment (delivery orders)
   |
   v
Accepted -> Picked up -> Out for delivery
   |
   v
Delivered -> Review
```

The order state is separate from payment state. This prevents payment concerns from being tightly coupled to kitchen and delivery operations.

---

## 2. Goals of the Build

The project was built around these goals:

1. Provide a real Laravel application structure rather than a collection of unrelated PHP scripts.
2. Centralize business rules in services and API endpoints.
3. Support multiple branches from one database.
4. Keep customer, staff, kitchen, waiter and rider workflows distinct.
5. Provide a reusable REST API for both web and Flutter clients.
6. Make the web interface responsive with Bootstrap 5.
7. Keep external providers behind integration boundaries so credentials are not hard-coded.
8. Store operational history such as order status changes and rider locations.
9. Provide a Digital Twin layer for operational snapshots and what-if scenarios.
10. Make the project straightforward to run in a Windows/XAMPP development environment.

---

## 3. Technology Stack

### Backend

- **Laravel 12** — routing, controllers, validation, middleware, ORM, migrations, notifications and application structure.
- **PHP 8.2+** — runtime language.
- **Laravel Sanctum** — token-based API authentication.
- **Eloquent ORM** — database access and relationships.
- **Blade** — server-rendered web pages.
- **Bootstrap 5.3** — responsive interface and layout system.

### Database

- **MySQL 8 / MariaDB-compatible**.
- Laravel migrations define the schema.
- Seeders provide development/demo data.

### Mobile

- **Flutter / Dart**.
- REST client communicates with Laravel `/api/v1`.
- Feature structure uses screens, view models, repositories and core services.
- `geolocator` is used for rider location support in the source architecture.

### Development tools

- XAMPP
- Apache
- MySQL
- Composer
- PHP CLI
- Flutter SDK
- Android Studio or another Android development environment
- VS Code or another code editor

---

## 4. High-Level Architecture

```text
                         FOODHUB CLIENTS
 ┌───────────────────────┬───────────────────────┬─────────────────────┐
 │ Customer Website      │ Operations Portals    │ Flutter Applications│
 │ Blade + Bootstrap     │ Admin/Kitchen/Rider   │ Customer/Rider      │
 └───────────┬───────────┴────────────┬──────────┴──────────┬──────────┘
             │                         │                     │
             └──────────── REST/JSON / HTTP ───────────────┘
                                      │
                                      v
                         ┌─────────────────────────┐
                         │      Laravel 12 API    │
                         │                         │
                         │ Auth / Roles            │
                         │ Catalog / Branches      │
                         │ Cart / Orders           │
                         │ Pricing / Coupons       │
                         │ Payments                │
                         │ Kitchen / Rider         │
                         │ Reviews / Notifications │
                         │ POS / QR / Reports      │
                         │ Digital Twin            │
                         └────────────┬────────────┘
                                      │
                                      v
                         ┌─────────────────────────┐
                         │         MySQL           │
                         │ Commerce + Operations   │
                         │ History + Telemetry     │
                         └─────────────────────────┘
                                      │
                 ┌────────────────────┴────────────────────┐
                 v                                         v
       External provider boundaries                Digital Twin
       Payments / FCM / Maps / SMS                Snapshot + Scenarios
```

### Separation of responsibilities

**Controllers** receive HTTP requests and return responses.  
**Services** contain business rules that should not be duplicated across controllers.  
**Models** represent persistent domain data and relationships.  
**Middleware** protects role-specific resources.  
**Blade views** render browser interfaces.  
**Flutter repositories** communicate with API resources.  
**View models** expose application state to Flutter screens.

---

## 5. Laravel Project Structure

Important backend directories:

```text
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   ├── Notifications/
│   └── Services/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── create_database.sql
├── resources/
│   └── views/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
├── public/
├── storage/
├── tests/
├── artisan
├── composer.json
└── .env
```

### Important services

| Service | Responsibility |
|---|---|
| `OrderService` | Order creation and order workflow rules |
| `PricingService` | Tax, delivery and pricing calculations |
| `PaymentService` | Payment method/state abstraction and webhook/refund boundaries |
| `NotificationService` | Application notification behavior |
| `DigitalTwinService` | Operational snapshots and scenario simulation |

### Important middleware

| Middleware | Purpose |
|---|---|
| `StaffMiddleware` | Restricts operations resources to staff roles |
| `RiderMiddleware` | Restricts rider resources to rider accounts |
| `RoleMiddleware` | Generic role-based access boundary |

---

## 6. Database Design

The schema is migration-driven. The major entities are:

```text
users
  ├── addresses
  ├── orders
  ├── reviews
  ├── payments
  └── notifications

branches
  ├── products (through branch_product)
  ├── orders
  ├── inventory_items
  └── restaurant_tables

categories
  └── products

products
  ├── product_options
  ├── branch_product
  └── order_items

orders
  ├── order_items
  ├── order_status_histories
  ├── payments
  └── delivery_assignments

users/riders
  └── rider_location_points
```

### Core tables

- `users`
- `branches`
- `categories`
- `products`
- `product_options`
- `branch_product`
- `addresses`
- `orders`
- `order_items`
- `coupons`
- `payments`
- `delivery_assignments`
- `reviews`
- `restaurant_tables`
- `inventory_items`
- `personal_access_tokens`
- `order_status_histories`
- `notifications`
- `rider_location_points`

### Why order history is stored separately

The current order status answers "where is the order now?". The `order_status_histories` table answers "how did it get here?". This creates an auditable timeline that can be used by customer tracking, support staff, reporting and future analytics.

### Why rider locations are stored separately

The rider's latest location is useful for the current delivery screen, while a location history allows later route analysis, delivery-time analysis and Digital Twin scenarios without overwriting historical information.

---

## 7. Authentication and Roles

FoodHub uses Laravel Sanctum token authentication for API clients.

### Roles

- `admin`
- `branch_manager`
- `kitchen`
- `waiter`
- `rider`
- `customer`

### Authentication flow

```text
Login form
   |
   v
POST /api/v1/auth/login
   |
   v
Laravel validates credentials
   |
   v
Sanctum token created
   |
   v
Client stores token
   |
   v
Authorization: Bearer <token>
```

The operations dashboard uses its own browser storage key (`fh_staff_token`) while the customer browser portal uses `fh_customer_token`. This avoids accidentally treating a customer session as an operations session.

### Authorization

Authentication proves who the user is. Middleware then verifies whether the authenticated user is allowed to access a staff, rider or role-specific resource.

A production deployment should keep authorization checks on the server even when the UI hides controls from unauthorized users.

---

## 8. Web Application Workflow

The web application is built from Laravel Blade templates and Bootstrap.

### Customer-facing pages

- `/` — landing page
- `/menu` — menu/catalog
- `/product/{id}` — product detail
- `/app` — customer browser portal
- `/qr/{table-token}` — QR dine-in entry

### Operations pages

- `/dashboard` — operations dashboard
- `/admin` — administrative control center
- `/kitchen` — kitchen workflow
- `/rider` — rider workflow
- `/reports` — reporting portal
- `/digital-twin` — Digital Twin simulation portal

### UI design

FoodHub uses the project palette:

- `#003135`
- `#024950`
- `#964734`
- `#0FA4AF`
- `#AFDDE5`

The shared Blade layout provides the brand, navigation, footer, responsive behavior and back-to-top interaction. The current UI phase also adds stronger cards, food-image placeholders, category discovery, feature cards, status presentation and mobile-friendly spacing.

---

## 9. REST API Architecture

The main API prefix is:

```text
/api/v1
```

API groups include:

```text
/api/v1/auth/*
/api/v1/catalog/*
/api/v1/branches/*
/api/v1/cart/*
/api/v1/orders/*
/api/v1/payments/*
/api/v1/notifications/*
/api/v1/reports/*
/api/v1/pos/*
/api/v1/digital-twin/*
/api/v1/rider/*
```

### API design principle

The browser and Flutter clients should not implement business calculations independently. The server is the source of truth for:

- product availability;
- branch availability;
- prices;
- taxes;
- delivery charges;
- coupon validity;
- order status transitions;
- payment state;
- rider assignment;
- authorization.

This reduces inconsistent behavior between web and mobile clients.

---

## 10. Customer Ordering Workflow

### Step 1 — Catalog

The customer requests branches, categories and products. Products can have branch-specific availability and pricing.

### Step 2 — Product options

A product may have configurable options/add-ons. Selected options are included in the cart/order representation.

### Step 3 — Cart

The cart contains the selected branch, items, quantities and options.

### Step 4 — Quote

`PricingService` calculates the order amount, including applicable tax and delivery pricing.

### Step 5 — Coupon

Coupon rules are validated before the order is finalized. Dates and usage limits are stored in the coupon model.

### Step 6 — Checkout

The customer selects fulfillment:

- delivery;
- pickup;
- dine-in.

Scheduled orders can store a requested future time.

### Step 7 — Order creation

The server creates the order and its order items. The initial order status and history entry are recorded.

### Step 8 — Payment

The payment record is independent from the order state. Cash can be used in the local/demo flow. External gateways require real provider credentials.

---

## 11. Kitchen Workflow

Kitchen staff work from a dedicated operational screen.

```text
Pending
  |
  v
Confirmed
  |
  v
Preparing
  |
  v
Ready
```

The kitchen should not directly manipulate customer-facing totals. It changes operational status, while the order and payment records remain authoritative for commerce data.

---

## 12. Rider Workflow

For delivery orders, the rider process is:

```text
Order ready
   |
   v
Assignment offered
   |
   v
Rider accepts
   |
   v
Picked up
   |
   v
Out for delivery
   |
   v
Delivered
```

The rider can also:

- toggle online/offline availability;
- view available assignments;
- accept an assignment;
- record pickup;
- send location updates;
- complete delivery;
- view delivery history.

The source stores latest coordinates and location history. Real-time map rendering and continuous broadcasting require a production mapping/realtime provider.

---

## 13. Dine-in and QR Ordering

Each restaurant table can have a QR token. A customer can enter through:

```text
/qr/{table-token}
```

The QR entry identifies the table context and uses the same order engine rather than creating a separate ordering system.

This is important because dine-in, pickup and delivery should share the same product, pricing, payment and order infrastructure wherever business rules are common.

---

## 14. POS Architecture

The POS component provides a staff-facing API entry point for walk-in orders.

The design supports a fallback walk-in customer when no registered customer is associated with the sale.

A full production POS deployment may additionally require:

- receipt printers;
- barcode scanners;
- cash drawer hardware;
- payment terminal integration;
- branch-specific tax/receipt configuration.

Those hardware integrations are deployment-specific and are not assumed to be available in a normal XAMPP development environment.

---

## 15. Payments

Payment architecture is intentionally provider-neutral.

### Supported method model

The application can represent methods such as:

- cash;
- GCash;
- card;
- PayPal.

### Important distinction

The local/demo application can record payment states, but it must not pretend that a recorded database payment is the same thing as a real gateway capture.

For production:

```text
Customer
  |
  v
FoodHub PaymentService
  |
  v
Provider Adapter
  |
  v
External Gateway
  |
  v
Signed Webhook
  |
  v
FoodHub verifies webhook
  |
  v
Payment state updated
```

`FOODHUB_WEBHOOK_SECRET` should be configured before a public payment webhook is exposed.

---

## 16. Notifications

Database-backed notifications are part of the application architecture.

The notification layer can support:

- order created;
- order confirmed;
- preparing;
- ready;
- rider assignment;
- out for delivery;
- delivered;
- other application events.

For mobile push notifications, a production Firebase Cloud Messaging setup is required. Provider credentials should be stored in environment/configuration rather than committed to source control.

---

## 17. Reports and Analytics

The reporting layer provides operational KPIs such as:

- sales summaries;
- daily sales series;
- top products by quantity;
- top products by sales;
- branch/date filtering.

These reports are derived from transactional data rather than from manually maintained dashboard counters.

For a larger production deployment, reporting can be expanded with queued aggregation jobs, materialized summaries or a dedicated analytics database.

---

## 18. Digital Twin

FoodHub includes a Digital Twin service intended as an operational simulation layer rather than a visual 3D model.

### Digital Twin concept in this project

The Digital Twin represents a simplified digital model of current restaurant operations using application data such as:

- demand;
- riders;
- kitchen capacity;
- average order value;
- operating hours.

### Current workflow

```text
Live operational data
        |
        v
DigitalTwinService
        |
        +--> Snapshot
        |
        +--> Scenario inputs
        |
        v
Projected demand / sales / capacity load / unserved demand
```

### Example scenario variables

- demand growth percentage;
- number of available riders;
- kitchen capacity;
- average order value;
- operating hours.

### Why it is useful

The simulation can help a future operations team ask questions such as:

- What happens if order demand increases?
- How much capacity is available?
- What is the projected sales impact?
- Does the current rider capacity support the modeled demand?

The current implementation is a simulation foundation. It should not be presented as a guaranteed real-world forecast.

---

## 19. Flutter Architecture

The Flutter project follows a lightweight layered structure:

```text
flutter_app/lib/
├── core/
│   ├── config/
│   ├── services/
│   └── theme/
├── models/
├── repositories/
├── viewmodels/
└── screens/
```

### Responsibilities

**Models** represent API data.  
**Repositories** perform feature-level server access.  
**ViewModels** hold presentation state.  
**Screens** render the UI.  
**Core services** handle API transport, session state and device-related functionality.

### Current mobile surfaces

- login;
- customer home/catalog;
- cart;
- orders;
- notifications;
- rider workflow;
- rider availability;
- rider location updates.

The source is prepared for Android generation using the installed Flutter SDK.

---

## 20. Building the Project from Scratch

### Step 1 — Install prerequisites

Install:

1. XAMPP with Apache and MySQL.
2. PHP 8.2 or newer.
3. Composer.
4. Flutter SDK.
5. Android Studio for Android builds/emulators.
6. Git if version control is desired.

Verify:

```bash
php -v
composer -V
flutter --version
```

### Step 2 — Create the Laravel application

For a new implementation, create the Laravel project with the desired Laravel 12 version, then place the FoodHub application files into the Laravel structure.

The final project already contains the Laravel application structure, so a normal user should **not recreate the Laravel project** unless intentionally starting over.

### Step 3 — Configure environment

Copy:

```text
backend/.env.example -> backend/.env
```

Set at minimum:

```dotenv
APP_NAME=FoodHub
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=foodhub
DB_USERNAME=root
DB_PASSWORD=
```

Use the password configured on the local MySQL installation. Do not commit real passwords to source control.

### Step 4 — Create the database

Create a MySQL database named `foodhub` in phpMyAdmin, or use the supplied database creation script.

### Step 5 — Install PHP dependencies

From the `backend` directory:

```bash
composer install
```

### Step 6 — Generate application key

```bash
php artisan key:generate
```

### Step 7 — Run migrations and seed data

For a fresh development database:

```bash
php artisan migrate --seed
```

For an existing FoodHub database:

```bash
php artisan migrate
```

**Do not use `migrate:fresh` unless you intentionally want to delete and rebuild the database.**

### Step 8 — Create storage link

```bash
php artisan storage:link
```

### Step 9 — Clear cached configuration

```bash
php artisan optimize:clear
```

### Step 10 — Start Laravel

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Open:

```text
http://127.0.0.1:8000/
```

---

## 21. Running with XAMPP Apache

Laravel's development server is the easiest local option. If Apache is preferred, its document root must point to Laravel's `public` directory:

```text
backend/public
```

Do not point Apache at the Laravel project root because that can expose files that should remain outside the web root.

Example virtual host:

```apache
<VirtualHost *:80>
    ServerName foodhub.local
    DocumentRoot "C:/xampp/htdocs/FoodHub/backend/public"

    <Directory "C:/xampp/htdocs/FoodHub/backend/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add this to the Windows hosts file:

```text
127.0.0.1 foodhub.local
```

Restart Apache and visit:

```text
http://foodhub.local
```

---

## 22. Flutter Build Process

From the project root:

```bash
cd flutter_app
flutter create --platforms=android .
flutter pub get
```

For an Android emulator:

```bash
flutter run --dart-define=FOODHUB_API=http://10.0.2.2:8000/api/v1
```

`10.0.2.2` is the Android emulator's route to the host computer's localhost.

For a physical Android phone, use the computer's LAN address, for example:

```bash
flutter run --dart-define=FOODHUB_API=http://192.168.1.10:8000/api/v1
```

The phone and computer must be on a network that allows the phone to reach the Laravel server, and Windows Firewall must permit the chosen port.

---

## 23. Demo Accounts

The seeded development accounts use the password `password`.

| Role | Email |
|---|---|
| Admin | `admin@foodhub.test` |
| Customer | `customer@foodhub.test` |
| Rider | `rider@foodhub.test` |
| Kitchen | `kitchen@foodhub.test` |
| Branch Manager | `manager@foodhub.test` |
| Waiter | `waiter@foodhub.test` |

These credentials are for local development only and must be replaced/disabled before production use.

---

## 24. Testing Strategy

### Static validation

The project was source-checked with PHP syntax validation (`php -l`) across backend PHP files.

### Laravel feature testing

The repository contains an end-to-end order test covering the main operational chain:

```text
Customer
 -> Order
 -> Kitchen status changes
 -> Rider assignment
 -> Rider acceptance
 -> Pickup
 -> Location update
 -> Delivery
```

Run:

```bash
cd backend
php artisan test
```

The full runtime test requires the local Composer dependencies and a working PHP/MySQL environment.

### Manual smoke test

1. Open `/api/v1/health`.
2. Sign in to `/app` as customer.
3. Browse catalog.
4. Add an item.
5. Checkout.
6. Open `/dashboard` or `/kitchen`.
7. Advance the kitchen status.
8. Assign/accept rider.
9. Update delivery state.
10. Confirm the customer order timeline.

---

## 25. Troubleshooting Guide

### `Route [login] not defined`

This occurs when a web route uses Laravel's session `auth` middleware without a corresponding web login route. The FoodHub operations dashboard uses API/Sanctum authentication instead of relying on a missing web login route.

### `Class "App\\Http\\Controllers\\Controller" not found`

Laravel controllers extend the base controller class. The project contains:

```text
backend/app/Http/Controllers/Controller.php
```

If rebuilding the project manually, ensure this file exists.

### `Staff access required`

The dashboard requires an authenticated staff account. Use a staff demo account and make sure the browser has the current `fh_staff_token`.

### Migration error for `ends_at`

The coupon migration uses nullable timestamps for `starts_at` and `ends_at`, avoiding invalid timestamp defaults on MySQL configurations that reject them.

### Flutter cannot reach API

Check:

- Laravel is running;
- the API URL is correct;
- Android emulator uses `10.0.2.2` rather than `127.0.0.1`;
- a physical phone uses the PC's LAN IP;
- firewall rules permit the Laravel port.

---

## 26. Production Hardening Checklist

Before deploying publicly:

- set `APP_ENV=production`;
- set `APP_DEBUG=false`;
- use a strong `APP_KEY`;
- use a dedicated MySQL user rather than `root`;
- use HTTPS;
- protect payment webhooks with signatures/secrets;
- configure real payment providers;
- configure Firebase/FCM if push notifications are required;
- configure a maps provider for navigation/live map features;
- configure a real-time broadcasting provider if live status/location updates are required;
- configure queues and workers for asynchronous jobs;
- configure scheduled tasks;
- configure backups;
- restrict filesystem permissions;
- remove or rotate development credentials;
- enable monitoring and application logs;
- test authorization for every staff role;
- run database migrations in a controlled deployment process.

---

## 27. External Integration Boundaries

The project deliberately separates internal business logic from external providers.

| Integration | Current project boundary | Production requirement |
|---|---|---|
| GCash | Payment method/adapter boundary | Real merchant/provider credentials |
| Card | Payment method/adapter boundary | Gateway credentials + webhook |
| PayPal | Payment method/adapter boundary | PayPal application credentials |
| Firebase | Notification boundary | Firebase project + service credentials |
| Maps/GPS | Rider location boundary | Maps/API key and routing service |
| WebSockets | Realtime boundary | Broadcasting server/provider |
| Email/SMS | Notification boundary | Provider account/API credentials |
| POS hardware | POS API boundary | Hardware/vendor integration |

This distinction is important: **integration-ready is not the same as live third-party integration**.

---

## 28. Development Workflow Used to Build FoodHub

The project was built incrementally around the following sequence:

### Phase A — Foundation

- Laravel project structure.
- MySQL migrations.
- authentication.
- user roles.
- branches, categories and products.

### Phase B — Commerce

- catalog.
- options/add-ons.
- branch availability.
- addresses.
- cart.
- pricing.
- coupons.
- checkout.
- order records.

### Phase C — Operations

- order status history.
- admin dashboard.
- kitchen workflow.
- rider assignment.
- pickup/delivery lifecycle.
- rider location storage.

### Phase D — Platform features

- payments abstraction.
- notifications.
- POS entry point.
- QR dine-in.
- reporting.
- Digital Twin.
- Flutter customer/rider foundations.

### Phase E — UI/UX

- FoodHub visual identity.
- responsive Bootstrap layout.
- customer landing page.
- customer portal.
- Flutter theme.
- improved cards, hero sections, navigation and status presentation.

### Phase F — Validation and handoff

- PHP syntax validation.
- migration/runtime troubleshooting.
- authentication fixes.
- role/token separation.
- end-to-end test coverage.
- documentation.

---

## 29. Recommended Next Development Steps

The project is now at the point where further work should focus on integration and hardening rather than randomly adding features.

Recommended order:

1. Complete production payment adapters and webhook verification.
2. Configure Firebase push notifications.
3. Add real-time broadcasting for order/rider status.
4. Connect production maps/navigation services.
5. Complete Flutter checkout and detailed order tracking parity with the web portal.
6. Expand POS and QR workflows for branch operations.
7. Connect Digital Twin metrics to richer historical operational data.
8. Perform a complete authorization/security audit.
9. Expand automated API/feature tests.
10. Perform a final responsive/accessibility review.
11. Configure production deployment, queues, backups and monitoring.

---

## 30. Architectural Principles

The following principles should remain stable as FoodHub grows:

### Single source of truth

The Laravel backend owns business rules and transactional state.

### Reuse business logic

Web and Flutter clients call the same APIs rather than maintaining separate order engines.

### Separate state domains

Order status, payment state, rider assignment and notifications are related but independently modeled.

### Secure by server-side authorization

UI visibility is not authorization. Every sensitive operation must be protected by server-side middleware and role checks.

### Provider neutrality

Payment, notification, mapping and realtime integrations should remain behind replaceable boundaries.

### Auditable operations

Important transitions should leave history records so support and reporting can explain what happened.

### Production honesty

A local simulation, adapter or placeholder must never be described as a live external integration until it has real credentials and has been tested against that provider.

---

## 31. Project Deliverables

The FoodHub project contains:

- Laravel backend;
- database migrations and seeders;
- REST API;
- customer web portal;
- admin portal;
- kitchen portal;
- rider portal;
- reporting portal;
- Digital Twin portal;
- QR dine-in entry;
- Flutter customer/rider application source;
- API and architecture documentation;
- testing documentation;
- setup scripts for XAMPP/Flutter;
- UI/UX documentation.

This document is the high-level technical and educational reference for understanding how those parts fit together and how to continue development safely.


## Latest Hardening Additions

The current documented build also includes baseline production-hardening measures:

- API request throttling for the versioned API.
- Role-aware order visibility.
- Payment webhook event IDs and duplicate-event protection.
- Optional payment amount verification during webhook processing.
- Feature tests for these security boundaries.

See `docs/SECURITY_HARDENING.md` for implementation details and production deployment recommendations.
