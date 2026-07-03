# 10 - Clinics

Clinic creation, info, and update operations.

**Test Cases:** 9 | **Endpoint Folder:** [`endpoint/clinic/`](./endpoint/clinic/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | POST | [`/doctors/register`](#post-create-doctor) | Register a doctor into clinic | Owner |
| 2 | POST | [`/secretaries/register`](#post-create-secretary) | Register a secretary into clinic | Owner |
| 3 | GET | [`/info`](#get-info) | Get clinic public info | Auth |
| 4 | POST | [`/update/{id}`](#post-update) | Update clinic | Owner |

---

## POST `/doctors/register`

Register a doctor into the clinic. Owner only.

### Test Cases

#### `create-doctor-success.json` — Success (200)
> [View JSON](./endpoint/clinic/create/create-doctor-success.json)

**Request:**
```json
{
    "fname": "NewDoc",
    "lname": "Test",
    "email": "newdoc_6a451aef826b0@test.com",
    "dob": "1999-01-01",
    "gender": "male",
    "clinic_id": 1,
    "room_id": 1,
    "consultation_fee": 10,
    "specialty_ids": [1, 3],
    "appointment_duration": 30
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Doctor created successfully.",
    "data": null
}
```

---

#### `create-doctor-validation.json` — Validation Error (422)
> [View JSON](./endpoint/clinic/create/create-doctor-validation.json)

**Response (422):**
```json
{
    "message": "First name is required. (and 9 more errors)",
    "errors": {
        "fname": ["First name is required."],
        "lname": ["Last name is required."],
        "email": ["Email address is required."],
        "dob": ["Date of birth is required."],
        "gender": ["Gender selection is required."],
        "clinic_id": ["Please select a clinic."],
        "room_id": ["Please select a room."],
        "appointment_duration": ["Appointment duration is required."],
        "consultation_fee": ["Consultation fee is required."],
        "specialty_ids": ["At least one specialty must be selected."]
    }
}
```

---

#### `create-doctor-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/clinic/create/create-doctor-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/secretaries/register`

Register a secretary into the clinic. Owner only.

### Test Cases

#### `create-secretary-success.json` — Success (200)
> [View JSON](./endpoint/clinic/create/create-secretary-success.json)

**Request:**
```json
{
    "fname": "NewSec",
    "lname": "Test",
    "email": "newsec_6a4519ea381bb@test.com",
    "dob": "1999-01-01",
    "gender": "male",
    "clinic_id": 1,
    "room_ids": [1]
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Secretary created successfully.",
    "data": null
}
```

---

#### `create-secretary-unauthorized-doctor.json` — Forbidden (403)
> [View JSON](./endpoint/clinic/create/create-secretary-unauthorized-doctor.json)

**Response (403):**
```json
{
    "success": false,
    "message": "Permission Denied"
}
```

---

## GET `/info`

Get clinic public info. Authenticated users.

### Test Cases

#### `info-public-success.json` — Success (200)
> [View JSON](./endpoint/clinic/info/info-public-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1,
        "title": "Updated Clinic 6a4519e5d198f",
        "location": "123 Main Street",
        "phone": "0951232317",
        "rooms_count": {
            "total": 5,
            "details": [
                { "id": 1, "name": "Room 1" },
                { "id": 2, "name": "Room 2" },
                { "id": 3, "name": "Room 3" },
                { "id": 4, "name": "Room 4" },
                { "id": 5, "name": "Room 5" }
            ]
        },
        "created_at": "2026-07-01",
        "doctors": [
            { "name": "Amira Hassan", "specialities": [], "phone": "0951232301", "room_id": 1 },
            { "name": "Omar Nasser", "specialities": [], "phone": "0951232302", "room_id": 2 }
        ],
        "secretaries": [
            { "name": "Sara Ali", "phone": "0900000001", "room_ids": [2, 3] },
            { "name": "Mona Hassan", "phone": "0900000002", "room_ids": [2] }
        ]
    }
}
```

---

## POST `/update/{id}`

Update clinic details. Owner only.

### Test Cases

#### `update-success.json` — Success (200)
> [View JSON](./endpoint/clinic/update/update-success.json)

**Request:**
```json
{ "title": "Updated Clinic 6a4519e5d198f" }
```

**Response (200):**
```json
{
    "success": true,
    "message": "Clinic updated successfully.",
    "data": null
}
```

---

#### `update-unauthorized-doctor.json` — Forbidden (403)
> [View JSON](./endpoint/clinic/update/update-unauthorized-doctor.json)

**Response (403):**
```json
{
    "success": false,
    "message": "Permission Denied"
}
```

---

#### `update-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/clinic/update/update-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
