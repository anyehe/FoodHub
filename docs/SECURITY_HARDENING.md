# FoodHub Security & Integration Hardening

## Purpose

This phase strengthens the existing FoodHub platform without changing its core architecture. The focus is on API abuse protection, authorization boundaries, and payment webhook safety.

## 1. API rate limiting

The `/api/v1` route group now uses Laravel's request throttle middleware at **60 requests per minute per client**.

This provides a baseline protection against accidental request storms and simple API abuse. Production deployments can replace this with a Redis-backed limiter and different limits for login, catalog, checkout, rider location, and webhook endpoints.

## 2. Order authorization

Order details are no longer treated as staff-readable simply because a user is not a customer. The order-detail endpoint now permits:

- the order's customer;
- administrators;
- branch managers;
- kitchen staff;
- waiters;
- the rider assigned to that order.

An unrelated rider cannot retrieve another rider's order by guessing an order ID.

## 3. Payment webhook idempotency

Payment webhooks now accept an `event_id`. The event is stored in `payment_webhook_events` with a unique database constraint.

If the same event is delivered again, FoodHub returns a duplicate response without applying the payment update a second time.

### Example

```http
POST /api/v1/payments/webhook
X-FoodHub-Signature: <configured-secret>
Content-Type: application/json
```

```json
{
  "event_id": "evt_20260924_0001",
  "provider": "gcash",
  "order_id": 15,
  "status": "paid",
  "reference": "GCASH-123456",
  "amount": 597.60,
  "payload": {
    "provider_status": "SUCCESS"
  }
}
```

The optional `amount` is checked against the recorded payment amount when present.

## 4. Webhook signature

`FOODHUB_WEBHOOK_SECRET` is used for the internal webhook signature header `X-FoodHub-Signature`.

For a real provider integration, the provider's official signature scheme should be implemented in a provider-specific adapter rather than exposing a generic shared-secret endpoint publicly.

## 5. Payment gateway boundary

FoodHub deliberately does not pretend that GCash, PayPal, or card payments are live merely because their names appear in the payment method list.

The current service exposes a provider-neutral payment lifecycle:

```text
pending -> paid
pending -> failed
paid -> refunded
```

Live gateway credentials and provider SDK/API calls must be configured before production payments are enabled.

## 6. Production recommendations

Before public deployment:

1. Set `APP_ENV=production`.
2. Set `APP_DEBUG=false`.
3. Use a strong `APP_KEY`.
4. Use a dedicated MySQL account instead of the local `root` account.
5. Configure HTTPS.
6. Store secrets outside source control.
7. Use Redis for cache/queue/rate limiting where appropriate.
8. Configure real payment provider signatures.
9. Add audit logs for administrative actions.
10. Restrict CORS to the actual FoodHub web/app domains.
11. Add automated backup and restore testing.
12. Monitor failed authentication, payment, and webhook events.

## 7. What was actually implemented in this phase

Implemented in the project source:

- API throttling on `/api/v1`.
- More restrictive order-detail authorization.
- Payment webhook event storage.
- Unique webhook event IDs.
- Duplicate webhook detection.
- Optional webhook amount verification.
- Documentation of the payment/integration boundary.

These changes are intentionally integration-ready rather than falsely claiming live third-party credentials or production provider connectivity.


## Additional pre-release hardening

The pre-release build now includes: 

- **Audit logging** for authenticated staff/admin mutating API requests, with password/secret fields excluded from captured request payloads.
- **Admin audit-log endpoint** at `/api/v1/admin/audit-logs`, protected by the admin role.
- **Audit retention command**: `php artisan foodhub:audit-prune`, controlled by `AUDIT_LOG_RETENTION_DAYS`.
- **Authentication throttling** at 10 requests/minute for registration and login, in addition to the global API throttle.
- **Database-aware health endpoint**: `/api/v1/health` returns HTTP 503 when the database is unavailable instead of reporting a false healthy state.
- **Staff permission hardening** so rider assignment is limited to administrators and branch managers.

These changes still require real Laravel/MySQL execution on the target XAMPP environment. Static validation does not replace runtime verification.
