# Clinic System API - Full Reference

**Base URL:** /api/clinic-system

**Standard Response Envelope:**

```json
{
  "success": true|false,
  "message": "...",
  "data": { ... } | [ ... ] | null
}
```

**Authentication:** Bearer token via auth:sanctum. Attach as:

```
Authorization: Bearer {token}
Accept: application/json
```

## Table of Contents

### Auth

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/auth/google](#auth-google-redirect) | Redirect to Google OAuth. |
| GET | [/auth/google/callback](#auth-google-callback) | Handle Google OAuth callback. |
| POST | [/refresh-token](#auth-refresh-token) | Refresh authentication token. |
| POST | [/reset-password](#auth-reset-password) | Reset password (authenticated). |
| POST | [/reset-password-with-code](#auth-reset-with-code) | Reset password using verification code. |
| POST | [/signout](#auth-signout) | Revoke current token. |
| POST | [/login](#auth-login) | Authenticate user credentials. |
| POST | [/register](#auth-register) | Register a new user. |
| POST | [/forgot-password](#auth-forgot-password) | Send password reset link. |

### Verification

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | [/resend-code](#verification-resend-code) | Resend verification code. |
| POST | [/verify-code](#verification-verify-code) | Verify email verification code. |

### Appointment Types

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/appointment-types](#appointment-types-index) | List all appointment types. |
| POST | [/appointment-types](#appointment-types-add) | Create a new appointment type. |
| DELETE | [/appointment-types/{id}](#appointment-types-delete) | Delete an appointment type. |

### Devices

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | [/devices/register-token](#devices-register-token) | Register FCM device token. |

### Specialties

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/specialty/getAll](#specialties-get-all-specialties) | Show all doctor specialties. |
| GET | [/clinic/specialty/showPrimary/{doctorId}](#specialties-show-primary) | Show primary specialty. |
| GET | [/clinic/specialty/index](#specialties-index) | List all specialties. |
| POST | [/clinic/specialty/changePrimary/{specialtyId}](#specialties-change-primary) | Change primary specialty. |
| POST | [/clinic/specialty/add](#specialties-attach-specialties) | Attach specialties to doctor. |
| DELETE | [/clinic/specialty/delete/{specialId}](#specialties-detach-specialty) | Detach specialty from doctor. |

### Schedules

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/schedule/get-weekly/{doctorId}](#schedules-get-weekly) | Get weekly schedule for a doctor. |
| GET | [/clinic/schedule/work-hour/{doctorId}](#schedules-work-hour-by-date) | Get work hours for a specific date. |
| POST | [/clinic/schedule/add](#schedules-store) | Create work hour entry. |
| PUT | [/clinic/schedule/edit](#schedules-update) | Update work hour. |
| DELETE | [/clinic/schedule/delete/{dayOfWeek}/{doctorId}](#schedules-destroy) | Delete work hour. |

### Schedule Overrides

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/schedule/override](#overrides-index) | List overrides for a doctor. |
| GET | [/clinic/schedule/override/{id}](#overrides-show) | Get a single override. |
| POST | [/clinic/schedule/override/add](#overrides-store) | Create a schedule override. |
| PUT | [/clinic/schedule/override/{id}/edit](#overrides-update) | Update a schedule override. |
| DELETE | [/clinic/schedule/override/{id}/delete](#overrides-destroy) | Delete a schedule override. |
| GET | [/clinic/schedule/override/date/single](#overrides-get-by-date) | Get override by exact date. |
| GET | [/clinic/schedule/override/date/range](#overrides-get-by-date-range) | Get overrides in a date range. |

### Medicines

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/medicines/search](#medicines-search) | Search medicines by name. |
| POST | [/clinic/medicines/store](#medicines-store) | Create custom medicine. |

### Diseases

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/diseases/search](#diseases-search) | Search diseases by name. |
| POST | [/clinic/diseases/store](#diseases-store) | Create custom disease. |

### Clinic

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | [/clinic/clinic/secretary/register](#clinic-create-secretary) | Create secretary (owner only). |
| POST | [/clinic/clinic/doctor/register](#clinic-create-doctor) | Create doctor (owner only). |

### Rooms

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/clinic/rooms/userRooms/get](#rooms-user-rooms) | Get current user's rooms. |
| GET | [/clinic/clinic/rooms/{clinicId}](#rooms-index) | List rooms in a clinic. |
| GET | [/clinic/clinic/rooms/{clinicId}/info](#rooms-index-with-info) | List rooms with additional info. |
| POST | [/clinic/clinic/rooms/sync/doctorRoom](#rooms-add-doctor-to-room) | Add doctor to room. |
| POST | [/clinic/clinic/rooms/sync/secRooms](#rooms-add-sec-to-room) | Add secretary to room. |
| DELETE | [/clinic/clinic/rooms/detach/secRooms](#rooms-del-sec-from-room) | Remove secretary from room. |
| DELETE | [/clinic/clinic/rooms/detach/doctorRoom](#rooms-del-doctor-from-room) | Remove doctor from room. |

### Secretaries

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/clinic/secretaries/{id}](#secretaries-info) | Get secretary info. |
| POST | [/clinic/clinic/secretaries/update](#secretaries-update) | Update secretary info. |

### Patients

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/clinic/patients/{patientId}/medical-history](#patients-medical-history) | Get patient medical history. |
| GET | [/clinic/clinic/patients/restore](#patients-restore) | Restore soft-deleted patient. |
| GET | [/clinic/clinic/patients/{patientId}/show](#patients-show) | Get patient details. |
| GET | [/clinic/clinic/patients](#patients-index) | List patients (requires clinic_id). |
| GET | [/clinic/clinic/patients/trashed](#patients-index-trashed) | List soft-deleted patients. |
| POST | [/clinic/clinic/patients/update](#patients-update) | Update patient info. |
| DELETE | [/clinic/clinic/patients/delete](#patients-destroy) | Soft-delete patient. |

### Users

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/clinic/users/image-url](#users-image-url) | Get user profile image URL. |
| GET | [/clinic/clinic/users/info](#users-info) | Get authenticated user info. |
| POST | [/clinic/clinic/users/update-image](#users-update-image) | Update user profile image. |

### Doctors

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/clinic/doctors/{id}/info](#doctors-info) | Get doctor info. |
| GET | [/clinic/clinic/doctors/filter](#doctors-index) | List doctors with filters. |
| POST | [/clinic/clinic/doctors/{id}/restore](#doctors-restore) | Restore soft-deleted doctor. |
| POST | [/clinic/clinic/doctors/update](#doctors-update) | Update doctor info. |
| DELETE | [/clinic/clinic/doctors/{id}/force](#doctors-force-delete) | Force-delete doctor. |
| DELETE | [/clinic/clinic/doctors/{id}/leave](#doctors-destroy) | Soft-delete doctor. |

### Appointments

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/clinic/appointments/clinic/{clinicId}](#appointments-clinic-appointments) | List clinic appointments. |
| GET | [/clinic/clinic/appointments/doctor/{doctorId}](#appointments-doctor-appointments) | List doctor appointments. |
| GET | [/clinic/clinic/appointments/get/available-slots](#appointments-available-slots) | Get available appointment slots. |
| GET | [/clinic/clinic/appointments/clinic/{clinicId}/schedule](#appointments-clinic-schedule) | Get clinic schedule for a date. |
| GET | [/clinic/clinic/appointments/doctor/{doctorId}/schedule](#appointments-doctor-schedule) | Get doctor schedule for a date. |
| GET | [/clinic/clinic/appointments/room/appo](#appointments-room-appointments) | List room appointments. |
| GET | [/clinic/clinic/appointments/{id}](#appointments-show) | Show appointment details. |
| GET | [/clinic/clinic/appointments/patient/{patientId}](#appointments-patient-appointments) | List patient appointments. |
| POST | [/clinic/clinic/appointments/book](#appointments-book) | Book a new appointment. |
| POST | [/clinic/clinic/appointments/{id}/cancel](#appointments-cancel) | Cancel an appointment. |
| POST | [/clinic/clinic/appointments/{id}/reschedule](#appointments-reschedule) | Reschedule an appointment. |
| POST | [/clinic/clinic/appointments/{id}/complete](#appointments-complete) | Complete a confirmed appointment. |
| POST | [/clinic/clinic/appointments/{id}/confirmed](#appointments-mark-confirmed) | Mark appointment as confirmed. |

### Phone

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | [/phone/verify-update](#phone-verify-update) | Verify phone update with code. |
| POST | [/phone/update](#phone-update) | Request phone update (sends code if already set). |

### Patient Records

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | [/clinic/clinic/patient-records/doctor/{doctorId}/all](#patient-records-get-all-by-doctor) | Get all records for a doctor. |
| GET | [/clinic/clinic/patient-records/patient/{patientId}/doctor/{doctorId}](#patient-records-get-by-doctor) | Get records by patient and doctor. |
| GET | [/clinic/clinic/patient-records/patient/{patientId}/history](#patient-records-history) | Get patient medical history records. |
| GET | [/clinic/clinic/patient-records/filtered](#patient-records-index) | List filtered patient records. |
| GET | [/clinic/clinic/patient-records/show/{id}](#patient-records-show) | Show patient record. |
| POST | [/clinic/clinic/patient-records/rooms/search](#patient-records-get-by-room) | Search records by rooms. |
| POST | [/clinic/clinic/patient-records](#patient-records-store) | Create patient record. |
| PUT | [/clinic/clinic/patient-records/{id}](#patient-records-update) | Update patient record. |
| DELETE | [/clinic/clinic/patient-records/{id}](#patient-records-destroy) | Delete patient record. |

---

## Auth

Public authentication endpoints.

<div id="auth-refresh-token"></div>

**`POST /api/clinic-system/refresh-token`**

Refresh authentication token. Auth required.

**Request Body:**

```json
{
    "refresh_token": "..."         // required
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Token refreshed successfully",
    "data":  {
                 "auth_token":  "5|iLcjLqZ4CCfjL3VnJLX74FxNscrcdRnrrO0i8Zb7c6e55c1c"
             }
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

<div id="auth-reset-password"></div>

**`POST /api/clinic-system/reset-password`**

Reset password (authenticated). Auth required.

**Request Body:**

```json
{
    "email": "patient@test.com",   // required, exists:users
    "password": "currentpass",      // required, min:8
    "new_password": "newpass123",   // required, min:8, different, confirmed
    "new_password_confirmation": "newpass123" // required
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "the password is reset",
    "data":  null
}
```

**Response (400) - Error: wrong-current:**

```json
{
    "success":  false,
    "message":  "Current password is incorrect",
    "data":  null
}
```

<div id="auth-reset-with-code"></div>

**`POST /api/clinic-system/reset-password-with-code`**

Reset password using verification code. Public.

**Request Body:**

```json
{
    "email": "patient@test.com",   // required, email, exists:users
    "code": "123456",               // required, digits:6
    "password": "newpass123",       // required, min:8, confirmed
    "password_confirmation": "newpass123" // required
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "Email address is required. (and 3 more errors)",
    "errors":  {
                   "email":  [
                                 "Email address is required."
                             ],
                   "code":  [
                                "Code must be exactly 6 digits."
                            ],
                   "password":  [
                                    "Password must be at least 8 characters.",
                                    "Password confirmation does not match."
                                ]
               }
}
```

<div id="auth-signout"></div>

**`POST /api/clinic-system/signout`**

Revoke current token. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Logged out successfully",
    "data":  null
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

<div id="auth-login"></div>

**`POST /api/clinic-system/login`**

Authenticate user credentials. Public.

**Request Body:**

```json
{
    "login": "patient@test.com",   // required, email
    "password": "password"         // required, min:8
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Email verification code sent successfully.",
    "data":  null
}
```

**Response (401) - Error: invalid-credentials:**

```json
{
    "success":  false,
    "message":  "invalid credentials.",
    "data":  null
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The login field is required. (and 1 more error)",
    "errors":  {
                   "login":  [
                                 "The login field is required."
                             ],
                   "password":  [
                                    "The password field must be at least 8 characters."
                                ]
               }
}
```

<div id="auth-register"></div>

**`POST /api/clinic-system/register`**

Register a new user. Public.

**Request Body:**

```json
{
    "fname": "New",                // required
    "lname": "Patient",             // required
    "email": "newpatient@test.com", // required, unique
    "password": "password123",      // required, min:8, confirmed
    "password_confirmation": "password123", // required
    "dob": "1990-01-01",            // optional
    "gender": "male",               // optional
    "nationality": null,            // optional
    "address": null,                // optional
    "marital_status": null,         // optional
    "emergency_phone": null,        // optional, digits_between:10,13
    "allergies": null,              // optional
    "chronic_conditions": null,     // optional
    "career": null,                 // optional
    "blood_type": null,             // optional
    "profile_image": null           // optional, image, max:2048
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Registration successful. Please check your email for the verification code.",
    "data":  null
}
```

**Response (422) - Error: duplicate-email:**

```json
{
    "message":  "This email is already registered.",
    "errors":  {
                   "email":  [
                                 "This email is already registered."
                             ]
               }
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "First name is required. (and 5 more errors)",
    "errors":  {
                   "fname":  [
                                 "First name is required."
                             ],
                   "lname":  [
                                 "Last name is required."
                             ],
                   "email":  [
                                 "Please provide a valid email address."
                             ],
                   "password":  [
                                    "Password must be at least 8 characters.",
                                    "Password confirmation does not match."
                                ],
                   "clinic_id":  [
                                     "Please select a clinic."
                                 ]
               }
}
```

<div id="auth-forgot-password"></div>

**`POST /api/clinic-system/forgot-password`**

Send password reset link. Public.

**Request Body:**

```json
{
    "email": "patient@test.com"    // required, email, exists:users
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Password reset code sent to your email.",
    "data":  null
}
```

**Response (422) - Error: not-found:**

```json
{
    "message":  "No account found with this email address.",
    "errors":  {
                   "email":  [
                                 "No account found with this email address."
                             ]
               }
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "Please provide a valid email address.",
    "errors":  {
                   "email":  [
                                 "Please provide a valid email address."
                             ]
               }
}
```

---

## Google Auth

Google OAuth2 authentication.

<div id="auth-google-redirect"></div>

**`GET /api/auth/google`**

Redirect to Google OAuth. Public.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  {
                 "url":  "https://accounts.google.com/o/oauth2/auth?client_id=..."
             }
}
```

<div id="auth-google-callback"></div>

**`GET /api/auth/google/callback`**

Handle Google OAuth callback. Public (called by Google).

**Query Parameters:**

```
code=4/0AfJohX...
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  {
                 "access_token":  "1|abc123token",
                 "token_type":  "bearer",
                 "id":  1,
                 "name":  "John",
                 "role":  "patient"
             }
}
```

**Response (401) - Error: invalid-credentials:**

```json
{
    "success":  false,
    "message":  "Invalid credentials from Google.",
    "data":  null
}
```

---

## Verification

Email verification.

<div id="verification-resend-code"></div>

**`POST /api/clinic-system/resend-code`**

Resend verification code. Public.

**Request Body:**

```json
{
    "login": "patient@test.com",   // required, email, exists:users
    "password": "password"         // required, min:8
}
```

**Response (401) - Error: invalid-credentials:**

```json
{
    "success":  false,
    "message":  "invalid credentials.",
    "data":  null
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The login field is required. (and 1 more error)",
    "errors":  {
                   "login":  [
                                 "The login field is required."
                             ],
                   "password":  [
                                    "Password must be at least 8 characters"
                                ]
               }
}
```

<div id="verification-verify-code"></div>

**`POST /api/clinic-system/verify-code`**

Verify email verification code. Public.

**Request Body:**

```json
{
    "login": "patient@test.com",   // required, email, exists:users
    "code": "123456",               // required, digits:6
    "type": "email"                 // required, in:phone,email
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The login field is required. (and 2 more errors)",
    "errors":  {
                   "login":  [
                                 "The login field is required."
                             ],
                   "code":  [
                                "Code must be exactly 6 digits"
                            ],
                   "type":  [
                                "The selected type is invalid."
                            ]
               }
}
```

---

## Appointment Types

Appointment type CRUD.

<div id="appointment-types-index"></div>

**`GET /api/clinic-system/appointment-types`**

List all appointment types. Public.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Appointment types retrieved",
    "data":  [
                 {
                     "id":  10,
                     "ar_name":  "مراجعة",
                     "en_name":  "Review",
                     "types":  "1",
                     "created_at":  "2026-06-21 15:11:29"
                 },
                 {
                     "id":  9,
                     "ar_name":  "جلسة طويلة",
                     "en_name":  "Long Session",
                     "types":  "3",
                     "created_at":  "2026-06-21 15:11:29"
                 },
                 {
                     "id":  8,
                     "ar_name":  "جلسة متوسطة",
                     "en_name":  "Medium Session",
                     "types":  "2",
                     "created_at":  "2026-06-21 15:11:29"
                 },
                 {
                     "id":  7,
                     "ar_name":  "جلسة قصيرة",
                     "en_name":  "Short Session",
                     "types":  "1",
                     "created_at":  "2026-06-21 15:11:29"
                 },
                 {
                     "id":  6,
                     "ar_name":  "فحص",
                     "en_name":  "Examination",
                     "types":  "1",
                     "created_at":  "2026-06-21 15:11:29"
                 },
                 {
                     "id":  5,
                     "ar_name":  "طوارئ",
                     "en_name":  "Emergency",
                     "types":  "1",
                     "created_at":  "2026-06-21 15:11:29"
                 },
                 {
                     "id":  4,
                     "ar_name":  "متابعة 3",
                     "en_name":  "Follow Up 3",
                     "types":  "3",
                     "created_at":  "2026-06-21 15:11:29"
                 },
                 {
                     "id":  3,
                     "ar_name":  "متابعة 2",
                     "en_name":  "Follow Up 2",
                     "types":  "2",
                     "created_at":  "2026-06-21 15:11:29"
                 },
                 {
                     "id":  2,
                     "ar_name":  "متابعة 1",
                     "en_name":  "Follow Up 1",
                     "types":  "1",
                     "created_at":  "2026-06-21 15:11:29"
                 },
                 {
                     "id":  1,
                     "ar_name":  "استشارة عامة",
                     "en_name":  "General Consultation",
                     "types":  "1",
                     "created_at":  "2026-06-21 15:11:29"
                 }
             ]
}
```

<div id="appointment-types-add"></div>

**`POST /api/clinic-system/appointment-types`**

Create a new appointment type. Public.

**Request Body:**

```json
{
    "types": 1,                     // required, integer, min:1, max:3
    "ar_name": "...",               // required
    "en_name": "..."                // required
}
```

**Response (201) - Success:**

```json
{
    "success":  true,
    "message":  "Appointment type created",
    "data":  null
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The ar name field is required. (and 2 more errors)",
    "errors":  {
                   "ar_name":  [
                                   "The ar name field is required."
                               ],
                   "en_name":  [
                                   "The en name field is required."
                               ],
                   "types":  [
                                 "The types field must be at least 1."
                             ]
               }
}
```

<div id="appointment-types-delete"></div>

**`DELETE /api/clinic-system/appointment-types/{id}`**

Delete an appointment type. Public.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Appointment type deleted",
    "data":  null
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Appointment type not found",
    "data":  null
}
```

---

## Devices

FCM device token registration.

<div id="devices-register-token"></div>

**`POST /api/clinic-system/devices/register-token`**

Register FCM device token. Auth required.

**Request Body:**

```json
{
    "fcm_token": "..."             // required
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (200) - response:**

```json
{
    "message":  "Not implemented yet."
}
```

---

## Specialties

Medical specialty management.

<div id="specialties-get-all-specialties"></div>

**`GET /api/clinic-system/clinic/specialty/getAll`**

Show all doctor specialties. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Doctor specialties retrieved",
    "data":  [
                 {
                     "id":  1,
                     "ar":  "الطب العام",
                     "en":  "General Medicine"
                 }
             ]
}
```

<div id="specialties-show-primary"></div>

**`GET /api/clinic-system/clinic/specialty/showPrimary/{doctorId}`**

Show primary specialty. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Primary specialty retrieved",
    "data":  {
                 "id":  1,
                 "ar":  "الطب العام",
                 "en":  "General Medicine"
             }
}
```

<div id="specialties-index"></div>

**`GET /api/clinic-system/clinic/specialty/index`**

List all specialties. Public.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Specialties retrieved successfully",
    "data":  [
                 {
                     "id":  1,
                     "ar_name":  "الطب العام",
                     "en_name":  "General Medicine"
                 },
                 {
                     "id":  2,
                     "ar_name":  "الطب الباطني",
                     "en_name":  "Internal Medicine"
                 },
                 {
                     "id":  3,
                     "ar_name":  "أمراض القلب",
                     "en_name":  "Cardiology"
                 },
                 {
                     "id":  4,
                     "ar_name":  "أمراض الجهاز الهضمي",
                     "en_name":  "Gastroenterology"
                 },
                 {
                     "id":  5,
                     "ar_name":  "أمراض الصدر والجهاز التنفسي",
                     "en_name":  "Pulmonology"
                 },
                 {
                     "id":  6,
                     "ar_name":  "أمراض الكلى",
                     "en_name":  "Nephrology"
                 },
                 {
                     "id":  7,
                     "ar_name":  "الغدد الصم والسكري",
                     "en_name":  "Endocrinology \u0026 Diabetes"
                 },
                 {
                     "id":  8,
                     "ar_name":  "أمراض الأعصاب",
                     "en_name":  "Neurology"
                 },
                 {
                     "id":  9,
                     "ar_name":  "أمراض الأورام",
                     "en_name":  "Oncology"
                 },
                 {
                     "id":  10,
                     "ar_name":  "أمراض الروماتيزم والمفاصل",
                     "en_name":  "Rheumatology"
                 },
                 {
                     "id":  11,
                     "ar_name":  "طب الأطفال",
                     "en_name":  "Pediatrics"
                 },
                 {
                     "id":  12,
                     "ar_name":  "التوليد وأمراض النساء",
                     "en_name":  "Obstetrics \u0026 Gynecology"
                 },
                 {
                     "id":  13,
                     "ar_name":  "طب الشيخوخة",
                     "en_name":  "Geriatrics"
                 },
                 {
                     "id":  14,
                     "ar_name":  "الأمراض الجلدية",
                     "en_name":  "Dermatology"
                 },
                 {
                     "id":  15,
                     "ar_name":  "طب وجراحة العيون",
                     "en_name":  "Ophthalmology"
                 },
                 {
                     "id":  16,
                     "ar_name":  "أذن وأنف وحنجرة",
                     "en_name":  "Otolaryngology (ENT)"
                 },
                 {
                     "id":  17,
                     "ar_name":  "طب الأسنان",
                     "en_name":  "Dentistry"
                 },
                 {
                     "id":  18,
                     "ar_name":  "الطب النفسي",
                     "en_name":  "Psychiatry"
                 },
                 {
                     "id":  19,
                     "ar_name":  "الجراحة العامة",
                     "en_name":  "General Surgery"
                 },
                 {
                     "id":  20,
                     "ar_name":  "جراحة العظام",
                     "en_name":  "Orthopedic Surgery"
                 },
                 {
                     "id":  21,
                     "ar_name":  "جراحة التجميل والترميم",
                     "en_name":  "Plastic Surgery"
                 },
                 {
                     "id":  22,
                     "ar_name":  "أمراض وجراحة المسالك البولية",
                     "en_name":  "Urology"
                 },
                 {
                     "id":  23,
                     "ar_name":  "العلاج الفيزيائي وإعادة التأهيل",
                     "en_name":  "Physical Therapy \u0026 Rehabilitation"
                 },
                 {
                     "id":  24,
                     "ar_name":  "الأشعة والتصوير الطبي",
                     "en_name":  "Radiology"
                 },
                 {
                     "id":  25,
                     "ar_name":  "التخدير وتدبير الألم",
                     "en_name":  "Anesthesiology"
                 }
             ]
}
```

<div id="specialties-change-primary"></div>

**`POST /api/clinic-system/clinic/specialty/changePrimary/{specialtyId}`**

Change primary specialty. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Primary specialty updated successfully",
    "data":  null
}
```

<div id="specialties-attach-specialties"></div>

**`POST /api/clinic-system/clinic/specialty/add`**

Attach specialties to doctor. Auth required.

**Request Body:**

```json
{
    "specialty_ids": [1, 2]         // required, array, min:1, each exists:specialties
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "current_specialties",
    "data":  [
                 {
                     "id":  1,
                     "ar":  "الطب العام",
                     "en":  "General Medicine"
                 },
                 {
                     "id":  26,
                     "ar":  "اختبار",
                     "en":  "Test Spec"
                 }
             ]
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The specialty ids field is required.",
    "errors":  {
                   "specialty_ids":  [
                                         "The specialty ids field is required."
                                     ]
               }
}
```

<div id="specialties-detach-specialty"></div>

**`DELETE /api/clinic-system/clinic/specialty/delete/{specialId}`**

Detach specialty from doctor. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Specialty detached successfully",
    "data":  [

             ]
}
```

---

## Schedules

Doctor work hours and schedules.

<div id="schedules-get-weekly"></div>

**`GET /api/clinic-system/clinic/schedule/get-weekly/{doctorId}`**

Get weekly schedule for a doctor. Public.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Schedule retrieved successfully.",
    "data":  [
                 {
                     "id":  1,
                     "doctor":  {
                                    "id":  1,
                                    "name":  "Glenna Lebsack"
                                },
                     "day_of_week":  0,
                     "day_name":  "Sunday",
                     "start_time":  "09:00",
                     "end_time":  "17:00",
                     "break_start":  "13:00",
                     "break_end":  "14:00",
                     "max_patients_per_day":  20,
                     "duration_minutes":  480,
                     "created_at":  "2026-06-21"
                 }
             ]
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Doctor profile not found.",
    "data":  null
}
```

<div id="schedules-work-hour-by-date"></div>

**`GET /api/clinic-system/clinic/schedule/work-hour/{doctorId}`**

Get work hours for a specific date. Public.

**Query Parameters:**

```
date=2026-06-28
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Work hour retrieved successfully.",
    "data":  {
                 "id":  1,
                 "doctor":  {
                                "id":  1,
                                "name":  "Anibal Grimes"
                            },
                 "day_of_week":  0,
                 "day_name":  "Sunday",
                 "start_time":  "09:00",
                 "end_time":  "17:00",
                 "break_start":  "13:00",
                 "break_end":  "14:00",
                 "max_patients_per_day":  20,
                 "duration_minutes":  480,
                 "created_at":  "2026-06-21"
             }
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The date field must match the format Y-m-d.",
    "errors":  {
                   "date":  [
                                "The date field must match the format Y-m-d."
                            ]
               }
}
```

<div id="schedules-store"></div>

**`POST /api/clinic-system/clinic/schedule/add`**

Create work hour entry. Auth required.

**Request Body:**

```json
{
    "doctor_id": 1,                 // required, integer, exists:doctors
    "day_of_week": 0,               // required, integer, between:0,6
    "start_time": "09:00",          // required, format:H:i
    "end_time": "17:00",            // required, format:H:i, after:start_time
    "is_active": true,              // optional, boolean
    "max_patients_per_day": 20,     // optional, integer, min:1
    "break_start": "12:00",         // optional, format:H:i
    "break_end": "13:00"            // optional, format:H:i
}
```

**Response (201) - Success:**

```json
{
    "success":  true,
    "message":  "Work hour added successfully.",
    "data":  {
                 "id":  2,
                 "doctor":  {
                                "id":  1,
                                "name":  "Odessa Bode"
                            },
                 "day_of_week":  1,
                 "day_name":  "Monday",
                 "start_time":  "09:00",
                 "end_time":  "17:00",
                 "break_start":  null,
                 "break_end":  null,
                 "max_patients_per_day":  15,
                 "duration_minutes":  480,
                 "created_at":  "2026-06-21"
             }
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The doctor id field must be an integer. (and 3 more errors)",
    "errors":  {
                   "doctor_id":  [
                                     "The doctor id field must be an integer."
                                 ],
                   "day_of_week":  [
                                       "يوم الأسبوع يجب أن يكون بين 0 و 6."
                                   ],
                   "start_time":  [
                                      "وقت البداية يجب أن يكون بصيغة H:i (مثال: 14:30)."
                                  ],
                   "end_time":  [
                                    "وقت نهاية الدوام يجب أن يكون بعد وقت البداية."
                                ]
               }
}
```

<div id="schedules-update"></div>

**`PUT /api/clinic-system/clinic/schedule/edit`**

Update work hour. Auth required.

**Request Body:**

```json
{
    "doctor_id": 1,                 // sometimes, integer, exists:doctors
    "day_of_week": 0,               // sometimes, integer, between:0,6
    "start_time": "09:00",          // sometimes, format:H:i
    "end_time": "18:00",            // sometimes, format:H:i, after:start_time
    "is_active": true,              // optional, boolean
    "max_patients_per_day": 20,     // optional, integer, min:1
    "break_start": "12:00",         // optional, format:H:i
    "break_end": "13:00"            // optional, format:H:i
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Work hour updated successfully.",
    "data":  {
                 "id":  1,
                 "doctor":  {
                                "id":  1,
                                "name":  "Leonardo Luettgen"
                            },
                 "day_of_week":  0,
                 "day_name":  "Sunday",
                 "start_time":  "10:00",
                 "end_time":  "16:00",
                 "break_start":  "13:00",
                 "break_end":  "14:00",
                 "max_patients_per_day":  10,
                 "duration_minutes":  360,
                 "created_at":  "2026-06-21"
             }
}
```

<div id="schedules-destroy"></div>

**`DELETE /api/clinic-system/clinic/schedule/delete/{dayOfWeek}/{doctorId}`**

Delete work hour. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Work hour deleted successfully (Soft Deleted).",
    "data":  null
}
```

---

## Schedule Overrides

Schedule override management for doctors (closed days or time adjustments).

<div id="overrides-index"></div>

**`GET /api/clinic-system/clinic/schedule/override`**

List overrides for a doctor. Public.

**Query Parameters:**

```
doctor_id=1
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Overrides retrieved successfully.",
    "data":  [
                 {
                     "id":  1,
                     "doctor":  {
                                    "id":  1,
                                    "name":  "Dr. Smith"
                                },
                     "override_date":  "2026-07-01",
                     "override_type":  "time_change",
                     "start_time":  "14:00",
                     "end_time":  "16:00",
                     "reason":  "Personal appointment",
                     "is_closed":  false,
                     "created_at":  "2026-06-21 15:00",
                     "updated_at":  "2026-06-21 15:00"
                 },
                 {
                     "id":  2,
                     "doctor":  {
                                    "id":  1,
                                    "name":  "Dr. Smith"
                                },
                     "override_date":  "2026-07-04",
                     "override_type":  "closed",
                     "start_time":  null,
                     "end_time":  null,
                     "reason":  "Public holiday",
                     "is_closed":  true,
                     "created_at":  "2026-06-21 15:00",
                     "updated_at":  "2026-06-21 15:00"
                 }
             ]
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Doctor profile not found.",
    "data":  null
}
```

<div id="overrides-show"></div>

**`GET /api/clinic-system/clinic/schedule/override/{id}`**

Get a single override. Public.

**Query Parameters:**

```
doctor_id=1
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  {
                 "id":  1,
                 "doctor":  {
                                "id":  1,
                                "name":  "Dr. Smith"
                            },
                 "override_date":  "2026-07-01",
                 "override_type":  "time_change",
                 "start_time":  "14:00",
                 "end_time":  "16:00",
                 "reason":  "Personal appointment",
                 "is_closed":  false,
                 "created_at":  "2026-06-21 15:00",
                 "updated_at":  "2026-06-21 15:00"
             }
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Override not found.",
    "data":  null
}
```

<div id="overrides-store"></div>

**`POST /api/clinic-system/clinic/schedule/override/add`**

Create a schedule override. Auth required.

**Request Body:**

```json
{
    "doctor_id": 1,                 // required, integer, exists:doctors
    "override_date": "2026-07-01",  // required, date_format:Y-m-d
    "override_type": "time_change", // optional, string, max:50
    "start_time": "14:00",          // nullable, format:H:i
    "end_time": "16:00",            // nullable, format:H:i, after:start_time
    "reason": "Personal appointment", // optional, string, max:500
    "is_closed": false              // optional, boolean
}
```

**Response (201) - Success:**

```json
{
    "success":  true,
    "message":  "Override added successfully.",
    "data":  {
                 "id":  1,
                 "doctor":  {
                                "id":  1,
                                "name":  "Dr. Smith"
                            },
                 "override_date":  "2026-07-01",
                 "override_type":  "time_change",
                 "start_time":  "14:00",
                 "end_time":  "16:00",
                 "reason":  "Personal appointment",
                 "is_closed":  false,
                 "created_at":  "2026-06-21 15:00",
                 "updated_at":  "2026-06-21 15:00"
             }
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The doctor id field is required. (and 1 more error)",
    "errors":  {
                   "doctor_id":  [
                                     "الطبيب مطلوب."
                                 ],
                   "override_date":  [
                                         "التاريخ مطلوب."
                                     ]
               }
}
```

**Response (422) - Error: date-conflict:**

```json
{
    "success":  false,
    "message":  "يوجد بالفعل استثناء لهذا التاريخ.",
    "data":  null
}
```

<div id="overrides-update"></div>

**`PUT /api/clinic-system/clinic/schedule/override/{id}/edit`**

Update a schedule override. Auth required.

**Request Body:**

```json
{
    "doctor_id": 1,                 // sometimes, integer, exists:doctors
    "override_date": "2026-07-01",  // sometimes, date_format:Y-m-d
    "override_type": "extended",    // optional, string, max:50
    "start_time": "10:00",          // nullable, format:H:i
    "end_time": "14:00",            // nullable, format:H:i, after:start_time
    "reason": "Extended training",  // optional, string, max:500
    "is_closed": false              // optional, boolean
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Override updated successfully.",
    "data":  {
                 "id":  1,
                 "doctor":  {
                                "id":  1,
                                "name":  "Dr. Smith"
                            },
                 "override_date":  "2026-07-01",
                 "override_type":  "extended",
                 "start_time":  "10:00",
                 "end_time":  "14:00",
                 "reason":  "Extended training",
                 "is_closed":  false,
                 "created_at":  "2026-06-21 15:00",
                 "updated_at":  "2026-06-21 15:00"
             }
}
```

**Response (422) - Error: conflict:**

```json
{
    "success":  false,
    "message":  "الأوقات المدخلة تتعارض مع استثناء موجود مسبقاً.",
    "data":  null
}
```

<div id="overrides-destroy"></div>

**`DELETE /api/clinic-system/clinic/schedule/override/{id}/delete`**

Delete a schedule override (soft delete). Auth required.

**Request Body:**

```json
{
    "doctor_id": 1                  // required, integer, exists:doctors
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Override deleted successfully.",
    "data":  null
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "السجل غير موجود.",
    "data":  null
}
```

<div id="overrides-get-by-date"></div>

**`GET /api/clinic-system/clinic/schedule/override/date/single`**

Get override by exact date. Public.

**Query Parameters:**

```
doctor_id=1&date=2026-07-01
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Override retrieved successfully.",
    "data":  {
                 "id":  1,
                 "doctor":  {
                                "id":  1,
                                "name":  "Dr. Smith"
                            },
                 "override_date":  "2026-07-01",
                 "override_type":  "closed",
                 "start_time":  null,
                 "end_time":  null,
                 "reason":  "Holiday",
                 "is_closed":  true,
                 "created_at":  "2026-06-21 15:00",
                 "updated_at":  "2026-06-21 15:00"
             }
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "No override for this date.",
    "data":  null
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The date field must match the format Y-m-d.",
    "errors":  {
                   "date":  [
                                "The date field must match the format Y-m-d."
                            ]
               }
}
```

<div id="overrides-get-by-date-range"></div>

**`GET /api/clinic-system/clinic/schedule/override/date/range`**

Get overrides in a date range. Public.

**Query Parameters:**

```
doctor_id=1&from=2026-07-01&to=2026-07-07
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Overrides retrieved successfully.",
    "data":  [
                 {
                     "id":  1,
                     "doctor":  {
                                    "id":  1,
                                    "name":  "Dr. Smith"
                                },
                     "override_date":  "2026-07-01",
                     "override_type":  "time_change",
                     "start_time":  "12:00",
                     "end_time":  "14:00",
                     "reason":  null,
                     "is_closed":  false,
                     "created_at":  "2026-06-21 15:00",
                     "updated_at":  "2026-06-21 15:00"
                 }
             ]
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The from field is required. (and 2 more errors)",
    "errors":  {
                   "from":  [
                                "The from field is required."
                            ],
                   "to":  [
                              "The to field is required."
                          ],
                   "doctor_id":  [
                                     "The doctor id field is required."
                                 ]
               }
}
```

---

## Medicines

Medicine search and creation.

<div id="medicines-search"></div>

**`GET /api/clinic-system/clinic/medicines/search`**

Search medicines by name. Public.

**Query Parameters:**

```
query=para
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Medicines search results retrieved successfully.",
    "data":  [
                 {
                     "id":  1,
                     "ar_name":  "باراسيتامول",
                     "en_name":  "Paracetamol",
                     "generic_name_ar":  null,
                     "generic_name_en":  "molestias",
                     "strength":  "500mg",
                     "form":  "tablet",
                     "created_at":  "2026-06-21T15:12:59.000000Z",
                     "updated_at":  "2026-06-21T15:12:59.000000Z",
                     "api_medicine_id":  null,
                     "is_custom":  1
                 },
                 {
                     "id":  2,
                     "ar_name":  "sequi",
                     "en_name":  "Paracetamol",
                     "generic_name_ar":  null,
                     "generic_name_en":  "harum",
                     "strength":  "250mg",
                     "form":  "injection",
                     "created_at":  "2026-06-21T15:12:59.000000Z",
                     "updated_at":  "2026-06-21T15:12:59.000000Z",
                     "api_medicine_id":  null,
                     "is_custom":  1
                 }
             ]
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The query field is required.",
    "errors":  {
                   "query":  [
                                 "The query field is required."
                             ]
               }
}
```

<div id="medicines-store"></div>

**`POST /api/clinic-system/clinic/medicines/store`**

Create custom medicine. Auth required.

**Request Body:**

```json
{
    "ar_name": "...",               // required_without:en_name
    "en_name": "Paracetamol",        // required_without:ar_name
    "api_medicine_id": null,         // optional
    "generic_name_ar": null,         // optional
    "generic_name_en": null,         // optional
    "strength": "500mg",             // optional
    "form": "tablet"                 // optional, in:tablet,capsule,syrup,injection,ointment
    // Note: at least one of ar_name or en_name is required
}
```

**Response (201) - Success:**

```json
{
    "success":  true,
    "message":  "Medicine processed successfully.",
    "data":  {
                 "id":  2,
                 "api_id":  null,
                 "arabic_name":  "ايبوبروفين",
                 "english_name":  "Ibuprofen",
                 "generic_arabic":  null,
                 "generic_english":  null,
                 "strength":  "400mg",
                 "form":  "tablet",
                 "is_custom_added":  true,
                 "created_at":  "2026-06-21T15:13:06+00:00"
             }
}
```

**Response (401) - Error: unauthenticated:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (403) - Error: unauthorized:**

```json
{
    "message":  "This action is unauthorized.",
    "exception":  "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException",
    "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Exceptions\\Handler.php",
    "line":  640,
    "trace":  [

              ]
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "Please provide either the Arabic or English name of the medicine. (and 2 more errors)",
    "errors":  {
                   "ar_name":  [
                                   "Please provide either the Arabic or English name of the medicine."
                               ],
                   "en_name":  [
                                   "Please provide either the Arabic or English name of the medicine."
                               ],
                   "form":  [
                                "The selected medicine form is invalid. Choose from: tablet, capsule, syrup, injection, ointment."
                            ]
               }
}
```

---

## Diseases

Disease search and creation.

<div id="diseases-search"></div>

**`GET /api/clinic-system/clinic/diseases/search`**

Search diseases by name. Public.

**Query Parameters:**

```
query=dia
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Diseases search results retrieved successfully.",
    "data":  [
                 {
                     "id":  1,
                     "code":  "WFW180",
                     "ar_name":  "السكري",
                     "en_name":  "Diabetes",
                     "description":  "Et sunt quis qui quia.",
                     "disease_nature":  "chronic",
                     "created_at":  "2026-06-21T15:09:09.000000Z",
                     "updated_at":  "2026-06-21T15:09:09.000000Z",
                     "is_custom":  1
                 },
                 {
                     "id":  2,
                     "code":  "SUS919",
                     "ar_name":  "suscipit",
                     "en_name":  "Diabetes",
                     "description":  "Eveniet occaecati sit recusandae.",
                     "disease_nature":  "infectious",
                     "created_at":  "2026-06-21T15:09:09.000000Z",
                     "updated_at":  "2026-06-21T15:09:09.000000Z",
                     "is_custom":  1
                 },
                 {
                     "code":  "E08.00",
                     "en_name":  "Diabetes mellitus due to underlying condition with hyperosmolarity without nonketotic hyperglycemic-hyperosmolar coma (NKHHC)",
                     "ar_name":  null,
                     "disease_nature":  "other",
                     "description":  "ICD-10 International Classification"
                 },
                 {
                     "code":  "E08.01",
                     "en_name":  "Diabetes mellitus due to underlying condition with hyperosmolarity with coma",
                     "ar_name":  null,
                     "disease_nature":  "other",
                     "description":  "ICD-10 International Classification"
                 },
                 {
                     "code":  "E08.10",
                     "en_name":  "Diabetes mellitus due to underlying condition with ketoacidosis without coma",
                     "ar_name":  null,
                     "disease_nature":  "other",
                     "description":  "ICD-10 International Classification"
                 },
                 {
                     "code":  "E08.11",
                     "en_name":  "Diabetes mellitus due to underlying condition with ketoacidosis with coma",
                     "ar_name":  null,
                     "disease_nature":  "other",
                     "description":  "ICD-10 International Classification"
                 },
                 {
                     "code":  "E08.21",
                     "en_name":  "Diabetes mellitus due to underlying condition with diabetic nephropathy",
                     "ar_name":  null,
                     "disease_nature":  "other",
                     "description":  "ICD-10 International Classification"
                 },
                 {
                     "code":  "E08.22",
                     "en_name":  "Diabetes mellitus due to underlying condition with diabetic chronic kidney disease",
                     "ar_name":  null,
                     "disease_nature":  "other",
                     "description":  "ICD-10 International Classification"
                 },
                 {
                     "code":  "E08.29",
                     "en_name":  "Diabetes mellitus due to underlying condition with other diabetic kidney complication",
                     "ar_name":  null,
                     "disease_nature":  "other",
                     "description":  "ICD-10 International Classification"
                 }
             ]
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The query field is required.",
    "errors":  {
                   "query":  [
                                 "The query field is required."
                             ]
               }
}
```

<div id="diseases-store"></div>

**`POST /api/clinic-system/clinic/diseases/store`**

Create custom disease. Auth required.

**Request Body:**

```json
{
    "ar_name": "...",               // required
    "en_name": "Diabetes",           // required
    "disease_nature": "chronic",     // required, in:infectious,genetic,chronic,acute,mental,other
    "code": null,                    // optional
    "description": null              // optional
}
```

**Response (201) - Success:**

```json
{
    "success":  true,
    "message":  "Disease processed successfully.",
    "data":  {
                 "id":  2,
                 "icd10_code":  "E10",
                 "arabic_name":  "سكري",
                 "english_name":  "Diabetes",
                 "description":  "Type 1 Diabetes Mellitus",
                 "nature":  "chronic",
                 "is_custom":  false,
                 "created_at":  "2026-06-21T15:13:15+00:00"
             }
}
```

**Response (401) - Error: unauthenticated:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (403) - Error: unauthorized:**

```json
{
    "message":  "This action is unauthorized.",
    "exception":  "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException",
    "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Exceptions\\Handler.php",
    "line":  640,
    "trace":  [

              ]
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The Arabic name of the disease is required. (and 2 more errors)",
    "errors":  {
                   "ar_name":  [
                                   "The Arabic name of the disease is required."
                               ],
                   "en_name":  [
                                   "The English name of the disease is required."
                               ],
                   "disease_nature":  [
                                          "The selected disease nature is invalid."
                                      ]
               }
}
```

---

## Clinic

Clinic management (all routes require auth:sanctum).

<div id="clinic-create-secretary"></div>

**`POST /api/clinic-system/clinic/clinic/secretary/register`**

Create secretary (owner only). Auth required.

**Request Body:**

```json
{
    "fname": "New",               // required, min:2, max:50
    "lname": "Secretary",         // required, min:2, max:50
    "email": "newsecretary@test.com", // required, email, unique:users
    "dob": "1990-01-01",          // required, date, before:today
    "gender": "female",           // required, in:male,female,unknown
    "clinic_id": 1,               // required, exists:clinics
    "room_ids": [1]               // required, array, min:1, each exists:rooms
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Secretary created successfully.",
    "data":  null
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "First name is required. (and 6 more errors)",
    "errors":  {
                   "fname":  [
                                 "First name is required."
                             ],
                   "lname":  [
                                 "Last name is required."
                             ],
                   "email":  [
                                 "Email address is required."
                             ],
                   "dob":  [
                               "Date of birth is required."
                           ],
                   "gender":  [
                                  "Gender selection is required."
                              ],
                   "clinic_id":  [
                                     "Please select a clinic."
                                 ],
                   "room_ids":  [
                                    "Please select a room."
                                ]
               }
}
```

<div id="clinic-create-doctor"></div>

**`POST /api/clinic-system/clinic/clinic/doctor/register`**

Create doctor (owner only). Auth required.

**Request Body:**

```json
{
    "fname": "New",               // required, min:2, max:50
    "lname": "Doctor",            // required, min:2, max:50
    "email": "newdoctor@test.com", // required, email, unique:users
    "dob": "1985-05-15",          // required, date, before:today
    "gender": "male",             // required, in:male,female,unknown
    "clinic_id": 1,               // required, exists:clinics
    "room_id": 1,                 // required, exists:rooms
    "appointment_duration": 30,   // required, integer, min:5, max:120
    "consultation_fee": 200,      // required, numeric, min:0
    "specialty_ids": [1],         // required, array, min:1
    "bio": "Experienced doctor"   // optional, max:1000
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Doctor created successfully.",
    "data":  null
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "First name is required. (and 9 more errors)",
    "errors":  {
                   "fname":  [
                                 "First name is required."
                             ],
                   "lname":  [
                                 "Last name is required."
                             ],
                   "email":  [
                                 "Please provide a valid email address."
                             ],
                   "dob":  [
                               "Date of birth is required."
                           ],
                   "gender":  [
                                  "Gender selection is required."
                              ],
                   "clinic_id":  [
                                     "Please select a clinic."
                                 ],
                   "room_id":  [
                                   "Please select a room."
                               ],
                   "appointment_duration":  [
                                                "Appointment duration is required."
                                            ],
                   "consultation_fee":  [
                                            "Consultation fee is required."
                                        ],
                   "specialty_ids":  [
                                         "At least one specialty must be selected."
                                     ]
               }
}
```

---

## Rooms

Room management (auth required).

<div id="rooms-user-rooms"></div>

**`GET /api/clinic-system/clinic/clinic/rooms/userRooms/get`**

Get current user's rooms. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  [
                 {
                     "id":  1,
                     "name":  "Test Room",
                     "clinic_id":  1,
                     "created":  "2026-06-21",
                     "doctors":  [
                                     {
                                         "id":  1,
                                         "name":  "Madeline Lind"
                                     }
                                 ],
                     "secretaries":  [
                                         {
                                             "id":  1,
                                             "name":  "Derek Osinski"
                                         }
                                     ]
                 }
             ]
}
```

<div id="rooms-index"></div>

**`GET /api/clinic-system/clinic/clinic/rooms/{clinicId}`**

List rooms in a clinic. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  [
                 {
                     "id":  1,
                     "name":  "Test Room"
                 }
             ]
}
```

**Response (200) - with-info-success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  [
                 {
                     "id":  1,
                     "clinic_id":  1,
                     "name":  "Test Room",
                     "created":  "2026-06-21",
                     "doctors":  [
                                     {
                                         "id":  1,
                                         "name":  "Sabrina Nicolas"
                                     }
                                 ],
                     "secretaries":  [
                                         {
                                             "id":  1,
                                             "name":  "Althea Kiehn"
                                         }
                                     ]
                 }
             ]
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

<div id="rooms-index-with-info"></div>

**`GET /api/clinic-system/clinic/clinic/rooms/{clinicId}/info`**

List rooms with additional info. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  [
                 {
                     "id":  1,
                     "clinic_id":  1,
                     "name":  "Test Room",
                     "created":  "2026-06-21",
                     "doctors":  [
                                     {
                                         "id":  1,
                                         "name":  "Sabrina Nicolas"
                                     }
                                 ],
                     "secretaries":  [
                                         {
                                             "id":  1,
                                             "name":  "Althea Kiehn"
                                         }
                                     ]
                 }
             ]
}
```

<div id="rooms-add-doctor-to-room"></div>

**`POST /api/clinic-system/clinic/clinic/rooms/sync/doctorRoom`**

Add doctor to room. Auth required.

**Request Body:**

```json
{
    "room_id": 1,                   // required
    "doctor_id": 1                  // required
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "The doctor changes the room successfuly",
    "data":  null
}
```

<div id="rooms-add-sec-to-room"></div>

**`POST /api/clinic-system/clinic/clinic/rooms/sync/secRooms`**

Add secretary to room. Auth required.

**Request Body:**

```json
{
    "room_id": 1,                   // required
    "secretary_id": 1               // required
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "The secretary changes the room successfuly",
    "data":  null
}
```

<div id="rooms-del-sec-from-room"></div>

**`DELETE /api/clinic-system/clinic/clinic/rooms/detach/secRooms`**

Remove secretary from room. Auth required.

**Request Body:**

```json
{
    "room_id": 1,                   // required
    "secretary_id": 1               // required
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "The secretary detach the room successfuly",
    "data":  null
}
```

<div id="rooms-del-doctor-from-room"></div>

**`DELETE /api/clinic-system/clinic/clinic/rooms/detach/doctorRoom`**

Remove doctor from room. Auth required.

**Request Body:**

```json
{
    "room_id": 1,                   // required
    "doctor_id": 1                  // required
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "The doctor detach the room successfuly",
    "data":  null
}
```

---

## Secretaries

Secretary management (auth required).

<div id="secretaries-info"></div>

**`GET /api/clinic-system/clinic/clinic/secretaries/{id}`**

Get secretary info. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  {
                 "id":  1,
                 "user_id":  4,
                 "clinic_id":  1,
                 "created_at":  "2026-06-21",
                 "role":  "secretary",
                 "name":  "Rozella Kuhic",
                 "phone":  "0900000003",
                 "dob":  "1972-10-06",
                 "gender":  "unknown"
             }
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Secretary not found",
    "data":  null
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

<div id="secretaries-update"></div>

**`POST /api/clinic-system/clinic/clinic/secretaries/update`**

Update secretary info. Auth required.

**Request Body:**

```json
{
    "clinic_id": 1,                 // optional, exists:clinics
    "fname": "Updated",             // optional
    "lname": "Name",                // optional
    "dob": "1990-01-01",            // optional, date, before:today
    "gender": "female"              // optional, in:male,female,unknown
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  null
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The dob field must be a valid date. (and 1 more error)",
    "errors":  {
                   "dob":  [
                               "The dob field must be a valid date."
                           ],
                   "gender":  [
                                  "The selected gender is invalid."
                              ]
               }
}
```

---

## Patients

Patient management (auth required).

<div id="patients-medical-history"></div>

**`GET /api/clinic-system/clinic/clinic/patients/{patientId}/medical-history`**

Get patient medical history. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Medical history retrieved successfully.",
    "data":  {
                 "id":  1,
                 "name":  "Isai Weissnat",
                 "phone":  "0900000004",
                 "email":  "patient@test.com",
                 "gender":  "male",
                 "dob":  "1972-06-03",
                 "profile_image":  null,
                 "clinic_id":  1,
                 "appointments":  [
                                      {
                                          "id":  1,
                                          "doctor_name":  "Shanna Gaylord",
                                          "type":  null,
                                          "start_time":  "2026-06-22 10:00",
                                          "end_time":  "2026-06-22 10:30",
                                          "status":  "scheduled",
                                          "visit_reason":  "Checkup"
                                      }
                                  ],
                 "records":  [
                                 {
                                     "id":  1,
                                     "doctor_name":  "Shanna Gaylord",
                                     "diagnosis_summary":  "Test diagnosis",
                                     "description":  "Nihil eum debitis officia voluptatum. Ut necessitatibus est voluptatum molestiae et ut quo quis. Reprehenderit non est ipsa quia repellat iusto maiores facilis. Aut qui velit sequi natus veniam.",
                                     "status":  "open",
                                     "diseases":  [

                                                  ],
                                     "prescriptions":  [

                                                       ],
                                     "created_at":  "2026-06-21"
                                 }
                             ],
                 "invoices":  [

                              ]
             }
}
```

**Response (500) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Patient not found.",
    "data":  null
}
```

<div id="patients-restore"></div>

**`GET /api/clinic-system/clinic/clinic/patients/restore`**

Restore soft-deleted patient. Auth required.

**Query Parameters:**

```
patient_id=1
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  null
}
```

<div id="patients-show"></div>

**`GET /api/clinic-system/clinic/clinic/patients/{patientId}/show`**

Get patient details. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "the patient data.",
    "data":  {
                 "id":  1,
                 "user_id":  5,
                 "name":  "Salvador Daugherty",
                 "gender":  "female",
                 "profile_image":  null,
                 "nationality":  "Saint Martin",
                 "address":  "357 Jody Manors\nPort Camylle, MS 02399-4171",
                 "marital_status":  "single",
                 "emergency_phone":  "0905262508",
                 "allergies":  null,
                 "chronic_conditions":  "Iure dicta enim ea nam voluptas et quaerat.",
                 "career":  "Textile Dyeing Machine Operator",
                 "blood_type":  "AB+",
                 "phone":  "0900000004",
                 "email":  "patient@test.com",
                 "dob":  "1981-12-23",
                 "created_at":  "2026-06-21"
             }
}
```

**Response (500) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "The patient is not found.",
    "data":  null
}
```

<div id="patients-index"></div>

**`GET /api/clinic-system/clinic/clinic/patients`**

List patients (requires clinic_id). Auth required.

**Query Parameters:**

```
clinic_id=1
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Patients retrieved successfully",
    "data":  [
                 {
                     "id":  1,
                     "user_id":  5,
                     "name":  "Brycen Flatley",
                     "gender":  "male",
                     "profile_image":  null,
                 }
             ],
    "pagination":  {
                       "total":  1,
                       "count":  1,
                       "per_page":  15,
                       "current_page":  1,
                       "last_page":  1,
                       "first_page_url":  "http://localhost/api/clinic-system/clinic/clinic/patients?page=1",
                       "last_page_url":  "http://localhost/api/clinic-system/clinic/clinic/patients?page=1",
                       "next_page_url":  null,
                       "prev_page_url":  null
                   }
}
```

**Response (200) - trashed-success:**

```json
{
    "success":  true,
    "message":  "success",
    "data":  [

             ],
    "pagination":  {
                       "total":  0,
                       "count":  0,
                       "per_page":  15,
                       "current_page":  1,
                       "last_page":  1,
                       "first_page_url":  "http://localhost/api/clinic-system/clinic/clinic/patients/trashed?page=1",
                       "last_page_url":  "http://localhost/api/clinic-system/clinic/clinic/patients/trashed?page=1",
                       "next_page_url":  null,
                       "prev_page_url":  null
                   }
}
```

**Response (500) - Error: no-clinic:**

```json
{
    "success":  false,
    "message":  "Please enter the required valid clinic.",
    "data":  null
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

<div id="patients-index-trashed"></div>

**`GET /api/clinic-system/clinic/clinic/patients/trashed`**

List soft-deleted patients. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "success",
    "data":  [

             ],
    "pagination":  {
                       "total":  0,
                       "count":  0,
                       "per_page":  15,
                       "current_page":  1,
                       "last_page":  1,
                       "first_page_url":  "http://localhost/api/clinic-system/clinic/clinic/patients/trashed?page=1",
                       "last_page_url":  "http://localhost/api/clinic-system/clinic/clinic/patients/trashed?page=1",
                       "next_page_url":  null,
                       "prev_page_url":  null
                   }
}
```

<div id="patients-update"></div>

**`POST /api/clinic-system/clinic/clinic/patients/update`**

Update patient info. Auth required.

**Request Body:**

```json
{
    "patient_id": 1,                // required, exists:patient_infos
    "fname": "Updated",             // optional
    "lname": "Name",                // optional
    "dob": "1990-01-01",            // optional, date, before:today
    "gender": "male",               // optional, in:male,female,other,unknown
    "nationality": null,            // optional
    "address": null,                // optional
    "marital_status": null,         // optional, in:married,single,divorced,widowed,other
    "emergency_phone": null,        // optional, digits_between:10,13
    "allergies": null,              // optional
    "chronic_conditions": null,     // optional
    "career": null,                 // optional
    "blood_type": "A+"              // optional, in:A+,A-,B+,B-,AB+,AB-,O+,O-
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  null
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The selected patient does not exist. (and 1 more error)",
    "errors":  {
                   "patient_id":  [
                                      "The selected patient does not exist."
                                  ],
                   "blood_type":  [
                                      "Blood type must be A+, A-, B+, B-, AB+, AB-, O+, or O-."
                                  ]
               }
}
```

<div id="patients-destroy"></div>

**`DELETE /api/clinic-system/clinic/clinic/patients/delete`**

Soft-delete patient. Auth required.

**Request Body:**

```json
{
    "patient_id": "1"               // required, exists:patient_infos
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  null
}
```

---

## Users

User profile management (auth required).

<div id="users-image-url"></div>

**`GET /api/clinic-system/clinic/clinic/users/image-url`**

Get user profile image URL. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  {
                 "profile_image_url":  "http://localhost/storage/defaults/avatar.svg"
             }
}
```

<div id="users-info"></div>

**`GET /api/clinic-system/clinic/clinic/users/info`**

Get authenticated user info. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  {
                 "id":  5,
                 "name":  "Dandre Lockman",
                 "phone":  "0900000004",
                 "email":  "patient@test.com",
                 "gender":  "male",
                 "dob":  "1993-02-07",
                 "profile_image":  null,
                 "created":  "2026-06-21",
                 "role":  "patient",
                 "clinic_id":  1,
                 "nationality":  "Martinique",
                 "address":  "68759 Rebecca Fields Apt. 469\nJanychester, MN 52058",
                 "marital_status":  "single",
                 "emergency_phone":  "0943194555",
                 "allergies":  null,
                 "chronic_conditions":  null,
                 "career":  "Dentist",
                 "blood_type":  "AB-"
             }
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

<div id="users-update-image"></div>

**`POST /api/clinic-system/clinic/clinic/users/update-image`**

Update user profile image. Auth required.

**Response (422) - Error: validation:**

```json
{
    "message":  "The profile image field is required.",
    "errors":  {
                   "profile_image":  [
                                         "The profile image field is required."
                                     ]
               }
}
```

---

## Doctors

Doctor management (auth required).

<div id="doctors-info"></div>

**`GET /api/clinic-system/clinic/clinic/doctors/{id}/info`**

Get doctor info. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  {
                 "id":  1,
                 "user_id":  3,
                 "clinic_id":  1,
                 "room_id":  1,
                 "name":  "Georgianna Herzog",
                 "phone":  "0900000002",
                 "email":  "doctor@test.com",
                 "dob":  "1976-08-30 08:44:36",
                 "gender":  "unknown",
                 "created_at":  "2026-06-21",
                 "appointment_duration":  30,
                 "consultation_fee":  150,
                 "bio":  "Dolor officiis expedita non. Saepe tenetur quis quam blanditiis quas suscipit quis. Reiciendis molestiae suscipit porro excepturi accusantium cumque minima. Sed ea sunt quos rerum sequi minima.",
                 "specialties":  [
                                     {
                                         "ar_name":  "الطب العام",
                                         "en_name":  "General Medicine"
                                     }
                                 ]
             }
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Doctor not found",
    "data":  null
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

<div id="doctors-index"></div>

**`GET /api/clinic-system/clinic/clinic/doctors/filter`**

List doctors with filters. Auth required.

**Query Parameters:**

```
clinic_id=1
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Doctors collection retrieved successfully.",
    "data":  [
                 {
                     "id":  1,
                     "user_id":  3,
                     "clinic_id":  1,
                     "room_id":  1,
                     "name":  "Lura Lang",
                     "phone":  "0900000002",
                     "email":  "doctor@test.com",
                     "dob":  "1990-05-04 17:42:35",
                     "gender":  "unknown",
                     "created_at":  "2026-06-21",
                     "appointment_duration":  30,
                     "consultation_fee":  150,
                     "bio":  "Magni nam voluptatum assumenda sint a quod. Aperiam aut aut ipsam. Soluta ad est qui quibusdam placeat. Error quia quia illo occaecati temporibus.",
                     "specialties":  [
                                         {
                                             "ar_name":  "الطب العام",
                                             "en_name":  "General Medicine"
                                         }
                                     ]
                 }
             ],
    "pagination":  {
                       "total":  1,
                       "count":  1,
                       "per_page":  15,
                       "current_page":  1,
                       "last_page":  1,
                       "first_page_url":  "http://localhost/api/clinic-system/clinic/clinic/doctors/filter?page=1",
                       "last_page_url":  "http://localhost/api/clinic-system/clinic/clinic/doctors/filter?page=1",
                       "next_page_url":  null,
                       "prev_page_url":  null
                   }
}
```

<div id="doctors-restore"></div>

**`POST /api/clinic-system/clinic/clinic/doctors/{id}/restore`**

Restore soft-deleted doctor. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Doctor restored successfully.",
    "data":  {
                 "id":  null,
                 "user_id":  null,
                 "clinic_id":  null,
                 "room_id":  null,
                 "name":  " ",
                 "phone":  null,
                 "email":  null,
                 "dob":  null,
                 "gender":  null,
                 "created_at":  null,
                 "appointment_duration":  null,
                 "consultation_fee":  null,
                 "bio":  null,
                 "specialties":  [

                                 ]
             }
}
```

<div id="doctors-update"></div>

**`POST /api/clinic-system/clinic/clinic/doctors/update`**

Update doctor info. Auth required.

**Request Body:**

```json
{
    "doctor_id": 1,                 // required, exists:doctors
    "fname": "Updated",             // optional
    "lname": "Doctor",              // optional
    "dob": "1985-05-15",            // optional, date, before:today
    "gender": "male",               // optional, in:male,female,unknown
    "appointment_duration": 30,     // optional, integer, min:5, max:120
    "bio": "Experienced",           // optional
    "consultation_fee": 200,        // optional, numeric, min:0
    "specialties": [1, 2]           // optional, array, each exists:specialties
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Your profile has been updated successfully.",
    "data":  null
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The appointment duration field must be at least 5. (and 1 more error)",
    "errors":  {
                   "appointment_duration":  [
                                                "The appointment duration field must be at least 5."
                                            ],
                   "consultation_fee":  [
                                            "The consultation fee field must be at least 0."
                                        ]
               }
}
```

<div id="doctors-force-delete"></div>

**`DELETE /api/clinic-system/clinic/clinic/doctors/{id}/force`**

Force-delete doctor. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "The doctor has been permanently deleted from the system.",
    "data":  null
}
```

<div id="doctors-destroy"></div>

**`DELETE /api/clinic-system/clinic/clinic/doctors/{id}/leave`**

Soft-delete doctor. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "The doctor has successfully left the clinic.",
    "data":  null
}
```

---

## Appointments

Appointment booking and management (auth required).

<div id="appointments-clinic-appointments"></div>

**`GET /api/clinic-system/clinic/clinic/appointments/clinic/{clinicId}`**

List clinic appointments. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Clinic appointments",
    "data":  [
                 {
                     "id":  1,
                     "clinic_id":  1,
                     "doctor":  {
                                    "id":  1,
                                    "name":  "Rhoda Gerhold"
                                },
                     "patient":  {
                                     "id":  1,
                                     "name":  "Alexanne Doyle",
                                     "phone":  "0900000004"
                                 },
                     "room":  {
                                  "id":  1,
                                  "name":  "Test Room"
                              },
                     "appointment_type":  {
                                              "id":  1,
                                              "ar_name":  "استشارة عامة",
                                              "en_name":  "General Consultation"
                                          },
                     "date":  "2026-06-22",
                     "dayOfWeek":  1,
                     "start_time":  "10:00",
                     "end_time":  "10:30",
                     "status":  "scheduled",
                     "visit_reason":  "Checkup",
                     "cancel_reason":  null,
                     "notes":  null,
                     "created_at":  "2026-06-21"
                 }
             ],
    "pagination":  {
                       "total":  1,
                       "count":  1,
                       "per_page":  15,
                       "current_page":  1,
                       "last_page":  1,
                       "first_page_url":  "http://localhost/api/clinic-system/clinic/clinic/appointments/clinic/1?page=1",
                       "last_page_url":  "http://localhost/api/clinic-system/clinic/clinic/appointments/clinic/1?page=1",
                       "next_page_url":  null,
                       "prev_page_url":  null
                   }
}
```

<div id="appointments-doctor-appointments"></div>

**`GET /api/clinic-system/clinic/clinic/appointments/doctor/{doctorId}`**

List doctor appointments. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Doctor appointments",
    "data":  [
                 {
                     "id":  1,
                     "clinic_id":  1,
                     "patient":  {
                                     "id":  1,
                                     "name":  "Blanca Bernhard",
                                     "phone":  "0900000004"
                                 },
                     "room":  {
                                  "id":  1,
                                  "name":  "Test Room"
                              },
                     "appointment_type":  {
                                              "id":  1,
                                              "ar_name":  "استشارة عامة",
                                              "en_name":  "General Consultation"
                                          },
                     "date":  "2026-06-22",
                     "dayOfWeek":  1,
                     "start_time":  "10:00",
                     "end_time":  "10:30",
                     "status":  "scheduled",
                     "visit_reason":  "Checkup",
                     "cancel_reason":  null,
                     "notes":  null,
                     "created_at":  "2026-06-21"
                 }
             ],
    "pagination":  {
                       "total":  1,
                       "count":  1,
                       "per_page":  15,
                       "current_page":  1,
                       "last_page":  1,
                       "first_page_url":  "http://localhost/api/clinic-system/clinic/clinic/appointments/doctor/1?page=1",
                       "last_page_url":  "http://localhost/api/clinic-system/clinic/clinic/appointments/doctor/1?page=1",
                       "next_page_url":  null,
                       "prev_page_url":  null
                   }
}
```

<div id="appointments-available-slots"></div>

**`GET /api/clinic-system/clinic/clinic/appointments/get/available-slots`**

Get available appointment slots. Auth required.

**Query Parameters:**

```
doctor_id=1&date=2026-06-28
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  {
                 "data":  [
                              {
                                  "start":  "09:00",
                                  "end":  "09:30"
                              },
                              {
                                  "start":  "09:30",
                                  "end":  "10:00"
                              },
                              {
                                  "start":  "10:00",
                                  "end":  "10:30"
                              },
                              {
                                  "start":  "10:30",
                                  "end":  "11:00"
                              },
                              {
                                  "start":  "11:00",
                                  "end":  "11:30"
                              },
                              {
                                  "start":  "11:30",
                                  "end":  "12:00"
                              },
                              {
                                  "start":  "12:00",
                                  "end":  "12:30"
                              },
                              {
                                  "start":  "12:30",
                                  "end":  "13:00"
                              },
                              {
                                  "start":  "14:00",
                                  "end":  "14:30"
                              },
                              {
                                  "start":  "14:30",
                                  "end":  "15:00"
                              },
                              {
                                  "start":  "15:00",
                                  "end":  "15:30"
                              },
                              {
                                  "start":  "15:30",
                                  "end":  "16:00"
                              },
                              {
                                  "start":  "16:00",
                                  "end":  "16:30"
                              },
                              {
                                  "start":  "16:30",
                                  "end":  "17:00"
                              }
                          ],
                 "meta":  {
                              "date":  "2026-06-28",
                              "dayOfWeek":  0,
                              "dayOfWeekEN":  "Sunday",
                              "count":  14
                          }
             }
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The doctor id field is required. (and 1 more error)",
    "errors":  {
                   "doctor_id":  [
                                     "The doctor id field is required."
                                 ],
                   "date":  [
                                "The date field is required."
                            ]
               }
}
```

<div id="appointments-clinic-schedule"></div>

**`GET /api/clinic-system/clinic/clinic/appointments/clinic/{clinicId}/schedule`**

Get clinic schedule for a date. Auth required.

**Query Parameters:**

```
date=2026-06-28
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  [
                 {
                     "id":  1,
                     "clinic_id":  1,
                     "doctor":  {
                                    "id":  1,
                                    "name":  "Sammy Bode"
                                },
                     "patient":  {
                                     "id":  1,
                                     "name":  "Vaughn Zieme",
                                     "phone":  "0900000004"
                                 },
                     "room":  {
                                  "id":  1,
                                  "name":  "Test Room"
                              },
                     "appointment_type":  {
                                              "id":  1,
                                              "ar_name":  "استشارة عامة",
                                              "en_name":  "General Consultation"
                                          },
                     "date":  "2026-06-22",
                     "dayOfWeek":  1,
                     "start_time":  "10:00",
                     "end_time":  "10:30",
                     "status":  "scheduled",
                     "visit_reason":  "Checkup",
                     "cancel_reason":  null,
                     "notes":  null,
                     "created_at":  "2026-06-21"
                 }
             ]
}
```

<div id="appointments-doctor-schedule"></div>

**`GET /api/clinic-system/clinic/clinic/appointments/doctor/{doctorId}/schedule`**

Get doctor schedule for a date. Auth required.

**Query Parameters:**

```
date=2026-06-28
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  [
                 {
                     "id":  1,
                     "clinic_id":  1,
                     "doctor":  {
                                    "id":  1,
                                    "name":  "Terrell Waters"
                                },
                     "patient":  {
                                     "id":  1,
                                     "name":  "Ena Paucek",
                                     "phone":  "0900000004"
                                 },
                     "room":  {
                                  "id":  1,
                                  "name":  "Test Room"
                              },
                     "appointment_type":  {
                                              "id":  1,
                                              "ar_name":  "استشارة عامة",
                                              "en_name":  "General Consultation"
                                          },
                     "date":  "2026-06-22",
                     "dayOfWeek":  1,
                     "start_time":  "10:00",
                     "end_time":  "10:30",
                     "status":  "scheduled",
                     "visit_reason":  "Checkup",
                     "cancel_reason":  null,
                     "notes":  null,
                     "created_at":  "2026-06-21"
                 }
             ]
}
```

<div id="appointments-room-appointments"></div>

**`GET /api/clinic-system/clinic/clinic/appointments/room/appo`**

List room appointments. Auth required.

**Query Parameters:**

```
roomIds[0]=1
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Room appointments",
    "data":  [
                 {
                     "id":  1,
                     "clinic_id":  1,
                     "doctor":  {
                                    "id":  1,
                                    "name":  "Terrence Leffler"
                                },
                     "patient":  {
                                     "id":  1,
                                     "name":  "Rupert Veum",
                                     "phone":  "0900000004"
                                 },
                     "room":  {
                                  "id":  1,
                                  "name":  "Test Room"
                              },
                     "appointment_type":  {
                                              "id":  1,
                                              "ar_name":  "استشارة عامة",
                                              "en_name":  "General Consultation"
                                          },
                     "date":  "2026-06-22",
                     "dayOfWeek":  1,
                     "start_time":  "10:00",
                     "end_time":  "10:30",
                     "status":  "scheduled",
                     "visit_reason":  "Checkup",
                     "cancel_reason":  null,
                     "notes":  null,
                     "created_at":  "2026-06-21"
                 }
             ],
    "pagination":  {
                       "total":  1,
                       "count":  1,
                       "per_page":  15,
                       "current_page":  1,
                       "last_page":  1,
                       "first_page_url":  "http://localhost/api/clinic-system/clinic/clinic/appointments/room/appo?page=1",
                       "last_page_url":  "http://localhost/api/clinic-system/clinic/clinic/appointments/room/appo?page=1",
                       "next_page_url":  null,
                       "prev_page_url":  null
                   }
}
```

<div id="appointments-show"></div>

**`GET /api/clinic-system/clinic/clinic/appointments/{id}`**

Show appointment details. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  {
                 "id":  1,
                 "clinic_id":  1,
                 "doctor":  {
                                "id":  1,
                                "name":  "Herminia Batz"
                            },
                 "patient":  {
                                 "id":  1,
                                 "name":  "Delia Gislason",
                                 "phone":  "0900000004"
                             },
                 "room":  {
                              "id":  1,
                              "name":  "Test Room"
                          },
                 "appointment_type":  {
                                          "id":  1,
                                          "ar_name":  "استشارة عامة",
                                          "en_name":  "General Consultation"
                                      },
                 "date":  "2026-06-22",
                 "dayOfWeek":  1,
                 "start_time":  "10:00",
                 "end_time":  "10:30",
                 "status":  "scheduled",
                 "visit_reason":  "Checkup",
                 "cancel_reason":  null,
                 "notes":  null,
                 "created_at":  "2026-06-21"
             }
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Appointment not found",
    "data":  null
}
```

<div id="appointments-patient-appointments"></div>

**`GET /api/clinic-system/clinic/clinic/appointments/patient/{patientId}`**

List patient appointments. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Patient appointments",
    "data":  [
                 {
                     "id":  1,
                     "clinic_id":  1,
                     "doctor":  {
                                    "id":  1,
                                    "name":  "Hardy Gulgowski"
                                },
                     "patient":  {
                                     "id":  1,
                                     "name":  "Nona Gleason",
                                     "phone":  "0900000004"
                                 },
                     "room":  {
                                  "id":  1,
                                  "name":  "Test Room"
                              },
                     "appointment_type":  {
                                              "id":  1,
                                              "ar_name":  "استشارة عامة",
                                              "en_name":  "General Consultation"
                                          },
                     "date":  "2026-06-22",
                     "dayOfWeek":  1,
                     "start_time":  "10:00",
                     "end_time":  "10:30",
                     "status":  "scheduled",
                     "visit_reason":  "Checkup",
                     "cancel_reason":  null,
                     "notes":  null,
                     "created_at":  "2026-06-21"
                 }
             ],
    "pagination":  {
                       "total":  1,
                       "count":  1,
                       "per_page":  15,
                       "current_page":  1,
                       "last_page":  1,
                       "first_page_url":  "http://localhost/api/clinic-system/clinic/clinic/appointments/patient/1?page=1",
                       "last_page_url":  "http://localhost/api/clinic-system/clinic/clinic/appointments/patient/1?page=1",
                       "next_page_url":  null,
                       "prev_page_url":  null
                   }
}
```

<div id="appointments-book"></div>

**`POST /api/clinic-system/clinic/clinic/appointments/book`**

Book a new appointment. Auth required.

**Request Body:**

```json
{
    "patient_id": 1,                // required, integer, exists:patient_infos
    "doctor_id": 1,                 // required, integer, exists:doctors
    "clinic_id": 1,                 // required, integer, exists:clinics
    "appointment_type_id": 1,       // required, integer, exists:appointment_types
    "start_time": "11:00",          // required, format:H:i
    "date": "2026-06-28",           // required, date, format:Y-m-d
    "visit_reason": "Routine checkup" // optional
}
```

**Response (201) - Success:**

```json
{
    "success":  true,
    "message":  "Appointment booked successfully",
    "data":  {
                 "id":  2,
                 "clinic_id":  1,
                 "doctor":  {
                                "id":  1,
                                "name":  "Weldon Schneider"
                            },
                 "patient":  {
                                 "id":  1,
                                 "name":  "Micaela Marquardt",
                                 "phone":  "0900000004"
                             },
                 "room":  {
                              "id":  1,
                              "name":  "Test Room"
                          },
                 "appointment_type":  {
                                          "id":  1,
                                          "ar_name":  "استشارة عامة",
                                          "en_name":  "General Consultation"
                                      },
                 "date":  "2026-06-28",
                 "dayOfWeek":  0,
                 "start_time":  "11:00",
                 "end_time":  "11:30",
                 "status":  "scheduled",
                 "visit_reason":  "Routine checkup",
                 "cancel_reason":  null,
                 "notes":  null,
                 "created_at":  "2026-06-21"
             }
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The patient id field is required. (and 6 more errors)",
    "errors":  {
                   "patient_id":  [
                                      "The patient id field is required."
                                  ],
                   "doctor_id":  [
                                     "The doctor id field is required."
                                 ],
                   "clinic_id":  [
                                     "The clinic id field is required."
                                 ],
                   "appointment_type_id":  [
                                               "The appointment type id field is required."
                                           ],
                   "start_time":  [
                                      "The start time field must match the format H:i."
                                  ],
                   "date":  [
                                "The date field must be a valid date.",
                                "The date field must match the format Y-m-d."
                            ]
               }
}
```

<div id="appointments-cancel"></div>

**`POST /api/clinic-system/clinic/clinic/appointments/{id}/cancel`**

Cancel an appointment. Auth required.

**Request Body:**

```json
{
    "cancel_reason": "Patient requested" // optional
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Appointment cancelled",
    "data":  null
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Appointment not found",
    "data":  null
}
```

<div id="appointments-reschedule"></div>

**`POST /api/clinic-system/clinic/clinic/appointments/{id}/reschedule`**

Reschedule an appointment. Auth required.

**Request Body:**

```json
{
    "start_time": "14:00",          // required, format:H:i
    "date": "2026-06-28",           // required, date, format:Y-m-d
    "type_id": 1                    // optional, exists:appointment_types
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Appointment updated successfully",
    "data":  {
                 "id":  1,
                 "clinic_id":  1,
                 "doctor":  {
                                "id":  1,
                                "name":  "Luella Morar"
                            },
                 "patient":  {
                                 "id":  1,
                                 "name":  "Lee Wilkinson",
                                 "phone":  "0900000004"
                             },
                 "room":  {
                              "id":  1,
                              "name":  "Test Room"
                          },
                 "appointment_type":  {
                                          "id":  1,
                                          "ar_name":  "استشارة عامة",
                                          "en_name":  "General Consultation"
                                      },
                 "date":  "2026-06-28",
                 "dayOfWeek":  0,
                 "start_time":  "14:00",
                 "end_time":  "14:30",
                 "status":  "scheduled",
                 "visit_reason":  "Checkup",
                 "cancel_reason":  null,
                 "notes":  null,
                 "created_at":  "2026-06-21"
             }
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The start time field must match the format H:i. (and 2 more errors)",
    "errors":  {
                   "start_time":  [
                                      "The start time field must match the format H:i."
                                  ],
                   "date":  [
                                "The date field must be a valid date.",
                                "The date field must match the format Y-m-d."
                            ]
               }
}
```

<div id="appointments-complete"></div>

**`POST /api/clinic-system/clinic/clinic/appointments/{id}/complete`**

Complete a confirmed appointment. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Appointment completed",
    "data":  null
}
```

**Response (400) - Error: not-confirmed:**

```json
{
    "success":  false,
    "message":  "the appointment is not confirmed",
    "data":  null
}
```

<div id="appointments-mark-confirmed"></div>

**`POST /api/clinic-system/clinic/clinic/appointments/{id}/confirmed`**

Mark appointment as confirmed. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Appointment marked confirmed",
    "data":  null
}
```

---

## Phone

Phone number management (auth required).

<div id="phone-verify-update"></div>

**`POST /api/clinic-system/phone/verify-update`**

Verify phone update with code. Auth required.

**Request Body:**

```json
{
    "code": "123456"               // required, digits:6
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Phone number updated successfully.",
    "data":  null
}
```

**Response (500) - Error: invalid-code:**

```json
{
    "success":  false,
    "message":  "No verification code found. Please request a new one.",
    "data":  null
}
```

**Response (500) - Error: no-request:**

```json
{
    "success":  false,
    "message":  "No verification code found. Please request a new one.",
    "data":  null
}
```

<div id="phone-update"></div>

**`POST /api/clinic-system/phone/update`**

Request phone update (sends code if already set). Auth required.

**Request Body:**

```json
{
    "phone": "0911111111"          // required, digits:10, starts_with:09
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The phone field must be 10 digits. (and 1 more error)",
    "errors":  {
                   "phone":  [
                                 "The phone field must be 10 digits.",
                                 "The phone field must start with one of the following: 09."
                             ]
               }
}
```

**Response (200) - send-code-first-time:**

```json
{
    "success":  true,
    "message":  "Verification code sent to your new phone number.",
    "data":  {
                 "new_phone":  "0911111111"
             }
}
```

---

## Patient Records

Patient medical records (auth required).

<div id="patient-records-get-all-by-doctor"></div>

**`GET /api/clinic-system/clinic/clinic/patient-records/doctor/{doctorId}/all`**

Get all records for a doctor. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Doctor records retrieved successfully",
    "data":  [
                 {
                     "id":  1,
                     "patient_id":  1,
                     "doctor_id":  1,
                     "clinic_id":  1,
                     "appointment_id":  1,
                     "diagnosis_summary":  "Test diagnosis",
                     "description":  "Praesentium dolor fugit nesciunt sed. Ut quis esse qui voluptas omnis cumque exercitationem. Quia et id quae quo eligendi tenetur iure voluptas. Nihil temporibus suscipit sunt accusantium quis.",
                     "status":  "open",
                     "notes":  "Non nobis dolorem recusandae vel non eius in. Qui animi eaque et ut voluptatem. Iusto eveniet placeat est molestiae officiis.",
                     "patient":  {
                                     "name":  "Miracle Weissnat",
                                     "phone":  "0900000004"
                                 },
                     "doctor":  {
                                    "name":  "Jamar Rosenbaum"
                                },
                     "created_at":  "2026-06-21 14:21:11"
                 }
             ]
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Doctor not found",
    "data":  null
}
```

<div id="patient-records-get-by-doctor"></div>

**`GET /api/clinic-system/clinic/clinic/patient-records/patient/{patientId}/doctor/{doctorId}`**

Get records by patient and doctor. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Records retrieved successfully",
    "data":  [
                 {
                     "id":  1,
                     "patient_id":  1,
                     "doctor_id":  1,
                     "clinic_id":  1,
                     "appointment_id":  1,
                     "diagnosis_summary":  "Test diagnosis",
                     "description":  "Facilis quis ducimus aut ut neque labore vitae. Adipisci aut facilis hic perferendis in dolores velit. Enim est vel asperiores fuga perferendis. Et perferendis deserunt ducimus autem natus quas.",
                     "status":  "open",
                     "notes":  "Eligendi quisquam sed odit error voluptatem inventore dolor exercitationem. Aut quia eos harum quisquam. Placeat ipsa ut eveniet laudantium autem officiis. Aut in harum officia veniam est modi repellendus.",
                     "patient":  {
                                     "name":  "Arch Fisher",
                                     "phone":  "0900000004"
                                 },
                     "doctor":  {
                                    "name":  "Naomie Schneider"
                                },
                     "created_at":  "2026-06-21 14:28:38"
                 }
             ]
}
```

**Response (404) - Error: doctor-not-found:**

```json
{
    "success":  false,
    "message":  "Doctor not found",
    "data":  null
}
```

**Response (404) - Error: patient-not-found:**

```json
{
    "success":  false,
    "message":  "Patient not found",
    "data":  null
}
```

<div id="patient-records-history"></div>

**`GET /api/clinic-system/clinic/clinic/patient-records/patient/{patientId}/history`**

Get patient medical history records. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Patient history retrieved successfully",
    "data":  [
                 {
                     "id":  1,
                     "patient_id":  1,
                     "doctor_id":  1,
                     "clinic_id":  1,
                     "appointment_id":  1,
                     "diagnosis_summary":  "Test diagnosis",
                     "description":  "Quia nobis et est et. Laudantium commodi est occaecati reprehenderit sunt. Suscipit ab impedit labore praesentium reiciendis. Dolorem architecto quo distinctio aut in aspernatur animi.",
                     "status":  "open",
                     "notes":  "Ducimus ea eos asperiores exercitationem voluptas. Quae dolores delectus consequatur consequatur. Et qui culpa nisi eos.",
                     "patient":  {
                                     "name":  "Joaquin Ondricka",
                                     "phone":  "0900000004"
                                 },
                     "doctor":  {
                                    "name":  "Louie Emard"
                                },
                     "created_at":  "2026-06-21 15:09:46"
                 }
             ]
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Patient not found",
    "data":  null
}
```

<div id="patient-records-index"></div>

**`GET /api/clinic-system/clinic/clinic/patient-records/filtered`**

List filtered patient records. Auth required.

**Query Parameters:**

```
clinic_id=1&search=&status=open&date_from=&date_to=&disease_code=
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Records retrieved successfully",
    "data":  [
                 {
                     "id":  1,
                     "patient_id":  1,
                     "doctor_id":  1,
                     "clinic_id":  1,
                     "appointment_id":  1,
                     "diagnosis_summary":  "Test diagnosis",
                     "description":  "Sit vel dolores minima qui. Et nisi nulla numquam omnis ea vel. Quo sequi quis sit impedit. Ipsum eius itaque deserunt excepturi beatae delectus ut.",
                     "status":  "open",
                     "notes":  "Ut fuga ea repellat doloremque omnis placeat. Suscipit id delectus est consectetur sed distinctio amet est. In nemo nisi fugiat et hic fuga. Esse blanditiis possimus aut cupiditate et esse.",
                     "patient":  {
                                     "name":  "Dagmar Braun",
                                     "phone":  "0900000004"
                                 },
                     "doctor":  {
                                    "name":  "Mara Keebler"
                                },
                     "created_at":  "2026-06-21 14:20:57"
                 }
             ],
    "pagination":  {
                       "total":  1,
                       "count":  1,
                       "per_page":  15,
                       "current_page":  1,
                       "last_page":  1,
                       "first_page_url":  "http://localhost/api/clinic-system/clinic/clinic/patient-records/filtered?page=1",
                       "last_page_url":  "http://localhost/api/clinic-system/clinic/clinic/patient-records/filtered?page=1",
                       "next_page_url":  null,
                       "prev_page_url":  null
                   }
}
```

<div id="patient-records-show"></div>

**`GET /api/clinic-system/clinic/clinic/patient-records/show/{id}`**

Show patient record. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "the record found",
    "data":  {
                 "id":  1,
                 "patient_id":  1,
                 "doctor_id":  1,
                 "clinic_id":  1,
                 "appointment_id":  1,
                 "diagnosis_summary":  "Test diagnosis",
                 "description":  "Nulla suscipit nam dolorum alias at magnam. Eum reprehenderit voluptas consequuntur molestiae ab ut iure. Quaerat dolores alias aperiam et eaque aut id.",
                 "status":  "open",
                 "notes":  "A et eveniet quia odio rerum natus. Tempore tempore id impedit sit.",
                 "patient":  {
                                 "name":  "Everett Kunde",
                                 "phone":  "0900000004"
                             },
                 "doctor":  {
                                "name":  "Tabitha Grimes"
                            },
                 "diseases":  [

                              ],
                 "prescriptions":  [

                                   ],
                 "created_at":  "2026-06-21 15:09:36"
             }
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "no record found",
    "data":  null
}
```

<div id="patient-records-get-by-room"></div>

**`POST /api/clinic-system/clinic/clinic/patient-records/rooms/search`**

Search records by rooms. Auth required.

**Request Body:**

```json
{
    "room_ids": [1]                 // required, array, min:1, each exists:rooms
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Records retrieved successfully",
    "data":  [
                 {
                     "id":  1,
                     "patient_id":  1,
                     "doctor_id":  1,
                     "clinic_id":  1,
                     "appointment_id":  1,
                     "diagnosis_summary":  "Test diagnosis",
                     "description":  "Tenetur veritatis non dolor sint. Suscipit nobis ab maxime sequi quia autem. Cumque adipisci consequuntur laudantium voluptatem in voluptas enim aut.",
                     "status":  "open",
                     "notes":  "Earum distinctio omnis ut accusamus laboriosam. Temporibus molestiae quia in aspernatur. Reprehenderit nam deleniti eum dolores quia non. Illum molestiae quaerat itaque nulla quis saepe. Optio et numquam excepturi aperiam dolores libero qui.",
                     "patient":  {
                                     "name":  "Patricia Bechtelar",
                                     "phone":  "0900000004"
                                 },
                     "doctor":  {
                                    "name":  "Hilario Jones"
                                },
                     "created_at":  "2026-06-21 15:09:57"
                 }
             ]
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The room ids field is required.",
    "errors":  {
                   "room_ids":  [
                                    "The room ids field is required."
                                ]
               }
}
```

<div id="patient-records-store"></div>

**`POST /api/clinic-system/clinic/clinic/patient-records`**

Create patient record. Auth required.

**Request Body:**

```json
{
    "patient_id": 1,                // required, exists:patient_infos
    "doctor_id": 1,                 // required, exists:doctors
    "clinic_id": 1,                 // required, exists:clinics
    "appointment_id": 1,            // required, exists:appointments
    "diagnosis_summary": "hypertension", // required, max:1000
    "description": null,            // optional, max:1000
    "status": "open",               // optional, in:open,closed,follow-up
    "notes": null,                  // optional, max:2000
    "diseases": [{                  // optional, array
        "id": 1,                    // required_without:code, exists:diseases
        "code": null,               // required_without:id
        "en_name": "Diabetes",       // required_without:id
        "ar_name": "...",           // required_without:id
        "disease_nature": "chronic", // required_without:id
        "description": null,        // optional
        "status": "active",         // optional, in:active,resolved,chronic
        "severity": "moderate"      // optional, in:mild,moderate,severe
    }],
    "prescription_items": [{        // optional, array
        "id": null,                 // required_without:api_medicine_id
        "api_medicine_id": null,    // required_without:id
        "en_name": "Paracetamol",   // required_without:id
        "ar_name": "...",           // required_without:id
        "generic_name_en": null,    // required_without:id
        "generic_name_ar": null,    // required_without:id
        "form": "tablet",           // required_without:id
        "strength": "500mg",        // required_without:id
        "dosage_instruction": null, // optional
        "frequency": null,          // optional
        "duration": null            // optional
    }]
}
```

**Response (201) - Success:**

```json
{
    "success":  true,
    "message":  "Record created successfully",
    "data":  null
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (422) - Error: validation:**

```json
{
    "message":  "The patient id field is required. (and 4 more errors)",
    "errors":  {
                   "patient_id":  [
                                      "The patient id field is required."
                                  ],
                   "doctor_id":  [
                                     "The doctor id field is required."
                                 ],
                   "clinic_id":  [
                                     "The clinic id field is required."
                                 ],
                   "appointment_id":  [
                                          "The appointment id field is required."
                                      ],
                   "diagnosis_summary":  [
                                             "Diagnosis summary is required."
                                         ]
               }
}
```

<div id="patient-records-update"></div>

**`PUT /api/clinic-system/clinic/clinic/patient-records/{id}`**

Update patient record. Auth required.

**Request Body:**

```json
{
    "diagnosis_summary": "Updated", // optional, max:1000
    "description": null,            // optional, max:1000
    "status": "follow-up",          // optional, in:open,closed,follow-up
    "notes": null,                  // optional, max:2000
    "diseases": [{ ... }],          // optional (same structure as store)
    "preid": null,                  // required_with:prescription_items, exists:prescriptions
    "prescription_items": [{ ... }] // optional (same structure as store)
}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Record updated successfully",
    "data":  {
                 "id":  1,
                 "patient_id":  1,
                 "doctor_id":  1,
                 "clinic_id":  1,
                 "appointment_id":  1,
                 "diagnosis_summary":  "Updated diagnosis summary",
                 "description":  "Updated description",
                 "status":  "follow-up",
                 "notes":  "Updated notes",
                 "patient":  {
                                 "name":  "Luz Hahn",
                                 "phone":  "0900000004"
                             },
                 "doctor":  {
                                "name":  "Leonel Stehr"
                            },
                 "diseases":  [

                              ],
                 "prescriptions":  [

                                   ],
                 "created_at":  "2026-06-21 15:09:39"
             }
}
```

<div id="patient-records-destroy"></div>

**`DELETE /api/clinic-system/clinic/clinic/patient-records/{id}`**

Delete patient record. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Record deleted successfully",
    "data":  null
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Patient record not found",
    "data":  null
}
```

---

_Generated from test fixtures in `tests/Fixtures/api-responses/`. 143 responses across 88 endpoints._
