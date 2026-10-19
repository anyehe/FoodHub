# FoodHub Final Architecture

```text
                        ┌──────────────────────────┐
                        │       FoodHub Web        │
                        │ Bootstrap 5 / Blade     │
                        └────────────┬─────────────┘
                                     │ REST/JSON
                        ┌────────────▼─────────────┐
                        │       Laravel 12 API     │
                        │ Auth / Orders / Catalog  │
                        │ Payments / Delivery      │
                        │ Reports / POS / QR       │
                        │ Digital Twin             │
                        └──────┬─────────┬─────────┘
                               │         │
                 ┌─────────────┘         └──────────────┐
                 ▼                                      ▼
        ┌───────────────────┐                  ┌───────────────────┐
        │      MySQL        │                  │ Flutter Apps      │
        │ Commerce state    │                  │ Customer / Rider  │
        │ Audit history     │                  │ REST client       │
        │ Notifications     │                  └───────────────────┘
        └───────────────────┘
                 │
                 ▼
        ┌───────────────────┐
        │ Digital Twin      │
        │ snapshot + what-if│
        │ capacity model    │
        └───────────────────┘
```

## Order lifecycle

`pending -> confirmed -> preparing -> ready -> assigned -> picked_up -> out_for_delivery -> delivered -> completed`

Alternative terminal state: `cancelled`.

Payment lifecycle is independent:

`unpaid -> pending -> paid`

with failure/refund states available.

## Roles

- `admin`
- `branch_manager`
- `kitchen`
- `waiter`
- `rider`
- `customer`

## Operational surfaces

- Operations dashboard
- Admin control center
- Kitchen screen
- Rider screen
- Customer web/app
- POS API
- QR dine-in entry
- Reports
- Digital Twin

## Production integrations

The application provides the abstraction points for payment gateways, push notifications and mapping/location services. Provider credentials are intentionally not hard-coded. Real gateway credentials and production webhook URLs must be supplied before live transactions are enabled.
