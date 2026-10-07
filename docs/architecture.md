# Architecture and implementation plan

## 1. Tenant model

Every tenant is represented by a `Shop`. Users can access one or many shops through `shop_user`. Ownership is not inferred from the request body. The server resolves the current shop from the authenticated user and the shop membership relationship.

## 2. Authorization pattern

- `ShopPolicy` checks whether the user belongs to the shop.
- `ProductPolicy`, `SupplierPolicy`, and `SalesPolicy` extend the same rule using the tenant-scoped relation.
- All write routes must reject a client-supplied `shop_id` field if it does not match the authenticated shop.

## 3. Data model

Core entities and ownership:

- `users` — application users
- `shops` — tenant container
- `shop_user` — membership relation
- `products` — tenant-owned catalog items
- `categories` — shop-owned taxonomy
- `inventory_transactions` — immutable ledger
- `sales` — POS transactions
- `sale_items` — line items
- `suppliers` — shop-owned contacts
- `purchase_orders` — supplier orders
- `purchase_order_items` — order line items
- `notifications` — store-wide alerts
- `device_tokens` — mobile push registration
- `product_images` — image metadata and storage keys
- `ai_product_recognition_logs` — audit metadata for AI extraction

## 4. Security rules

- Use Sanctum tokens for API auth.
- Validate all request payloads with Form Request classes.
- Use mass-assignment protection via `$fillable`.
- Never trust `shop_id` from clients.
- Use database transactions for stock adjustment and sale creation.
- Store only file references for images; images are kept on the configured disk, not DB blobs.

## 5. API route approach

- Versioned under `/api/v1`
- API controllers use resources for consistent JSON payloads.
- Requests are routed through middleware that automatically resolves the active shop and enforces policies.

## 6. External integration map

- AI: provider abstraction + queued processing
- Messaging: WhatsApp provider abstraction + SMS provider abstraction
- Notifications: FCM integration through jobs
- Storage: local in dev, S3-compatible in production
- Cache and queues: Redis
- Database: PostgreSQL in production, SQLite in testing

## 7. Recommended next implementation steps

1. Add `shops`, `shop_user`, `products`, and `inventory_transactions` migrations.
2. Add `ShopScoped` behavior for queries.
3. Implement secure auth and shop selection.
4. Add product APIs and inventory ledger tests.
5. Add sales and purchase order flows.
6. Add notification and message abstractions.
7. Add Docker, web dashboard, and Flutter app integration.
