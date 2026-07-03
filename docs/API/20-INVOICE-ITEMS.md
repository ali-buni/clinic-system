# 20 - Invoice Items

Manage items/services that can be added to invoices.

**Test Cases:** 19 | **Endpoint Folder:** [`endpoint/item/`](./endpoint/item/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | DELETE | [`/items/{id}`](#delete-delete) | Delete item | Owner |
| 2 | GET | [`/items`](#get-list) | List all items | Doctor |
| 3 | POST | [`/items`](#post-store) | Create item | Owner |

---

## DELETE `/items/{id}`

Delete an item. Owner only.

### Test Cases

#### `delete-success.json` — Success (200)
> [View JSON](./endpoint/item/delete/delete-success.json)

**Response (200):**
```json
{ "success": true, "message": "Item deleted successfully.", "data": null }
```

---

#### `delete-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/item/delete/delete-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `delete-not-found.json` — Not Found (404)
> [View JSON](./endpoint/item/delete/delete-not-found.json)

**Response (404):** `{ "success": false, "message": "Item not found", "data": null }`

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/item/delete/delete-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `delete-unauthorized.json` — Forbidden (403)
> [View JSON](./endpoint/item/delete/delete-unauthorized.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## GET `/items`

List all items. Doctor access.

### Test Cases

#### `list-doctor-success.json` — Success (200)
> [View JSON](./endpoint/item/list/list-doctor-success.json)

**Response (200):**
```json
{ "success": true, "message": "Items retrieved successfully.", "data": [ { "id": 669, "item_name": " التطعيمات", "clinic_id": null, "created_at": "2026-07-02 17:57:21" }, { "id": 129, "item_name": "24-Hour Urine Collection", "clinic_id": null, "created_at": "2026-07-02 17:57:19" } ], "pagination": { "total": 870, "count": 15, "per_page": 15, "current_page": 1, "last_page": 58 } }
```

---

#### `list-filter-by-clinic.json` — Filter by Clinic (200)
> [View JSON](./endpoint/item/list/list-filter-by-clinic.json)

**Response (200):**
```json
{ "success": true, "message": "Items retrieved successfully.", "data": [...], "pagination": { "total": 50, "count": 15, "per_page": 15, "current_page": 1, "last_page": 4 } }
```

---

#### `list-filter-by-name.json` — Filter by Name (200)
> [View JSON](./endpoint/item/list/list-filter-by-name.json)

**Response (200):**
```json
{ "success": true, "message": "Items retrieved successfully.", "data": [...], "pagination": { "total": 10, "count": 10, "per_page": 15, "current_page": 1, "last_page": 1 } }
```

---

#### `list-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/item/list/list-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `list-pagination.json` — Pagination (200)
> [View JSON](./endpoint/item/list/list-pagination.json)

**Response (200):**
```json
{ "success": true, "message": "Items retrieved successfully.", "data": [...], "pagination": { "total": 870, "count": 15, "per_page": 15, "current_page": 2, "last_page": 58 } }
```

---

#### `list-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/item/list/list-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `list-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/item/list/list-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## POST `/items`

Create an item. Owner only.

### Test Cases

#### `store-success.json` — Success (201)
> [View JSON](./endpoint/item/store/store-success.json)

**Request:**
```json
{ "item_name": "Test Item 6a46b31d18180", "clinic_id": 1 }
```

**Response (201):**
```json
{ "success": true, "message": "Item created successfully.", "data": { "id": 871, "item_name": "Test Item 6a46b31d18180", "clinic_id": 1, "created_at": "2026-07-02 18:51:09" } }
```

---

#### `store-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/item/store/store-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `store-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/item/store/store-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `store-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/item/store/store-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

#### `store-unauthorized-secretary.json` — Forbidden (403)
> [View JSON](./endpoint/item/store/store-unauthorized-secretary.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

#### `store-validation-empty.json` — Validation Error (422)
> [View JSON](./endpoint/item/store/store-validation-empty.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "The item name field is required.", "errors": { "item_name": ["The item name field is required."] } }
```

---

#### `store-validation-missing-name.json` — Validation Error (422)
> [View JSON](./endpoint/item/store/store-validation-missing-name.json)

**Request:** `{ "clinic_id": 1 }` (missing name)

**Response (422):**
```json
{ "message": "The item name field is required.", "errors": { "item_name": ["The item name field is required."] } }
```

---

[Back to Index](./00-INDEX.md)
