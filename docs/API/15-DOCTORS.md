# 15 - Doctors

Doctor info, filter, delete, restore, force-delete, and update operations.

**Test Cases:** 20 | **Endpoint Folder:** [`endpoint/doctor/`](./endpoint/doctor/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/doctors/{id}/info`](#get-info) | Get doctor info | Owner/Doctor |
| 2 | GET | [`/doctors/filter`](#get-filter) | Filter doctors list | Owner/Secretary |
| 3 | DELETE | [`/doctors/{id}/leave`](#delete-delete) | Doctor leave clinic | Doctor/Owner |
| 4 | POST | [`/doctors/{id}/restore`](#post-restore) | Restore doctor | Owner |
| 5 | DELETE | [`/doctors/{id}/force`](#delete-force) | Force delete doctor | Owner |
| 6 | POST | [`/doctors/update`](#post-update) | Update doctor profile | Doctor |

---

## GET `/doctors/{id}/info`

Get doctor info. Owner or Doctor (self).

### Test Cases

#### `info-success.json` — Owner Success (200)
> [View JSON](./endpoint/doctor/info/info-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1, "user_id": 2, "clinic_id": 1, "room_id": 1,
        "name": "Amira Hassan", "phone": "0951232301",
        "email": "doctor0@clinic.test", "dob": null, "gender": "female",
        "created_at": "2026-07-01", "appointment_duration": 30,
        "consultation_fee": 177.37, "bio": null, "specialties": []
    }
}
```

---

#### `info-self-success.json` — Doctor Self Success (200)
> [View JSON](./endpoint/doctor/info/info-self-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1, "user_id": 2, "clinic_id": 1, "room_id": 1,
        "name": "Amira Hassan", "phone": "0951232301",
        "email": "doctor0@clinic.test", "dob": null, "gender": "female",
        "created_at": "2026-07-01", "appointment_duration": 30,
        "consultation_fee": 177.37, "bio": null, "specialties": []
    }
}
```

---

#### `info-not-found.json` — Not Found (404)
> [View JSON](./endpoint/doctor/info/info-not-found.json)

**Response (404):**
```json
{
    "success": false,
    "message": "Doctor not found",
    "data": null
}
```

---

#### `info-invalid-token.json` — Unauthenticated (401)
> [View JSON](./endpoint/doctor/info/info-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `info-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/doctor/info/info-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `info-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/doctor/info/info-unauthorized-patient.json)

**Response (403):**
```json
{
    "success": false,
    "message": "Permission Denied"
}
```

---

## GET `/doctors/filter`

Filter doctors list. Owner or Secretary.

### Test Cases

#### `filter-secretary-success.json` — Secretary Success (200)
> [View JSON](./endpoint/doctor/filter/filter-secretary-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Doctors collection retrieved successfully.",
    "data": [
        { "id": 1, "user_id": 2, "clinic_id": 1, "room_id": 1, "name": "Amira Hassan", "phone": "0951232301", "email": "doctor0@clinic.test", "gender": "female", "consultation_fee": 350, "specialties": [] },
        { "id": 2, "user_id": 3, "clinic_id": 1, "room_id": 2, "name": "Omar Nasser", "phone": "0951232302", "email": "doctor1@clinic.test", "gender": "male", "consultation_fee": 234.33, "specialties": [] }
    ],
    "pagination": { "total": 7, "count": 7, "per_page": 15, "current_page": 1, "last_page": 1 }
}
```

---

#### `filter-invalid-token.json` — Unauthenticated (401)
> [View JSON](./endpoint/doctor/filter/filter-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `filter-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/doctor/filter/filter-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `filter-unauthorized-doctor.json` — Forbidden (403)
> [View JSON](./endpoint/doctor/filter/filter-unauthorized-doctor.json)

**Response (403):**
```json
{
    "success": false,
    "message": "Permission Denied"
}
```

---

## DELETE `/doctors/{id}/leave`

Doctor leaves the clinic. Doctor or Owner.

### Test Cases

#### `delete-success.json` — Success (200)
> [View JSON](./endpoint/doctor/delete/delete-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "The doctor has successfully left the clinic.",
    "data": null
}
```

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/doctor/delete/delete-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `delete-unauthorized-doctor.json` — Forbidden (403)
> [View JSON](./endpoint/doctor/delete/delete-unauthorized-doctor.json)

**Response (403):**
```json
{
    "success": false,
    "message": "Permission Denied"
}
```

---

## POST `/doctors/{id}/restore`

Restore a soft-deleted doctor. Owner only.

### Test Cases

#### `restore-success.json` — Success (200)
> [View JSON](./endpoint/doctor/restore/restore-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Doctor restored successfully.",
    "data": { "id": null, "user_id": null, "name": " ", "specialties": [] }
}
```

---

#### `restore-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/doctor/restore/restore-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## DELETE `/doctors/{id}/force`

Permanently delete a doctor. Owner only.

### Test Cases

#### `force-delete-success.json` — Success (200)
> [View JSON](./endpoint/doctor/force/force-delete-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "The doctor has been permanently deleted from the system.",
    "data": null
}
```

---

#### `force-delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/doctor/force/force-delete-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/doctors/update`

Update doctor profile. Doctor only.

### Test Cases

#### `update-success.json` — Success (200)
> [View JSON](./endpoint/doctor/update/update-success.json)

**Request:**
```json
{ "consultation_fee": 350 }
```

**Response (200):**
```json
{
    "success": true,
    "message": "Your profile has been updated successfully.",
    "data": null
}
```

---

#### `update-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/doctor/update/update-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `update-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/doctor/update/update-unauthorized-patient.json)

**Response (403):**
```json
{
    "success": false,
    "message": "Permission Denied"
}
```

---

[Back to Index](./00-INDEX.md)
