# 16 - Appointments

Full appointment lifecycle: available slots, booking, confirmation, completion, cancellation, rescheduling, and role-based views.

**Test Cases:** 36 | **Endpoint Folder:** [`endpoint/appointment/`](./endpoint/appointment/README.md)

---

## Table of Contents

| # | Method | Endpoint | Description | Auth |
|---|--------|----------|-------------|------|
| 1 | GET | [`/appointments/available-slots`](#get-available) | Get available slots | Auth |
| 2 | POST | [`/appointments/book`](#post-book) | Book an appointment | Patient |
| 3 | GET | [`/appointments/{id}`](#get-show) | Show appointment | Owner/Patient |
| 4 | POST | [`/appointments/{id}/confirmed`](#post-confirm) | Confirm appointment | Doctor |
| 5 | POST | [`/appointments/{id}/complete`](#post-complete) | Complete appointment | Doctor |
| 6 | POST | [`/appointments/{id}/cancel`](#post-cancel) | Cancel appointment | Owner |
| 7 | POST | [`/appointments/{id}/reschedule`](#post-reschedule) | Reschedule appointment | Patient |
| 8 | GET | [`/appointments/doctor/{id}`](#get-doctor-appointments) | Doctor's appointments | Doctor |
| 9 | GET | [`/appointments/doctor/{id}/schedule`](#get-doctor-schedule) | Doctor's schedule by date | Doctor |
| 10 | GET | [`/appointments/patient/{id}`](#get-patient-appointments) | Patient's appointments | Patient |
| 11 | GET | [`/appointments/clinic/{id}`](#get-clinic-appointments) | Clinic's appointments | Owner |
| 12 | GET | [`/appointments/clinic/{id}/schedule`](#get-clinic-schedule) | Clinic schedule by date | Owner |
| 13 | GET | [`/appointments/room`](#get-room-appointments) | Room appointments | Secretary |

---

## GET `/appointments/available-slots`

Get available appointment slots for a doctor on a date. Authenticated users.

### Test Cases

#### `available-slots-success.json` — Success (200)
> [View JSON](./endpoint/appointment/available/available-slots-success.json)

**Query:** `?doctor_id=2&date=2026-07-02`

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "data": [
            { "start": "09:00", "end": "09:30" },
            { "start": "09:30", "end": "10:00" },
            { "start": "10:00", "end": "10:30" }
        ],
        "meta": { "date": "2026-07-02", "dayOfWeek": 4, "dayOfWeekEN": "Thursday", "count": 10 }
    }
}
```

---

#### `available-slots-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/available/available-slots-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/appointments/book`

Book an appointment. Patient only.

**Note:** The appointment date must be at least 1 day in advance (tomorrow or later). Booking for today is not allowed.

### Test Cases

#### `book-patient-success.json` — Success (201)
> [View JSON](./endpoint/appointment/book/book-patient-success.json)

**Request:**
```json
{
    "doctor_id": 2, "patient_id": 1, "clinic_id": 1,
    "appointment_type_id": 1, "date": "2099-06-01", "start_time": "10:00"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Appointment booked successfully",
    "data": {
        "id": 520, "clinic_id": 1,
        "doctor": { "id": 2, "name": "Omar Nasser" },
        "patient": { "id": 1, "name": "Dulce McKenzie", "phone": "0924778300" },
        "room": { "id": 2, "name": "Room 2" },
        "appointment_type": { "id": 1, "ar_name": "استشارة عامة", "en_name": "General Consultation" },
        "date": "2099-06-01", "start_time": "10:00", "end_time": "10:30", "status": "scheduled"
    }
}
```

---

#### `book-validation.json` — Validation Error (422)
> [View JSON](./endpoint/appointment/book/book-validation.json)

**Response (422):**
```json
{
    "message": "The patient id field is required. (and 5 more errors)",
    "errors": {
        "patient_id": ["The patient id field is required."],
        "doctor_id": ["The doctor id field is required."],
        "clinic_id": ["The clinic id field is required."],
        "appointment_type_id": ["The appointment type id field is required."],
        "start_time": ["The start time field is required."],
        "date": ["The date field is required."]
    }
}
```

---

#### `book-not-found-doctor.json` — Validation Error (422)
> [View JSON](./endpoint/appointment/book/book-not-found-doctor.json)

**Response (422):**
```json
{
    "message": "The selected doctor id is invalid. (and 3 more errors)",
    "errors": {
        "doctor_id": ["The selected doctor id is invalid."],
        "clinic_id": ["The clinic id field is required."],
        "start_time": ["The start time field must match the format H:i."],
        "date": ["The date field is required."]
    }
}
```

---

#### `book-invalid-token.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/book/book-invalid-token.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

#### `book-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/book/book-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/appointments/{id}`

Show appointment details. Owner or Patient.

### Test Cases

#### `show-owner-success.json` — Owner Success (200)
> [View JSON](./endpoint/appointment/show/show-owner-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1, "clinic_id": 1,
        "doctor": { "id": 1, "name": "Amira Hassan" },
        "patient": { "id": 24, "name": "Deshaun D'Amore", "phone": "0915391648" },
        "room": null,
        "appointment_type": { "id": 8, "ar_name": "جلسة متوسطة", "en_name": "Medium Session" },
        "date": "2026-03-01", "start_time": "09:00", "end_time": "09:30",
        "status": "completed", "visit_reason": "Fugit est hic."
    }
}
```

---

#### `show-patient-success.json` — Patient Success (200)
> [View JSON](./endpoint/appointment/show/show-patient-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1, "clinic_id": 1,
        "doctor": { "id": 1, "name": "Amira Hassan" },
        "patient": { "id": 24, "name": "Deshaun D'Amore", "phone": "091****648" },
        "room": null,
        "appointment_type": { "id": 8, "ar_name": "جلسة متوسطة", "en_name": "Medium Session" },
        "date": "2026-03-01", "start_time": "09:00", "end_time": "09:30", "status": "completed"
    }
}
```

---

#### `show-not-found.json` — Not Found (404)
> [View JSON](./endpoint/appointment/show/show-not-found.json)

**Response (404):**
```json
{
    "success": false,
    "message": "Appointment not found",
    "data": null
}
```

---

#### `show-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/show/show-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/appointments/{id}/confirmed`

Confirm an appointment. Doctor only.

### Test Cases

#### `confirm-book-success.json` — Book for Confirm (201)
> [View JSON](./endpoint/appointment/confirm/confirm-book-success.json)

**Request:**
```json
{
    "doctor_id": 2, "patient_id": 1, "clinic_id": 1,
    "appointment_type_id": 1, "date": "2099-06-01", "start_time": "13:00"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Appointment booked successfully",
    "data": { "id": 523, "status": "scheduled", ... }
}
```

---

#### `confirm-success.json` — Success (200)
> [View JSON](./endpoint/appointment/confirm/confirm-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Appointment marked confirmed",
    "data": null
}
```

---

## POST `/appointments/{id}/complete`

Complete an appointment. Doctor only.

### Test Cases

#### `complete-success.json` — Success (200)
> [View JSON](./endpoint/appointment/complete/complete-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Appointment completed",
    "data": null
}
```

---

#### `complete-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/complete/complete-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/appointments/{id}/cancel`

Cancel an appointment. Owner only.

**Note:** Appointments cannot be cancelled less than 1 day before the start time.

### Test Cases

#### `cancel-book-success.json` — Book for Cancel (201)
> [View JSON](./endpoint/appointment/cancel/cancel-book-success.json)

**Response (201):**
```json
{
    "success": true,
    "message": "Appointment booked successfully",
    "data": { "id": 521, "status": "scheduled", ... }
}
```

---

#### `cancel-success.json` — Success (200)
> [View JSON](./endpoint/appointment/cancel/cancel-success.json)

**Request:**
```json
{ "cancel_reason": "Patient changed mind" }
```

**Response (200):**
```json
{
    "success": true,
    "message": "Appointment cancelled",
    "data": null
}
```

---

#### `cancel-not-found.json` — Not Found (404)
> [View JSON](./endpoint/appointment/cancel/cancel-not-found.json)

**Response (404):**
```json
{
    "success": false,
    "message": "Appointment not found",
    "data": null
}
```

---

#### `cancel-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/cancel/cancel-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## POST `/appointments/{id}/reschedule`

Reschedule an appointment. Patient only.

**Note:** The new appointment date must be at least 1 day in advance (tomorrow or later). Rescheduling to today is not allowed.

### Test Cases

#### `reschedule-book-success.json` — Book for Reschedule (201)
> [View JSON](./endpoint/appointment/reschedule/reschedule-book-success.json)

**Response (201):**
```json
{
    "success": true,
    "message": "Appointment booked successfully",
    "data": { "id": 522, "status": "scheduled", ... }
}
```

---

#### `reschedule-success.json` — Success (200)
> [View JSON](./endpoint/appointment/reschedule/reschedule-success.json)

**Request:**
```json
{ "start_time": "14:00", "date": "2099-06-01" }
```

**Response (200):**
```json
{
    "success": true,
    "message": "Appointment updated successfully",
    "data": { "id": 522, "start_time": "14:00", "end_time": "14:30", "status": "scheduled", ... }
}
```

---

#### `reschedule-not-found.json` — Not Found (404)
> [View JSON](./endpoint/appointment/reschedule/reschedule-not-found.json)

**Response (404):**
```json
{
    "success": false,
    "message": "Appointment not found",
    "data": null
}
```

---

#### `reschedule-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/reschedule/reschedule-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/appointments/doctor/{id}`

Get doctor's appointments. Doctor only (self).

### Test Cases

#### `doctor-appointments-self-success.json` — Success (200)
> [View JSON](./endpoint/appointment/doctor/doctor-appointments-self-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Doctor appointments",
    "data": [
        { "id": 3, "clinic_id": 1, "patient": { "id": 68, "name": "Maye Langworth" }, "room": { "id": 2, "name": "Room 2" }, "appointment_type": { "id": 5, "en_name": "Emergency" }, "date": "2026-03-01", "start_time": "16:00", "end_time": "16:30", "status": "completed" }
    ],
    "pagination": { "total": 117, "count": 15, "per_page": 15, "current_page": 1, "last_page": 8 }
}
```

---

#### `doctor-appointments-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/doctor/doctor-appointments-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/appointments/doctor/{id}/schedule`

Get doctor's schedule by date. Doctor only (self).

### Test Cases

#### `doctor-schedule-self-success.json` — Success (200)
> [View JSON](./endpoint/appointment/doctor/doctor-schedule-self-success.json)

**Query:** `?date=2099-06-01`

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": [
        { "id": 520, "date": "2099-06-01", "start_time": "10:00", "end_time": "10:30", "status": "scheduled", ... },
        { "id": 521, "date": "2099-06-01", "start_time": "11:00", "end_time": "11:30", "status": "cancelled", ... }
    ]
}
```

---

#### `doctor-schedule-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/doctor/doctor-schedule-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/appointments/patient/{id}`

Get patient's appointments. Patient only (self).

### Test Cases

#### `patient-appointments-self-success.json` — Success (200)
> [View JSON](./endpoint/appointment/patient/patient-appointments-self-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Patient appointments",
    "data": [
        { "id": 336, "clinic_id": 1, "doctor": { "id": 3, "name": "Layla Farah" }, "patient": { "id": 1, "name": "Dulce McKenzie" }, "room": { "id": 3, "name": "Room 3" }, "date": "2026-04-15", "start_time": "12:00", "end_time": "12:30", "status": "completed" }
    ],
    "pagination": { "total": 24, "count": 15, "per_page": 15, "current_page": 1, "last_page": 2 }
}
```

---

#### `patient-appointments-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/patient/patient-appointments-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/appointments/clinic/{id}`

Get clinic's appointments. Owner only.

### Test Cases

#### `clinic-appointments-owner-success.json` — Success (200)
> [View JSON](./endpoint/appointment/clinic/clinic-appointments-owner-success.json)

**Response (200):**
```json
{
    "success": true,
    "message": "Clinic appointments",
    "data": [
        { "id": 1, "clinic_id": 1, "doctor": { "id": 1, "name": "Amira Hassan" }, "patient": { "id": 24, "name": "Deshaun D'Amore" }, "date": "2026-03-01", "start_time": "09:00", "end_time": "09:30", "status": "completed" }
    ],
    "pagination": { "total": 517, "count": 15, "per_page": 15, "current_page": 1, "last_page": 35 }
}
```

---

#### `clinic-appointments-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/clinic/clinic-appointments-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/appointments/clinic/{id}/schedule`

Get clinic's schedule by date. Owner only.

### Test Cases

#### `clinic-schedule-owner-success.json` — Success (200)
> [View JSON](./endpoint/appointment/clinic/clinic-schedule-owner-success.json)

**Query:** `?date=2099-06-01`

**Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": [
        { "id": 520, "date": "2099-06-01", "start_time": "10:00", "end_time": "10:30", "status": "scheduled", ... },
        { "id": 521, "date": "2099-06-01", "start_time": "11:00", "end_time": "11:30", "status": "cancelled", ... }
    ]
}
```

---

#### `clinic-schedule-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/clinic/clinic-schedule-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

## GET `/appointments/room`

Get room appointments. Secretary only.

### Test Cases

#### `room-appointments-secretary-success.json` — Success (200)
> [View JSON](./endpoint/appointment/room/room-appointments-secretary-success.json)

**Query:** `?roomIds[]=2`

**Response (200):**
```json
{
    "success": true,
    "message": "Room appointments",
    "data": [
        { "id": 3, "clinic_id": 1, "doctor": { "id": 2, "name": "Omar Nasser" }, "patient": { "id": 68, "name": "Maye Langworth" }, "room": { "id": 2, "name": "Room 2" }, "date": "2026-03-01", "start_time": "16:00", "end_time": "16:30", "status": "completed" }
    ],
    "pagination": { "total": 117, "count": 15, "per_page": 15, "current_page": 1, "last_page": 8 }
}
```

---

#### `room-appointments-unauthenticated.json` — Unauthenticated (401)
> [View JSON](./endpoint/appointment/room/room-appointments-unauthenticated.json)

**Response (401):**
```json
{ "message": "Unauthenticated." }
```

---

[Back to Index](./00-INDEX.md)

## Invoice & Payment Flow

Each appointment can have **at most 2 invoices**:
1. **Booking Invoice** — auto-created on booking
2. **Treatment Invoice** — created by doctor after completion via `POST /invoices`

### Auto-Invoice on Booking
When an appointment is booked via `POST /appointments/book`, a booking invoice is automatically created inside the transaction:
- **total_cost**: `doctor.consultation_fee × appointment_type.types` (slot multiplier)
- **status**: `draft`
- **description**: `Booking fee` (encrypted, queryable via blind index)
- **items**: Auto-attached "Consultation Fee" item from items catalog
- **appointment_id**: Linked to the booked appointment

The booking response now includes an `invoices` array:
```json
{
    "data": {
        "appointment": { ... },
        "invoices": [
            {
                "id": 1,
                "invoice_number": "INV-2026-000001",
                "total_cost": 200.00,
                "status": "draft",
                "description": "Booking fee"
            }
        ]
    }
}
```

### Treatment Invoice (After Completion)
After the doctor marks an appointment as completed, they can create a treatment invoice via the existing `POST /invoices` endpoint. The system validates:
- Maximum 2 invoices per appointment (1 booking + 1 treatment)
- Returns 422 error if limit is reached

### Payment Flow
- Patient views their invoices (via `GET /invoices/patient/{id}`)
- Patient pays via Stripe from the invoice page
- Payment confirms via Stripe webhook → invoice status updates

### Cancellation Refund
When an appointment is cancelled via `POST /appointments/{id}/cancel`:
1. System finds the booking invoice (identified by description `Booking fee` via blind index)
2. All completed payments on the booking invoice are refunded (full refund)
3. Booking invoice status is set to `void`
4. Refunds are processed via the original payment gateway (Stripe)

### Payment Reminders (Hourly Job)
- Appointments within 2 hours with unpaid booking invoices → patient receives payment reminder
- Past appointments with unpaid booking invoices → marked as `no_show`, booking invoice voided

### Reschedule
When an appointment is rescheduled, the booking invoice remains unchanged (cost is per-appointment, not per-time-slot). The reschedule response includes the updated `invoices` array.
