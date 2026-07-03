# 01 - Auth

Authentication endpoints for login, registration, password management, and OAuth.

**Test Cases:** 24 | **Endpoint Folder:** [`endpoint/auth/`](./endpoint/auth/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | POST | [`/login`](#post-login) | Authenticate user credentials | Public |
| 2 | POST | [`/register`](#post-register) | Register a new user | Public |
| 3 | POST | [`/signout`](#post-signout) | Revoke current token | Required |
| 4 | POST | [`/refresh-token`](#post-refresh-token) | Refresh authentication token | Required |
| 5 | POST | [`/reset-password`](#post-reset-password) | Reset password (authenticated) | Required |
| 6 | POST | [`/reset-password-with-code`](#post-reset-password-with-code) | Reset password using verification code | Public |
| 7 | POST | [`/forgot-password`](#post-forgot-password) | Send password reset link | Public |

---

## POST `/login`

Authenticate user credentials.

### Test Cases

#### `login-owner-success.json` — Success (200)
> [View JSON](./endpoint/auth/login/login-owner-success.json)

**Request:**
```json
{
    "login": "owner@gmail.com",
    "password": "password"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Email verification code sent successfully.",
    "data": null
}
```

---

#### `login-missing-fields.json` — Validation Error (422)
> [View JSON](./endpoint/auth/login/login-missing-fields.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{
    "message": "The login field is required. (and 1 more error)",
    "errors": {
        "login": ["The login field is required."],
        "password": ["The password field is required."]
    }
}
```

---

#### `login-invalid-credentials.json` — Invalid Credentials (401)
> [View JSON](./endpoint/auth/login/login-invalid-credentials.json)

**Request:**
```json
{
    "login": "owner@gmail.com",
    "password": "wrongpassword"
}
```

**Response (401):**
```json
{
    "success": false,
    "message": "invalid credentials.",
    "data": null
}
```

---

## POST `/register`

Register a new user.

### Test Cases

#### `register-success.json` — Success (200)
> [View JSON](./endpoint/auth/register/register-success.json)

**Request:**
```json
{
    "fname": "New",
    "lname": "User",
    "email": "newuser_6a44efa13579d@test.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Registration successful. Please check your email for the verification code.",
    "data": null
}
```

---

#### `register-validation.json` — Validation Error (422)
> [View JSON](./endpoint/auth/register/register-validation.json)

**Request:**
```json
{
    "fname": "",
    "lname": "",
    "email": "not-an-email",
    "password": "short",
    "password_confirmation": "short",
    "clinic_id": 999
}
```

**Response (422):**
```json
{
    "message": "First name is required. (and 3 more errors)",
    "errors": {
        "fname": ["First name is required."],
        "lname": ["Last name is required."],
        "email": ["Please provide a valid email address."],
        "password": ["Password must be at least 8 characters."]
    }
}
```

---

#### `register-missing-fields.json` — Missing Fields (422)
> [View JSON](./endpoint/auth/register/register-missing-fields.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{
    "message": "First name is required. (and 3 more errors)",
    "errors": {
        "fname": ["First name is required."],
        "lname": ["Last name is required."],
        "email": ["Email address is required."],
        "password": ["Password is required."]
    }
}
```

---

#### `register-duplicate-email.json` — Duplicate Email (200)
> [View JSON](./endpoint/auth/register/register-duplicate-email.json)

**Request:**
```json
{
    "fname": "Another",
    "lname": "User",
    "email": "owner@gmail.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Registration successful. Please check your email for the verification code.",
    "data": null
}
```

---

#### `register-password-mismatch.json` — Password Mismatch (422)
> [View JSON](./endpoint/auth/register/register-password-mismatch.json)

**Request:**
```json
{
    "fname": "New",
    "lname": "User",
    "email": "newuser_6a44ef9d79044@test.com",
    "password": "password123",
    "password_confirmation": "different"
}
```

**Response (422):**
```json
{
    "message": "Password confirmation does not match.",
    "errors": {
        "password": ["Password confirmation does not match."]
    }
}
```

---

## POST `/signout`

Revoke current token. Auth required.

### Test Cases

#### `signout-success.json` — Success (200)
> [View JSON](./endpoint/auth/signout/signout-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Logged out successfully",
    "data": null
}
```

---

#### `signout-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/auth/signout/signout-invalid-token.json)

**Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

#### `signout-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/auth/signout/signout-unauthenticated.json)

**Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

## POST `/refresh-token`

Refresh authentication token. Auth required.

### Test Cases

#### `refresh-token-success.json` — Success (200)
> [View JSON](./endpoint/auth/refresh/refresh-token-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Token refreshed successfully",
    "data": {
        "auth_token": "55|YjZYlrNYk0M1TZiBfMkWNLNLJOhoIpYmpiBkzS6Yfbc6766a"
    }
}
```

---

#### `refresh-token-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/auth/refresh/refresh-token-invalid-token.json)

**Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

#### `refresh-token-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/auth/refresh/refresh-token-unauthenticated.json)

**Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

## POST `/reset-password`

Reset password (authenticated). Auth required.

### Test Cases

#### `reset-password-success.json` — Success (200)
> [View JSON](./endpoint/auth/reset/reset-password-success.json)

**Request:**
```json
{
    "email": "owner@gmail.com",
    "password": "password",
    "password_confirmation": "password",
    "new_password": "newpass123",
    "new_password_confirmation": "newpass123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "the password is reset",
    "data": null
}
```

---

#### `reset-password-validation.json` — Validation Error (422)
> [View JSON](./endpoint/auth/reset/reset-password-validation.json)

**Request:**
```json
{
    "password": "short",
    "password_confirmation": "short"
}
```

**Response (422):**
```json
{
    "message": "The email field is required. (and 2 more errors)",
    "errors": {
        "email": ["The email field is required."],
        "password": ["Current password must be at least 8 characters"],
        "new_password": ["New password is required"]
    }
}
```

---

#### `reset-password-wrong-current.json` — Wrong Current Password (422)
> [View JSON](./endpoint/auth/reset/reset-password-wrong-current.json)

**Request:**
```json
{
    "password": "wrongpassword",
    "password_confirmation": "wrongpassword",
    "new_password": "newpass123",
    "new_password_confirmation": "newpass123"
}
```

**Response (422):**
```json
{
    "message": "The email field is required.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

---

## POST `/reset-password-with-code`

Reset password using verification code. Public.

### Test Cases

#### `reset-with-code-success.json` — Success (200)
> [View JSON](./endpoint/auth/reset/reset-with-code-success.json)

**Request:**
```json
{
    "email": "reset_test_6a44f267127a1@test.com",
    "code": "123456",
    "password": "newpass123",
    "password_confirmation": "newpass123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Password has been reset successfully.",
    "data": null
}
```

---

#### `reset-with-code-validation.json` — Validation Error (422)
> [View JSON](./endpoint/auth/reset/reset-with-code-validation.json)

**Request:**
```json
{
    "email": "not-an-email",
    "code": "abc",
    "password": "short",
    "password_confirmation": "short"
}
```

**Response (422):**
```json
{
    "message": "The email field must be a valid email address. (and 2 more errors)",
    "errors": {
        "email": ["The email field must be a valid email address."],
        "code": ["Code must be exactly 6 digits."],
        "password": ["Password must be at least 8 characters."]
    }
}
```

---

#### `reset-with-code-not-found.json` — Not Found (404)
> [View JSON](./endpoint/auth/reset/reset-with-code-not-found.json)

**Request:**
```json
{
    "email": "nonexistent@test.com",
    "code": "123456",
    "password": "newpass123",
    "password_confirmation": "newpass123"
}
```

**Response (404):**
```json
{
    "success": false,
    "message": "No account found.",
    "data": null
}
```

---

#### `reset-with-code-missing.json` — Missing Fields (422)
> [View JSON](./endpoint/auth/reset/reset-with-code-missing.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{
    "message": "Email address is required. (and 2 more errors)",
    "errors": {
        "email": ["Email address is required."],
        "code": ["Verification code is required."],
        "password": ["New password is required."]
    }
}
```

---

## POST `/forgot-password`

Send password reset link. Public.

### Test Cases

#### `forgot-password-success.json` — Success (200)
> [View JSON](./endpoint/auth/forgot/forgot-password-success.json)

**Request:**
```json
{
    "email": "owner@gmail.com"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Password reset code sent to your email.",
    "data": null
}
```

---

#### `forgot-password-not-found.json` — Not Found (404)
> [View JSON](./endpoint/auth/forgot/forgot-password-not-found.json)

**Request:**
```json
{
    "email": "nonexistent@test.com"
}
```

**Response (404):**
```json
{
    "success": false,
    "message": "No account found with this email.",
    "data": null
}
```

---

#### `forgot-password-validation.json` — Validation Error (422)
> [View JSON](./endpoint/auth/forgot/forgot-password-validation.json)

**Request:**
```json
{
    "email": "not-an-email"
}
```

**Response (422):**
```json
{
    "message": "Please provide a valid email address.",
    "errors": {
        "email": ["Please provide a valid email address."]
    }
}
```

---

[Back to Index](./00-INDEX.md)
