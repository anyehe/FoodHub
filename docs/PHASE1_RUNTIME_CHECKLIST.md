# FoodHub Phase 1 — Runtime Completion Checklist

Phase 1 is deliberately focused on **runtime correctness before adding new features**.

## Local XAMPP procedure

From `C:\xampp\htdocs\FoodHub\backend`:

```bat
composer install
php artisan optimize:clear
php artisan migrate
php artisan test
php artisan route:list
php artisan serve
```

Or use:

```bat
scripts\phase1-runtime-check.bat
```

The helper stops when dependencies are missing or a Laravel command/test fails. It does not wipe the database.

## Manual smoke test

After `php artisan serve`:

1. `/` — public FoodHub homepage
2. `/menu` — public menu
3. `/app` — customer ordering portal
4. `/portal` — role portal selector
5. `/admin` — admin portal
6. `/branch` — branch manager portal
7. `/kitchen` — kitchen portal
8. `/rider` — rider portal
9. `/waiter` — waiter/POS portal
10. `/reports` — reporting workspace for admin/branch manager
11. `/digital-twin` — Digital Twin workspace for admin/branch manager
12. `/dashboard` — legacy route; should redirect to `/portal`

## Role separation checks

- Customer credentials must be rejected by `/admin`, `/branch`, `/kitchen`, `/rider`, and `/waiter`.
- Admin credentials must not be accepted as a branch-manager session.
- Branch manager credentials must have a `branch_id`.
- Kitchen credentials must remain in the kitchen role.
- Rider credentials must remain in the rider role.
- Waiter credentials must remain in the waiter role and have a `branch_id`.
- A branch manager must not see another branch's orders/reports/Digital Twin data.

## Important development behavior

Seed credentials are retained only for automated development tests. They are **not pre-filled in the browser login forms**. Replace or remove seeded credentials before production deployment.

## Simulation requirement

After every source change, run from the project root:

```bat
php scripts/audit-project.php
php scripts/simulate-platform.php
```

The deterministic simulation is a safety gate, not a substitute for Laravel/MySQL runtime tests.
