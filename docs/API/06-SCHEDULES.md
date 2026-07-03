# 06 - Schedules

Work schedule and slot management for doctors.

**Test Cases:** 12 | **Endpoint Folder:** [`endpoint/schedule/`](./endpoint/schedule/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | POST | [`/schedules`](#post-store) | Create a work hour | Owner |
| 2 | PUT | [`/schedules`](#put-update) | Update a work hour | Owner |
| 3 | DELETE | [`/schedules/{doctorId}/{id}`](#delete-delete) | Delete a work hour | Owner |
| 4 | GET | [`/schedules/weekly/{id}`](#get-weekly) | Get weekly schedule | Public |
| 5 | GET | [`/schedules/work-hour/{id}`](#get-work) | Get work hour for date | Public |

---

## POST `/schedules`

Create a work hour. Owner only.

### Test Cases

#### `store-success.json` — Success (201)
> [View JSON](./endpoint/schedule/store/store-success.json)

**Request:**
```json
{ "day_of_week": 6, "start_time": "09:00", "end_time": "17:00", "doctor_id": 1, "max_patients_per_day": 10 }
```

**Response (201):**
```json
{ "success": true, "message": "Work hour added successfully.", "data": { "id": 26, "doctor": { "id": 1, "name": "Amira Hassan" }, "day_of_week": 6, "day_name": "Saturday", "start_time": "09:00", "end_time": "17:00", "break_start": null, "break_end": null, "max_patients_per_day": 10, "duration_minutes": 480, "created_at": "2026-07-01" } }
```

---

#### `store-validation.json` — Validation Error (422)
> [View JSON](./endpoint/schedule/store/store-validation.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "The doctor id field is required. (and 3 more errors)", "errors": { "doctor_id": ["The doctor id field is required."], "day_of_week": ["يوم الأسبوع مطلوب."], "start_time": ["وقت البداية مطلوب."], "end_time": ["وقت النهاية مطلوب."] } }
```

---

#### `store-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/schedule/store/store-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `store-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/schedule/store/store-unauthorized-patient.json)

**Response (403):**
```json
{ "success": false, "message": "Permission Denied" }
```

---

#### `store-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/schedule/store/store-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## PUT `/schedules`

Update a work hour. Owner only.

### Test Cases

#### `update-success.json` — Success (200)
> [View JSON](./endpoint/schedule/update/update-success.json)

**Request:**
```json
{ "day_of_week": 2, "doctor_id": 1, "start_time": "10:00", "end_time": "16:00" }
```

**Response (200):**
```json
{ "success": true, "message": "Work hour updated successfully.", "data": { "id": 3, "doctor": { "id": 1, "name": "Amira Hassan" }, "day_of_week": 2, "day_name": "Tuesday", "start_time": "10:00", "end_time": "16:00", "break_start": null, "break_end": null, "max_patients_per_day": 17, "duration_minutes": 360, "created_at": "2026-07-01" } }
```

---

#### `update-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/schedule/update/update-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## DELETE `/schedules/{doctorId}/{id}`

Delete a work hour. Owner only.

### Test Cases

#### `delete-success.json` — Success (200)
> [View JSON](./endpoint/schedule/delete/delete-success.json)

**Response (200):**
```json
{ "success": true, "message": "Work hour deleted successfully (Soft Deleted).", "data": null }
```

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/schedule/delete/delete-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/schedules/weekly/{id}`

Get weekly schedule for a doctor. Public.

### Test Cases

#### `weekly-public-success.json` — Success (200)
> [View JSON](./endpoint/schedule/weekly/weekly-public-success.json)

**Response (200):**
```json
{ "success": true, "message": "Schedule retrieved successfully.", "data": [ { "id": 1, "doctor": { "id": 1, "name": "Amira Hassan" }, "day_of_week": 0, "day_name": "Sunday", "start_time": "09:00:00", "end_time": "17:00:00", "break_start": null, "break_end": null, "max_patients_per_day": 15, "duration_minutes": 480, "created_at": "2026-07-01" }, ... ] }
```

---

#### `weekly-not-found.json` — Not Found (404)
> [View JSON](./endpoint/schedule/weekly/weekly-not-found.json)

**Response (404):**
```json
{ "success": false, "message": "Doctor profile not found.", "data": null }
```

---

## GET `/schedules/work-hour/{id}`

Get work hour for a specific date. Public.

### Test Cases

#### `work-hour-public-success.json` — Success (200)
> [View JSON](./endpoint/schedule/work/work-hour-public-success.json)

**Response (200):**
```json
{ "success": true, "message": "Work hour retrieved successfully.", "data": { "id": 4, "doctor": { "id": 1, "name": "Amira Hassan" }, "day_of_week": 3, "day_name": "Wednesday", "start_time": "09:00:00", "end_time": "15:00:00", "break_start": null, "break_end": null, "max_patients_per_day": 19, "duration_minutes": 360, "created_at": "2026-07-01" } }
```

---

[Back to Index](./00-INDEX.md)
