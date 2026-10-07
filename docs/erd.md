# Database ERD plan

```mermaid
erDiagram
    USERS ||--o{ SHOP_USER : belongs_to
    SHOPS ||--o{ SHOP_USER : has_members
    SHOPS ||--o{ PRODUCTS : owns
    SHOPS ||--o{ SUPPLIERS : owns
    SHOPS ||--o{ PURCHASE_ORDERS : owns
    SHOPS ||--o{ SALES : owns
    SHOPS ||--o{ INVENTORY_TRANSACTIONS : owns
    SHOPS ||--o{ NOTIFICATIONS : owns
    SHOPS ||--o{ PRODUCT_IMAGES : owns
    SHOPS ||--o{ AI_PRODUCT_RECOGNITION_LOGS : owns

    USERS ||--o{ SALES : creates
    USERS ||--o{ INVENTORY_TRANSACTIONS : creates
    USERS ||--o{ PURCHASE_ORDERS : creates
    USERS ||--o{ NOTIFICATIONS : receives

    PRODUCTS ||--o{ INVENTORY_TRANSACTIONS : logs
    PRODUCTS ||--o{ SALE_ITEMS : appears_in
    PRODUCTS ||--o{ PURCHASE_ORDER_ITEMS : requested_on
    SALES ||--o{ SALE_ITEMS : contains
    PURCHASE_ORDERS ||--o{ PURCHASE_ORDER_ITEMS : contains
    SUPPLIERS ||--o{ PURCHASE_ORDERS : supplies
```

Key constraints:

- `products` uses `unique(shop_id, barcode)` when barcode is enabled.
- `shop_user` uses a composite unique constraint on `(shop_id, user_id)`.
- `inventory_transactions` is append-only, never updated once created.
- Sales and purchase orders are transactional and can be audited.
