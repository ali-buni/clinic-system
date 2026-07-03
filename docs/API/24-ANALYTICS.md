# 24 - Analytics

Comprehensive clinic analytics, dashboards, and AI-powered insights.

**Test Cases:** 52 | **Endpoint Folder:** [`endpoint/analytics/`](./endpoint/analytics/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | POST | [`/analytics/dashboard`](#post-dashboard) | Dashboard analytics | Owner |
| 2 | POST | [`/analytics/financial`](#post-financial) | Financial analytics | Owner |
| 3 | POST | [`/analytics/health-score`](#post-health-score) | Health score analytics | Owner |
| 4 | GET | [`/analytics/medical`](#get-medical) | Medical analytics | Owner |
| 5 | POST | [`/analytics/nla`](#post-nla) | Natural language analytics | Owner |
| 6 | POST | [`/analytics/operational`](#post-operational) | Operational analytics | Owner |
| 7 | POST | [`/analytics/patients`](#post-patients) | Patient analytics | Owner |
| 8 | POST | [`/analytics/predictive`](#post-predictive) | Predictive analytics | Owner |

---

## POST `/analytics/dashboard`

Dashboard analytics with operational, financial, patient, medical, predictive, and health score data. Owner only.

### Test Cases

#### `dashboard-owner-period-default.json` — Success (200)
> [View JSON](./endpoint/analytics/dashboard/dashboard-owner-period-default.json)

**Request:**
```json
{ "from": "2020-01-01", "to": "2026-07-01" }
```

**Response (200):**
```json
{ "status": "success", "period": "total", "from": "2020-01-01", "to": "2026-07-01", "operational": { "today_utilization": [...], "appointments": [ { "total": 500, "completed": "430", "no_show": "15", "cancelled": "20" } ], "completion": [ { "total": 500, "completed": "430", "completion_rate": "86.00" } ] }, "financial": { "by_period": [ { "total_revenue": "83445.97" } ], "by_doctor": [...] }, "patients": { "retention": { "total_patients": 70, "returning_patients": 68, "retention_rate": "97.14%" } }, "medical": { "top_diseases": [...] }, "health_score": { "overall_score": 85.9, "overall_status": "ممتاز" } }
```

---

#### `dashboard-bad-period.json` — Bad Period (422)
> [View JSON](./endpoint/analytics/dashboard/dashboard-bad-period.json)

**Response (422):**
```json
{ "message": "The from date must be before the to date.", "errors": { "from": [...], "to": [...] } }
```

---

#### `dashboard-empty-body.json` — Empty Body (422)
> [View JSON](./endpoint/analytics/dashboard/dashboard-empty-body.json)

**Response (422):**
```json
{ "message": "The from field is required. (and 1 more error)", "errors": { "from": [...], "to": [...] } }
```

---

#### `dashboard-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/analytics/dashboard/dashboard-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `dashboard-owner-period-day.json` — Success - Day Period (200)
> [View JSON](./endpoint/analytics/dashboard/dashboard-owner-period-day.json)

**Response (200):**
```json
{ "status": "success", "period": "day", ... }
```

---

#### `dashboard-owner-period-month.json` — Success - Month Period (200)
> [View JSON](./endpoint/analytics/dashboard/dashboard-owner-period-month.json)

**Response (200):**
```json
{ "status": "success", "period": "month", ... }
```

---

#### `dashboard-owner-period-total.json` — Success - Total Period (200)
> [View JSON](./endpoint/analytics/dashboard/dashboard-owner-period-total.json)

**Response (200):**
```json
{ "status": "success", "period": "total", ... }
```

---

#### `dashboard-owner-period-year.json` — Success - Year Period (200)
> [View JSON](./endpoint/analytics/dashboard/dashboard-owner-period-year.json)

**Response (200):**
```json
{ "status": "success", "period": "year", ... }
```

---

#### `dashboard-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/analytics/dashboard/dashboard-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## POST `/analytics/financial`

Financial analytics with revenue, outstanding balance, and doctor performance. Owner only.

### Test Cases

#### `financial-owner-period-day.json` — Success - Day Period (200)
> [View JSON](./endpoint/analytics/financial/financial-owner-period-day.json)

**Response (200):**
```json
{ "status": "success", "period": "day", ... }
```

---

#### `financial-owner-period-month.json` — Success - Month Period (200)
> [View JSON](./endpoint/analytics/financial/financial-owner-period-month.json)

**Response (200):**
```json
{ "status": "success", "period": "month", ... }
```

---

#### `financial-owner-period-total.json` — Success - Total Period (200)
> [View JSON](./endpoint/analytics/financial/financial-owner-period-total.json)

**Response (200):**
```json
{ "status": "success", "period": "total", ... }
```

---

#### `financial-owner-period-year.json` — Success - Year Period (200)
> [View JSON](./endpoint/analytics/financial/financial-owner-period-year.json)

**Response (200):**
```json
{ "status": "success", "period": "year", ... }
```

---

#### `financial-bad-period.json` — Bad Period (422)
> [View JSON](./endpoint/analytics/financial/financial-bad-period.json)

**Response (422):** `{ "message": "The from date must be before the to date." }`

---

#### `financial-empty-body.json` — Empty Body (422)
> [View JSON](./endpoint/analytics/financial/financial-empty-body.json)

**Response (422):**
```json
{ "message": "The from field is required. (and 1 more error)", "errors": { "from": [...], "to": [...] } }
```

---

#### `financial-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/analytics/financial/financial-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `financial-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/analytics/financial/financial-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## POST `/analytics/health-score`

Health score analytics for a specific patient. Owner only.

### Test Cases

#### `health-score-owner-period-default.json` — Success (200)
> [View JSON](./endpoint/analytics/health/health-score-owner-period-default.json)

**Request:**
```json
{ "from": "2020-01-01", "to": "2026-07-01", "patient_id": 1 }
```

**Response (200):**
```json
{ "status": "success", "data": { "from": "2020-01-01", "to": "2026-07-01", "overall_score": 85.9, "overall_status": "ممتاز", "sub_scores": { "financial": { "score": 100, "status": "ممتاز", "total_revenue": 83445.97, "outstanding": 0, "outstanding_ratio": "0%" }, "operational": { "score": 54.99, "status": "متوسط", "active_doctors": 5, "completion_rate": "91.49%", "avg_utilization": "0.23%", "no_show_count": 15 }, "patient": { "score": 98.28, "status": "ممتاز", "retention_rate": "97.14%", "lost_patients": 0, "new_patients": 70, "growth_balance": "100%" } }, "recommendations": [ { "area": "operational", "priority": "medium", "message": "الكفاءة التشغيلية مقبولة. حسّن توزيع المواعيد." } ] } }
```

---

#### `health-score-bad-patient.json` — Bad Patient (422)
> [View JSON](./endpoint/analytics/health/health-score-bad-patient.json)

**Response (422):**
```json
{ "message": "The patient id field is required.", "errors": { "patient_id": ["The patient id field is required."] } }
```

---

#### `health-score-bad-period.json` — Bad Period (422)
> [View JSON](./endpoint/analytics/health/health-score-bad-period.json)

**Response (422):** `{ "message": "The from date must be before the to date." }`

---

#### `health-score-empty-body.json` — Empty Body (422)
> [View JSON](./endpoint/analytics/health/health-score-empty-body.json)

**Response (422):**
```json
{ "message": "The patient id field is required. (and 2 more errors)", "errors": { "patient_id": [...], "from": [...], "to": [...] } }
```

---

#### `health-score-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/analytics/health/health-score-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## GET `/analytics/medical`

Medical analytics with top diseases and age group breakdown. Owner only.

### Test Cases

#### `medical-owner-success.json` — Success (200)
> [View JSON](./endpoint/analytics/medical/medical-owner-success.json)

**Response (200):**
```json
{ "status": "success", "top_diseases": [ { "ar_name": "الفصال العظمي في الركبة", "en_name": "Knee Osteoarthritis", "cases_count": 98 }, { "ar_name": "ذات الرئة البكتيري", "en_name": "Bacterial Pneumonia", "cases_count": 91 } ], "by_age": { "بالغين": [...], "شباب": [...], "كبار سن": [...] } }
```

---

#### `medical-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/analytics/medical/medical-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `medical-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/analytics/medical/medical-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## POST `/analytics/nla`

Natural language analytics. Ask questions in plain English. Owner only.

### Test Cases

#### `nla-owner-howmanypatients.json` — How Many Patients (200)
> [View JSON](./endpoint/analytics/nla/nla-owner-howmanypatients.json)

**Request:**
```json
{ "question": "How many patients?" }
```

**Response (200):**
```json
{ "status": "success", "answer": "..." }
```

---

#### `nla-owner-whatistherevenuetren.json` — Revenue Trend (200)
> [View JSON](./endpoint/analytics/nla/nla-owner-whatistherevenuetren.json)

**Request:**
```json
{ "question": "What is the revenue trend?" }
```

**Response (200):**
```json
{ "status": "success", "answer": "..." }
```

---

#### `nla-empty-body.json` — Empty Body (422)
> [View JSON](./endpoint/analytics/nla/nla-empty-body.json)

**Response (422):**
```json
{ "message": "The question field is required.", "errors": { "question": ["The question field is required."] } }
```

---

#### `nla-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/analytics/nla/nla-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `nla-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/analytics/nla/nla-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## POST `/analytics/operational`

Operational analytics with utilization, completion rates, and no-show data. Owner only.

### Test Cases

#### `operational-owner-period-day.json` — Success - Day Period (200)
> [View JSON](./endpoint/analytics/operational/operational-owner-period-day.json)

**Response (200):**
```json
{ "status": "success", "period": "day", ... }
```

---

#### `operational-owner-period-month.json` — Success - Month Period (200)
> [View JSON](./endpoint/analytics/operational/operational-owner-period-month.json)

**Response (200):**
```json
{ "status": "success", "period": "month", ... }
```

---

#### `operational-owner-period-total.json` — Success - Total Period (200)
> [View JSON](./endpoint/analytics/operational/operational-owner-period-total.json)

**Response (200):**
```json
{ "status": "success", "period": "total", ... }
```

---

#### `operational-owner-period-year.json` — Success - Year Period (200)
> [View JSON](./endpoint/analytics/operational/operational-owner-period-year.json)

**Response (200):**
```json
{ "status": "success", "period": "year", ... }
```

---

#### `operational-bad-period.json` — Bad Period (422)
> [View JSON](./endpoint/analytics/operational/operational-bad-period.json)

**Response (422):** `{ "message": "The from date must be before the to date." }`

---

#### `operational-empty-body.json` — Empty Body (422)
> [View JSON](./endpoint/analytics/operational/operational-empty-body.json)

**Response (422):**
```json
{ "message": "The from field is required. (and 1 more error)", "errors": { "from": [...], "to": [...] } }
```

---

#### `operational-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/analytics/operational/operational-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `operational-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/analytics/operational/operational-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## POST `/analytics/patients`

Patient analytics with retention and growth data. Owner only.

### Test Cases

#### `patients-owner-period-day.json` — Success - Day Period (200)
> [View JSON](./endpoint/analytics/patients/patients-owner-period-day.json)

**Response (200):**
```json
{ "status": "success", "period": "day", ... }
```

---

#### `patients-owner-period-month.json` — Success - Month Period (200)
> [View JSON](./endpoint/analytics/patients/patients-owner-period-month.json)

**Response (200):**
```json
{ "status": "success", "period": "month", ... }
```

---

#### `patients-owner-period-total.json` — Success - Total Period (200)
> [View JSON](./endpoint/analytics/patients/patients-owner-period-total.json)

**Response (200):**
```json
{ "status": "success", "period": "total", ... }
```

---

#### `patients-owner-period-year.json` — Success - Year Period (200)
> [View JSON](./endpoint/analytics/patients/patients-owner-period-year.json)

**Response (200):**
```json
{ "status": "success", "period": "year", ... }
```

---

#### `patients-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/analytics/patients/patients-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `patients-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/analytics/patients/patients-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

## POST `/analytics/predictive`

Predictive analytics with crowding, no-show, and utilization forecasts. Owner only.

### Test Cases

#### `predictive-owner-period-day.json` — Success - Day Period (200)
> [View JSON](./endpoint/analytics/predictive/predictive-owner-period-day.json)

**Response (200):**
```json
{ "status": "success", "period": "day", ... }
```

---

#### `predictive-owner-period-month.json` — Success - Month Period (200)
> [View JSON](./endpoint/analytics/predictive/predictive-owner-period-month.json)

**Response (200):**
```json
{ "status": "success", "period": "month", ... }
```

---

#### `predictive-owner-period-total.json` — Success - Total Period (200)
> [View JSON](./endpoint/analytics/predictive/predictive-owner-period-total.json)

**Response (200):**
```json
{ "status": "success", "period": "total", ... }
```

---

#### `predictive-owner-period-year.json` — Success - Year Period (200)
> [View JSON](./endpoint/analytics/predictive/predictive-owner-period-year.json)

**Response (200):**
```json
{ "status": "success", "period": "year", ... }
```

---

#### `predictive-bad-period.json` — Bad Period (422)
> [View JSON](./endpoint/analytics/predictive/predictive-bad-period.json)

**Response (422):** `{ "message": "The from date must be before the to date." }`

---

#### `predictive-empty-body.json` — Empty Body (422)
> [View JSON](./endpoint/analytics/predictive/predictive-empty-body.json)

**Response (422):**
```json
{ "message": "The from field is required. (and 1 more error)", "errors": { "from": [...], "to": [...] } }
```

---

#### `predictive-invalid-token.json` — Invalid Token (401)
> [View JSON](./endpoint/analytics/predictive/predictive-invalid-token.json)

**Response (401):** `{ "message": "Invalid token." }`

---

#### `predictive-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/analytics/predictive/predictive-unauthenticated.json)

**Response (401):** `{ "message": "Unauthenticated." }`

---

[Back to Index](./00-INDEX.md)
