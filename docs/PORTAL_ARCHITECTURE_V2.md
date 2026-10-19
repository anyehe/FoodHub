# FoodHub Portal Architecture v2

FoodHub uses separate web workspaces rather than one shared dashboard. The customer website remains the public ordering surface, while staff functions are isolated by purpose and role.

## Workspaces

- `/` `/menu` `/product/*` `/customer` — customer website and ordering flow
- `/admin` — platform administration only
- `/branch` — branch manager workspace, including branch POS
- `/kitchen` — kitchen display/work queue only
- `/rider` — web rider access intentionally disabled; riders use the Flutter mobile app
- `/portal` — workspace selector only

The legacy `/waiter` route redirects to `/branch#pos` so POS is not a second staff website.

## Design principle

The separation is inspired by the general multi-workspace pattern documented for modern restaurant systems such as 6amTech eFood: customer website/app, admin panel, branch panel, kitchen, table/POS and delivery surfaces can have different purposes. FoodHub uses its own Laravel/Bootstrap implementation, naming, layout, authentication keys and workflows rather than copying proprietary source code or UI.

6amTech's current eFood listing describes a multi-branch restaurant solution with separate admin and branch panels, customer/delivery applications, website, built-in POS, QR ordering and kitchen/table-related components. citeturn0search1turn0search9

## Security boundaries

1. Customer session is not reused by staff portals.
2. Admin has a platform-level shell and role check.
3. Branch manager sessions are scoped to the assigned branch.
4. POS is available inside the authenticated branch workspace and the API still enforces the user's branch.
5. Kitchen has its own session key and dedicated workspace.
6. Riders do not receive a web operations workspace; delivery operations are mobile-first.

## User experience

A customer who visits FoodHub sees only customer ordering/navigation. Staff do not need to enter through the customer website. Direct role URLs open the correct workspace, and the portal selector is only a discovery/entry page.
