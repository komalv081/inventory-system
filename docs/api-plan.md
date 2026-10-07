# API endpoint plan

## Auth

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

## Products

- `GET /api/v1/products`
- `POST /api/v1/products`
- `GET /api/v1/products/{id}`
- `PUT /api/v1/products/{id}`
- `DELETE /api/v1/products/{id}`
- `POST /api/v1/products/scan`
- `POST /api/v1/products/recognize`

## Inventory

- `GET /api/v1/inventory`
- `POST /api/v1/inventory/adjust`
- `GET /api/v1/inventory/history`

## Sales

- `GET /api/v1/sales`
- `POST /api/v1/sales`
- `GET /api/v1/sales/{id}`

## Suppliers

- `GET /api/v1/suppliers`
- `POST /api/v1/suppliers`
- `GET /api/v1/suppliers/{id}`
- `PUT /api/v1/suppliers/{id}`

## Purchase orders

- `GET /api/v1/purchase-orders`
- `POST /api/v1/purchase-orders`
- `GET /api/v1/purchase-orders/{id}`
- `PATCH /api/v1/purchase-orders/{id}/send`

## Notifications

- `GET /api/v1/notifications`
- `POST /api/v1/device-tokens`
- `DELETE /api/v1/device-tokens/{id}`

## Security and conventions

- All routes are protected by Sanctum.
- All writes validate `shop_id` server-side.
- 403/404 responses are returned for cross-tenant access attempts.
- Responses are returned as JSON with clear error payloads.
