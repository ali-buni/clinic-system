# Clinic System API Documentation

**Base URL**: `{{base_url}}/api`  
**Default**: `http://localhost:8000/api`  
**Auth**: Bearer JWT Token (Sanctum)  
**Format**: JSON

---

## Authentication Flow

```
Register → Verify Email → Login → (use token) → Sign Out
                              ↓
                    Forgot Password → Reset with Code
```

1. **Register** a new patient account
2. **Verify** email using the code sent (logged to `storage/logs/laravel.log` when `MAIL_MAILER=log`)
3. **Login** with email/phone + password → get `access_token`
4. Use `Authorization: Bearer {access_token}` for protected endpoints
5. **Refresh token** when expired
6. **Sign out** to revoke current token

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

---

## Standard Response Format

```json
// Success
{ "success": true, "message": "...", "data": { ... } }

// Error
{ "success": false, "message": "..." }

// Validation Error
{ "success": false, "message": "...", "errors": { "field": ["..."] } }

// Paginated
{ "success": true, "message": "...", "data": [...], "pagination": { "total": N, "count": N, "per_page": N, "current_page": N, "last_page": N, ... } }
```

---

## Endpoints

### 1. Authentication (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/clinic-system/register` | Register new patient |
| POST | `/clinic-system/login` | Login (email or phone) |
| POST | `/clinic-system/verify-code` | Verify email/phone code |
| POST | `/clinic-system/resend-code` | Resend verification code |
| POST | `/clinic-system/forgot-password` | Send password reset code |
| POST | `/clinic-system/reset-password-with-code` | Reset password with code |

#### POST `/clinic-system/register`
**Body** (multipart/form-data when including profile_image):
```json
{
  "fname": "Ahmed",
  "lname": "Alaa",
  "email": "ahmed@test.com",
  "password": "password",
  "password_confirmation": "password",
  "phone": "0912345678",
  "gender": "male",
  "dob": "1995-06-15",
  "clinic_id": 1,
  "nationality": "Syrian",
  "address": "Damascus",
  "profile_image": "(file) jpeg,png,jpg,gif,svg max 2MB"
}
```
If no `profile_image` is provided, a default avatar is automatically assigned.

#### POST `/clinic-system/login`
```json
{ "login": "ahmed@test.com", "password": "password" }
```
Response contains `access_token` and `refresh_token`.

#### POST `/clinic-system/verify-code`
```json
{ "login": "ahmed@test.com", "code": "1234", "type": "email" }
```

### 2. Authentication (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/clinic-system/signout` | Revoke current token |
| POST | `/clinic-system/refresh-token` | Get new access token |
| POST | `/clinic-system/reset-password` | Reset password (authenticated) |

---

### 3. Patients (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/clinic-system/clinic/patients` | List patients (paginated) |
| GET | `/clinic-system/clinic/patients/trashed` | List soft-deleted patients |
| GET | `/clinic-system/clinic/patients/{id}/show` | Get patient details |
| GET | `/clinic-system/clinic/patients/{id}/medical-history` | Full medical history |
| POST | `/clinic-system/clinic/patients/update` | Update patient |
| DELETE | `/clinic-system/clinic/patients/delete` | Soft-delete patient |
| GET | `/clinic-system/clinic/patients/restore` | Restore patient |

**Query params for list**: `clinic_id`, `search`, `column`, `sort`, `direction`, `per_page`, `page`

#### POST `/clinic-system/clinic/patients/update`
```json
{ "patient_id": 1, "fname": "Updated", "address": "New Address", "blood_type": "B+" }
```

#### DELETE `/clinic-system/clinic/patients/delete`
```json
{ "patient_id": 1 }
```

---

### 4. Appointments (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/clinic-system/clinic/appointments/book` | Book appointment |
| GET | `/clinic-system/clinic/appointments/{id}` | Show appointment |
| POST | `/clinic-system/clinic/appointments/{id}/cancel` | Cancel appointment |
| POST | `/clinic-system/clinic/appointments/{id}/complete` | Mark completed |
| POST | `/clinic-system/clinic/appointments/{id}/confirmed` | Mark confirmed |
| POST | `/clinic-system/clinic/appointments/{id}/reschedule` | Reschedule |
| GET | `/clinic-system/clinic/appointments/patient/{id}` | Patient appointments |
| GET | `/clinic-system/clinic/appointments/doctor/{id}` | Doctor appointments |
| GET | `/clinic-system/clinic/appointments/clinic-system/clinic/{id}` | Clinic appointments |
| GET | `/clinic-system/clinic/appointments/room/appo` | Room appointments |
| GET | `/clinic-system/clinic/appointments/doctor/{id}/schedule` | Doctor schedule |
| GET | `/clinic-system/clinic/appointments/clinic-system/clinic/{id}/schedule` | Clinic schedule |
| GET | `/clinic-system/clinic/appointments/get/available-slots` | Available time slots |

