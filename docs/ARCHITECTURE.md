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
