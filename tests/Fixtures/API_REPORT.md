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

---

## Auth

Public authentication endpoints.

**`POST /api/clinic-system/refresh-token`**

Refresh authentication token. Auth required.

**Request Body:**

```json
{"refresh_token":"..."}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Token refreshed successfully",
    "data":  {
                 "auth_token":  "5|5qNwWGDq8SiQvI092xccyhnyD7lpEE6tJWRtPVb49deb4052"
             }
}
```

**Response (401) - Error: unauthenticated:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

**`POST /api/clinic-system/reset-password`**

Reset password (authenticated). Auth required.

**Request Body:**

```json
{"current_password":"password","new_password":"newpass123"}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "the password is reset",
    "data":  null
}
```

**Response (500) - code-error-invalid:**

```json
{
    "success":  false,
    "message":  "No valid reset code found. Please request a new one.",
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

**`POST /api/clinic-system/reset-password-with-code`**

Reset password using verification code. Public.

**Request Body:**

```json
{"email":"","code":"","password":""}
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

**Response (401) - Error: unauthenticated:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

**`POST /api/clinic-system/login`**

Authenticate user credentials. Public.

**Request Body:**

```json
{"email":"patient@test.com","password":"password"}
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

**`POST /api/clinic-system/register`**

Register a new user. Public.

**Request Body:**

```json
{"email":"new@test.com","password":"password","phone":"0900000005"}
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

**`POST /api/clinic-system/forgot-password`**

Send password reset link. Public.

**Request Body:**

```json
{"email":"patient@test.com"}
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

## Verification

Email verification.

**`POST /api/clinic-system/resend-code`**

Resend verification code. Public.

**Request Body:**

```json
{"email":"..."}
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

**`POST /api/clinic-system/verify-code`**

Verify email verification code. Public.

**Request Body:**

```json
{"email":"...","code":"..."}
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
                     "created_at":  "2026-06-21 12:19:06"
                 },
                 {
                     "id":  9,
                     "ar_name":  "جلسة طويلة",
                     "en_name":  "Long Session",
                     "types":  "3",
                     "created_at":  "2026-06-21 12:19:06"
                 },
                 {
                     "id":  8,
                     "ar_name":  "جلسة متوسطة",
                     "en_name":  "Medium Session",
                     "types":  "2",
                     "created_at":  "2026-06-21 12:19:06"
                 },
                 {
                     "id":  7,
                     "ar_name":  "جلسة قصيرة",
                     "en_name":  "Short Session",
                     "types":  "1",
                     "created_at":  "2026-06-21 12:19:06"
                 },
                 {
                     "id":  6,
                     "ar_name":  "فحص",
                     "en_name":  "Examination",
                     "types":  "1",
                     "created_at":  "2026-06-21 12:19:06"
                 },
                 {
                     "id":  5,
                     "ar_name":  "طوارئ",
                     "en_name":  "Emergency",
                     "types":  "1",
                     "created_at":  "2026-06-21 12:19:06"
                 },
                 {
                     "id":  4,
                     "ar_name":  "متابعة 3",
                     "en_name":  "Follow Up 3",
                     "types":  "3",
                     "created_at":  "2026-06-21 12:19:06"
                 },
                 {
                     "id":  3,
                     "ar_name":  "متابعة 2",
                     "en_name":  "Follow Up 2",
                     "types":  "2",
                     "created_at":  "2026-06-21 12:19:06"
                 },
                 {
                     "id":  2,
                     "ar_name":  "متابعة 1",
                     "en_name":  "Follow Up 1",
                     "types":  "1",
                     "created_at":  "2026-06-21 12:19:06"
                 },
                 {
                     "id":  1,
                     "ar_name":  "استشارة عامة",
                     "en_name":  "General Consultation",
                     "types":  "1",
                     "created_at":  "2026-06-21 12:19:06"
                 }
             ]
}
```

**`POST /api/clinic-system/appointment-types`**

Create a new appointment type. Public.

**Request Body:**

