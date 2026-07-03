# 18 - Patient Records

Patient records management for clinic system.

**Test Cases:** 30 | **Endpoint Folder:** [`endpoint/patient-record/`](./endpoint/patient-record/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | DELETE | [`/patient-records/{id}`](#delete-delete) | Delete patient record | Doctor |
| 2 | GET | [`/patient-records/doctor/{id}/all`](#get-get-all-by-doctor) | Get all records by doctor | Doctor |
| 3 | GET | [`/patient-records/patient/{id}/doctor/{id}`](#get-get-by-doctor) | Get records by doctor for patient | Doctor |
| 4 | POST | [`/patient-records/rooms/search`](#post-get-by-room-secretary) | Search records by room (secretary) | Secretary |
| 5 | GET | [`/patient-records/patient/{id}/history`](#get-history-self) | Get patient history | Patient |
| 6 | GET | [`/patient-records`](#get-list) | List all records | Owner |
| 7 | GET | [`/patient-records/{id}`](#get-show) | Show patient record | Doctor |
| 8 | POST | [`/patient-records`](#post-store) | Create patient record | Doctor |
| 9 | PUT | [`/patient-records/{id}`](#put-update) | Update patient record | Doctor |

---

## DELETE `/patient-records/{id}`

Delete patient record. Doctor only.

### Test Cases

#### `delete-success.json` — Success (200)
> [View JSON](./endpoint/patient-record/delete/delete-success.json)

**Response (200):**
```json
{ "success": true, "message": "Record deleted successfully", "data": null }
```

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient-record/delete/delete-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## GET `/patient-records/doctor/{id}/all`

Get all patient records by doctor. Doctor only.

### Test Cases

#### `get-all-by-doctor-success.json` — Success (200)
> [View JSON](./endpoint/patient-record/get/get-all-by-doctor-success.json)

**Response (200):**
```json
{ "success": true, "message": "Doctor records retrieved successfully", "data": [ { "id": 502, "patient_id": 1, "doctor_id": 1, "appointment_id": 1, "diagnosis_summary": "Test diagnosis 6a4554057b971", "description": null, "status": "open", "notes": null, "patient": { "name": "Dulce McKenzie", "phone": "0924778300" }, "doctor": { "name": "Amira Hassan" }, "created_at": "2026-07-01 17:53:11" } ] }
```

---

#### `get-all-by-doctor-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient-record/get/get-all-by-doctor-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## GET `/patient-records/patient/{id}/doctor/{id}`

Get patient records by specific doctor. Doctor only.

### Test Cases

#### `get-by-doctor-success.json` — Success (200)
> [View JSON](./endpoint/patient-record/get/get-by-doctor-success.json)

**Response (200):**
```json
{ "success": true, "message": "Records retrieved successfully", "data": [ { "id": 502, "patient_id": 1, "doctor_id": 1, "appointment_id": 1, "diagnosis_summary": "Test diagnosis 6a4554057b971", "status": "open", "patient": { "name": "Dulce McKenzie", "phone": "0924778300" }, "doctor": { "name": "Amira Hassan" }, "created_at": "2026-07-01 17:53:11" } ] }
```

---

#### `get-by-doctor-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient-record/get/get-by-doctor-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## POST `/patient-records/rooms/search`

Search patient records by room IDs. Secretary only.

### Test Cases

#### `get-by-room-secretary-success.json` — Success (200)
> [View JSON](./endpoint/patient-record/get/get-by-room-secretary-success.json)

**Request:**
```json
{ "room_ids": [2] }
```

**Response (200):**
```json
{ "success": true, "message": "Records retrieved successfully", "data": [ { "id": 493, "patient_id": 48, "doctor_id": 2, "appointment_id": 493, "diagnosis_summary": "Accusamus minus esse vel culpa.", "status": "closed", "patient": { "name": "Columbus Senger", "phone": "0980945745" }, "doctor": { "name": "Omar Nasser" }, "created_at": "2026-07-01 12:37:21" } ] }
```

---

#### `get-by-room-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient-record/get/get-by-room-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## GET `/patient-records/patient/{id}/history`

Get patient medical history. Patient only.

### Test Cases

#### `history-self-success.json` — Success (200)
> [View JSON](./endpoint/patient-record/history/history-self-success.json)

**Response (200):**
```json
{ "success": true, "message": "Patient history retrieved successfully", "data": [ { "id": 336, "patient_id": 1, "doctor_id": 3, "appointment_id": 336, "diagnosis_summary": "Rerum alias expedita sit odio.", "status": "closed", "patient": { "name": "Dulce McKenzie", "phone": "0924778300" }, "doctor": { "name": "Layla Farah" }, "created_at": "2026-07-01 12:37:17" } ] }
```

---

#### `history-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient-record/history/history-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## GET `/patient-records`

List all patient records. Owner only.

### Test Cases

#### `list-success.json` — Success (200)
> [View JSON](./endpoint/patient-record/list/list-success.json)

**Response (200):**
```json
{ "success": true, "message": "Records retrieved successfully", "data": [ { "id": 2, "patient_id": 16, "doctor_id": 1, "appointment_id": 2, "diagnosis_summary": "Quam ut ut vitae et ut.", "status": "closed", "patient": { "name": "Pierce Heathcote", "phone": "0942949828" }, "doctor": { "name": "Amira Hassan" }, "created_at": "2026-07-01 12:37:09" } ], "pagination": { "total": 501, "count": 15, "per_page": 15, "current_page": 1, "last_page": 34 } }
```

---

#### `list-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient-record/list/list-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `list-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/patient-record/list/list-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

## GET `/patient-records/{id}`

Show patient record. Doctor only.

### Test Cases

#### `show-success.json` — Success (200)
> [View JSON](./endpoint/patient-record/show/show-success.json)

**Response (200):**
```json
{ "success": true, "message": "the record found", "data": { "id": 501, "patient_id": 1, "doctor_id": 1, "appointment_id": 1, "diagnosis_summary": "Test diagnosis 6a45534a00d18", "description": null, "status": "open", "notes": null, "patient": { "name": "Dulce McKenzie", "phone": "0924778300" }, "doctor": { "name": "Amira Hassan" }, "diseases": [], "prescriptions": [], "created_at": "2026-07-01 17:50:03" } }
```

---

#### `show-not-found.json` — Not Found (404)
> [View JSON](./endpoint/patient-record/show/show-not-found.json)

**Response (404):** `{ "success": false, "message": "Record not found", "data": null }`

---

#### `show-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient-record/show/show-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## POST `/patient-records`

Create patient record. Doctor only.

### Test Cases

#### `store-success.json` — Success (201)
> [View JSON](./endpoint/patient-record/store/store-success.json)

**Request:**
```json
{ "patient_id": 1, "doctor_id": 1, "clinic_id": 1, "appointment_id": 1, "diagnosis_summary": "Test diagnosis 6a4554057b971" }
```

**Response (201):**
```json
{ "success": true, "message": "Record created successfully", "data": null }
```

---

#### `store-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/patient-record/store/store-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `store-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient-record/store/store-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

#### `store-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/patient-record/store/store-unauthorized-patient.json)

**Response (403):** `{ "success": false, "message": "Permission Denied" }`

---

#### `store-validation.json` — Validation Error (422)
> [View JSON](./endpoint/patient-record/store/store-validation.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "Validation Error", "errors": { "patient_id": [...], "diagnosis_summary": [...] } }
```

---

## PUT `/patient-records/{id}`

Update patient record. Doctor only.

### Test Cases

#### `update-success.json` — Success (200)
> [View JSON](./endpoint/patient-record/update/update-success.json)

**Request:**
```json
{ "diagnosis_summary": "Updated diagnosis 6a4554133bbf7" }
```

**Response (200):**
```json
{ "success": true, "message": "Record updated successfully", "data": { "id": 501, "patient_id": 1, "doctor_id": 1, "appointment_id": 1, "diagnosis_summary": "Updated diagnosis 6a4554133bbf7", "status": "open", "patient": { "name": "Dulce McKenzie", "phone": "0924778300" }, "doctor": { "name": "Amira Hassan" }, "diseases": [], "prescriptions": [], "created_at": "2026-07-01 17:50:03" } }
```

---

#### `update-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient-record/update/update-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

[Back to Index](./00-INDEX.md)
