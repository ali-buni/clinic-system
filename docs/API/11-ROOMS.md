# 11 - Rooms

Room management within clinics.

**Test Cases:** 26 | **Endpoint Folder:** [`endpoint/room/`](./endpoint/room/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/rooms/{clinicId}`](#get-list) | List rooms for clinic | Owner |
| 2 | POST | [`/rooms`](#post-create) | Create a room | Owner |
| 3 | PATCH | [`/rooms/{id}`](#patch-update) | Update a room | Owner |
| 4 | DELETE | [`/rooms/{id}`](#delete-delete) | Delete a room | Owner |
| 5 | GET | [`/rooms/{id}/details`](#get-details) | Get room details | Owner |
| 6 | GET | [`/rooms/{clinicId}/info`](#get-list-with-info) | List rooms with info | Owner |
| 7 | POST | [`/rooms/add/doctors`](#post-add-doctor) | Add doctor to room | Owner |
| 8 | POST | [`/rooms/add/secretaries`](#post-add-secretary) | Add secretary to room | Owner |
| 9 | DELETE | [`/rooms/remove/doctors`](#delete-remove-doctor) | Remove doctor from room | Owner |
| 10 | DELETE | [`/rooms/remove/secretaries`](#delete-remove-secretary) | Remove secretary from room | Owner |
| 11 | GET | [`/rooms/user`](#get-user-rooms) | Get current user's rooms | Auth |

---

## GET `/rooms/{clinicId}`

List rooms for a clinic. Owner only.

### Test Cases

#### `list-success.json` — Success (200)
> [View JSON](./endpoint/room/list/list-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": [
        { "id": 1, "name": "Room 1" },
        { "id": 2, "name": "Room 2" },
        { "id": 3, "name": "Room 3" },
        { "id": 4, "name": "Room 4" },
        { "id": 5, "name": "Room 5" }
    ]
}
```

---

#### `list-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/list/list-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `list-unauthorized-doctor.json` — Forbidden (403)
> [View JSON](./endpoint/room/list/list-unauthorized-doctor.json)

**Response (403):**
```json
{
    "success": false,
    "message": "Permission Denied"
}
```

---

## GET `/rooms/{clinicId}/info`

List rooms with doctor/secretary info. Owner only.

### Test Cases

#### `list-with-info-success.json` — Success (200)
> [View JSON](./endpoint/room/list/list-with-info-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": [
        {
            "id": 1, "clinic_id": 1, "name": "Room 1", "created": "2026-07-01",
            "doctors": [{ "id": 1, "name": "Amira Hassan" }, { "id": 6, "name": "NewDoc Test" }],
            "secretaries": [{ "id": 3, "name": "NewSec Test" }]
        },
        {
            "id": 2, "clinic_id": 1, "name": "Room 2", "created": "2026-07-01",
            "doctors": [{ "id": 2, "name": "Omar Nasser" }],
            "secretaries": [{ "id": 1, "name": "Sara Ali" }, { "id": 2, "name": "Mona Hassan" }]
        }
    ]
}
```

---

#### `list-with-info-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/list/list-with-info-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/rooms`

Create a room. Owner only.

### Test Cases

#### `create-success.json` — Success (201)
> [View JSON](./endpoint/room/create/create-success.json)

**Request:**
```json
{ "name": "New Room 6a454c8be85a9", "clinic_id": 1 }
```

**Response (201):**
```json
{
    "success": true,
    "message": "Room created successfully.",
    "data": null
}
```

---

#### `create-validation.json` — Validation Error (422)
> [View JSON](./endpoint/room/create/create-validation.json)

**Response (422):**
```json
{
    "message": "A room name is required. (and 1 more error)",
    "errors": {
        "name": ["A room name is required."],
        "clinic_id": ["A clinic id is required."]
    }
}
```

---

#### `create-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/create/create-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## PATCH `/rooms/{id}`

Update a room. Owner only.

### Test Cases

#### `update-success.json` — Success (200)
> [View JSON](./endpoint/room/update/update-success.json)

**Request:**
```json
{ "name": "Updated Room 6a454e4e4124a" }
```

**Response (200):**
```json
{
    "success": true,
    "message": "Room updated successfully.",
    "data": null
}
```

---

#### `update-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/update/update-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## DELETE `/rooms/{id}`

