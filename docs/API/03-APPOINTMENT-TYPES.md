# 03 - Appointment Types

Appointment type CRUD operations.

**Test Cases:** 8 | **Endpoint Folder:** [`endpoint/appointment-type/`](./endpoint/appointment-type/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/appointment-types`](#get-index) | List all appointment types | Public |
| 2 | POST | [`/appointment-types`](#post-add) | Create a new appointment type | Owner |
| 3 | DELETE | [`/appointment-types/{id}`](#delete-remove) | Delete an appointment type | Owner |

---

## GET `/appointment-types`

List all appointment types. Public.

### Test Cases

#### `index-success.json` — Success (200)
> [View JSON](./endpoint/appointment-type/index/index-success.json)

**Response (200):**
```json
{ "success": true, "message": "Appointment types retrieved", "data": [ { "id": 10, "ar_name": "مراجعة", "en_name": "Review", "types": "1", "created_at": "2026-07-01 09:02:25" }, { "id": 9, "ar_name": "جلسة طويلة", "en_name": "Long Session", "types": "3", "created_at": "2026-07-01 09:02:25" }, ... (10 total items) ] }
```

---

## POST `/appointment-types`

Create a new appointment type. Owner only.

### Test Cases

#### `add-owner-success.json` — Success (201)
> [View JSON](./endpoint/appointment-type/add/add-owner-success.json)

**Request:**
```json
{ "en_name": "New Type 6a44f2e3a4c03", "ar_name": "New Type 6a44f2e3a4c08", "types": 1 }
```

**Response (201):**
```json
{ "success": true, "message": "Appointment type created", "data": null }
```

---

#### `add-validation.json` — Validation Error (422)
> [View JSON](./endpoint/appointment-type/add/add-validation.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "The ar name field is required. (and 2 more errors)", "errors": { "ar_name": ["The ar name field is required."], "en_name": ["The en name field is required."], "types": ["The types field is required."] } }
```

---

#### `add-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment-type/add/add-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `add-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/appointment-type/add/add-unauthorized-patient.json)

**Response (403):**
```json
{ "success": false, "message": "Permission Denied" }
```

---

## DELETE `/appointment-types/{id}`

Delete an appointment type. Owner only.

### Test Cases

#### `delete-owner-success.json` — Success (200)
> [View JSON](./endpoint/appointment-type/delete/delete-owner-success.json)

**Response (200):**
```json
{ "success": true, "message": "Appointment type deleted", "data": null }
```

---

#### `delete-not-found.json` — Not Found (404)
> [View JSON](./endpoint/appointment-type/delete/delete-not-found.json)

**Response (404):**
```json
{ "success": false, "message": "Appointment type not found", "data": null }
```

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment-type/delete/delete-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
