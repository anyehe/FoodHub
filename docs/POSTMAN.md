# Postman / REST Client Test Sequence

1. `POST /api/v1/auth/login`
   ```json
   {"email":"customer@foodhub.test","password":"password"}
   ```
2. Save `token` from the response.
3. `GET /api/v1/branches`
4. `GET /api/v1/products`
5. `GET /api/v1/addresses` with Bearer token.
6. `POST /api/v1/orders` with branch/address/product IDs.
7. Login as kitchen and move the order `pending -> confirmed -> preparing -> ready`.
8. Login as manager/admin and assign the seeded rider.
9. Login as rider and `accept -> pickup -> location -> deliver`.
10. Login as customer and `GET /api/v1/orders`.