Delete a room. Owner only.

### Test Cases

#### `delete-success.json` — Success (200)
> [View JSON](./endpoint/room/delete/delete-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Room removed successfully.",
    "data": null
}
```

---

#### `delete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/delete/delete-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/rooms/{id}/details`

Get room details with doctors and secretaries. Owner only.

### Test Cases

#### `details-success.json` — Success (200)
> [View JSON](./endpoint/room/details/details-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1, "clinic_id": 1, "name": "Room 1", "created": "2026-07-01",
        "doctors": [
            { "id": 1, "name": "Amira Hassan", "phone": "0951232301", "created": "2026-07-01", "gender": "female", "bio": null, "specialties": [] },
            { "id": 6, "name": "NewDoc Test", "phone": null, "created": "2026-07-01", "gender": "male", "bio": null, "specialties": [{ "ar_name": "الطب العام", "en_name": "General Medicine" }] }
        ],
        "secretaries": [
            { "id": 3, "name": "NewSec Test", "phone": null, "created": "2026-07-01", "gender": "male" }
        ]
    }
}
```

---

#### `details-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/details/details-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `details-not-found.json` — Not Found (404)
> [View JSON](./endpoint/room/details/details-not-found.json)

**Response (404):**
```json
{
    "success": false,
    "message": "Room not found",
    "data": null
}
```

---

## POST `/rooms/add/doctors`

Add a doctor to a room. Owner only.

### Test Cases

#### `add-doctor-success.json` — Success (200)
> [View JSON](./endpoint/room/add/add-doctor-success.json)

**Request:**
```json
{ "doctor_id": 1, "room_id": 2 }
```

**Response (200):**
```json
{
    "success": true,
    "message": "The doctor changes the room successfuly",
    "data": null
}
```

---

#### `add-doctor-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/add/add-doctor-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/rooms/add/secretaries`

Add a secretary to rooms. Owner only.

### Test Cases

#### `add-secretary-success.json` — Success (200)
> [View JSON](./endpoint/room/add/add-secretary-success.json)

**Request:**
```json
{ "secretary_id": 1, "room_ids": [1, 2] }
```

**Response (200):**
```json
{
    "success": true,
    "message": "The secretary changes the room successfuly",
    "data": null
}
```

---

#### `add-secretary-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/add/add-secretary-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## DELETE `/rooms/remove/doctors`

Remove a doctor from a room. Owner only.

### Test Cases

#### `remove-doctor-success.json` — Success (200)
> [View JSON](./endpoint/room/remove/remove-doctor-success.json)

**Request:**
```json
{ "doctor_id": 1, "room_id": 2 }
```

**Response (200):**
```json
{
    "success": true,
    "message": "The doctor detach the room successfuly",
    "data": null
}
```

---

#### `remove-doctor-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/remove/remove-doctor-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## DELETE `/rooms/remove/secretaries`

Remove a secretary from rooms. Owner only.

### Test Cases

#### `remove-secretary-success.json` — Success (200)
> [View JSON](./endpoint/room/remove/remove-secretary-success.json)

**Request:**
```json
{ "secretary_id": 1, "room_ids": [1] }
```

**Response (200):**
```json
{
    "success": true,
    "message": "The secretary detach the room successfuly",
    "data": null
}
```

---

#### `remove-secretary-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/remove/remove-secretary-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/rooms/user`

Get current user's assigned rooms. Authenticated users.

### Test Cases

#### `user-rooms-success.json` — Success (200)
> [View JSON](./endpoint/room/user/user-rooms-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": [
        {
            "id": 2, "name": "Room 2", "clinic_id": 1, "created": "2026-07-01",
            "doctors": [{ "id": 2, "name": "Omar Nasser" }],
            "secretaries": [{ "id": 1, "name": "Sara Ali" }, { "id": 2, "name": "Mona Hassan" }]
        },
        {
            "id": 3, "name": "Room 3", "clinic_id": 1, "created": "2026-07-01",
            "doctors": [{ "id": 3, "name": "Layla Farah" }],
            "secretaries": [{ "id": 1, "name": "Sara Ali" }]
        }
    ]
}
```

---

#### `user-rooms-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/user/user-rooms-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `user-rooms-invalid-token.json` — Unauthenticated (401)
> [View JSON](./endpoint/room/user/user-rooms-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
