# 21 - Payments

Payment processing for invoices.

**Test Cases:** 23 | **Endpoint Folder:** [`endpoint/payment/`](./endpoint/payment/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | DELETE | [`/payments/{id}`](#delete-delete) | Delete payment | Patient |
| 2 | GET | [`/payments`](#get-list) | List payments | Patient |
| 3 | GET | [`/payments/{id}`](#get-show) | Show payment | Patient |
| 4 | POST | [`/payments`](#post-store) | Create payment | Patient |

---

## DELETE `/payments/{id}`

Delete (cancel) a payment. Patient only.

### Test Cases

#### `delete-success.json` — Success (200)
> [View JSON](./endpoint/payment/delete/delete-success.json)

**Response (200):**
```json
{ "success": true, "message": "the payment is canceled", "data": null }
```

---

#### `delete-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/payment/delete/delete-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `delete-not-found.json` — Not Found (404)
> [View JSON](./endpoint/payment/delete/delete-not-found.json)

**Response (404):** `{ "success": false, "message": "Payment not found", "data": null }`

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/payment/delete/delete-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `delete-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/payment/delete/delete-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## GET `/payments`

List payments. Patient access (with invoice_id filter).

### Test Cases

#### `list-success.json` — Success (200)
> [View JSON](./endpoint/payment/list/list-success.json)

**Query:** `?invoice_id=1`

**Response (200):**
```json
{ "success": true, "message": "payments fetched successfully", "data": [ { "id": 1, "invoice_id": 1, "payment_method": { "id": 2, "ar_name": "بطاقة", "en_name": "Card" }, "amount": 250.13, "paid_at": "2026-03-01 13:00:00", "created_at": "2026-07-02 19:31:31" } ] }
```

---

#### `list-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/payment/list/list-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `list-missing-invoice-id.json` — Validation Error (422)
> [View JSON](./endpoint/payment/list/list-missing-invoice-id.json)

**Response (422):**
```json
{ "message": "The invoice id field is required.", "errors": { "invoice_id": ["The invoice id field is required."] } }
```

---

#### `list-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/payment/list/list-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `list-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/payment/list/list-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## GET `/payments/{id}`

Show payment details. Patient only.

### Test Cases

#### `show-success.json` — Success (200)
> [View JSON](./endpoint/payment/show/show-success.json)

**Response (200):**
```json
{ "success": true, "message": "payment fetched successfully", "data": { "id": 1, "invoice_id": 1, "payment_method": { "id": 2, "ar_name": "بطاقة", "en_name": "Card" }, "amount": 250.13, "paid_at": "2026-03-01 13:00:00", "created_at": "2026-07-02 19:31:31" } }
```

---

#### `show-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/payment/show/show-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `show-not-found.json` — Not Found (404)
> [View JSON](./endpoint/payment/show/show-not-found.json)

**Response (404):** `{ "success": false, "message": "Payment not found", "data": null }`

---

#### `show-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/payment/show/show-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `show-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/payment/show/show-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## POST `/payments`

Create a payment. Patient only.

### Test Cases

#### `store-success.json` — Success (200)
> [View JSON](./endpoint/payment/store/store-success.json)

**Request:**
```json
{ "invoice_id": 7, "payment_method_id": 4, "amount": "252.38" }
```

**Response (200):**
```json
{ "success": true, "message": "Success", "data": { "payment_url": "https://checkout.stripe.com/c/pay/cs_test_a18KHhlFJjNGEramjCQnEBk0JFZSyPrjAemM2uQzZFsoU8mDSDEb5tLHxn", "session_id": "cs_test_a18KHhlFJjNGEramjCQnEBk0JFZSyPrjAemM2uQzZFsoU8mDSDEb5tLHxn" } }
```

---

#### `store-invalid-amount.json` — Validation Error (422)
> [View JSON](./endpoint/payment/store/store-invalid-amount.json)

**Response (422):**
```json
{ "message": "The amount must be a positive number.", "errors": { "amount": ["The amount must be a positive number."] } }
```

---

#### `store-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/payment/store/store-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `store-not-found-invoice.json` — Not Found (404)
> [View JSON](./endpoint/payment/store/store-not-found-invoice.json)

**Response (404):** `{ "success": false, "message": "Invoice not found", "data": null }`

---

#### `store-not-found-payment-method.json` — Not Found (404)
> [View JSON](./endpoint/payment/store/store-not-found-payment-method.json)

**Response (404):** `{ "success": false, "message": "Payment method not found", "data": null }`

---

#### `store-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/payment/store/store-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `store-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/payment/store/store-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

#### `store-validation-empty.json` — Validation Error (422)
> [View JSON](./endpoint/payment/store/store-validation-empty.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "The invoice id field is required. (and 2 more errors)", "errors": { "invoice_id": [...], "payment_method_id": [...], "amount": [...] } }
```

---

#### `store-validation-missing-fields.json` — Validation Error (422)
> [View JSON](./endpoint/payment/store/store-validation-missing-fields.json)

**Response (422):**
```json
{ "message": "The invoice id field is required.", "errors": { "invoice_id": ["The invoice id field is required."] } }
```

---

[Back to Index](./00-INDEX.md)
