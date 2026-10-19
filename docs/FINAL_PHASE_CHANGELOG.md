# FoodHub Final Platform Phase

This phase completes the backend operations, commerce support, reporting, notifications, delivery telemetry, POS entry point, QR dine-in entry point, Digital Twin simulation API, and Flutter rider/customer routing on top of the working Phase 2 foundation.

## Added

### Payments
- Payment method discovery endpoint.
- Customer payment initiation endpoint.
- Signed webhook endpoint with optional `FOODHUB_WEBHOOK_SECRET`.
- Admin refund endpoint for recorded refunds.
- Payment state remains separate from order state.

### Notifications
- Database notifications.
- Customer order-status notifications.
- Unread count, list, mark-read and mark-all-read endpoints.

### Delivery
- Rider online/offline state.
- Rider location history in addition to last-known location.
- Location history endpoint.
- Corrected rider acceptance so accepting an offered assignment does not attempt an invalid `assigned -> assigned` order transition.

### POS
- Staff POS order endpoint.
- Walk-in customer fallback.
- Branch-manager branch restriction.

### Reports
- Summary KPIs.
- Daily sales series.
- Top products by quantity and sales.
- Branch/date filters.

### QR dine-in
- Web QR table entry route: `/qr/{token}`.
- Existing dine-in API remains the order submission backend.

### Digital Twin
- Live operational snapshot.
- Scenario simulation for demand growth, riders, kitchen capacity, average order value and operating hours.
- Projected sales, capacity load and estimated unserved demand.

### Flutter
- Customer notifications screen.
- Rider role routing after login.
- Rider queue and delivery actions.
- Rider availability control.
- Rider location update using `geolocator`.

## Security/logic corrections
- Customer cancellation is blocked after fulfillment has materially progressed.
- Paid customer orders are not silently cancelled because a real gateway refund cannot be performed automatically by the demo abstraction.
- Reports/POS/Digital Twin routes are role-scoped.

## Validation
- All PHP source files pass `php -l` syntax validation.
- PHPUnit tests were added for final platform endpoints.
- Full Laravel runtime tests still require `composer install` and the user's local PHP/MySQL environment.
- Flutter compilation requires the Flutter SDK and platform files to be generated with `flutter create` on the target development machine.


## Security & Integration Hardening

The next hardening pass added:

- API rate limiting on `/api/v1` at 60 requests per minute per client.
- More precise order-detail authorization for customers, operational staff, and assigned riders.
- Idempotent payment webhook event tracking with a unique `event_id`.
- Optional webhook amount verification against the recorded payment.
- Feature tests covering webhook duplication, amount mismatch, and unrelated-rider order access.

The implementation remains provider-neutral. Live GCash, PayPal, card, maps, Firebase, and WebSocket credentials/services still require configuration before production use.
