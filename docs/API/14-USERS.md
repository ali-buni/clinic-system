# 14 - Users

User info, profile image, and update operations.

**Test Cases:** 11 | **Endpoint Folder:** [`endpoint/user/`](./endpoint/user/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/users/info`](#get-info) | Get current user info | Auth |
| 2 | GET | [`/users/image-url`](#get-image-url) | Get profile image URL | Auth |
| 3 | POST | [`/users/update-image`](#post-update-image) | Update profile image | Auth |

---

## GET `/users/info`

Get current user's info. Returns role-specific data.

### Test Cases

#### `info-owner-success.json` — Owner Success (200)
> [View JSON](./endpoint/user/info/info-owner-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1, "name": "Clinic Owner", "phone": "0911111111",
        "email": "owner@gmail.com", "gender": "male", "dob": "2026-07-01",
        "profile_image": null, "created": "2026-07-01", "role": "owner"
    }
}
```

---

#### `info-doctor-success.json` — Doctor Success (200)
> [View JSON](./endpoint/user/info/info-doctor-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 2, "name": "Amira Hassan", "phone": "0951232301",
        "email": "doctor0@clinic.test", "gender": "female", "dob": "2026-07-01",
        "profile_image": null, "created": "2026-07-01", "role": "doctor",
        "specialties": [{ "ar_name": "الطب العام", "en_name": "General Medicine" }],
        "appointment_duration": 30, "bio": null, "consultation_fee": 141.37
    }
}
```

---

#### `info-secretary-success.json` — Secretary Success (200)
> [View JSON](./endpoint/user/info/info-secretary-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 7, "name": "Sara Ali", "phone": "0900000001",
        "email": "secretary0@clinic.test", "gender": "female", "dob": "2026-07-01",
        "profile_image": null, "created": "2026-07-01", "role": "secretary"
    }
}
```

---

#### `info-patient-success.json` — Patient Success (200)
> [View JSON](./endpoint/user/info/info-patient-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 9, "name": "Matilde Fay", "phone": "0960533102",
        "email": "emmanuel12@example.org", "gender": "unknown", "dob": "2002-10-07",
        "profile_image": null, "created": "2026-07-01", "role": "patient",
        "clinic_id": null, "nationality": "Tajikistan",
        "address": "9477 Richie Mission Apt. 173\nRosenbaumstad, DE 15401",
        "marital_status": "single", "emergency_phone": "0936762689",
        "allergies": "Explicabo placeat ut in laudantium quae corporis.",
        "chronic_conditions": "Blanditiis maiores non accusantium voluptas harum et quia.",
        "career": "Train Crew", "blood_type": "B-"
    }
}
```

---

#### `info-invalid-token.json` — Unauthenticated (401)
> [View JSON](./endpoint/user/info/info-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `info-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/user/info/info-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/users/image-url`

Get current user's profile image URL. Authenticated users.

### Test Cases

#### `image-url-owner-success.json` — Success (200)
> [View JSON](./endpoint/user/image/image-url-owner-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "profile_image_url": "http://localhost:8000/storage/defaults/avatar.svg"
    }
}
```

---

#### `image-url-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/user/image/image-url-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/users/update-image`

Update profile image. Authenticated users.

### Test Cases

#### `update-image-patient-success.json` — Patient Success (200)
> [View JSON](./endpoint/user/update/update-image-patient-success.json)

**Request:** `multipart/form-data` with `profile_image` file

**Response (200):**
```json
{
    "success": true,
    "message": "Profile image updated successfully.",
    "data": {
        "id": 9, "name": "Matilde Fay", "phone": "0960533102",
        "email": "emmanuel12@example.org", "gender": "unknown",
        "dob": "2002-10-07",
        "profile_image": "profile_images/20260701_114632_6a44fe1889dc1.png",
        "created": "2026-07-01", "role": "patient"
    }
}
```

---

#### `update-image-validation.json` — Validation Error (422)
> [View JSON](./endpoint/user/update/update-image-validation.json)

**Response (422):**
```json
{
    "message": "The profile image field is required.",
    "errors": {
        "profile_image": ["The profile image field is required."]
    }
}
```

---

#### `update-image-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/user/update/update-image-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
