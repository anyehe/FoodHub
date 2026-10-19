# FoodHub Priority Phases

## Phase 0 — Role and portal separation
- Customer ordering portal
- Super Admin portal
- Branch Manager portal
- Kitchen portal
- Rider portal
- Waiter/POS portal
- Separate browser token keys and role validation
- Public navigation does not expose internal operations as one shared dashboard

## Phase 1 — Runtime completion
- Composer/vendor installation
- MySQL migrations and seed verification
- PHPUnit suite
- API endpoint smoke tests
- Authentication/authorization tests
- Order lifecycle tests

## Phase 2 — Real integrations
- Firebase push notifications
- Maps/GPS
- WebSockets/realtime
- Payment providers
- Email/SMS

## Phase 3 — Application completion
- Customer Flutter app
- Rider Flutter app
- Checkout/order tracking
- Live rider location
- Dine-in QR/POS completion

## Phase 4 — Advanced operations
- Digital Twin expansion
- Analytics
- Loyalty/wallet
- Promotions
- Audit/reporting expansion

## Phase 5 — Production
- Load testing
- Security review
- Backup/restore
- Deployment
- Monitoring
- Final acceptance testing

## Phase 4 implementation notes

Phase 4 is implemented in `docs/PHASE4_ADVANCED_OPERATIONS.md`. The remaining work is runtime acceptance, provider-specific business configuration, and later production hardening rather than another structural placeholder phase.
