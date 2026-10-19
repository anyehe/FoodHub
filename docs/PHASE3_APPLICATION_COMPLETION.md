# FoodHub Phase 3 — Application Completion

## Scope
Phase 3 turns the existing customer/rider foundations into an end-to-end mobile ordering flow while preserving the Phase 0 role separation and Phase 2 provider boundaries.

### Customer mobile flow
1. Login/register.
2. Browse active products.
3. Open a product detail page and select add-ons.
4. Add items to cart.
5. Checkout with branch, fulfillment type, saved delivery address, coupon, notes, and payment method.
6. Request a server-side checkout quote.
7. Place the order through the existing transactional `OrderService`.
8. View order history.
9. Open an order and see status progression, rider assignment, and the latest rider coordinates.
10. Tracking refreshes automatically while the screen is open.

### Rider mobile flow
1. Rider logs in through the role-separated session.
2. Rider toggles online/offline.
3. Rider sees assigned delivery work.
4. Rider accepts, picks up, starts delivery, and marks delivered.
5. While an assignment is active, the app periodically sends the device's current coordinates to FoodHub.

## Backend additions
- `GET /api/v1/orders/{order}/tracking` — customer-owned order tracking payload.
- Tracking includes the order, assignment, latest rider coordinates, recent location points, status history, payment, address, branch, and items.
- Customer authorization is enforced before returning tracking data.

## Safety / operational behavior
- Checkout pricing remains server-side; the mobile subtotal is only an estimate.
- Delivery still requires an address on the backend.
- Product availability, branch pricing, option validity, coupons, payment records, and order transitions remain server-authoritative.
- Rider location updates are best-effort client telemetry and do not change order state by themselves.
- Provider credentials are still optional until configured in Phase 2.

## Local validation
Run:

```text
php scripts/audit-project.php
php scripts/simulate-platform.php
php scripts/verify-project.php
php artisan test
```

Flutter compilation requires the Flutter SDK on the developer machine. The build environment used for this phase does not contain Flutter, so a Flutter build/analyze result is not claimed here.
