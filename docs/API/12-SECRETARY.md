# 12 - Secretary

Secretary info and update operations.

**Test Cases:** 7 | **Endpoint Folder:** [`endpoint/secretary/`](./endpoint/secretary/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/secretaries/{id}`](#get-info) | Get secretary info | Owner/Doctor |
| 2 | POST | [`/secretaries/update`](#post-update) | Update secretary | Secretary |

---

## GET `/secretaries/{id}`

Get secretary info. Owner or Doctor.

### Test Cases

#### `info-owner-success.json` — Owner Success (200)
> [View JSON](./endpoint/secretary/info/info-owner-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1,
        "user_id": 7,
        "clinic_id": 1,
        "created_at": "2026-07-01",
        "role": "secretary",
        "name": "UpdatedSec 6a44fe84ad461 Ali",
        "email": "secretary0@clinic.test",
        "phone": "0900000001",
        "dob": "2026-07-01",
        "gender": "female"
    }
}
```

---

#### `info-doctor-success.json` — Doctor Success (200)
> [View JSON](./endpoint/secretary/info/info-doctor-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1,
        "user_id": 7,
        "clinic_id": 1,
        "created_at": "2026-07-01",
        "role": "secretary",
        "name": "UpdatedSec 6a44fe84ad461 Ali",
        "email": "secretary0@clinic.test",
        "phone": "0900000001",
        "dob": "2026-07-01",
        "gender": "female"
    }
}
```

---

#### `info-not-found.json` — Not Found (404)
> [View JSON](./endpoint/secretary/info/info-not-found.json)

**Response (404):**
```json
{
    "success": false,
    "message": "Secretary not found",
    "data": null
}
```

---

#### `info-invalid-token.json` — Unauthenticated (401)
> [View JSON](./endpoint/secretary/info/info-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `info-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/secretary/info/info-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/secretaries/update`

Update secretary profile. Secretary only.

### Test Cases

#### `update-secretary-success.json` — Success (200)
> [View JSON](./endpoint/secretary/update/update-secretary-success.json)

**Request:**
```json
{ "fname": "UpdatedSec 6a44ff999b5ed" }
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

#### `update-unauthorized-doctor.json` — Forbidden (403)
> [View JSON](./endpoint/secretary/update/update-unauthorized-doctor.json)

**Response (403):**
```json
{
    "success": false,
    "message": "Permission Denied"
}
```

---

[Back to Index](./00-INDEX.md)
