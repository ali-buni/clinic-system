# 09 - Diseases

Disease search and management.

**Test Cases:** 7 | **Endpoint Folder:** [`endpoint/disease/`](./endpoint/disease/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/diseases/search`](#get-search) | Search diseases | Public |
| 2 | POST | [`/diseases`](#post-store) | Create a disease | Owner |

---

## GET `/diseases/search`

Search diseases. Public.

### Test Cases

#### `search-public-success.json` — Success (200)
> [View JSON](./endpoint/disease/search/search-public-success.json)

**Response (200):**
```json
{ "success": true, "message": "Diseases search results retrieved successfully.", "data": [ { "id": 1, "code": "I10", "ar_name": "ارتفاع ضغط الدم", "en_name": "Hypertension", "description": null, "disease_nature": "chronic", ... }, ... (many results) ] }
```

---

#### `search-no-query.json` — Validation Error (422)
> [View JSON](./endpoint/disease/search/search-no-query.json)

**Response (422):**
```json
{ "message": "The query field is required.", "errors": { "query": ["The query field is required."] } }
```

---

## POST `/diseases`

Create a disease. Owner only.

### Test Cases

#### `store-success.json` — Success (201)
> [View JSON](./endpoint/disease/store/store-success.json)

**Request:**
```json
{ "en_name": "Owner Disease 6a450422a1836", "disease_nature": "other", "ar_name": "owner" }
```

**Response (201):**
```json
{ "success": true, "message": "Disease processed successfully.", "data": { "id": 17, "icd10_code": null, "arabic_name": "owner", "english_name": "Owner Disease 6a450422a1836", "description": null, "nature": "other", "is_custom": true, "created_at": "2026-07-01T12:12:19+00:00" } }
```

---

#### `store-validation.json` — Validation Error (422)
> [View JSON](./endpoint/disease/store/store-validation.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "The Arabic name of the disease is required. (and 2 more errors)", "errors": { "ar_name": ["The Arabic name of the disease is required."], "en_name": ["The English name of the disease is required."], "disease_nature": ["The disease nature field is required."] } }
```

---

#### `store-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/disease/store/store-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `store-unauthorized.json` — Forbidden (403)
> [View JSON](./endpoint/disease/store/store-unauthorized.json)

**Response (403):**
```json
{ "success": false, "message": "Permission Denied" }
```

---

#### `store-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/disease/store/store-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
