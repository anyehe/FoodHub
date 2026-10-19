# FoodHub API Quick Reference

Base URL: `/api/v1`

## Public

- `GET /health`
- `POST /auth/register`
- `POST /auth/login`
- `GET /branches`
- `GET /categories`
- `GET /products?search=burger&branch_id=1`
- `GET /products/{id}`
- `POST /checkout/quote`
- `GET /dine-in/tables/{qr_token}`

## Authenticated customer

Send:

`Authorization: Bearer <token>`

- `GET /me`
- `POST /auth/logout`
- `GET|POST|PUT|DELETE /addresses`
- `GET|POST /orders`
- `GET /orders/{id}`
- `POST /orders/{id}/cancel`
- `POST /reviews`
- `POST /dine-in/orders`

## Staff

- `GET /staff/dashboard`
- `GET /staff/orders?status=pending`
- `POST /staff/orders/{id}/transition`
- `POST /staff/orders/{id}/assign-rider`

## Admin

- `GET /admin/dashboard`
- `GET|POST|PUT|DELETE /admin/branches`
- `GET|POST|PUT|DELETE /admin/categories`
- `GET|POST|PUT|DELETE /admin/products`
- `GET /admin/users`
- `GET /admin/inventory`
- `POST /admin/inventory/{id}/adjust`
- `GET /admin/tables`
- `PUT /admin/tables/{id}`
- `GET /admin/coupons`

## Rider

- `GET /rider/queue`
- `POST /rider/assignments/{id}/accept`
- `POST /rider/assignments/{id}/pickup`
- `POST /rider/assignments/{id}/location`
- `POST /rider/assignments/{id}/deliver`

## Order state machine

```text
pending
  -> confirmed
  -> preparing
  -> ready
  -> assigned
  -> picked_up
  -> out_for_delivery
  -> delivered
  -> completed
```

Cancellation is allowed from pending/confirmed/assigned according to the service rules.

## Phase 2 administration

Admin-only endpoints:

- `GET /admin/orders`
- `GET /admin/orders/{id}`
- `POST /admin/orders/{id}/transition`
- `GET|POST|PUT|DELETE /admin/branches`
- `PUT /admin/branches/{branch}/products/{product}`
- `GET|POST|PUT|DELETE /admin/categories`
- `GET|POST|PUT|DELETE /admin/products`
- `GET|POST /admin/products/{product}/options`
- `DELETE /admin/product-options/{option}`
- `GET|PUT /admin/users`
- `GET|POST /admin/inventory`
- `POST /admin/inventory/{id}/adjust`
- `GET|PUT /admin/tables`
- `GET|POST|PUT|DELETE /admin/coupons`

Rider delivery now uses the complete handoff sequence:

```text
accepted -> picked_up -> out_for_delivery -> delivered
```

Order status history is returned as `status_history` on order detail/list responses.
