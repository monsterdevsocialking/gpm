# GPM Conecta — safe Laravel migration runbook

This guide is for the first local migration rehearsal. It deliberately keeps the
PHP application and its original database available until the Laravel version has
been verified.

## 1. Keep the source untouched

- Keep `gpm2/` as the reference implementation.
- Keep `gpm_mvp_nova.sql` as the source snapshot.
- Never run `php artisan migrate:fresh`, `db:wipe`, or a destructive reset against
  the original database.
- Do not point Laravel at a production database for the first rehearsal.

## 2. Create a separate MySQL database

Create a new empty database in MySQL/MariaDB, for example `gpm_conecta`, with
`utf8mb4` and `utf8mb4_unicode_ci`. Import a copy of `gpm_mvp_nova.sql` into
that database using phpMyAdmin or the MySQL command line.

The SQL dump itself creates the legacy tables and includes sample records. It
must be imported before the first Laravel migration rehearsal.

## 3. Configure Laravel

Copy `.env.example` to `.env` and set the local MySQL credentials:

```dotenv
APP_NAME="GPM Conecta"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Africa/Luanda

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gpm_conecta
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Then install dependencies and generate the application key:

```bash
composer install
php artisan key:generate
```

## 4. Rehearse the additive Laravel migrations

The first Laravel users migration checks whether the legacy `users` table already
exists. If it does, Laravel leaves that table intact and only creates the framework
support tables that are missing.

Run:

```bash
php artisan migrate:status
php artisan migrate
php artisan migrate:status
```

This is not permission to run destructive migration commands. Review every new
migration before applying it to any database containing important data.

## 5. Reconcile the legacy data

Run these checks in phpMyAdmin after import and again after any data transformation:

```sql
SELECT 'users' AS table_name, COUNT(*) AS total FROM users
UNION ALL SELECT 'customers', COUNT(*) FROM customers
UNION ALL SELECT 'categories', COUNT(*) FROM categories
UNION ALL SELECT 'products', COUNT(*) FROM products
UNION ALL SELECT 'orders', COUNT(*) FROM orders
UNION ALL SELECT 'order_items', COUNT(*) FROM order_items
UNION ALL SELECT 'proformas', COUNT(*) FROM proformas
UNION ALL SELECT 'conversations', COUNT(*) FROM conversations
UNION ALL SELECT 'messages', COUNT(*) FROM messages
UNION ALL SELECT 'stock_movements', COUNT(*) FROM stock_movements;
```

Also verify that order totals, order item snapshots, proforma numbers, customer
ownership, stock quantities, and message sender references remain unchanged.

## 6. Current Laravel compatibility layer

Eloquent models now map to the existing legacy tables without creating or changing
those tables:

- Staff: `User`
- Customer accounts and addresses: `Customer`, `CustomerAddress`
- Catalog: `Category`, `Product`, `ProductImage`
- Orders and history: `Order`, `OrderItem`, `OrderStatusHistory`
- Proformas: `Proforma`
- Conversations: `Conversation`, `Message`
- Inventory: `StockMovement`
- Company configuration and contact: `CompanySetting`, `ContactMessage`
- Audit: `ActivityLog`

These models are a compatibility layer, not proof that all legacy pages have been
migrated. Controllers, authorization, business actions, views, and feature tests
still need to be implemented and verified.

## 7. New supply-request tables

After the legacy SQL snapshot has been imported into the cloned database, the
Laravel migrations add:

- `quote_requests`: one customer supply request, its reference, contact snapshot,
  notes, submission date and workflow status.
- `quote_request_items`: requested product snapshots, quantities, units and
  per-item observations. The product foreign key becomes nullable if a catalog
  product is later removed, while the requested description remains preserved.
- `quote_request_status_history`: who changed the stage, previous/new status,
  timestamp and note.

Submitting the cart creates a supply request only. It does not create an entry in
legacy `orders`, does not create a proforma, does not charge a payment method and
does not deduct stock. The initial and subsequent status events are audited.

## 7. Cutover criteria

Do not switch the public site to Laravel until:

1. Catalog and product images match the PHP version.
2. Customer registration, login, account ownership, cart and order submission work.
3. Staff roles and server-side authorization match or improve on the old system.
4. Order confirmation, stock deduction and movement history reconcile correctly.
5. Proformas, conversations, messages and customer history are preserved.
6. Automated tests and a manual end-to-end rehearsal pass on a database copy.
7. A fresh backup and rollback procedure are ready.
