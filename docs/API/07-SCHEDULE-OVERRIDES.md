# 07 - Schedule Overrides

Override a doctor's standard schedule for specific dates.

**Test Cases:** 14 | **Endpoint Folder:** [`endpoint/schedule-override/`](./endpoint/schedule-override/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/schedule-overrides`](#get-list) | List all overrides | Owner |
| 2 | GET | [`/schedule-overrides/{id}`](#get-show) | Show a specific override | Owner |
| 3 | GET | [`/schedule-overrides/date/single`](#get-by-date) | Get override by date | Owner |
| 4 | GET | [`/schedule-overrides/date/range`](#get-by-date-range) | Get overrides by date range | Owner |
| 5 | POST | [`/schedule-overrides`](#post-store) | Create schedule override | Owner |
| 6 | PUT | [`/schedule-overrides/{id}`](#put-update) | Update schedule override | Owner |
| 7 | DELETE | [`/schedule-overrides/{id}`](#delete-delete) | Delete schedule override | Owner |

---

## GET `/schedule-overrides`

List all overrides for a doctor. Owner only.

### Test Cases

#### `list-success.json` — Success (200)
> [View JSON](./endpoint/schedule-override/list/list-success.json)

**Response (200):**
```json
{ "success": true, "message": "Overrides retrieved successfully.", "data": [ { "id": 3, "doctor": { "id": 1, "name": "Amira Hassan" }, "override_date": "2026-07-27", "override_type": "closed", "start_time": "16:52", "end_time": "16:52", "reason": "مرضية", "is_closed": 1, "created_at": "2026-07-01" }, ... ] }
```

---

#### `list-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/schedule-override/list/list-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/schedule-overrides/{id}`

Show a specific override. Owner only.

### Test Cases

#### `show-success.json` — Success (200)
> [View JSON](./endpoint/schedule-override/show/show-success.json)

**Response (200):**
```json
{ "success": true, "message": "Success", "data": { "id": 2, "doctor": { "id": 1, "name": "Amira Hassan" }, "override_date": "2026-08-13", "override_type": "time_change", "start_time": "10:00", "end_time": "14:00", "reason": "دوام جزئي", "is_closed": 0, "created_at": "2026-07-01" } }
```

---

#### `show-not-found.json` — Not Found (404)
> [View JSON](./endpoint/schedule-override/show/show-not-found.json)

**Response (404):**
```json
{ "success": false, "message": "Doctor profile not found.", "data": null }
```

---

#### `show-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/schedule-override/show/show-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/schedule-overrides/date/single`

Get override by date. Owner only.

### Test Cases

#### `by-date-success.json` — Success (200)
> [View JSON](./endpoint/schedule-override/by/by-date-success.json)

**Response (200):**
```json
{ "success": true, "message": "Override retrieved successfully.", "data": { "id": 3, "doctor": { "id": 1, "name": "Amira Hassan" }, "override_date": "2026-07-27", "override_type": "closed", "start_time": "17:03", "end_time": "17:03", "reason": "مرضية", "is_closed": 1, "created_at": "2026-07-01" } }
```

---

#### `by-date-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/schedule-override/by/by-date-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/schedule-overrides/date/range`

Get overrides by date range. Owner only.

### Test Cases

#### `by-date-range-success.json` — Success (200)
> [View JSON](./endpoint/schedule-override/by/by-date-range-success.json)

**Response (200):**
```json
{ "success": true, "message": "Overrides retrieved successfully.", "data": [ { "id": 3, "doctor": { "id": 1, "name": "Amira Hassan" }, "override_date": "2026-07-27", "override_type": "closed", "start_time": "16:52", "end_time": "16:52", "reason": "مرضية", "is_closed": 1, "created_at": "2026-07-01" } ] }
```

---

#### `by-date-range-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/schedule-override/by/by-date-range-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/schedule-overrides`

Create a schedule override. Owner only.

### Test Cases

#### `store-success.json` — Success (201)
> [View JSON](./endpoint/schedule-override/store/store-success.json)

**Request:**
```json
{ "doctor_id": 1, "override_date": "2099-06-15", "override_type": "closed", "is_closed": true }
```

**Response (201):**
```json
{ "success": true, "message": "Override added successfully.", "data": { "id": 16, "doctor": { "id": 1, "name": "Amira Hassan" }, "override_date": "2099-06-15", "override_type": "closed", "start_time": "16:44", "end_time": "16:44", "reason": null, "is_closed": true, "created_at": "2026-07-01" } }
```

---

#### `store-validation.json` — Validation Error (422)
> [View JSON](./endpoint/schedule-override/store/store-validation.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "الطبيب مطلوب. (and 1 more error)", "errors": { "doctor_id": ["الطبيب مطلوب."], "override_date": ["التاريخ مطلوب."] } }
```

---

#### `store-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/schedule-override/store/store-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## PUT `/schedule-overrides/{id}`

Update a schedule override. Owner only.

### Test Cases

#### `update-success.json` — Success (200)
> [View JSON](./endpoint/schedule-override/update/update-success.json)

**Request:**
```json
{ "reason": "Updated reason", "override_date": "2099-07-01", "override_type": "time_change", "start_time": "14:00", "end_time": "18:00", "doctor_id": 1 }
```

**Response (200):**
```json
{ "success": true, "message": "Override updated successfully.", "data": { "id": 1, "doctor": { "id": 1, "name": "Amira Hassan" }, "override_date": "2099-07-01", "override_type": "time_change", "start_time": "14:00", "end_time": "18:00", "reason": "Updated reason", "is_closed": 1, "created_at": "2026-07-01" } }
```

---

#### `update-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/schedule-override/update/update-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## DELETE `/schedule-overrides/{id}`

Delete a schedule override. Owner only.

### Test Cases

#### `delete-success.json` — Success (200)
> [View JSON](./endpoint/schedule-override/delete/delete-success.json)

**Response (200):**
```json
{ "success": true, "message": "Override deleted successfully.", "data": null }
```

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/schedule-override/delete/delete-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
