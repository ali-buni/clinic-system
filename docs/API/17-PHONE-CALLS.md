# 17 - Phone

Phone number update and verification operations.

**Test Cases:** 8 | **Endpoint Folder:** [`endpoint/phone/`](./endpoint/phone/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | POST | [`/phone/update`](#post-update) | Request phone update | Auth |
| 2 | POST | [`/phone/verify-update`](#post-verify) | Verify phone update | Auth |

---

## POST `/phone/update`

Request a phone number change. Sends a verification code to the new number. Authenticated users.

### Test Cases

#### `update-phone-success.json` — Success (200)
> [View JSON](./endpoint/phone/update/update-phone-success.json)

**Request:**
```json
{ "phone": "0944444445" }
```

**Response (200):**
```json
{
    "success": true,
    "message": "Verification code sent to your new phone number.",
    "data": {
        "new_phone": "0944444445"
    }
}
```

---

#### `update-phone-validation.json` — Validation Error (422)
> [View JSON](./endpoint/phone/update/update-phone-validation.json)

**Response (422):**
```json
{
    "message": "The phone field is required.",
    "errors": {
        "phone": ["The phone field is required."]
    }
}
```

---

#### `update-phone-invalid-token.json` — Unauthenticated (401)
> [View JSON](./endpoint/phone/update/update-phone-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `update-phone-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/phone/update/update-phone-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/phone/verify-update`

Verify the phone number change with the code received. Authenticated users.

### Test Cases

#### `verify-phone-update-success.json` — Success (200)
> [View JSON](./endpoint/phone/verify/verify-phone-update-success.json)

**Request:**
```json
{ "code": "123456" }
```

**Response (200):**
```json
{
    "success": true,
    "message": "Phone number updated successfully.",
    "data": null
}
```

---

#### `verify-phone-update-missing.json` — Missing Code (422)
> [View JSON](./endpoint/phone/verify/verify-phone-update-missing.json)

**Response (422):**
```json
{
    "message": "The code field is required.",
    "errors": {
        "code": ["The code field is required."]
    }
}
```

---

#### `verify-phone-update-validation.json` — Validation Error (422)
> [View JSON](./endpoint/phone/verify/verify-phone-update-validation.json)

**Request:**
```json
{ "code": "" }
```

**Response (422):**
```json
{
    "message": "The code field is required.",
    "errors": {
        "code": ["The code field is required."]
    }
}
```

---

#### `verify-phone-update-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/phone/verify/verify-phone-update-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
