# FoodHub Portal Separation

## Purpose

FoodHub now uses separate web entry points for each web role instead of presenting one shared operational login experience.

## Web workspaces

| Role | Web entry | Authentication | Scope |
|---|---|---|---|
| Customer | `/customer` | Customer account | Catalog, cart, checkout, orders and tracking |
| Super Admin | `/admin` | `admin` role only | Platform administration |
| Branch Manager | `/branch` | `branch_manager` role + assigned branch | Branch operations |
| Kitchen | `/kitchen` | `kitchen` role only | Kitchen queue |
| Waiter / POS | `/waiter` | `waiter` role + assigned branch | Dine-in/POS |
| Rider | Mobile app only | Flutter rider account | Delivery assignments and GPS |

`/portal` is now a role selector and does not perform a shared login. Convenience login URLs redirect to the dedicated workspace: `/login/customer`, `/login/admin`, `/login/branch`, `/login/kitchen`, and `/login/waiter`.

## Session separation

Each browser workspace uses a distinct local-storage token key:

- `fh_customer_token`
- `fh_admin_token`
- `fh_branch_token`
- `fh_kitchen_token`
- `fh_waiter_token`

On login, the web workspace clears other FoodHub role-session keys from that browser. Each workspace also validates `/api/v1/me` before restoring a saved session. This prevents a stale or wrong-role token from displaying the wrong dashboard.

## Customer experience

The customer workspace keeps customer-only state together:

- cart
- saved addresses
- checkout
- coupon quote
- order history
- order status history
- delivery tracking

Staff workspaces do not render customer cart or customer order history.

## Rider experience

Riders do not have a browser operational console. `/rider` is an informational mobile-only page and contains no rider web login. Rider authentication and delivery operations are handled by the Flutter application.

The Flutter login now accepts only `customer` and `rider` roles. Staff roles are directed to the dedicated FoodHub web portal instead of being opened inside the customer mobile experience.

## Login restoration

The Branch Manager portal previously displayed its login form after a page refresh even when a valid branch token existed. It now validates the saved token through `/api/v1/me`, restores the dashboard when valid, and returns to the login form only when the session is invalid.

Kitchen and customer sessions also validate the saved role before restoring their workspaces.
