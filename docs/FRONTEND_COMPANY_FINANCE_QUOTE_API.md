# Frontend API — Company Estimated Quote + Finances

Base: `/api/company`  
Auth: Bearer Sanctum + role `company`

## Estimated Quote

```
POST /api/company/offers/{offerId}/estimated-quote
POST /api/company/offers/estimated-quote   # body includes offer_id
GET  /api/company/offers/{offerId}/estimated-quote
```

Body:

```json
{
  "mode": "single",
  "amount": 850,
  "note": "Endpreis nach Besichtigung",
  "currency": "EUR"
}
```

or range:

```json
{
  "mode": "range",
  "amount_from": 750,
  "amount_to": 950,
  "note": "…"
}
```

Success `201`:

- `quote`
- `conversation_id` (chat opened/created)
- `message` (chat message payload)

Rules: must have purchased the offer; max 5 quotes / offer / day (`429`).

---

## Invoices

```
GET /api/company/invoices?page=1&limit=10&status=all|paid|pending|cancelled|refunded
GET /api/company/invoices/{id}
GET /api/company/invoices/{id}/pdf
```

Invoices are synced from company credit top-up `orders`.

---

## Credit notes

```
GET /api/company/credit-notes?page=1&limit=10&status=all
```

Returns `summary.total_credit_notes` (+ wallet `refund` fallback) and `rows`.

---

## Payment methods

```
GET /api/company/payment-methods
```

At least `bank_transfer` from ConfigApp. Stripe card setup not enabled yet (`can_add_card: false`).

---

## Finance overview

```
GET /api/company/finances/overview
```

Wallet + orders + invoices + credit notes + `pending_banner`.

---

## Notifications badge

```
GET /api/company/notifications/unread-count
```

```json
{ "unread_total": 4 }
```