```json
{"types":1,"ar_name":"...","en_name":"..."}
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

**`POST /api/clinic-system/devices/register-token`**

Register FCM device token. Auth required.

**Request Body:**

```json
{"fcm_token":"..."}
```

**Response (200) - Success:**

```json
{
    "message":  "Not implemented yet."
}
```

**Response (401) - Error: unauthenticated:**

```json
{
    "message":  "Unauthenticated."
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

**`POST /api/clinic-system/clinic/specialty/changePrimary/{specialtyId}`**

Change primary specialty. Auth required.

**Request Body:**

```json
{"doctor_id":1}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Primary specialty updated successfully",
    "data":  null
}
```

**`POST /api/clinic-system/clinic/specialty/add`**

Attach specialties to doctor. Auth required.

**Request Body:**

```json
{"doctor_id":1,"specialties":[1,2]}
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
                                    "name":  "Hortense Fisher"
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
                                "name":  "Patricia Kutch"
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

**`POST /api/clinic-system/clinic/schedule/add`**

Create work hour entry. Auth required.

**Request Body:**

```json
{"doctor_id":1,"day_of_week":0,"start_time":"09:00","end_time":"17:00"}
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
                                "name":  "Kory Gerhold"
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

**`PUT /api/clinic-system/clinic/schedule/edit`**

Update work hour. Auth required.

**Request Body:**

```json
{"doctor_id":1,"day_of_week":0,"start_time":"09:00","end_time":"18:00"}
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
                                "name":  "Maegan Wiza"
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

## Medicines

Medicine search and creation.

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
                     "generic_name_en":  "nulla",
                     "strength":  "500mg",
                     "form":  "tablet",
                     "created_at":  "2026-06-21T11:48:41.000000Z",
                     "updated_at":  "2026-06-21T11:48:41.000000Z",
                     "api_medicine_id":  null,
                     "is_custom":  1
                 },
                 {
                     "api_medicine_id":  "83027-0122_37909445-becc-47ba-9ed4-008f7027b8f0",
                     "en_name":  "B Complex Liquescence",
                     "ar_name":  null,
                     "generic_name_en":  "Cholinum (Choline), Inositol (Vitamin B8), Folic Acid (Vitamin B9), Pantothenic Acid (Calcium Pantothenate, Vitamin B5), Thiaminum Hydrochloricum (Vitamin B1), Heart (Bovine), Hepar (Bovine), Methylcobalamin (Vitamin B12), Nicotinamidum (Vitamin B3), PABA (Para Aminobenzoic Acid Vitamin B10), Pyridoxinum Hydrochloricum (Vitamin B6), Biotin (Vitamin B7), Hordeum Vulgare, Riboflavinum (Vitamin B2), Torula Cerevisiae",
                     "generic_name_ar":  null,
                     "strength":  "6 [hp_X]/mL",
                     "form":  "tablet"
                 },
                 {
                     "api_medicine_id":  "64616-102_462ee395-047c-8300-e063-6294a90a251d",
                     "en_name":  "Para-BN",
                     "ar_name":  null,
                     "generic_name_en":  "Parathyroid Booster",
                     "generic_name_ar":  null,
                     "strength":  "7 [hp_X]/mL",
                     "form":  "tablet"
                 },
                 {
                     "api_medicine_id":  "55714-2594_27d4f0eb-04b6-cf82-e063-6394a90a897e",
                     "en_name":  "Para-Si",
                     "ar_name":  null,
                     "generic_name_en":  "Juglans reg, Abrotanum, Aesculus hipp, Allium sat, Arsenicum alb, Artemisia, Baptisia, Cina, Cuprum met, Filix mas, Granatum, Ipecac, Lacheis, Lycopodium, Merc viv, Naphthalinum, Nat mur, Nux vom, Pulsatilla, Ratanhia, Ruta, Sabadilla, Santoninum, Silicea, Spigelia anth, Terebinthina, Teucrium mar, Thymolum, Zingiber",
                     "generic_name_ar":  null,
                     "strength":  "15 [hp_X]/g",
                     "form":  "tablet"
                 },
                 {
                     "api_medicine_id":  "55714-2593_27d41614-3a78-e814-e063-6394a90a94bc",
                     "en_name":  "Para-Si",
                     "ar_name":  null,
                     "generic_name_en":  "Juglans reg, Abrotanum, Aesculus hipp, Allium sat, Arsenicum alb, Artemisia, Baptisia, Cina, Cuprum met, Filix mas, Granatum, Ipecac, Lachesis, Lycopodium, Merc viv, Naphthalinum, Nat mur, Nux vom, Pulsatilla, Ratanhia, Ruta, Sabadilla, Santoninum, Silicea, Spigelia anth, Terebinthina, Teucrium mar, Thymolum, Zingiber",
                     "generic_name_ar":  null,
                     "strength":  "15 [hp_X]/mL",
                     "form":  "tablet"
                 },
                 {
                     "api_medicine_id":  "0942-9603_71e5dcd0-4aa3-4cb6-89b3-8229f73c75fc",
                     "en_name":  "Solucion InterSol",
                     "ar_name":  null,
                     "generic_name_en":  "Solucion aditiva para plaquetas 3",
                     "generic_name_ar":  null,
                     "strength":  "442 mg/100mL",
                     "form":  "injection"
                 },
                 {
                     "api_medicine_id":  "84219-003_15078c5d-2c0c-7e71-e063-6394a90ad193",
                     "en_name":  "Insect Sting Relief Pad TOALLITAS PARA PICADURAS DE INSECTOS",
                     "ar_name":  null,
                     "generic_name_en":  "BENZOCAINE, ALCOHOL",
                     "generic_name_ar":  null,
                     "strength":  "60 g/100g",
                     "form":  "ointment"
                 },
                 {
                     "api_medicine_id":  "69710-115_40e5d3f5-5380-5a99-e063-6294a90aaa24",
                     "en_name":  "SYMBIO PARA DROPS",
                     "ar_name":  null,
                     "generic_name_en":  "Candida parapsilosis",
                     "generic_name_ar":  null,
                     "strength":  "4 [hp_X]/mL",
                     "form":  "tablet"
                 },
                 {
                     "api_medicine_id":  "69710-165_40e5e3bf-76fa-7552-e063-6294a90af768",
                     "en_name":  "SYMBIO PARA/ROQUEFORTI DROPS",
                     "ar_name":  null,
                     "generic_name_en":  "Candida parapsilosis, Penicillium roqueforti",
                     "generic_name_ar":  null,
                     "strength":  "4 [hp_X]/mL",
                     "form":  "tablet"
                 },
                 {
                     "api_medicine_id":  "14141-307_3ce46bfb-8d6d-05f6-e063-6394a90aac2f",
                     "en_name":  "CYZONE CY PLAY CREAMY LIP BALM HIDRATANTE EN BARRA PARA LABIOS 24H FPS 12 PINK CREAMY",
                     "ar_name":  null,
                     "generic_name_en":  "OCTINOXATE, ZINC OXIDE",
                     "generic_name_ar":  null,
                     "strength":  "25 mg/g",
                     "form":  "ointment"
                 },
                 {
                     "api_medicine_id":  "61626-0107_b9492b5a-908a-4f3c-e053-2a95a90a6650",
                     "en_name":  "Para Solve",
                     "ar_name":  null,
                     "generic_name_en":  "Calc carb, Chenopodium anth, Cina, Croton, Filix Mas, Gambocia, Granatum, Lycopodium, Merc corros, Nat phos, Santoninum, Senna, Spigelia anth, Stannum met, Tanacetum, Teuricum mar, Zingiber",
                     "generic_name_ar":  null,
                     "strength":  "12 [hp_X]/59.1mL",
                     "form":  "tablet"
                 },
                 {
                     "api_medicine_id":  "14141-306_3ce46bfb-8d6d-05f6-e063-6394a90aac2f",
                     "en_name":  "CYZONE CY PLAY CREAMY LIP BALM HIDRATANTE EN BARRA PARA LABIOS 24H FPS 12 FUCHSIA CREAMY",
                     "ar_name":  null,
                     "generic_name_en":  "OCTINOXATE, ZINC OXIDE",
                     "generic_name_ar":  null,
                     "strength":  "25 mg/g",
                     "form":  "ointment"
                 },
                 {
                     "api_medicine_id":  "14141-308_3ce46bfb-8d6d-05f6-e063-6394a90aac2f",
                     "en_name":  "CYZONE CY PLAY CREAMY LIP BALM HIDRATANTE EN BARRA PARA LABIOS 24H FPS 12 NUDE CREAMY",
                     "ar_name":  null,
                     "generic_name_en":  "OCTINOXATE, ZINC OXIDE",
                     "generic_name_ar":  null,
                     "strength":  "25 mg/g",
                     "form":  "ointment"
                 },
                 {
                     "api_medicine_id":  "14141-309_3ce46bfb-8d6d-05f6-e063-6394a90aac2f",
                     "en_name":  "CYZONE CY PLAY CREAMY LIP BALM HIDRATANTE EN BARRA PARA LABIOS 24H FPS 12 CORAL CREAMY",
                     "ar_name":  null,
                     "generic_name_en":  "OCTINOXATE, ZINC OXIDE",
                     "generic_name_ar":  null,
                     "strength":  "25 mg/g",
                     "form":  "ointment"
                 },
                 {
                     "api_medicine_id":  "14141-348_3ce46bfb-8d6d-05f6-e063-6394a90aac2f",
                     "en_name":  "CYZONE CY PLAY CREAMY LIP BALM HIDRATANTE EN BARRA PARA LABIOS 24H FPS 12 BERRY CREAMY",
                     "ar_name":  null,
                     "generic_name_en":  "OCTINOXATE, ZINC OXIDE",
                     "generic_name_ar":  null,
                     "strength":  "25 mg/g",
                     "form":  "ointment"
                 },
                 {
                     "api_medicine_id":  "55714-4859_27d587ec-b67e-ea05-e063-6394a90a9aac",
                     "en_name":  "Para-Si",
                     "ar_name":  null,
                     "generic_name_en":  "Juglans reg, Abrotanum, Aesculus hipp, Allium sat, Arsenicum alb, Artemisia, Baptisia, Cina, Cuprum met, Filix mas, Granatum, Ipecac, Lachesis, Lycopodium, Merc viv, Naphthalinum, Nat mur Nux vom, Pulsatilla, Ratanhia, Ruta, Sabadilla, Santoninum, Silicea, Spigelia anth, Terebinthina, Teucrium mar, Thymolum, Zingiber",
                     "generic_name_ar":  null,
                     "strength":  "20 [hp_X]/mL",
                     "form":  "tablet"
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

**`POST /api/clinic-system/clinic/medicines/store`**

Create custom medicine. Auth required.

**Request Body:**

```json
{"ar_name":"...","en_name":"Paracetamol","form":"tablet","strength":"500mg","is_custom":true}
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
                 "created_at":  "2026-06-21T11:48:46+00:00"
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
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Exceptions\\Handler.php",
                      "line":  584,
                      "function":  "prepareException",
                      "class":  "Illuminate\\Foundation\\Exceptions\\Handler",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Pipeline.php",
                      "line":  51,
                      "function":  "render",
                      "class":  "Illuminate\\Foundation\\Exceptions\\Handler",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  172,
                      "function":  "handleException",
                      "class":  "Illuminate\\Routing\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Middleware\\SubstituteBindings.php",
                      "line":  51,
                      "function":  "{closure:Illuminate\\Pipeline\\Pipeline::prepareDestination():168}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Routing\\Middleware\\SubstituteBindings",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Auth\\Middleware\\Authenticate.php",
                      "line":  64,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Auth\\Middleware\\Authenticate",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  127,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  807,
                      "function":  "then",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  786,
                      "function":  "runRouteWithinStack",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  750,
                      "function":  "runRoute",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  739,
                      "function":  "dispatchToRoute",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php",
                      "line":  201,
                      "function":  "dispatch",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  170,
                      "function":  "{closure:Illuminate\\Foundation\\Http\\Kernel::dispatchToRouter():198}",
                      "class":  "Illuminate\\Foundation\\Http\\Kernel",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest.php",
                      "line":  21,
                      "function":  "{closure:Illuminate\\Pipeline\\Pipeline::prepareDestination():168}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull.php",
                      "line":  31,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest.php",
                      "line":  21,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TrimStrings.php",
                      "line":  51,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\TrimStrings",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\ValidatePostSize.php",
                      "line":  27,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Http\\Middleware\\ValidatePostSize",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance.php",
                      "line":  110,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\HandleCors.php",
                      "line":  62,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Http\\Middleware\\HandleCors",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\TrustProxies.php",
                      "line":  58,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Http\\Middleware\\TrustProxies",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks.php",
                      "line":  22,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  127,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php",
                      "line":  176,
                      "function":  "then",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php",
                      "line":  145,
                      "function":  "sendRequestThroughRouter",
                      "class":  "Illuminate\\Foundation\\Http\\Kernel",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php",
                      "line":  607,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Kernel",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php",
                      "line":  573,
                      "function":  "call",
                      "class":  "Illuminate\\Foundation\\Testing\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php",
                      "line":  411,
                      "function":  "json",
                      "class":  "Illuminate\\Foundation\\Testing\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\tests\\Feature\\Api\\MedicineTest.php",
                      "line":  66,
                      "function":  "postJson",
                      "class":  "Illuminate\\Foundation\\Testing\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php",
                      "line":  1548,
                      "function":  "test_store_unauthorized",
                      "class":  "Tests\\Feature\\Api\\MedicineTest",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php",
                      "line":  686,
                      "function":  "runTest",
                      "class":  "PHPUnit\\Framework\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestRunner.php",
                      "line":  106,
                      "function":  "runBare",
                      "class":  "PHPUnit\\Framework\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php",
                      "line":  516,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestRunner",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php",
                      "line":  374,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php",
                      "line":  374,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestSuite",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\TextUI\\TestRunner.php",
                      "line":  64,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestSuite",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\TextUI\\Application.php",
                      "line":  204,
                      "function":  "run",
                      "class":  "PHPUnit\\TextUI\\TestRunner",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\phpunit",
                      "line":  104,
                      "function":  "run",
                      "class":  "PHPUnit\\TextUI\\Application",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\bin\\phpunit",
                      "line":  122,
                      "function":  "include"
                  }
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
                     "code":  "VDK131",
                     "ar_name":  "السكري",
                     "en_name":  "Diabetes",
                     "description":  "Aut aut cumque animi voluptatem hic sit.",
                     "disease_nature":  "chronic",
                     "created_at":  "2026-06-21T12:20:05.000000Z",
                     "updated_at":  "2026-06-21T12:20:05.000000Z",
                     "is_custom":  1
                 },
                 {
                     "code":  "D61.02",
                     "en_name":  "Shwachman-Diamond syndrome",
                     "ar_name":  null,
                     "disease_nature":  "other",
                     "description":  "ICD-10 International Classification"
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

**`POST /api/clinic-system/clinic/diseases/store`**

Create custom disease. Auth required.

**Request Body:**

```json
{"ar_name":"...","en_name":"Diabetes","disease_nature":"chronic","is_custom":true}
```

**Response (201) - Success:**

```json
{
    "success":  true,
    "message":  "Disease processed successfully.",
    "data":  {
                 "id":  2,
                 "icd10_code":  null,
                 "arabic_name":  "ضغط الدم",
                 "english_name":  "Hypertension",
                 "description":  null,
                 "nature":  "chronic",
                 "is_custom":  true,
                 "created_at":  "2026-06-21T12:20:11+00:00"
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
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Exceptions\\Handler.php",
                      "line":  584,
                      "function":  "prepareException",
                      "class":  "Illuminate\\Foundation\\Exceptions\\Handler",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Pipeline.php",
                      "line":  51,
                      "function":  "render",
                      "class":  "Illuminate\\Foundation\\Exceptions\\Handler",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  172,
                      "function":  "handleException",
                      "class":  "Illuminate\\Routing\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Middleware\\SubstituteBindings.php",
                      "line":  51,
                      "function":  "{closure:Illuminate\\Pipeline\\Pipeline::prepareDestination():168}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Routing\\Middleware\\SubstituteBindings",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Auth\\Middleware\\Authenticate.php",
                      "line":  64,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Auth\\Middleware\\Authenticate",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  127,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  807,
                      "function":  "then",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  786,
                      "function":  "runRouteWithinStack",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  750,
                      "function":  "runRoute",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  739,
                      "function":  "dispatchToRoute",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php",
                      "line":  201,
                      "function":  "dispatch",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  170,
                      "function":  "{closure:Illuminate\\Foundation\\Http\\Kernel::dispatchToRouter():198}",
                      "class":  "Illuminate\\Foundation\\Http\\Kernel",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest.php",
                      "line":  21,
                      "function":  "{closure:Illuminate\\Pipeline\\Pipeline::prepareDestination():168}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull.php",
                      "line":  31,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest.php",
                      "line":  21,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TrimStrings.php",
                      "line":  51,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\TrimStrings",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\ValidatePostSize.php",
                      "line":  27,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Http\\Middleware\\ValidatePostSize",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance.php",
                      "line":  110,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\HandleCors.php",
                      "line":  62,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Http\\Middleware\\HandleCors",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\TrustProxies.php",
                      "line":  58,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Http\\Middleware\\TrustProxies",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks.php",
                      "line":  22,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  127,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php",
                      "line":  176,
                      "function":  "then",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php",
                      "line":  145,
                      "function":  "sendRequestThroughRouter",
                      "class":  "Illuminate\\Foundation\\Http\\Kernel",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php",
                      "line":  607,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Kernel",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php",
                      "line":  573,
                      "function":  "call",
                      "class":  "Illuminate\\Foundation\\Testing\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php",
                      "line":  411,
                      "function":  "json",
                      "class":  "Illuminate\\Foundation\\Testing\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\tests\\Feature\\Api\\DiseaseTest.php",
                      "line":  67,
                      "function":  "postJson",
                      "class":  "Illuminate\\Foundation\\Testing\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php",
                      "line":  1548,
                      "function":  "test_store_unauthorized",
                      "class":  "Tests\\Feature\\Api\\DiseaseTest",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php",
                      "line":  686,
                      "function":  "runTest",
                      "class":  "PHPUnit\\Framework\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestRunner.php",
                      "line":  106,
                      "function":  "runBare",
                      "class":  "PHPUnit\\Framework\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php",
                      "line":  516,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestRunner",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php",
                      "line":  374,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php",
                      "line":  374,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestSuite",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php",
                      "line":  374,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestSuite",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\TextUI\\TestRunner.php",
                      "line":  64,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestSuite",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\TextUI\\Application.php",
                      "line":  204,
                      "function":  "run",
                      "class":  "PHPUnit\\TextUI\\TestRunner",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\phpunit",
                      "line":  104,
                      "function":  "run",
                      "class":  "PHPUnit\\TextUI\\Application",
                      "type":  "-\u003e"
                  }
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

**`POST /api/clinic-system/clinic/clinic/secretary/register`**

Create secretary (owner only). Auth required.

**Request Body:**

```json
{"user_id":1,"clinic_id":1}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Secretary created successfully.",
    "data":  null
}
```

**Response (403) - Error: permission:**

```json
{
    "message":  "This action is unauthorized.",
    "exception":  "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException",
    "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Exceptions\\Handler.php",
    "line":  640,
    "trace":  [
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Exceptions\\Handler.php",
                      "line":  584,
                      "function":  "prepareException",
                      "class":  "Illuminate\\Foundation\\Exceptions\\Handler",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Pipeline.php",
                      "line":  51,
                      "function":  "render",
                      "class":  "Illuminate\\Foundation\\Exceptions\\Handler",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  172,
                      "function":  "handleException",
                      "class":  "Illuminate\\Routing\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Middleware\\SubstituteBindings.php",
                      "line":  51,
                      "function":  "{closure:Illuminate\\Pipeline\\Pipeline::prepareDestination():168}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Routing\\Middleware\\SubstituteBindings",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Auth\\Middleware\\Authenticate.php",
                      "line":  64,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Auth\\Middleware\\Authenticate",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  127,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  807,
                      "function":  "then",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  786,
                      "function":  "runRouteWithinStack",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  750,
                      "function":  "runRoute",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php",
                      "line":  739,
                      "function":  "dispatchToRoute",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php",
                      "line":  201,
                      "function":  "dispatch",
                      "class":  "Illuminate\\Routing\\Router",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  170,
                      "function":  "{closure:Illuminate\\Foundation\\Http\\Kernel::dispatchToRouter():198}",
                      "class":  "Illuminate\\Foundation\\Http\\Kernel",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest.php",
                      "line":  21,
                      "function":  "{closure:Illuminate\\Pipeline\\Pipeline::prepareDestination():168}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull.php",
                      "line":  31,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest.php",
                      "line":  21,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TrimStrings.php",
                      "line":  51,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\TrimStrings",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\ValidatePostSize.php",
                      "line":  27,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Http\\Middleware\\ValidatePostSize",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance.php",
                      "line":  110,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\HandleCors.php",
                      "line":  62,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Http\\Middleware\\HandleCors",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\TrustProxies.php",
                      "line":  58,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Http\\Middleware\\TrustProxies",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks.php",
                      "line":  22,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  209,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php",
                      "line":  127,
                      "function":  "{closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():184}:185}",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php",
                      "line":  176,
                      "function":  "then",
                      "class":  "Illuminate\\Pipeline\\Pipeline",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php",
                      "line":  145,
                      "function":  "sendRequestThroughRouter",
                      "class":  "Illuminate\\Foundation\\Http\\Kernel",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php",
                      "line":  607,
                      "function":  "handle",
                      "class":  "Illuminate\\Foundation\\Http\\Kernel",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php",
                      "line":  573,
                      "function":  "call",
                      "class":  "Illuminate\\Foundation\\Testing\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php",
                      "line":  411,
                      "function":  "json",
                      "class":  "Illuminate\\Foundation\\Testing\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\tests\\Feature\\Api\\ClinicTest.php",
                      "line":  103,
                      "function":  "postJson",
                      "class":  "Illuminate\\Foundation\\Testing\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php",
                      "line":  1548,
                      "function":  "test_create_secretary_fails_not_owner",
                      "class":  "Tests\\Feature\\Api\\ClinicTest",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php",
                      "line":  686,
                      "function":  "runTest",
                      "class":  "PHPUnit\\Framework\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestRunner.php",
                      "line":  106,
                      "function":  "runBare",
                      "class":  "PHPUnit\\Framework\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php",
                      "line":  516,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestRunner",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php",
                      "line":  374,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestCase",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php",
                      "line":  374,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestSuite",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\TextUI\\TestRunner.php",
                      "line":  64,
                      "function":  "run",
                      "class":  "PHPUnit\\Framework\\TestSuite",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\src\\TextUI\\Application.php",
                      "line":  204,
                      "function":  "run",
                      "class":  "PHPUnit\\TextUI\\TestRunner",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\phpunit\\phpunit\\phpunit",
                      "line":  104,
                      "function":  "run",
                      "class":  "PHPUnit\\TextUI\\Application",
                      "type":  "-\u003e"
                  },
                  {
                      "file":  "C:\\Files\\code\\laravelEX\\clinic-system\\vendor\\bin\\phpunit",
                      "line":  122,
                      "function":  "include"
                  }
              ]
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
                   "phone":  [
                                 "Phone number is required."
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

**`POST /api/clinic-system/clinic/clinic/doctor/register`**

Create doctor (owner only). Auth required.

**Request Body:**

```json
{"user_id":1,"clinic_id":1}
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
    "message":  "First name is required. (and 10 more errors)",
    "errors":  {
                   "fname":  [
                                 "First name is required."
                             ],
                   "lname":  [
                                 "Last name is required."
                             ],
                   "phone":  [
                                 "Phone number must be exactly 10 digits.",
                                 "Phone number must start with 09."
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
                                         "name":  "Torey Eichmann"
                                     }
                                 ],
                     "secretaries":  [
                                         {
                                             "id":  1,
                                             "name":  "Shakira Lemke"
                                         }
                                     ]
                 }
             ]
}
```

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
                                         "name":  "Haley Rice"
                                     }
                                 ],
                     "secretaries":  [
                                         {
                                             "id":  1,
                                             "name":  "Walter Bogisich"
                                         }
                                     ]
                 }
             ]
}
```

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Room not found",
    "data":  null
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

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
                                         "name":  "Haley Rice"
                                     }
                                 ],
                     "secretaries":  [
                                         {
                                             "id":  1,
                                             "name":  "Walter Bogisich"
                                         }
                                     ]
                 }
             ]
}
```