#### POST `/clinic-system/clinic/appointments/book`
```json
{
  "doctor_id": 1,
  "patient_id": 1,
  "clinic_id": 1,
  "appointment_type_id": 1,
  "date": "2026-06-21",
  "start_time": "09:00",
  "visit_reason": "Checkup"
}
```

#### POST `/clinic-system/clinic/appointments/{id}/cancel`
```json
{ "cancel_reason": "Patient unavailable" }
```

#### POST `/clinic-system/clinic/appointments/{id}/reschedule`
```json
{ "date": "2026-06-22", "start_time": "10:00" }
```

---

### 5. Medical Records (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/clinic-system/clinic/patient-records` | Create record |
| GET | `/clinic-system/clinic/patient-records/show/{id}` | Show record |
| PUT | `/clinic-system/clinic/patient-records/{id}` | Update record |
| DELETE | `/clinic-system/clinic/patient-records/{id}` | Delete record |
| GET | `/clinic-system/clinic/patient-records/filtered` | List records (filtered) |
| GET | `/clinic-system/clinic/patient-records/patient/{id}/history` | Patient history |
| GET | `/clinic-system/clinic/patient-records/patient/{pid}/doctor/{did}` | Records by doctor |
| GET | `/clinic-system/clinic/patient-records/doctor/{id}/all` | All doctor records |
| POST | `/clinic-system/clinic/patient-records/rooms/search` | Records by room |

#### POST `/clinic-system/clinic/patient-records`
```json
{
  "patient_id": 1,
  "doctor_id": 1,
  "clinic_id": 1,
  "appointment_id": 1,
  "diagnosis_summary": "Fever and cough",
  "description": "Patient needs rest and medication",
  "status": "open",
  "diseases": [
    { "id": 1, "status": "active", "severity": "mild" }
  ],
  "prescription_items": [
    { "id": 1, "dosage_instruction": "1 tablet daily", "frequency": "once daily", "duration": "7 days" }
  ]
}
```

---

### 6. Clinic Management (Auth Required, Owner)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/clinic-system/clinic/info` | Get clinic info |
| POST | `/clinic-system/clinic/update/{id}` | Update clinic |
| POST | `/clinic-system/clinic/doctor/register` | Register doctor |
| POST | `/clinic-system/clinic/secretary/register` | Register secretary |

#### POST `/clinic-system/clinic/doctor/register`
```json
{
  "fname": "New",
  "lname": "Doctor",
  "phone": "0911111111",
  "dob": "1985-01-01",
  "gender": "male",
  "clinic_id": 1,
  "room_id": 1,
  "appointment_duration": 30,
  "consultation_fee": 200,
  "specialty_ids": [1]
}
```

---

### 7. Doctors (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/clinic-system/clinic/doctors/filter` | List doctors |
| GET | `/clinic-system/clinic/doctors/{id}/info` | Doctor details |
| POST | `/clinic-system/clinic/doctors/update` | Update doctor |
| DELETE | `/clinic-system/clinic/doctors/{id}/leave` | Soft-delete doctor |
| POST | `/clinic-system/clinic/doctors/{id}/restore` | Restore doctor |
| DELETE | `/clinic-system/clinic/doctors/{id}/force` | Force delete doctor |

---

### 8. Rooms (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/clinic-system/clinic/rooms/{clinicId}` | List rooms |
| GET | `/clinic-system/clinic/rooms/{clinicId}/info` | Rooms with details |
| GET | `/clinic-system/clinic/rooms/{id}/details` | Room details |
| GET | `/clinic-system/clinic/rooms/userRooms/get` | Current user rooms |
| POST | `/clinic-system/clinic/rooms` | Create room |
| POST | `/clinic-system/clinic/rooms/{id}` | Update room |
| DELETE | `/clinic-system/clinic/rooms/{id}` | Delete room |
| POST | `/clinic-system/clinic/rooms/sync/doctorRoom` | Assign doctor to room |
| DELETE | `/clinic-system/clinic/rooms/detach/doctorRoom` | Remove doctor from room |
| POST | `/clinic-system/clinic/rooms/sync/secRooms` | Assign secretary to rooms |
| DELETE | `/clinic-system/clinic/rooms/detach/secRooms` | Remove secretary from rooms |

