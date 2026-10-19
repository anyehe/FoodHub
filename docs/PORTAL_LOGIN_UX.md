# FoodHub Dedicated Login UX

## Purpose

FoodHub staff workspaces now use a dedicated landing/login page instead of rendering the workspace sidebar and dashboard before authentication.

## Login routes

- `/login/admin` → Admin workspace → `/admin`
- `/login/branch` → Branch Manager workspace → `/branch`
- `/login/kitchen` → Kitchen workspace → `/kitchen`
- `/login/customer` → Customer account → `/customer`

## Workspace behavior

1. User selects a role from `/portal`.
2. The role-specific login page displays only the FoodHub branding, role description, credentials form, security notice, and portal return link.
3. Successful authentication validates the API-returned role before storing the role-specific token.
4. The browser is redirected to the dedicated workspace.
5. Workspace pages no longer contain their login form.
6. If a workspace has no valid token, it redirects back to its dedicated login page.
7. Logout clears the role-specific token and returns to the same login page.

## Separation

- Customer uses the customer website for catalog, cart, checkout, orders, and tracking.
- Admin has a dedicated platform administration shell.
- Branch Manager has a dedicated branch shell with POS, branch orders, and kitchen coordination.
- Kitchen has a dedicated kitchen display shell.
- Rider operations remain mobile-only through the Flutter app.

This is an original FoodHub UX/architecture implementation. It uses role separation as a product design principle without copying another vendor's source code, branding, or exact interface.
