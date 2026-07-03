# 02 - Verification

Email and phone verification endpoints.

**Test Cases:** 7 | **Endpoint Folder:** [`endpoint/verification/`](./endpoint/verification/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | POST | [`/resend-code`](#post-resend-code) | Resend verification code | Public |
| 2 | POST | [`/verify-code`](#post-verify-code) | Verify email verification code | Public |

---

## POST `/resend-code`

Resend verification code. Public.

### Test Cases

#### `resend-code-success.json` — Success (200)
> [View JSON](./endpoint/verification/resend/resend-code-success.json)

**Request:**
```json
{ "login": "zharris@example.org", "password": "password" }
```

**Response (200):**
```json
{ "success": true, "message": "Email verification code sent successfully.", "data": null }
```

---

#### `resend-code-validation.json` — Validation Error (422)
> [View JSON](./endpoint/verification/resend/resend-code-validation.json)

**Request:**
```json
{ "email": "not-an-email" }
```

**Response (422):**
```json
{ "message": "The login field is required. (and 1 more error)", "errors": { "login": ["The login field is required."], "password": ["Password is required"] } }
```

---

#### `resend-code-not-found.json` — Not Found (422)
> [View JSON](./endpoint/verification/resend/resend-code-not-found.json)

**Request:**
```json
{ "email": "nonexistent@test.com" }
```

**Response (422):**
```json
{ "message": "The login field is required. (and 1 more error)", "errors": { "login": ["The login field is required."], "password": ["Password is required"] } }
```

---

#### `resend-code-missing.json` — Missing Fields (422)
> [View JSON](./endpoint/verification/resend/resend-code-missing.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "The login field is required. (and 1 more error)", "errors": { "login": ["The login field is required."], "password": ["Password is required"] } }
```

---

## POST `/verify-code`

Verify email verification code. Public.

### Test Cases

#### `verify-code-success.json` — Success (200)
> [View JSON](./endpoint/verification/verify/verify-code-success.json)

**Request:**
```json
{ "login": "owner@gmail.com", "code": "123456", "type": "email" }
```

**Response (200):**
```json
{ "success": true, "message": "Email verified successfully.", "data": { "token": "90|V4SBK6hiXrak2XfkKPioTBPqdlDnfj7tUiX5vNtqb885a340", "id": 1, "role": "owner", "name": "Clinic Owner" } }
```

---

#### `verify-code-validation.json` — Validation Error (422)
> [View JSON](./endpoint/verification/verify/verify-code-validation.json)

**Request:**
```json
{ "email": "not-an-email", "code": "" }
```

**Response (422):**
```json
{ "message": "The login field is required. (and 2 more errors)", "errors": { "login": ["The login field is required."], "code": ["Code is required"], "type": ["The type field is required."] } }
```

---

#### `verify-code-missing.json` — Missing Fields (422)
> [View JSON](./endpoint/verification/verify/verify-code-missing.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "The login field is required. (and 2 more errors)", "errors": { "login": ["The login field is required."], "code": ["Code is required"], "type": ["The type field is required."] } }
```

---

[Back to Index](./00-INDEX.md)
