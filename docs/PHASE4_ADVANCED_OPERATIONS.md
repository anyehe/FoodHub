# FoodHub Phase 4 — Advanced Operations

Phase 4 extends the completed ordering platform into an operations and customer-value layer.

## 1. Digital Twin expansion

The Digital Twin now exposes:
- active orders by status
- kitchen queue pressure
- online/total riders
- active-order load per online rider
- inventory item count, low-stock count and estimated stock value
- configurable service time and cancellation rate in simulations
- projected completed orders and sales
- kitchen and delivery capacity
- bottleneck capacity and unserved-order estimate
- scenario comparison for up to ten named scenarios

Branch managers remain restricted to their assigned branch through the existing branch-isolation rule.

## 2. Analytics

New reporting endpoints cover:
- overview KPIs
- sales and average order value
- unique and repeat customers
- cancellation rate
- hourly demand
- branch performance
- customer retention detail
- payment-method mix

Date and branch filters reuse the existing report-scope rules.

## 3. FoodHub Wallet

Customers receive an internal PHP wallet ledger.

Supported operations:
- wallet balance
- wallet transaction history
- admin credit adjustments
- wallet checkout payment
- wallet refunds

Wallet debits and credits are transactionally locked. The application never treats an external top-up as successful without an actual provider integration.

## 4. Loyalty

Customers receive a loyalty account with:
- points balance
- Bronze/Silver/Gold/Platinum tier calculation
- earn transactions from completed paid orders
- point redemption into wallet credit
- admin adjustments
- idempotent order reward handling

Current deterministic policy:
- 1 point per PHP 1 of completed paid order value
- 100 points = PHP 1 wallet credit
- Silver: 500 points
- Gold: 2,000 points
- Platinum: 5,000 points

These are application defaults and can be changed in a later business-configuration phase.

## 5. Promotions

Promotions are now first-class records separate from coupons. They support:
- branch targeting
- title/description
- banner URL
- coupon code reference
- discount metadata
- date windows
- usage limits
- priority
- active/inactive state

Public users can view currently active promotions. Admin users can create, update and delete promotion campaigns.

## 6. Audit/reporting expansion

Existing audit logging is extended with report access for administrators, while operational reports add cancellation and payment-mix views.

## API additions

Customer:
- `GET /api/v1/wallet`
- `GET /api/v1/wallet/transactions`
- `GET /api/v1/loyalty/transactions`
- `POST /api/v1/loyalty/redeem`
- `GET /api/v1/promotions`

Admin:
- `/api/v1/admin/promotions/*`
- `/api/v1/admin/users/{user}/wallet/credit`
- `/api/v1/admin/users/{user}/loyalty/adjust`
- `/api/v1/reports/cancellation`
- `/api/v1/reports/payment-mix`
- `/api/v1/reports/audit`
- `/api/v1/reports/analytics/*`

Digital Twin:
- `POST /api/v1/digital-twin/compare`

## Runtime acceptance

After extraction:

```bash
composer install
php artisan migrate
php artisan test
php scripts/audit-project.php
php scripts/simulate-platform.php
php scripts/verify-project.php
```

Flutter acceptance:

```bash
flutter pub get
flutter analyze
flutter run
```

Real Laravel and Flutter execution still belongs on the developer machine because this build environment does not contain the Composer vendor tree or Flutter SDK.
