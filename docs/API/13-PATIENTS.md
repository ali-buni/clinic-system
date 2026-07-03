# 13 - Patients

Patient management, medical history, soft-deletes, and restore operations.

**Test Cases:** 20 | **Endpoint Folder:** [`endpoint/patient/`](./endpoint/patient/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/patients`](#get-list) | List all patients | Owner |
| 2 | GET | [`/patients/{id}`](#get-show) | Show patient details | Owner/Doctor |
| 3 | POST | [`/patients/update`](#post-update) | Update patient | Patient |
| 4 | DELETE | [`/patients/delete`](#delete-delete) | Soft-delete patient | Owner |
| 5 | GET | [`/patients/trashed`](#get-trashed) | List trashed patients | Owner |
| 6 | GET | [`/patients/restore/patient`](#get-restore) | Restore patient | Owner |
| 7 | GET | [`/patients/{id}/medical-history`](#get-medical) | Get medical history | Owner/Doctor/Patient |

---

## GET `/patients`

List all patients. Owner only.

### Test Cases

#### `list-success.json` — Success (200)
> [View JSON](./endpoint/patient/list/list-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Patients retrieved successfully",
    "data": [
        { "id": 1, "user_id": 9, "name": "Dulce McKenzie", "gender": "female", "profile_image": null },
        { "id": 2, "user_id": 11, "name": "Hollis Wilderman", "gender": "unknown", "profile_image": null }
    ],
    "pagination": {
        "total": 70, "count": 15, "per_page": 15,
        "current_page": 1, "last_page": 5
    }
}
```

---

#### `list-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient/list/list-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `list-invalid-token.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient/list/list-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `list-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/patient/list/list-unauthorized-patient.json)

**Response (403):**
```json
{
    "success": false,
    "message": "Permission Denied"
}
```

---

## GET `/patients/{id}`

Show patient details. Owner or Doctor.

### Test Cases

#### `show-success.json` — Success (200)
> [View JSON](./endpoint/patient/show/show-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "the patient data.",
    "data": {
        "id": 1, "user_id": 9, "name": "Dulce McKenzie", "gender": "female",
        "profile_image": null, "nationality": "Mali",
        "address": "797 Cassandra Course Suite 739\nEast Lorena, MS 65010",
        "marital_status": "other", "emergency_phone": "0937376226",
        "allergies": "Rerum in quia odit ut velit eum veritatis.",
        "chronic_conditions": "Ipsum possimus at est.",
        "career": "Medical Technician", "blood_type": "AB+",
        "phone": "0924778300", "email": "armando02@example.com",
        "dob": "1983-06-16", "created_at": "2026-07-01"
    }
}
```

---

#### `show-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient/show/show-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `show-not-found.json` — Not Found (500)
> [View JSON](./endpoint/patient/show/show-not-found.json)

**Response (500):**
```json
{
    "success": false,
    "message": "The patient is not found.",
    "data": null
}
```

---

## POST `/patients/update`

Update patient profile. Patient only.

### Test Cases

#### `update-success.json` — Success (200)
> [View JSON](./endpoint/patient/update/update-success.json)

**Request:**
```json
{ "patient_id": 1, "dob": "1995-06-15" }
```

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": null
}
```

---

#### `update-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient/update/update-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `update-invalid-token.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient/update/update-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## DELETE `/patients/delete`

Soft-delete a patient. Owner only.

### Test Cases

#### `delete-success.json` — Success (200)
> [View JSON](./endpoint/patient/delete/delete-success.json)

**Request:**
```json
{ "patient_id": 1 }
```

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": null
}
```

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient/delete/delete-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/patients/trashed`

List soft-deleted patients. Owner only.

### Test Cases

#### `trashed-success.json` — Success (200)
> [View JSON](./endpoint/patient/trashed/trashed-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "success",
    "data": [
        { "id": 1, "user_id": 9, "name": "Dulce McKenzie", "gender": "female", "profile_image": null }
    ],
    "pagination": {
        "total": 1, "count": 1, "per_page": 15,
        "current_page": 1, "last_page": 1
    }
}
```

---

#### `trashed-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient/trashed/trashed-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/patients/restore/patient`

Restore a soft-deleted patient. Owner only.

### Test Cases

#### `restore-success.json` — Success (200)
> [View JSON](./endpoint/patient/restore/restore-success.json)

**Request:**
```json
{ "patient_id": 1 }
```

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": null
}
```

---

#### `restore-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient/restore/restore-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/patients/{id}/medical-history`

Get patient medical history. Owner, Doctor, or Patient (self).

### Test Cases

#### `medical-history-success.json` — Doctor Success (200)
> [View JSON](./endpoint/patient/medical/medical-history-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Medical history retrieved successfully.",
    "data": {
        "id": 1, "name": "Dulce McKenzie", "phone": "0924778300",
        "email": "armando02@example.com", "gender": "female", "dob": "1983-06-16",
        "appointments": [
            { "id": 497, "doctor_name": "Khaled Sami", "start_time": "2026-05-07 11:30", "end_time": "2026-05-07 12:00", "status": "confirmed", "visit_reason": "Nisi optio harum non consequuntur." }
        ],
        "records": [
            { "id": 497, "doctor_name": "Khaled Sami", "diagnosis_summary": "Expedita architecto vero consequuntur.", "status": "closed", "diseases": [...], "prescriptions": [...] }
        ],
        "invoices": [
            { "id": 497, "invoice_number": "INV-PJZE3WGJ", "status": "issued", "total_cost": "297.38", "issued_at": "2026-05-07" }
        ]
    }
}
```

---

#### `medical-history-patient-self-success.json` — Patient Self Success (200)
> [View JSON](./endpoint/patient/medical/medical-history-patient-self-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Medical history retrieved successfully.",
    "data": { "id": 1, "name": "Dulce McKenzie", "appointments": [...], "records": [...], "invoices": [...] }
}
```

---

#### `medical-history-not-found.json` — Not Found (500)
> [View JSON](./endpoint/patient/medical/medical-history-not-found.json)

**Response (500):**
```json
{
    "success": false,
    "message": "Patient not found.",
    "data": null
}
```

---

#### `medical-history-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/patient/medical/medical-history-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
