# FoodHub Build Workflow and Architecture Walkthrough

This document explains the practical order in which the application is built and how a request travels through the system.

## A. Build order

```text
1. Environment
   ↓
2. Laravel foundation
   ↓
3. Database migrations
   ↓
4. Models + relationships
   ↓
5. Authentication + roles
   ↓
6. Catalog + branches
   ↓
7. Cart + pricing
   ↓
8. Checkout + orders
   ↓
9. Kitchen + rider operations
   ↓
10. Payments + notifications
   ↓
11. POS + QR + reports
   ↓
12. Digital Twin
   ↓
13. Flutter clients
   ↓
14. UI/UX polish
   ↓
15. Tests + security + deployment
```

## B. Example: customer creates a delivery order

```text
Browser / Flutter
      |
      | POST /api/v1/orders
      v
Route
      |
      v
Authentication middleware
      |
      v
Controller
      |
      v
OrderService
      |
      +--> validate branch/product availability
      +--> validate quantities/options
      +--> validate coupon
      +--> calculate price
      +--> calculate tax/delivery fee
      +--> create order
      +--> create order items
      +--> create status history
      +--> create payment record
      |
      v
MySQL transaction
      |
      v
JSON response
      |
      v
Client updates UI
```

## C. Example: kitchen advances an order

```text
Kitchen screen
   |
   | authenticated staff request
   v
Staff middleware
   |
   v
Order workflow endpoint
   |
   v
Validate allowed transition
   |
   v
Update orders.status
   |
   v
Insert order_status_histories row
   |
   v
Create order notification
   |
   v
Customer sees updated timeline
```

## D. Example: rider delivery

```text
Ready order
   |
   v
Staff assigns rider
   |
   v
DeliveryAssignment
   |
   v
Rider accepts
   |
   v
Order -> assigned
   |
   v
Rider pickup
   |
   v
Order -> picked_up
   |
   v
Rider location updates
   |
   +--> latest location
   +--> location history
   |
   v
Out for delivery
   |
   v
Delivered
```

## E. How to add a new feature

When adding a feature, use this sequence:

1. Define the business rule.
2. Decide which role can use it.
3. Add/modify the database migration if persistent data is required.
4. Add or update the Eloquent model.
5. Put reusable business logic in a service.
6. Add API routes/controllers.
7. Add middleware/authorization.
8. Add web UI and/or Flutter UI.
9. Add a feature test.
10. Update documentation.

Avoid placing large amounts of business logic directly inside Blade JavaScript or Flutter widgets. Keep the server authoritative.

## F. Definition of done

A FoodHub feature is considered complete when:

- its data model is correct;
- authorization is enforced;
- API behavior is defined;
- the intended client UI is connected;
- validation/error states exist;
- important transitions are auditable;
- tests cover the main success path;
- documentation explains how to operate it;
- production-only dependencies are clearly identified.


## Runtime release gate

Before production deployment, execute on the real XAMPP/MySQL environment:

```bash
cd C:\xampp\htdocs\FoodHub\backend
composer install
php artisan optimize:clear
php artisan migrate
php artisan test
php artisan route:list
php artisan foodhub:audit-prune
php artisan serve
```

Then verify `/api/v1/health`, customer authentication, checkout, order lifecycle, rider workflow, payment webhook signing, admin audit logs, and branch isolation using a disposable test database. Do not use `migrate:fresh` on a database containing data.
