# GPM Conecta — Legacy schema inventory

Source inspected: `gpm_mvp_nova.sql` in the repository root. This is an inventory of
the current PHP application's database, not yet the final Laravel schema.

## Existing tables (15)

| Legacy table | Current purpose inferred from columns and PHP usage | Migration direction |
| --- | --- | --- |
| `users` | Internal staff accounts with role, active flag and password | Migrate to staff users; retain role history and password hashes safely |
| `customers` | Individual/company customer records and login data | Split business identity into Companies and memberships where required; preserve individual customer support only if still needed |
| `customer_addresses` | Multiple customer delivery/contact addresses | Associate with the appropriate company/customer owner |
| `categories` | Product categories | Catalog category |
| `products` | Catalog, prices, stock, unit, SKU and images | Catalog product; separate stock policy from commercial proposal prices |
| `product_images` | Additional product images | Catalog media |
| `orders` | Existing customer order with customer snapshot, totals, payment and delivery fields | Do not map blindly to new QuoteRequest; inspect every legacy order status and workflow first |
| `order_items` | Existing order lines with product snapshots and values | Preserve snapshots; map according to the originating commercial process |
| `order_status_history` | Existing order status audit | Retain as historical event data |
| `proformas` | Existing proforma document linked to an order, with totals and validity | Candidate source for Proposal/ProposalVersion, but it is not yet a complete versioned proposal model |
| `conversations` | One conversation per order, assigned staff and customer | Conversation linked to a commercial process |
| `messages` | Customer/staff messages | Conversation messages; preserve sender attribution |
| `stock_movements` | Stock entry/exit history and order association | Inventory ledger, independent from proposal/payment logic |
| `activity_log` | Staff activity log with entity references | Activity/audit log |
| `company_settings` | Single GPM company profile and currency/delivery defaults | Platform settings, not a customer company |

## Relationships found in SQL

- `customers` → `customer_addresses`
- `customers` → `orders` → `order_items`
- `orders` → `order_status_history`
- `orders` → `conversations` → `messages`
- `orders` → `proformas`
- `categories` → `products` → `product_images`
- `products` → `stock_movements`
- `users` is referenced by activity, conversations, messages, order history,
  proformas and stock movements.

The SQL dump contains explicit foreign keys and indexes. Preserve these constraints
in the Laravel migrations, adjusting delete behavior only after data ownership and
retention rules are approved.

## Gaps versus the target commercial workflow

The current schema does not yet provide dedicated entities for:

- Supplier companies and supplier contacts.
- Supplier inquiries and supplier responses.
- Supply requests separate from confirmed orders.
- Commercial proposals with immutable numbered versions.
- Customer decisions tied to the exact proposal version.
- Explicit staff confirmation after customer acceptance.
- Payment attempts, provider references, verified webhook events and refunds.
- Company-to-user membership with company-level authorization.

## Migration safeguards

1. Back up the original database before any migration or transformation.
2. Do not drop, rename or repurpose legacy tables during the first migration phase.
3. Preserve original primary keys where practical and record legacy IDs in the
   migration mapping when a new domain entity is introduced.
4. Preserve password hashes only when compatible; never export or expose them in
   documentation, logs or commits.
5. Keep legacy order totals, item descriptions, SKU, unit and customer snapshots.
6. Reconcile counts and representative records before switching traffic.
7. Do not create production migrations until the complete PHP workflow and SQL
   constraints have been reviewed against the approved business rules.

## Known business distinction

The legacy app contains orders, payment status and proformas. The target GPM Conecta
workflow is intentionally different: a request is reviewed, supplier information is
collected, a proposal version is sent, the customer makes a decision, and authorized
GPM staff explicitly confirm the resulting order. This distinction must be reflected
in the new data model rather than hidden inside a generic `orders` table.


## Laravel implementation status (11 November 2026)

The repository now has a guarded Laravel baseline migration for the legacy tables. Existing tables are skipped rather than recreated; a clean database receives the legacy structure before the new workflow migrations. The baseline rollback is intentionally non-destructive to protect imported business data.

The contact form table contact_messages is also included in the baseline. It was created separately in the legacy PHP project and is not one of the 15 tables listed in the original SQL inventory.

New workflow tables added after the baseline:

- quote_requests and quote_request_items: a supply request is separate from an order.
- quote_request_status_history: staff transition audit.
- suppliers, supplier_inquiries, supplier_responses, and supplier_response_items: manual supplier sourcing and price/availability capture.
- proposals, proposal_versions, proposal_items, and proposal_decisions: immutable issued versions and customer decisions attached to a specific version.
- orders.currency and orders.proposal_version_id: currency-aware order totals and a unique link from an explicitly confirmed order to the exact accepted proposal version.

The current proposal workflow does not create an order when a customer accepts. An explicit staff confirmation workflow is still required before a new order is created from an accepted proposal. Payment integrations remain inactive.