---

### 9. Secretaries (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/clinic-system/clinic/secretaries/{id}` | Secretary info |
| POST | `/clinic-system/clinic/secretaries/update` | Update secretary |

---

### 10. Schedule & Specialties

#### Doctor Schedule

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/clinic-system/clinic/schedule/add` | Yes | Add work hour |
| PUT | `/clinic-system/clinic/schedule/edit` | Yes | Edit work hour |
| DELETE | `/clinic-system/clinic/schedule/delete/{day}/{docId}` | Yes | Delete work hour |
| GET | `/clinic-system/clinic/schedule/get-weekly/{docId}` | No | Get weekly schedule |
| GET | `/clinic-system/clinic/schedule/work-hour/{docId}` | No | Get work hour by date |

#### Specialties

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/clinic-system/clinic/specialty/index` | No | List all specialties |
| POST | `/clinic-system/clinic/specialty/add` | Yes | Attach specialties |
| DELETE | `/clinic-system/clinic/specialty/delete/{id}` | Yes | Detach specialty |
| POST | `/clinic-system/clinic/specialty/changePrimary/{id}` | Yes | Change primary |
| GET | `/clinic-system/clinic/specialty/showPrimary/{docId}` | Yes | Show primary specialty |
| GET | `/clinic-system/clinic/specialty/getAll` | Yes | Get doctor specialties |

```json
// POST /clinic-system/clinic/schedule/add
{ "doctor_id": 1, "day_of_week": 1, "start_time": "09:00", "end_time": "17:00", "max_patients_per_day": 10 }

// POST /clinic-system/clinic/specialty/add
{ "specialty_ids": [1, 2] }
```

---

### 11. Medicines & Diseases (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/clinic-system/clinic/medicines/search` | Search medicines |
| POST | `/clinic-system/clinic/medicines/store` | Store medicine |
| GET | `/clinic-system/clinic/diseases/search` | Search diseases |
| POST | `/clinic-system/clinic/diseases/store` | Store disease |

```json
// POST /clinic-system/clinic/medicines/store
{ "en_name": "NewMed", "ar_name": "دواء", "generic_name_en": "Generic", "strength": "500mg", "form": "tablet" }

// POST /clinic-system/clinic/diseases/store
{ "en_name": "New Disease", "ar_name": "مرض", "icd_code": "X99.9", "description": "Test", "disease_nature": "chronic" }
```

---

### 12. Appointment Types (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/clinic-system/appointment-types` | List types |
| POST | `/clinic-system/appointment-types` | Create type |
| DELETE | `/clinic-system/appointment-types/{id}` | Delete type |

---

### 13. User (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/clinic-system/clinic/users/info` | Get current user info |
| POST | `/clinic-system/clinic/users/update-image` | Update profile image |
| GET | `/clinic-system/clinic/users/image-url` | Get profile image URL |
| POST | `/clinic-system/devices/register-token` | Register FCM device token |

#### POST `/clinic-system/clinic/users/update-image`
**Body** (multipart/form-data):
```
profile_image: (file) required, jpeg/png/jpg/gif/svg, max 2MB
```
Response returns updated user info with new `profile_image` path.

#### GET `/clinic-system/clinic/users/image-url`
Returns the full URL to the profile image (default or uploaded).

---

## Postman Usage

1. **Import** `docs/postman_collection.json` into Postman (File → Import)
2. **Import** `docs/postman_environment.json` (File → Import → Environment)
3. Select **"Clinic System (Local)"** environment from dropdown
4. Run **Register** → copy verification code from `storage/logs/laravel.log`
5. Run **Verify Email Code** with the code (token auto-saves)
6. Run any authenticated endpoint

---

## Common Issues

| Problem | Solution |
|---------|----------|
| 401 Unauthorized | Token expired → Login again or Refresh Token |
| 422 Validation | Check field names, types, and constraints |
| 500 Server Error | Check `storage/logs/laravel.log` |
| Email code not sent | `MAIL_MAILER=log` → check `storage/logs/laravel.log` |
| Route not found | Check `php artisan route:list` for exact paths |
