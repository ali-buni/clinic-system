# 05 - Specialties

Medical specialty management for doctors.

**Test Cases:** 14 | **Endpoint Folder:** [`endpoint/specialty/`](./endpoint/specialty/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/specialties`](#get-index) | List all specialties | Public |
| 2 | GET | [`/specialties/doctor/{id}`](#get-doctor-specialties) | Show doctor specialties | Required |
| 3 | GET | [`/specialties/doctor/{id}/primary`](#get-primary) | Show primary specialty | Required |
| 4 | POST | [`/specialties`](#post-attach) | Attach specialties to doctor | Required |
| 5 | POST | [`/specialties/{id}/primary`](#post-change-primary) | Change primary specialty | Required |
| 6 | DELETE | [`/specialties/{id}`](#delete-detach) | Detach specialty from doctor | Required |

---

## GET `/specialties`

List all specialties. Public.

### Test Cases

#### `index-public-success.json` — Success (200)
> [View JSON](./endpoint/specialty/index/index-public-success.json)

**Response (200):**
```json
{ "success": true, "message": "Specialties retrieved successfully", "data": [ { "id": 1, "ar_name": "الطب العام", "en_name": "General Medicine" }, ... (25 total specialties) ] }
```

---

## GET `/specialties/doctor/{id}`

Show doctor specialties. Auth required.

### Test Cases

#### `show-doctor-specialties-success.json` — Success (200)
> [View JSON](./endpoint/specialty/show/show-doctor-specialties-success.json)

**Response (200):**
```json
{ "success": true, "message": "Doctor specialties retrieved", "data": [ { "id": 1, "ar": "الطب العام", "en": "General Medicine" } ] }
```

---

#### `show-doctor-specialties-not-found.json` — Not Found (200)
> [View JSON](./endpoint/specialty/show/show-doctor-specialties-not-found.json)

**Response (200):**
```json
{ "success": true, "message": "Doctor specialties retrieved", "data": [ { "id": 1, "ar": "الطب العام", "en": "General Medicine" } ] }
```

---

#### `show-doctor-specialties-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/specialty/show/show-doctor-specialties-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/specialties/doctor/{id}/primary`

Show primary specialty. Auth required.

### Test Cases

#### `show-primary-success.json` — Success (200)
> [View JSON](./endpoint/specialty/show/show-primary-success.json)

**Response (200):**
```json
{ "success": true, "message": "Primary specialty retrieved", "data": { "id": 1, "ar": "الطب العام", "en": "General Medicine" } }
```

---

#### `show-primary-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/specialty/show/show-primary-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/specialties`

Attach specialties to doctor. Auth required.

### Test Cases

#### `attach-success.json` — Success (200)
> [View JSON](./endpoint/specialty/attach/attach-success.json)

**Request:**
```json
{ "doctor_id": 1, "specialty_ids": [1, 2] }
```

**Response (200):**
```json
{ "success": true, "message": "current_specialties", "data": [ { "id": 1, "ar": "الطب العام", "en": "General Medicine" }, { "id": 2, "ar": "الطب الباطني", "en": "Internal Medicine" } ] }
```

---

#### `attach-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/specialty/attach/attach-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `attach-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/specialty/attach/attach-unauthorized-patient.json)

**Response (403):**
```json
{ "success": false, "message": "Permission Denied" }
```

---

#### `attach-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/specialty/attach/attach-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/specialties/{id}/primary`

Change primary specialty. Auth required.

### Test Cases

#### `change-primary-success.json` — Success (200)
> [View JSON](./endpoint/specialty/change/change-primary-success.json)

**Response (200):**
```json
{ "success": true, "message": "Primary specialty updated successfully", "data": null }
```

---

#### `change-primary-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/specialty/change/change-primary-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## DELETE `/specialties/{id}`

Detach specialty from doctor. Auth required.

### Test Cases

#### `detach-success.json` — Success (200)
> [View JSON](./endpoint/specialty/detach/detach-success.json)

**Response (200):**
```json
{ "success": true, "message": "Specialty detached successfully", "data": [ { "id": 1, "ar": "الطب العام", "en": "General Medicine" } ] }
```

---

#### `detach-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/specialty/detach/detach-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
