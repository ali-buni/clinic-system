# 22 - Payment Methods

Payment method configuration and management.

**Test Cases:** 18 | **Endpoint Folder:** [`endpoint/payment_method/`](./endpoint/payment_method/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | DELETE | [`/payment-methods/{id}`](#delete-delete) | Delete payment method | Owner |
| 2 | GET | [`/payment-methods`](#get-list) | List all payment methods | Owner |
| 3 | PATCH | [`/payment-methods/{id}/stop`](#patch-stop) | Stop payment method | Owner |
| 4 | POST | [`/payment-methods`](#post-store) | Create payment method | Owner |

---

## DELETE `/payment-methods/{id}`

Delete a payment method. Owner only.

### Test Cases

#### `delete-success.json` — Success (200)
> [View JSON](./endpoint/payment_method/delete/delete-success.json)

**Response (200):**
```json
{ "success": true, "message": "تم حذف طريقة الدفع بنجاح.", "data": null }
```

---

#### `delete-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/payment_method/delete/delete-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `delete-not-found.json` — Not Found (404)
> [View JSON](./endpoint/payment_method/delete/delete-not-found.json)

**Response (404):** `{ "success": false, "message": "Payment method not found", "data": null }`

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/payment_method/delete/delete-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `delete-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/payment_method/delete/delete-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## GET `/payment-methods`

List all payment methods. Owner only.

### Test Cases

#### `list-success.json` — Success (200)
> [View JSON](./endpoint/payment_method/list/list-success.json)

**Response (200):**
```json
{ "success": true, "message": "تم جلب طرق الدفع بنجاح.", "data": [ { "id": 1, "ar_name": "نقداً", "en_name": "Cash", "type": "Cash", "is_active": null }, { "id": 2, "ar_name": "بطاقة", "en_name": "Card", "type": "Card", "is_active": null }, { "id": 4, "ar_name": "Stripe", "en_name": "Stripe", "type": "Stripe", "is_active": null } ] }
```

---

#### `list-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/payment_method/list/list-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `list-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/payment_method/list/list-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## PATCH `/payment-methods/{id}/stop`

Stop (deactivate) a payment method. Owner only.

### Test Cases

#### `stop-success.json` — Success (200)
> [View JSON](./endpoint/payment_method/stop/stop-success.json)

**Response (200):**
```json
{ "success": true, "message": "تم إيقاف طريقة الدفع بنجاح.", "data": null }
```

---

#### `stop-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/payment_method/stop/stop-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `stop-not-found.json` — Not Found (404)
> [View JSON](./endpoint/payment_method/stop/stop-not-found.json)

**Response (404):** `{ "success": false, "message": "Payment method not found", "data": null }`

---

#### `stop-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/payment_method/stop/stop-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `stop-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/payment_method/stop/stop-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## POST `/payment-methods`

Create a payment method. Owner only.

### Test Cases

#### `store-success.json` — Success (201)
> [View JSON](./endpoint/payment_method/store/store-success.json)

**Request:**
```json
{ "ar_name": "طريقة دفع تجريبية 6a46a7f12c741", "en_name": "Test Method 6a46a7f12c745", "type": "Cash" }
```

**Response (201):**
```json
{ "success": true, "message": "تم إضافة طريقة الدفع بنجاح.", "data": { "id": 6, "ar_name": "طريقة دفع تجريبية 6a46a7f12c741", "en_name": "Test Method 6a46a7f12c745", "type": "Cash", "is_active": false } }
```

---

#### `store-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/payment_method/store/store-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `store-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/payment_method/store/store-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `store-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/payment_method/store/store-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

#### `store-validation-empty.json` — Validation Error (422)
> [View JSON](./endpoint/payment_method/store/store-validation-empty.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "The ar name field is required. (and 2 more errors)", "errors": { "ar_name": [...], "en_name": [...], "type": [...] } }
```

---

#### `store-validation-invalid-type.json` — Validation Error (422)
> [View JSON](./endpoint/payment_method/store/store-validation-invalid-type.json)

**Response (422):**
```json
{ "message": "The selected type is invalid.", "errors": { "type": ["The selected type is invalid."] } }
```

---

[Back to Index](./00-INDEX.md)
