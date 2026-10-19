# FoodHub Feature Matrix

| Area | Status | Notes |
|---|---|---|
| Laravel 12 backend | Implemented | Standard Laravel structure |
| Sanctum authentication | Implemented | Customer/staff/rider tokens |
| Bootstrap website | Implemented | Responsive FoodHub branding |
| Customer browser portal | Implemented | Login, catalog, cart, checkout |
| Flutter customer app | Implemented source | Android platform generated with installed Flutter SDK |
| Branches | Implemented | Multi-branch data model and API |
| Categories/products | Implemented | CRUD foundations and branch availability |
| Product options | Implemented | Snapshotted into order items |
| Delivery/pickup/dine-in | Implemented | Order fulfillment type |
| Scheduled order | Implemented | Stored on order |
| Coupons | Implemented | Fixed/percent, dates, usage limit |
| Pricing/tax/delivery | Implemented | Central PricingService |
| Payments | Demo/adapter-ready | Cash works in local flow; external gateways require credentials |
| Kitchen workflow | Implemented | Staff order transitions |
| Rider workflow | Implemented | Assignment, accept, pickup, location, deliver |
| Live location storage | Implemented | Latest coordinates stored; realtime broadcasting requires provider |
| Reviews | Implemented | Post-delivery validation |
| Inventory | Implemented foundation | Admin API and low-stock KPI |
| POS | Data/API foundation | Full hardware/payment terminal integration requires site-specific setup |
| QR dine-in | Data foundation | Table QR tokens and dine-in order type |
| Notifications | Integration boundary | FCM credentials required |
| Maps/navigation | Integration boundary | Maps provider key required |
| Analytics | Implemented core KPIs | Can be extended with reporting jobs |
| Automated E2E | Implemented | Customer -> kitchen -> rider -> delivery |
| Admin browser control center | Implemented | Orders, branches, menu, people, inventory, tables and coupons |
| Kitchen browser console | Implemented | Confirmed -> preparing -> ready |
| Rider browser console | Implemented | Accept -> pickup -> out for delivery -> delivered |
| Order status history | Implemented | Auditable timeline stored in MySQL |
| Customer order tracking timeline | API ready | `status_history` is returned; UI can be expanded next |

## Phase 4 additions

| Area | Phase 4 status |
|---|---|
| Digital Twin operational snapshot | Implemented |
| Digital Twin scenario comparison | Implemented |
| Advanced analytics | Implemented |
| FoodHub wallet ledger | Implemented |
| Wallet checkout/refund path | Implemented |
| Loyalty points and tiers | Implemented |
| Loyalty redemption | Implemented |
| Promotion campaigns | Implemented |
| Audit/report expansion | Implemented |
| Flutter wallet surface | Implemented |
| Flutter promotions surface | Implemented |
