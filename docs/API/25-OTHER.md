# 25 - Other

Google OAuth authentication endpoints.

**Test Cases:** 2 | **Endpoint Folder:** [`endpoint/other/`](./endpoint/other/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/auth/google`](#get-google-redirect) | Google OAuth redirect | None |
| 2 | GET | [`/auth/google/callback`](#get-google-callback) | Google OAuth callback | None |

---

## GET `/auth/google`

Google OAuth redirect. Initiates Google login flow.

### Test Cases

#### `google-redirect.json` — Success (200)
> [View JSON](./endpoint/other/google/google-redirect.json)

**Response (200):**
```json
{ "success": true, "message": "Success", "data": { "url": "https://accounts.google.com/o/oauth2/auth?client_id=249642602753-ikbjp3gmq1rm1142dlsm93ruk07tug8k.apps.googleusercontent.com&redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fapi%2Fauth%2Fgoogle%2Fcallback&scope=openid+profile+email&response_type=code" } }
```

---

## GET `/auth/google/callback`

Google OAuth callback. Handles the OAuth callback from Google.

### Test Cases

#### `google-callback-invalid.json` — Invalid Code (422)
> [View JSON](./endpoint/other/google/google-callback-invalid.json)

**Response (422):**
```json
{ "message": "Invalid authorization code.", "errors": { "code": ["The code field is required."] } }
```

---

[Back to Index](./00-INDEX.md)
