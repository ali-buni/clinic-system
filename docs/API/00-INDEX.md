# Clinic System API - Complete Reference

**Base URL:** `/api/clinic-system`
**Auth:** Bearer token via `auth:sanctum`
**Total Endpoints:** 100+ | **Total Test Cases:** 427

---

## Standard Response Envelope

```json
{
  "success": true | false,
  "message": "...",
  "data": { ... } | [ ... ] | null
}
```

## Authentication Header

```
Authorization: Bearer {token}
Accept: application/json
```

---

## Table of Contents

| #   | Section                                          | Test Cases | Folder                                                                    |
| --- | ------------------------------------------------ | ---------- | ------------------------------------------------------------------------- |
| 01  | [Auth](./01-AUTH.md)                             | 24         | [endpoint/auth](./endpoint/auth/README.md)                                |
| 02  | [Verification](./02-VERIFICATION.md)             | 7          | [endpoint/verification](./endpoint/verification/README.md)                |
| 03  | [Appointment Types](./03-APPOINTMENT-TYPES.md)   | 8          | [endpoint/appointment-type](./endpoint/appointment-type/README.md)        |
| 04  | [Devices](./04-DEVICES.md)                       | 4          | [endpoint/device](./endpoint/device/README.md)                            |
| 05  | [Specialties](./05-SPECIALTIES.md)               | 14         | [endpoint/specialty](./endpoint/specialty/README.md)                      |
| 06  | [Schedules](./06-SCHEDULES.md)                   | 20         | [endpoint/schedule](./endpoint/schedule/README.md)                        |
| 07  | [Schedule Overrides](./07-SCHEDULE-OVERRIDES.md) | 10         | [endpoint/schedule-override](./endpoint/schedule-override/README.md)      |
| 08  | [Medicines](./08-MEDICINES.md)                   | 10         | [endpoint/medicine](./endpoint/medicine/README.md)                        |
| 09  | [Diseases](./09-DISEASES.md)                     | 6          | [endpoint/disease](./endpoint/disease/README.md)                          |
| 10  | [Clinics](./10-CLINICS.md)                       | 8          | [endpoint/clinic](./endpoint/clinic/README.md)                            |
| 11  | [Rooms](./11-ROOMS.md)                           | 6          | [endpoint/room](./endpoint/room/README.md)                                |
| 12  | [Secretary](./12-SECRETARY.md)                   | 4          | [endpoint/secretary](./endpoint/secretary/README.md)                      |
| 13  | [Patients](./13-PATIENTS.md)                     | 15         | [endpoint/patient](./endpoint/patient/README.md)                          |
| 14  | [Users](./14-USERS.md)                           | 8          | [endpoint/user](./endpoint/user/README.md)                                |
| 15  | [Doctors](./15-DOCTORS.md)                       | 5          | [endpoint/doctor](./endpoint/doctor/README.md)                            |
| 16  | [Appointments](./16-APPOINTMENTS.md)             | 26         | [endpoint/appointment](./endpoint/appointment/README.md)                  |
| 17  | [Phone Calls](./17-PHONE-CALLS.md)               | 6          | [endpoint/phone](./endpoint/phone/README.md)                              |
| 18  | [Patient Records](./18-PATIENT-RECORDS.md)       | 6          | [endpoint/patient-record](./endpoint/patient-record/README.md)            |
| 19  | [Invoices](./19-INVOICES.md)                     | 8          | [endpoint/invoice](./endpoint/invoice/README.md)                          |
| 20  | [Invoice Items](./20-INVOICE-ITEMS.md)           | 8          | [endpoint/item](./endpoint/item/README.md)                                |
| 21  | [Payments](./21-PAYMENTS.md)                     | 10         | [endpoint/payment](./endpoint/payment/README.md)                          |
| 22  | [Payment Methods](./22-PAYMENT-METHODS.md)       | 6          | [endpoint/payment_method](./endpoint/payment_method/README.md)            |
| 23  | [AI](./23-AI.md)                                 | 8          | [endpoint/ai](./endpoint/ai/README.md)                                    |
| 24  | [Analytics](./24-ANALYTICS.md)                   | 12         | [endpoint/analytics](./endpoint/analytics/README.md)                      |
| 25  | [Other](./25-OTHER.md)                           | 10         | [endpoint/other](./endpoint/other/README.md)                              |

---

## Quick Reference by Role

### Owner

- Full clinic management (create doctors, secretaries, rooms)
- All CRUD operations across all entities
- Financial analytics and invoicing

### Doctor

- Manage own schedule, specialties, profile
- View/create patient records
- Book and manage appointments
- View own invoices

### Secretary

- Manage appointments for assigned rooms
- View patient info
- Update own profile

### Patient

- View own appointments, records, invoices
- Book appointments
- Update profile image

---

_Generated from test fixtures in `scripts/tests/endpoint/`. 427 test cases across 25 sections._
