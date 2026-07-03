# 08 - Medicines

Medicine search and management.

**Test Cases:** 7 | **Endpoint Folder:** [`endpoint/medicine/`](./endpoint/medicine/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/medicines/search`](#get-search) | Search medicines | Public |
| 2 | POST | [`/medicines`](#post-store) | Create a medicine | Owner |

---

## GET `/medicines/search`

Search medicines. Public.

### Test Cases

#### `search-public-success.json` — Success (200)
> [View JSON](./endpoint/medicine/search/search-public-success.json)

**Response (200):**
```json
{ "success": true, "message": "Medicines search results retrieved successfully.", "data": [ { "id": 1, "ar_name": "باراسيتامول", "en_name": "Paracetamol", "generic_name_ar": null, "generic_name_en": "Paracetamol", "strength": "500mg", "form": "tablet", ... }, ... (many results) ] }
```

---

#### `search-no-query.json` — Validation Error (422)
> [View JSON](./endpoint/medicine/search/search-no-query.json)

**Response (422):**
```json
{ "message": "The query field is required.", "errors": { "query": ["The query field is required."] } }
```

---

## POST `/medicines`

Create a medicine. Owner only.

### Test Cases

#### `store-success.json` — Success (201)
> [View JSON](./endpoint/medicine/store/store-success.json)

**Request:**
```json
{ "en_name": "Test Med 6a450231d96bb" }
```

**Response (201):**
```json
{ "success": true, "message": "Medicine processed successfully.", "data": { "id": 16, "api_id": null, "arabic_name": null, "english_name": "Test Med 6a450231d96bb", "generic_arabic": null, "generic_english": null, "strength": null, "form": null, "is_custom_added": true, "created_at": "2026-07-01T12:04:03+00:00" } }
```

---

#### `store-validation.json` — Validation Error (422)
> [View JSON](./endpoint/medicine/store/store-validation.json)

**Request:** `{}` (empty)

**Response (422):**
```json
{ "message": "Please provide either the Arabic or English name of the medicine. (and 1 more error)", "errors": { "ar_name": ["Please provide either the Arabic or English name of the medicine."], "en_name": ["Please provide either the Arabic or English name of the medicine."] } }
```

---

#### `store-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/medicine/store/store-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `store-unauthorized-patient.json` — Forbidden (403)
> [View JSON](./endpoint/medicine/store/store-unauthorized-patient.json)

**Response (403):**
```json
{ "success": false, "message": "Permission Denied" }
```

---

#### `store-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/medicine/store/store-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)
