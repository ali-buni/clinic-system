# 19 - Invoices

Invoice management for clinic system.

**Test Cases:** 40 | **Endpoint Folder:** [`endpoint/invoice/`](./endpoint/invoice/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | DELETE | [`/invoices/{id}`](#delete-delete) | Delete invoice | Owner |
| 2 | GET | [`/invoices/doctor/{id}`](#get-doctor-invoices) | Get doctor invoices | Doctor |
| 3 | GET | [`/invoices`](#get-list) | List all invoices | Owner |
| 4 | GET | [`/invoices/patient/{id}`](#get-patient-invoices) | Get patient invoices | Patient |
| 5 | GET | [`/invoices/{id}`](#get-show) | Show invoice | Owner |
| 6 | POST | [`/invoices`](#post-store) | Create invoice | Doctor |
| 7 | PUT | [`/invoices/{id}`](#put-update) | Update invoice | Secretary |

---

## DELETE `/invoices/{id}`

Delete invoice. Owner only.

### Test Cases

#### `delete-success.json` — Success (200)
> [View JSON](./endpoint/invoice/delete/delete-success.json)

**Response (200):**
```json
{ "success": true, "message": "success", "data": null }
```

---

#### `delete-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/invoice/delete/delete-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `delete-not-found.json` — Not Found (404)
> [View JSON](./endpoint/invoice/delete/delete-not-found.json)

**Response (404):** `{ "success": false, "message": "Invoice not found", "data": null }`

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/invoice/delete/delete-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `delete-unauthorized-secretary.json` — Forbidden (403)
> [View JSON](./endpoint/invoice/delete/delete-unauthorized-secretary.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## GET `/invoices/doctor/{id}`

Get invoices for a specific doctor.

### Test Cases

#### `doctor-invoices-doctor-success.json` — Success (200)
> [View JSON](./endpoint/invoice/doctor/doctor-invoices-doctor-success.json)

**Response (200):**
```json
{ "success": true, "message": "success", "data": [ { "id": 201, "invoice_number": "INV-2026-000001", "status": "draft", "total_cost": 100, "paid_amount": 0, "remaining_amount": 100, "clinic_id": 1, "patient_id": 1, "appointment_id": 1, "created_at": "2026-07-02 18:24:22" } ] }
```

---

#### `doctor-invoices-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/invoice/doctor/doctor-invoices-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `doctor-invoices-not-found.json` — Not Found (404)
> [View JSON](./endpoint/invoice/doctor/doctor-invoices-not-found.json)

**Response (404):** `{ "success": false, "message": "Doctor not found", "data": null }`

---

#### `doctor-invoices-owner-success.json` — Success (200)
> [View JSON](./endpoint/invoice/doctor/doctor-invoices-owner-success.json)

**Response (200):**
```json
{ "success": true, "message": "success", "data": [ { "id": 201, "invoice_number": "INV-2026-000001", "status": "draft", "total_cost": 100, "paid_amount": 0, "remaining_amount": 100, "clinic_id": 1, "patient_id": 1, "appointment_id": 1, "created_at": "2026-07-02 18:24:22" } ] }
```

---

#### `doctor-invoices-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/invoice/doctor/doctor-invoices-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `doctor-invoices-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/invoice/doctor/doctor-invoices-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## GET `/invoices`

List all invoices. Owner only.

### Test Cases

#### `list-success.json` — Success (200)
> [View JSON](./endpoint/invoice/list/list-success.json)

**Response (200):**
```json
{ "success": true, "message": "success", "data": [ { "id": 214, "invoice_number": "INV-2026-000014", "status": "draft", "total_cost": 100, "paid_amount": 0, "remaining_amount": 100, "clinic_id": 1, "patient_id": 1, "appointment_id": 1, "created_at": "2026-07-02 18:32:44" } ], "pagination": { "total": 213, "count": 15, "per_page": 15, "current_page": 1, "last_page": 15 } }
```

---

#### `list-filter-date-range.json` — Filter by Date Range (200)
> [View JSON](./endpoint/invoice/list/list-filter-date-range.json)

**Response (200):**
```json
{ "success": true, "message": "success", "data": [...], "pagination": { "total": 100, "count": 15, "per_page": 15, "current_page": 1, "last_page": 7 } }
```

---

#### `list-filter-invalid-status.json` — Filter by Invalid Status (422)
> [View JSON](./endpoint/invoice/list/list-filter-invalid-status.json)

**Response (422):** `{ "message": "The selected status is invalid." }`

---

#### `list-filter-status.json` — Filter by Status (200)
> [View JSON](./endpoint/invoice/list/list-filter-status.json)

**Response (200):**
```json
{ "success": true, "message": "success", "data": [...], "pagination": { "total": 50, "count": 15, "per_page": 15, "current_page": 1, "last_page": 4 } }
```

---

#### `list-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/invoice/list/list-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `list-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/invoice/list/list-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `list-unauthorized-secretary.json` — Forbidden (403)
> [View JSON](./endpoint/invoice/list/list-unauthorized-secretary.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## GET `/invoices/patient/{id}`

Get invoices for a specific patient.

### Test Cases

#### `patient-invoices-self-success.json` — Success (200)
> [View JSON](./endpoint/invoice/patient/patient-invoices-self-success.json)

**Response (200):**
```json
{ "success": true, "message": "success", "data": [ { "id": 23, "invoice_number": "INV-SE7LSQS2", "status": "paid", "total_cost": 233.1, "paid_amount": 233.1, "remaining_amount": 0, "clinic_id": 1, "patient_id": 1, "appointment_id": 23, "created_at": "2026-03-03 14:30:00" } ] }
```

---

#### `patient-invoices-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/invoice/patient/patient-invoices-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `patient-invoices-not-found.json` — Not Found (404)
> [View JSON](./endpoint/invoice/patient/patient-invoices-not-found.json)

**Response (404):** `{ "success": false, "message": "Patient not found", "data": null }`

---

#### `patient-invoices-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/invoice/patient/patient-invoices-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## GET `/invoices/{id}`

Show invoice details. Owner required.

### Test Cases

#### `show-patient-success.json` — Success (200)
> [View JSON](./endpoint/invoice/show/show-patient-success.json)

**Response (200):**
```json
{ "success": true, "message": "success", "data": { "id": 215, "invoice_number": "INV-2026-000015", "status": "draft", "total_cost": 100, "paid_amount": 0, "remaining_amount": 100, "clinic_id": 1, "patient_id": 1, "appointment_id": 1, "created_at": "2026-07-02 18:38:41", "payments": [], "items": [ { "item_id": 1, "item_name": "Consultation Fee", "unit_price": 100, "quantity": 1, "total_price": 100 } ] } }
```

---

#### `show-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/invoice/show/show-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `show-not-found.json` — Not Found (404)
> [View JSON](./endpoint/invoice/show/show-not-found.json)

**Response (404):** `{ "success": false, "message": "Invoice not found", "data": null }`

---

#### `show-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/invoice/show/show-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## POST `/invoices`

Create invoice. Doctor only.

### Test Cases

#### `store-doctor-success.json` — Success (201)
> [View JSON](./endpoint/invoice/store/store-doctor-success.json)

**Request:**
```json
{ "clinic_id": 1, "patient_id": 1, "appointment_id": 1, "invoice_items": [ { "item_id": 1, "quantity": 1, "price": 100 } ] }
```

**Response (201):**
```json
{ "success": true, "message": "success", "data": { "id": 215, "invoice_number": "INV-2026-000015", "status": "draft", "total_cost": 100, "paid_amount": 0, "remaining_amount": 100, "clinic_id": 1, "patient_id": 1, "appointment_id": 1, "created_at": "2026-07-02 18:38:41" } }
```

---

#### `store-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/invoice/store/store-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `store-not-found-patient.json` — Not Found (404)
> [View JSON](./endpoint/invoice/store/store-not-found-patient.json)

**Response (404):** `{ "success": false, "message": "Patient not found", "data": null }`

---

#### `store-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/invoice/store/store-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `store-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/invoice/store/store-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

#### `store-validation-empty.json` — Validation Error (422)
> [View JSON](./endpoint/invoice/store/store-validation-empty.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "The patient id field is required. (and 3 more errors)", "errors": { "patient_id": [...], "clinic_id": [...], "invoice_items": [...], "appointment_id": [...] } }
```

---

#### `store-validation-missing-items.json` — Validation Error (422)
> [View JSON](./endpoint/invoice/store/store-validation-missing-items.json)

**Request:** `{ "patient_id": 1, "clinic_id": 1 }` (missing items)

**Response (422):**
```json
{ "message": "The invoice items field is required.", "errors": { "invoice_items": ["The invoice items field is required."] } }
```

---

## PUT `/invoices/{id}`

Update invoice. Secretary only.

### Test Cases

#### `update-secretary-success.json` — Success (200)
> [View JSON](./endpoint/invoice/update/update-secretary-success.json)

**Request:**
```json
{ "description": "Updated by secretary 6a46b05065ed7" }
```

**Response (200):**
```json
{ "success": true, "message": "success", "data": { "id": 215, "invoice_number": "INV-2026-000015", "status": "draft", "total_cost": 100, "paid_amount": 0, "remaining_amount": 100, "description": "Updated by secretary 6a46b05065ed7", "clinic_id": 1, "patient_id": 1, "appointment_id": 1, "created_at": "2026-07-02 18:38:41" } }
```

---

#### `update-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/invoice/update/update-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `update-not-found.json` — Not Found (404)
> [View JSON](./endpoint/invoice/update/update-not-found.json)

**Response (404):** `{ "success": false, "message": "Invoice not found", "data": null }`

---

#### `update-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/invoice/update/update-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `update-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/invoice/update/update-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

[Back to Index](./00-INDEX.md)
