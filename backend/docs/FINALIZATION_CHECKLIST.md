# FoodHub Finalization Checklist

Before treating the project as finished, freeze new features and complete validation in this order:

1. **UI consistency** — verify customer, admin, branch/POS, kitchen and login/portal screens against the approved FoodHub visual direction.
2. **Navigation smoke test** — click every sidebar, top-bar, portal and logout link; verify every destination and active state.
3. **Authentication/authorization** — test each role independently and confirm staff cannot access another staff workspace by URL or token.
4. **Order lifecycle** — test customer checkout through branch/kitchen processing, rider assignment, delivery/completion, history and notifications.
5. **Payments** — validate cash plus sandbox credentials for GCash/card/PayPal before enabling any live gateway.
6. **Database** — run migrations on a clean database, seed only intended demo data, and verify foreign keys and indexes.
7. **Production configuration** — set `APP_ENV=production`, `APP_DEBUG=false`, strong `APP_KEY`, webhook secret, real gateway credentials and HTTPS.
8. **Backups and logs** — establish scheduled database backups, storage backups and log rotation before deployment.
9. **Mobile API** — verify Flutter login, catalog, cart, checkout, order tracking, rider assignment/location and push-notification flows against the same Laravel API.
10. **Documentation** — keep setup, deployment, role credentials, payment setup, webhook setup and troubleshooting instructions with the final release.
11. **Release freeze** — after these checks pass, tag/version the build and avoid adding new features until the first production validation cycle is complete.