**`POST /api/clinic-system/clinic/clinic/rooms/sync/doctorRoom`**

Add doctor to room. Auth required.

**Request Body:**

```json
{"room_id":1,"doctor_id":1}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "The doctor changes the room successfuly",
    "data":  null
}
```

**`POST /api/clinic-system/clinic/clinic/rooms/sync/secRooms`**

Add secretary to room. Auth required.

**Request Body:**

```json
{"room_id":1,"secretary_id":1}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "The secretary changes the room successfuly",
    "data":  null
}
```

**`DELETE /api/clinic-system/clinic/clinic/rooms/detach/secRooms`**

Remove secretary from room. Auth required.

**Request Body:**

```json
{"room_id":1,"secretary_id":1}
```

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "The secretary detach the room successfuly",
    "data":  null
}
```

**`DELETE /api/clinic-system/clinic/clinic/rooms/detach/doctorRoom`**

Remove doctor from room. Auth required.

**Request Body:**

```json
{"room_id":1,"doctor_id":1}
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
                 "name":  "Kayley Rutherford",
                 "phone":  "0900000003",
                 "dob":  "1968-08-22",
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

**`POST /api/clinic-system/clinic/clinic/secretaries/update`**

Update secretary info. Auth required.

**Request Body:**

```json
{"secretary_id":1,"fname":"...","lname":"..."}
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

**`GET /api/clinic-system/clinic/clinic/patients/{patientId}/medical-history`**

Get patient medical history. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Medical history retrieved successfully.",
    "data":  {
                 "id":  1,
                 "name":  "Jeanne Rutherford",
                 "phone":  "0900000004",
                 "email":  "patient@test.com",
                 "gender":  "male",
                 "dob":  "1966-01-30",
                 "profile_image":  null,
                 "clinic_id":  1,
                 "appointments":  [
                                      {
                                          "id":  1,
                                          "doctor_name":  "Florian Medhurst",
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
                                     "doctor_name":  "Florian Medhurst",
                                     "diagnosis_summary":  "Test diagnosis",
                                     "description":  "Odio aut tempora velit nisi molestiae consequatur. Et nostrum ipsa est iure qui. Numquam ex dolorem et laboriosam.",
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
                 "name":  "Jamal Grady",
                 "gender":  "unknown",
                 "profile_image":  null,
                 "clinic_id":  1,
                 "nationality":  "Central African Republic",
                 "address":  "3518 Schaden Spur\nNorth Genoveva, MD 81071-4722",
                 "marital_status":  "other",
                 "emergency_phone":  "0992410433",
                 "allergies":  null,
                 "chronic_conditions":  "Natus eum ratione illum repellendus voluptatem autem commodi.",
                 "career":  "Press Machine Setter, Operator",
                 "blood_type":  "B-",
                 "phone":  "0900000004",
                 "email":  "patient@test.com",
                 "dob":  "1994-06-19",
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
                     "name":  "Caleb Torphy",
                     "gender":  "male",
                     "profile_image":  null,
                     "clinic_id":  1
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

**`GET /api/clinic-system/clinic/clinic/patients/trashed`**

List soft-deleted patients. Auth required.

**Query Parameters:**

```
clinic_id=1
```

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

**`POST /api/clinic-system/clinic/clinic/patients/update`**

Update patient info. Auth required.

**Request Body:**

```json
{"patient_id":1,"fname":"Updated","blood_type":"A+"}
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

