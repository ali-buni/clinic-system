# 04 - Devices

FCM device token registration.

**Test Cases:** 4 | **Endpoint Folder:** [`endpoint/device/`](./endpoint/device/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | POST | [`/devices/register-token`](#post-register-token) | Register FCM device token | Required |

---

## POST `/devices/register-token`

Register FCM device token. Auth required.

### Test Cases

#### `register-token-success.json` — Success (200)
> [View JSON](./endpoint/device/register/register-token-success.json)

**Request:**
```json
{ "fcm_token": "test-token-owner-6a44f9433436c" }
```

**Response (200):**
```json
{ "message": "Not implemented yet." }
```

---

#### `register-token-validation.json` — Validation (200)
> [View JSON](./endpoint/device/register/register-token-validation.json)

**Request:** `{}` (empty)

**Response (200):**
```json
{ "message": "Not implemented yet." }
```

---

#### `register-token-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/device/register/register-token-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `register-token-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/device/register/register-token-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
