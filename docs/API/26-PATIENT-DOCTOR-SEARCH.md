# 26 - Patient Doctor Search

Patients can search and filter available doctors by name, location, specialty, and consultation fee with sorting and pagination.

## Table of Contents

| #   | Method | Endpoint                                  | Description    | Auth    |
| --- | ------ | ----------------------------------------- | -------------- | ------- |
| 1   | GET    | [`/patients/search/doctors`](#get-search) | Search doctors | Patient |

---

## GET `/patients/search/doctors`

Search and filter doctors. Patient only.

### Query Parameters

| Parameter              | Type    | Required | Description                                                       |
| ---------------------- | ------- | -------- | ----------------------------------------------------------------- |
| `name`                 | string  | No       | Search by doctor's first name or last name (LIKE match)           |
| `location`             | string  | No       | Search by clinic location (LIKE match)                            |
| `specialty`            | string  | No       | Search by specialty English or Arabic name (LIKE match)           |
| `consultation_fee_min` | number  | No       | Minimum consultation fee                                          |
| `consultation_fee_max` | number  | No       | Maximum consultation fee                                          |
| `sort_by`              | string  | No       | Sort field: `consultation_fee`, `appointment_duration`, or `name` |
| `sort_direction`       | string  | No       | Sort direction: `asc` (default) or `desc`                         |
| `per_page`             | integer | No       | Results per page (default: 15, max: 100)                          |
| `page`                 | integer | No       | Page number (default: 1)                                          |

---

### Test Cases

#### `search-success.json` — Success No Filters (200)

**Response (200):**

```json
{
    "success": true,
    "message": "Doctors retrieved successfully.",
    "data": [
        {
            "id": 1,
            "name": "Amira Hassan",
            "consultation_fee": 177.37,
            "appointment_duration": 30,
            "gender": "female",
            "created_at": "2026-07-01",
            "clinic": {
                "id": 1,
                "title": "Clinic One",
                "location": "Damascus"
            },
            "specialties": [
                {
                    "id": 1,
                    "en_name": "Cardiology",
                    "ar_name": "طب القلب",
                    "is_primary": true
                }
            ]
        }
    ],
    "pagination": {
        "total": 7,
        "count": 7,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

---

#### `search-by-name.json` — Filter by Name (200)

**Request:** `GET /patients/search/doctors?name=Amira`

Matches doctors where `user.fname` or `user.lname` contains "Amira".

**Response (200):**

```json
{
    "success": true,
    "message": "Doctors retrieved successfully.",
    "data": [
        {
            "id": 1,
            "name": "Amira Hassan",
            "consultation_fee": 177.37,
            "appointment_duration": 30,
            "gender": "female",
            "created_at": "2026-07-01",
            "clinic": {
                "id": 1,
                "title": "Clinic One",
                "location": "Damascus"
            },
            "specialties": [
                {
                    "id": 1,
                    "en_name": "Cardiology",
                    "ar_name": "طب القلب",
                    "is_primary": true
                }
            ]
        }
    ],
    "pagination": {
        "total": 1,
        "count": 1,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

---

#### `search-by-name-no-results.json` — No Results (200)

**Request:** `GET /patients/search/doctors?name=ZZZZNOTEXIST`

**Response (200):**

```json
{
    "success": true,
    "message": "Doctors retrieved successfully.",
    "data": [],
    "pagination": {
        "total": 0,
        "count": 0,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

---

#### `search-by-location.json` — Filter by Location (200)

**Request:** `GET /patients/search/doctors?location=Damascus`

Matches doctors whose clinic location contains "Damascus".

---

#### `search-by-specialty.json` — Filter by Specialty (200)

**Request:** `GET /patients/search/doctors?specialty=Cardiology`

Matches doctors with specialty name containing "Cardiology" (English or Arabic).

---

#### `search-by-fee-range.json` — Filter by Fee Range (200)

**Request:** `GET /patients/search/doctors?consultation_fee_min=50&consultation_fee_max=200`

Returns doctors with consultation fee between 50 and 200.

---

#### `search-sort-by-fee-asc.json` — Sort by Fee Ascending (200)

**Request:** `GET /patients/search/doctors?sort_by=consultation_fee&sort_direction=asc`

---

#### `search-sort-by-fee-desc.json` — Sort by Fee Descending (200)

**Request:** `GET /patients/search/doctors?sort_by=consultation_fee&sort_direction=desc`

---

#### `search-sort-by-duration.json` — Sort by Appointment Duration (200)

**Request:** `GET /patients/search/doctors?sort_by=appointment_duration`

---

#### `search-sort-by-name.json` — Sort by Name (200)

**Request:** `GET /patients/search/doctors?sort_by=name&sort_direction=asc`

Sorts by doctor's first name (joins users table).

---

#### `search-pagination.json` — Pagination (200)

**Request:** `GET /patients/search/doctors?per_page=2&page=1`

---

#### `search-combined-filters.json` — Combined Filters (200)

**Request:** `GET /patients/search/doctors?name=Amira&specialty=Cardiology&sort_by=consultation_fee`

---
