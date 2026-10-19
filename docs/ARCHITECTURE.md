# FoodHub Architecture

```text
Browser / Flutter Android
          |
       REST API
          |
       Laravel 12
  +-------+--------+
  |       |        |
Auth   Services   Policies/Middleware
  |       |
  |   OrderService -> PricingService
  |       |
  +---- MySQL -----+
          |
   Orders / Catalog / Branches
   Riders / Inventory / Tables
   Payments / Reviews / Coupons
```

## Design principles

1. Controllers validate and orchestrate requests.
2. Business rules that affect money/order state live in services.
3. Orders snapshot product names, prices and selected options so historical orders remain understandable after catalog changes.
4. Branch pricing and availability are separate from global products.
5. Sanctum provides token authentication for Flutter and API clients.
6. Role middleware protects staff/admin/rider surfaces.
7. External integrations are isolated behind configuration/service boundaries.
8. The browser portal and Flutter application use the same API rather than maintaining duplicate business logic.

## Operations layer added in Phase 2

The web application now exposes role-oriented browser surfaces that consume the same REST API:

- `/admin` — administrator control center
- `/dashboard` — staff operations dashboard
- `/kitchen` — kitchen display workflow
- `/rider` — rider delivery console
- `/app` — customer browser portal

Order state changes are recorded in `order_status_histories`, providing an auditable timeline for customer tracking and staff/admin operations.
