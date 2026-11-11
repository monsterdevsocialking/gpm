# GPM Conecta — Architecture

## Purpose

GPM Conecta is a B2B procurement and supply platform. It is not a conventional
instant-checkout store. A customer submits a supply request; the GPM team reviews
it, consults suppliers, prepares a commercial proposal, and explicitly confirms
an order after the customer's decision.

## Architectural principles

- Laravel 12 and MySQL are the application foundation.
- Keep the existing `gpm2/` PHP application as a migration reference until its
  features and data have been migrated and verified.
- Organize code by business responsibility: Requests, Suppliers, Proposals,
  Orders, Companies, Catalog, Conversations, Documents, Payments and Integrations.
- Controllers handle HTTP concerns; Actions/Services own business operations.
- Policies enforce company-level data isolation and staff permissions.
- External integrations are adapters behind contracts. The core application
  must remain usable manually when an integration is not configured.
- Store credentials only in environment variables or a future secret manager.
- Use database transactions for commercial state changes.
- Preserve immutable proposal versions and record important state transitions.
- Do not let a customer acceptance automatically confirm an order.

## Implemented Laravel vertical slice

The current Laravel branch now includes the public institutional page, catalog
search/category filtering, product details, legacy product image delivery, customer
registration/login, a customer portal, legacy order/proforma read views, a session
supply-request cart, and submission of a separate `quote_requests` record.

Supply requests start in `submitted` and have a controlled status lifecycle:
`under_review`, `supplier_research`, `proposal_preparation`, `proposal_sent`,
`closed`, or `cancelled`. Staff status changes are recorded in
`quote_request_status_history`. The current implementation does not create an
order, charge a customer, or deduct stock when a request is submitted.

The staff area now includes protected catalog and category management, image uploads,
stock movements, customer and supplier directories, supplier inquiry/response capture,
supply-request review, versioned commercial proposals, internal user administration,
company settings, contact inbox, order conversations and an activity audit log.
The customer portal includes profile/contact editing and a current-password-verified
password change. Customer and staff conversations are scoped to the order/customer
relationship.

Both public and internal layouts load the shared GPM design system through Vite and
Tailwind CSS 4. Brand colours, typography and the site container are defined as CSS
theme tokens so production builds and local development use the same styling.
Issued proposal versions are immutable, and customers can record one decision
(accept, reject, or request revision) against that exact version. A replacement version
can be prepared after a customer request/rejection or an unanswered proposal expires.

An accepted proposal can now be converted into an order only through an explicit
staff action. The transaction checks the decision belongs to the customer on the
request, locks the proposal/request, creates order and item snapshots, records the
staff member, and closes the supply request. It does not deduct stock or start a
payment.

After confirmation, authorized staff can move an order through preparation,
shipment and completion (or cancellation where allowed). Staff can issue a currency-aware proforma from a confirmed order; the document snapshots the order and company/customer details. Each transition is locked,
validated against the current state and appended to the order status history. The
customer order page displays this history. The initial conversion uses the approved proposal subtotal as the order
total; delivery, tax and discount calculations are not yet separate fields and must
be reflected in the proposal prices/terms until those rules are approved.

Payment collection and gateway callbacks are not active. Order confirmation requires an authorized staff action after the customer accepts. The order is linked to the exact proposal version and can then be tracked through preparation, shipment and completion. Stock movements are separately audited; order confirmation does not deduct stock. A provider-event ledger and recorder are now in place for future integrations: provider + external event ID is unique, duplicate deliveries return the original event, and processing/failure metadata is retained. Incoming payloads must be sanitized before persistence; secrets, signatures and card data must never be stored.

## Planned application areas

| Area | Responsibility |
| --- | --- |
| Public | Institutional pages, catalog, categories and product details |
| Client | Company profile, requests, proposals, conversations and order tracking |
| Admin | Commercial operations, suppliers, proposals, orders, catalog and access |
| API | Versioned external/mobile endpoints, beginning at `/api/v1` |
| Integrations | Payment, supplier, shipping and notification adapters |

## Payment integration boundary

Payment provider code must implement `App\Contracts\Payments\PaymentGateway`.
Provider-specific credentials and settings belong in environment/configuration,
not in controllers or commercial models. Webhook handlers must verify signatures,
validate event ownership and amount/currency, and be idempotent before changing
payment state.

No gateway is enabled by this foundation. Payment methods, fees, taxes, refund
rules and provider selection remain business decisions to be approved.

## Database baseline and migration approach

The Laravel migration set now includes a guarded baseline for the legacy GPM tables
(users, customers, catalog, orders, proformas, conversations, stock and activity).
When an existing legacy table is already present, the baseline leaves it untouched.
On a fresh database it creates the corresponding tables before the new supply-request,
supplier and proposal tables. The baseline rollback is intentionally non-destructive
because it may represent imported production data.

For a new installation, configure MySQL, set GPM_ADMIN_NAME / GPM_ADMIN_EMAIL / GPM_ADMIN_PASSWORD and run Laravel migrations followed by the initial administrator seeder. For an existing
installation, take a complete backup first; run migrations only after confirming that
the imported legacy schema matches the SQL dump. Do not import the sample SQL dump
into a database containing live tables without a deliberate merge plan.

## Migration approach

1. Inventory the existing PHP pages, SQL schema, relationships and assets.
2. Map legacy tables and workflows to the target domain model.
3. Create migrations in dependency order and plan data migration separately.
4. Migrate one vertical slice at a time, with feature tests and visual comparison.
5. Retire legacy PHP only after parity checks and backup/rollback planning.

## Important commercial invariants

- A company user can only access records belonging to their company.
- Only authorized GPM staff can confirm an order.
- Customer acceptance is a decision on a specific proposal version, not order
  confirmation.
- Proposal versions retain their original prices and conditions.
- Monetary values are calculated and validated server-side.
- External webhook retries must not duplicate payments or commercial actions.