**`DELETE /api/clinic-system/clinic/clinic/patients/delete`**

Soft-delete patient (patient_id must be string). Auth required.

**Request Body:**

```json
{"patient_id":"1"}
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

**`GET /api/clinic-system/clinic/clinic/users/info`**

Get authenticated user info. Auth required.

**Response (200) - Success:**

```json
{
    "success":  true,
    "message":  "Success",
    "data":  {
                 "id":  5,
                 "name":  "Mckayla Grant",
                 "phone":  "0900000004",
                 "email":  "patient@test.com",
                 "gender":  "female",
                 "dob":  "1979-09-19",
                 "profile_image":  null,
                 "created":  "2026-06-21",
                 "role":  "patient",
                 "clinic_id":  1,
                 "nationality":  "Austria",
                 "address":  "70155 Kilback Glen\nPort Stonebury, MN 60563",
                 "marital_status":  "other",
                 "emergency_phone":  "0991668315",
                 "allergies":  null,
                 "chronic_conditions":  null,
                 "career":  "Laundry OR Dry-Cleaning Worker",
                 "blood_type":  "AB-"
             }
}
```

**Response (401) - Error: unauthenticated:**

```json
{
    "message":  "Unauthenticated."
}
```

**Response (401) - Error: unauthorized:**

```json
{
    "message":  "Unauthenticated."
}
```

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
                 "name":  "Vivienne Gerlach",
                 "phone":  "0900000002",
                 "email":  "doctor@test.com",
                 "dob":  "1992-09-26 07:35:31",
                 "gender":  "female",
                 "created_at":  "2026-06-21",
                 "appointment_duration":  30,
                 "consultation_fee":  150,
                 "bio":  "Et nesciunt repellendus aut. Incidunt iste tenetur provident. Laboriosam beatae consequatur cum.",
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
                     "name":  "Ardella Erdman",
                     "phone":  "0900000002",
                     "email":  "doctor@test.com",
                     "dob":  "1986-10-02 01:51:57",
                     "gender":  "female",
                     "created_at":  "2026-06-21",
                     "appointment_duration":  30,
                     "consultation_fee":  150,
                     "bio":  "Dolorem omnis eum ea et optio. Deserunt dolorum et blanditiis qui aut quos. Dolores voluptates qui earum est. Fuga quia accusantium expedita officia corporis. Sed ipsa porro praesentium eaque reprehenderit quia.",
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

**Response (403) - Error: permission:**

```json
{
    "success":  false,
    "message":  "Not associated with any clinic."
}
```

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

**`POST /api/clinic-system/clinic/clinic/doctors/update`**

Update doctor info. Auth required.

**Request Body:**

```json
{"doctor_id":1,"consultation_fee":200}
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
                                    "name":  "Alycia Lehner"
                                },
                     "patient":  {
                                     "id":  1,
                                     "name":  "Estella Stamm",
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
                                     "name":  "Erna Hauck",
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
                                    "name":  "Elbert Kohler"
                                },
                     "patient":  {
                                     "id":  1,
                                     "name":  "D\u0027angelo Ryan",
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
                                    "name":  "Amy Predovic"
                                },
                     "patient":  {
                                     "id":  1,
                                     "name":  "Jaunita Bergnaum",
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
                                    "name":  "Hanna Fahey"
                                },
                     "patient":  {
                                     "id":  1,
                                     "name":  "Anderson Schimmel",
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
                                "name":  "Jessyca Kutch"
                            },
                 "patient":  {
                                 "id":  1,
                                 "name":  "Braulio Cartwright",
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
                                    "name":  "Maximillia Barrows"
                                },
                     "patient":  {
                                     "id":  1,
                                     "name":  "Ursula Dicki",
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

**`POST /api/clinic-system/clinic/clinic/appointments/book`**

Book a new appointment. Auth required.

**Request Body:**

```json
{"patient_id":1,"doctor_id":1,"clinic_id":1,"appointment_type_id":1,"start_time":"11:00","date":"2026-06-28","visit_reason":"Routine checkup"}
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
                                "name":  "Kiarra Daniel"
                            },
                 "patient":  {
                                 "id":  1,
                                 "name":  "Dock Purdy",
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

**Response (422) - Error: doctor-invalid:**

```json
{
    "success":  false,
    "message":  "no work hour valid for this date",
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

**`POST /api/clinic-system/clinic/clinic/appointments/{id}/cancel`**

Cancel an appointment. Auth required.

**Request Body:**

```json
{"cancel_reason":"Patient requested"}
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

**`POST /api/clinic-system/clinic/clinic/appointments/{id}/reschedule`**

Reschedule an appointment. Auth required.

**Request Body:**

```json
{"start_time":"14:00","date":"2026-06-28"}
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
                                "name":  "Porter Breitenberg"
                            },
                 "patient":  {
                                 "id":  1,
                                 "name":  "Hermann Boyle",
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

**Response (404) - Error: not-found:**

```json
{
    "success":  false,
    "message":  "Appointment not found",
    "data":  null
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

## Patient Records

Patient medical records (auth required).

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
                     "description":  "Provident laborum laudantium delectus. Iure est hic maiores id perspiciatis. Eligendi suscipit libero odio tenetur. Et ipsum unde corrupti beatae.",
                     "status":  "open",
                     "notes":  "Animi qui modi ullam alias earum. Id hic numquam assumenda aspernatur non ipsa. Nostrum aliquid id corrupti qui aliquam pariatur.",
                     "patient":  {
                                     "name":  "Emile Morar",
                                     "phone":  "0900000004"
                                 },
                     "doctor":  {
                                    "name":  "Daisy Gislason"
                                },
                     "created_at":  "2026-06-21 11:49:12"
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
                     "description":  "Molestias voluptates nobis necessitatibus praesentium iure qui est. Modi necessitatibus voluptas rerum sunt minima. Adipisci pariatur qui voluptas ipsam quaerat rerum.",
                     "status":  "open",
                     "notes":  "Rem ab harum ea qui quas libero enim. In et asperiores similique inventore. Et quia aut voluptates. Officia id reprehenderit omnis ad ut porro.",
                     "patient":  {
                                     "name":  "Sigrid Cummings",
                                     "phone":  "0900000004"
                                 },
                     "doctor":  {
                                    "name":  "Camren Sipes"
                                },
                     "created_at":  "2026-06-21 11:49:06"
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

**`GET /api/clinic-system/clinic/clinic/patient-records/patient/{patientId}/history`**

Get patient's medical history records. Auth required.

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
                     "description":  "Et nam velit corrupti molestias culpa. Nihil beatae et aliquam non labore dignissimos repellat error. Debitis at similique ipsam ducimus voluptatibus.",
                     "status":  "open",
                     "notes":  "Aut delectus nihil nobis odio alias. Maxime impedit vel error saepe omnis id. Velit earum omnis consequatur.",
                     "patient":  {
                                     "name":  "Cristian Zieme",
                                     "phone":  "0900000004"
                                 },
                     "doctor":  {
                                    "name":  "Kristoffer Hane"
                                },
                     "created_at":  "2026-06-21 11:49:03"
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

**`GET /api/clinic-system/clinic/clinic/patient-records/filtered`**

List filtered patient records. Auth required.

**Query Parameters:**

```
clinic_id=1
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
                     "description":  "Blanditiis dolorem libero facilis recusandae et fugiat. Veniam voluptatum totam ut facilis molestiae itaque. Neque aperiam illum aut eaque aliquam inventore. Totam est soluta enim ipsam vero dolorem.",
                     "status":  "open",
                     "notes":  "Minus velit quaerat quibusdam esse. Nulla enim rerum quaerat ut non rerum cum itaque. Et tempore autem quibusdam tempora neque esse.",
                     "patient":  {
                                     "name":  "Justyn Gusikowski",
                                     "phone":  "0900000004"
                                 },
                     "doctor":  {
                                    "name":  "Elinore Hegmann"
                                },
                     "created_at":  "2026-06-21 11:49:02"
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
                 "description":  "Ea modi laborum voluptas ex esse dolorum est quia. Dolorum voluptatem et eum nobis. Perspiciatis aut rem exercitationem repellendus.",
                 "status":  "open",
                 "notes":  "Exercitationem fugit nihil nesciunt optio deleniti quo est aperiam. Accusantium esse eligendi ut quod sint. Dolorem laudantium quaerat earum. Quis et vitae molestias error nihil recusandae maiores.",
                 "patient":  {
                                 "name":  "Chad Hegmann",
                                 "phone":  "0900000004"
                             },
                 "doctor":  {
                                "name":  "Zachery Langosh"
                            },
                 "diseases":  [

                              ],
                 "prescriptions":  [

                                   ],
                 "created_at":  "2026-06-21 11:48:55"
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

**`POST /api/clinic-system/clinic/clinic/patient-records/rooms/search`**

Search records by rooms. Auth required.

**Request Body:**

```json
{"room_ids":[1]}
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
                     "description":  "Officia veniam tempore corrupti itaque. Non dolorum ipsa illo pariatur. Tenetur velit porro voluptatem ipsum dolore culpa.",
                     "status":  "open",
                     "notes":  "Magni odit delectus accusamus fugiat. Provident voluptas dolores accusamus porro est aut dolor.",
                     "patient":  {
                                     "name":  "Adalberto Schneider",
                                     "phone":  "0900000004"
                                 },
                     "doctor":  {
                                    "name":  "Carmen Herzog"
                                },
                     "created_at":  "2026-06-21 11:49:10"
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

**`POST /api/clinic-system/clinic/clinic/patient-records`**

Create patient record. Auth required.

**Request Body:**

```json
{"patient_id":1,"doctor_id":1,"clinic_id":1,"appointment_id":1,"diagnosis_summary":"hypertension","status":"open"}
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

**`PUT /api/clinic-system/clinic/clinic/patient-records/{id}`**

Update patient record. Auth required.

**Request Body:**

```json
{"diagnosis_summary":"Updated","status":"follow-up"}
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
                                 "name":  "Johnson Gibson",
                                 "phone":  "0900000004"
                             },
                 "doctor":  {
                                "name":  "Mossie Weber"
                            },
                 "diseases":  [

                              ],
                 "prescriptions":  [

                                   ],
                 "created_at":  "2026-06-21 11:48:57"
             }
}
```

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

_Generated from test fixtures in `tests/Fixtures/api-responses/`. 148 responses across 77 endpoints._
