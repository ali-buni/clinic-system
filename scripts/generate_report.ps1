param(
    [string]$FixturesDir = "tests/Fixtures/api-responses",
    [string]$OutputFile = "tests/Fixtures/API_REPORT.md"
)

$base = "/api/clinic-system"

$groups = @(
    @{key="auth"; title="Auth"; sub="Public authentication endpoints."}
    @{key="verification"; title="Verification"; sub="Email verification."}
    @{key="appointment-types"; title="Appointment Types"; sub="Appointment type CRUD."}
    @{key="devices"; title="Devices"; sub="FCM device token registration."}
    @{key="specialties"; title="Specialties"; sub="Medical specialty management."}
    @{key="schedules"; title="Schedules"; sub="Doctor work hours and schedules."}
    @{key="medicines"; title="Medicines"; sub="Medicine search and creation."}
    @{key="diseases"; title="Diseases"; sub="Disease search and creation."}
    @{key="clinic"; title="Clinic"; sub="Clinic management (all routes require auth:sanctum)."}
    @{key="rooms"; title="Rooms"; sub="Room management (auth required)."}
    @{key="secretaries"; title="Secretaries"; sub="Secretary management (auth required)."}
    @{key="patients"; title="Patients"; sub="Patient management (auth required)."}
    @{key="users"; title="Users"; sub="User profile management (auth required)."}
    @{key="doctors"; title="Doctors"; sub="Doctor management (auth required)."}
    @{key="appointments"; title="Appointments"; sub="Appointment booking and management (auth required)."}
    @{key="phone"; title="Phone"; sub="Phone number management (auth required)."}
    @{key="patient-records"; title="Patient Records"; sub="Patient medical records (auth required)."}
)

# Define endpoints. Each entry: action prefix, method, uri, auth, description, request body
# Fixtures named {action}-*.json in the domain directory will be matched.
$defs = @{
    "auth" = @(
        @{a="login"; m="POST"; u="/login"; pub=$true;  d="Authenticate user credentials."; r=@('{','    "login": "patient@test.com",   // required, email','    "password": "password"         // required, min:8','}')}
        @{a="register"; m="POST"; u="/register"; pub=$true;  d="Register a new user."; r=@('{','    "fname": "New",                // required','    "lname": "Patient",             // required','    "email": "newpatient@test.com", // required, unique','    "password": "password123",      // required, min:8, confirmed','    "password_confirmation": "password123", // required','    "clinic_id": 1,                 // required, exists:clinics','    "dob": "1990-01-01",            // optional','    "gender": "male",               // optional','    "nationality": null,            // optional','    "address": null,                // optional','    "marital_status": null,         // optional','    "emergency_phone": null,        // optional, digits_between:10,13','    "allergies": null,              // optional','    "chronic_conditions": null,     // optional','    "career": null,                 // optional','    "blood_type": null,             // optional','    "profile_image": null           // optional, image, max:2048','}')}
        @{a="forgot-password"; m="POST"; u="/forgot-password"; pub=$true;  d="Send password reset link."; r=@('{','    "email": "patient@test.com"    // required, email, exists:users','}')}
        @{a="signout"; m="POST"; u="/signout"; pub=$false; d="Revoke current token."; r=$null}
        @{a="refresh-token"; m="POST"; u="/refresh-token"; pub=$false; d="Refresh authentication token."; r=@('{','    "refresh_token": "..."         // required','}')}
        @{a="reset-password"; m="POST"; u="/reset-password"; pub=$false; d="Reset password (authenticated)."; r=@('{','    "email": "patient@test.com",   // required, exists:users','    "password": "currentpass",      // required, min:8','    "new_password": "newpass123",   // required, min:8, different, confirmed','    "new_password_confirmation": "newpass123" // required','}')}
        @{a="reset-with-code"; m="POST"; u="/reset-password-with-code"; pub=$true;  d="Reset password using verification code."; r=@('{','    "email": "patient@test.com",   // required, email, exists:users','    "code": "123456",               // required, digits:6','    "password": "newpass123",       // required, min:8, confirmed','    "password_confirmation": "newpass123" // required','}')}
    )
    "verification" = @(
        @{a="verify-code"; m="POST"; u="/verify-code"; pub=$true;  d="Verify email verification code."; r=@('{','    "login": "patient@test.com",   // required, email, exists:users','    "code": "123456",               // required, digits:6','    "type": "email"                 // required, in:phone,email','}')}
        @{a="resend-code"; m="POST"; u="/resend-code"; pub=$true;  d="Resend verification code."; r=@('{','    "login": "patient@test.com",   // required, email, exists:users','    "password": "password"         // required, min:8','}')}
    )
    "appointment-types" = @(
        @{a="index"; m="GET"; u="/appointment-types"; pub=$true;  d="List all appointment types."; r=$null; q=$null}
        @{a="add"; m="POST"; u="/appointment-types"; pub=$true;  d="Create a new appointment type."; r=@('{','    "types": 1,                     // required, integer, min:1, max:3','    "ar_name": "...",               // required','    "en_name": "..."                // required','}')}
        @{a="delete"; m="DELETE"; u="/appointment-types/{id}"; pub=$true;  d="Delete an appointment type."; r=$null}
    )
    "devices" = @(
        @{a="register-token"; m="POST"; u="/devices/register-token"; pub=$false; d="Register FCM device token."; r=@('{','    "fcm_token": "..."             // required','}')}
    )
    "specialties" = @(
        @{a="index"; m="GET"; u="/clinic/specialty/index"; pub=$true;  d="List all specialties."; r=$null}
        @{a="attach-specialties"; m="POST"; u="/clinic/specialty/add"; pub=$false; d="Attach specialties to doctor."; r=@('{','    "specialty_ids": [1, 2]         // required, array, min:1, each exists:specialties','}')}
        @{a="detach-specialty"; m="DELETE"; u="/clinic/specialty/delete/{specialId}"; pub=$false; d="Detach specialty from doctor."; r=$null}
        @{a="change-primary"; m="POST"; u="/clinic/specialty/changePrimary/{specialtyId}"; pub=$false; d="Change primary specialty."; r=$null}
        @{a="show-primary"; m="GET"; u="/clinic/specialty/showPrimary/{doctorId}"; pub=$false; d="Show primary specialty."; r=$null}
        @{a="get-all-specialties"; m="GET"; u="/clinic/specialty/getAll"; pub=$false; d="Show all doctor specialties."; r=$null}
    )
    "schedules" = @(
        @{a="store"; m="POST"; u="/clinic/schedule/add"; pub=$false; d="Create work hour entry."; r=@('{','    "doctor_id": 1,                 // required, integer, exists:doctors','    "day_of_week": 0,               // required, integer, between:0,6','    "start_time": "09:00",          // required, format:H:i','    "end_time": "17:00",            // required, format:H:i, after:start_time','    "is_active": true,              // optional, boolean','    "max_patients_per_day": 20,     // optional, integer, min:1','    "break_start": "12:00",         // optional, format:H:i','    "break_end": "13:00"            // optional, format:H:i','}')}
        @{a="update"; m="PUT"; u="/clinic/schedule/edit"; pub=$false; d="Update work hour."; r=@('{','    "doctor_id": 1,                 // sometimes, integer, exists:doctors','    "day_of_week": 0,               // sometimes, integer, between:0,6','    "start_time": "09:00",          // sometimes, format:H:i','    "end_time": "18:00",            // sometimes, format:H:i, after:start_time','    "is_active": true,              // optional, boolean','    "max_patients_per_day": 20,     // optional, integer, min:1','    "break_start": "12:00",         // optional, format:H:i','    "break_end": "13:00"            // optional, format:H:i','}')}
        @{a="destroy"; m="DELETE"; u="/clinic/schedule/delete/{dayOfWeek}/{doctorId}"; pub=$false; d="Delete work hour."; r=$null}
        @{a="get-weekly"; m="GET"; u="/clinic/schedule/get-weekly/{doctorId}"; pub=$true;  d="Get weekly schedule for a doctor."; r=$null}
        @{a="work-hour-by-date"; m="GET"; u="/clinic/schedule/work-hour/{doctorId}"; pub=$true;  d="Get work hours for a specific date."; q="?date=2026-06-28"}
    )
    "medicines" = @(
        @{a="search"; m="GET"; u="/clinic/medicines/search"; pub=$true;  d="Search medicines by name."; q="?query=para"}
        @{a="store"; m="POST"; u="/clinic/medicines/store"; pub=$false; d="Create custom medicine."; r=@('{','    "ar_name": "...",               // required_without:en_name','    "en_name": "Paracetamol",        // required_without:ar_name','    "api_medicine_id": null,         // optional','    "generic_name_ar": null,         // optional','    "generic_name_en": null,         // optional','    "strength": "500mg",             // optional','    "form": "tablet"                 // optional, in:tablet,capsule,syrup,injection,ointment','    // Note: at least one of ar_name or en_name is required','}')}
    )
    "diseases" = @(
        @{a="search"; m="GET"; u="/clinic/diseases/search"; pub=$true;  d="Search diseases by name."; q="?query=dia"}
        @{a="store"; m="POST"; u="/clinic/diseases/store"; pub=$false; d="Create custom disease."; r=@('{','    "ar_name": "...",               // required','    "en_name": "Diabetes",           // required','    "disease_nature": "chronic",     // required, in:infectious,genetic,chronic,acute,mental,other','    "code": null,                    // optional','    "description": null              // optional','}')}
    )
    "phone" = @(
        @{a="update"; m="POST"; u="/phone/update"; pub=$false; d="Request phone update (sends code if already set)."; r=@('{','    "phone": "0911111111"          // required, digits:10, starts_with:09','}')}
        @{a="verify-update"; m="POST"; u="/phone/verify-update"; pub=$false; d="Verify phone update with code."; r=@('{','    "code": "123456"               // required, digits:6','}')}
    )
    "clinic" = @(
        @{a="clinic-info"; m="GET"; u="/clinic/clinic/info"; pub=$false; d="Get clinic info."; r=$null}
        @{a="update-clinic"; m="POST"; u="/clinic/clinic/update/{clinicId}"; pub=$false; d="Update clinic details."; r=@('{','    "phone": "0912345678",          // optional, digits:10, starts_with:09','    "location": "123 Main St",      // optional, min:10','    "title": "My Clinic"            // optional, min:6, max:60','}')}
        @{a="create-doctor"; m="POST"; u="/clinic/clinic/doctor/register"; pub=$false; d="Create doctor (owner only)."; r=@('{','    "fname": "New",               // required, min:2, max:50','    "lname": "Doctor",            // required, min:2, max:50','    "email": "newdoctor@test.com", // required, email, unique:users','    "dob": "1985-05-15",          // required, date, before:today','    "gender": "male",             // required, in:male,female,unknown','    "clinic_id": 1,               // required, exists:clinics','    "room_id": 1,                 // required, exists:rooms','    "appointment_duration": 30,   // required, integer, min:5, max:120','    "consultation_fee": 200,      // required, numeric, min:0','    "specialty_ids": [1],         // required, array, min:1','    "bio": "Experienced doctor"   // optional, max:1000','}')}
        @{a="create-secretary"; m="POST"; u="/clinic/clinic/secretary/register"; pub=$false; d="Create secretary (owner only)."; r=@('{','    "fname": "New",               // required, min:2, max:50','    "lname": "Secretary",         // required, min:2, max:50','    "email": "newsecretary@test.com", // required, email, unique:users','    "dob": "1990-01-01",          // required, date, before:today','    "gender": "female",           // required, in:male,female,unknown','    "clinic_id": 1,               // required, exists:clinics','    "room_ids": [1]               // required, array, min:1, each exists:rooms','}')}
    )
    "rooms" = @(
        @{a="index"; m="GET"; u="/clinic/clinic/rooms/{clinicId}"; pub=$false; d="List rooms in a clinic."; r=$null}
        @{a="index-with-info"; m="GET"; u="/clinic/clinic/rooms/{clinicId}/info"; pub=$false; d="List rooms with additional info."; r=$null}
        @{a="get-room-details"; m="GET"; u="/clinic/clinic/rooms/{roomId}/details"; pub=$false; d="Get room details."; r=$null}
        @{a="user-rooms"; m="GET"; u="/clinic/clinic/rooms/userRooms/get"; pub=$false; d="Get current user's rooms."; r=$null}
        @{a="create-room"; m="POST"; u="/clinic/clinic/rooms/"; pub=$false; d="Create a new room."; r=@('{','    "name": "New Room",             // required, unique:rooms','    "clinic_id": 1                  // required (POST), integer, exists:clinics','}')}
        @{a="update-room"; m="POST"; u="/clinic/clinic/rooms/{roomId}"; pub=$false; d="Update a room."; r=@('{','    "name": "Updated Room Name",    // required, unique:rooms','    "clinic_id": 1                  // optional (update), integer, exists:clinics','}')}
        @{a="destroy-room"; m="DELETE"; u="/clinic/clinic/rooms/{roomId}"; pub=$false; d="Delete a room."; r=$null}
        @{a="add-doctor-to-room"; m="POST"; u="/clinic/clinic/rooms/sync/doctorRoom"; pub=$false; d="Add doctor to room."; r=@('{','    "room_id": 1,                   // required','    "doctor_id": 1                  // required','}')}
        @{a="add-sec-to-room"; m="POST"; u="/clinic/clinic/rooms/sync/secRooms"; pub=$false; d="Add secretary to room."; r=@('{','    "room_id": 1,                   // required','    "secretary_id": 1               // required','}')}
        @{a="del-doctor-from-room"; m="DELETE"; u="/clinic/clinic/rooms/detach/doctorRoom"; pub=$false; d="Remove doctor from room."; r=@('{','    "room_id": 1,                   // required','    "doctor_id": 1                  // required','}')}
        @{a="del-sec-from-room"; m="DELETE"; u="/clinic/clinic/rooms/detach/secRooms"; pub=$false; d="Remove secretary from room."; r=@('{','    "room_id": 1,                   // required','    "secretary_id": 1               // required','}')}
    )
    "secretaries" = @(
        @{a="info"; m="GET"; u="/clinic/clinic/secretaries/{id}"; pub=$false; d="Get secretary info."; r=$null}
        @{a="update"; m="POST"; u="/clinic/clinic/secretaries/update"; pub=$false; d="Update secretary info."; r=@('{','    "clinic_id": 1,                 // optional, exists:clinics','    "fname": "Updated",             // optional','    "lname": "Name",                // optional','    "dob": "1990-01-01",            // optional, date, before:today','    "gender": "female"              // optional, in:male,female,unknown','}')}
    )
    "patients" = @(
        @{a="index"; m="GET"; u="/clinic/clinic/patients"; pub=$false; d="List patients (requires clinic_id)."; q="?clinic_id=1"}
        @{a="index-trashed"; m="GET"; u="/clinic/clinic/patients/trashed"; pub=$false; d="List soft-deleted patients."; q="?clinic_id=1"}
        @{a="show"; m="GET"; u="/clinic/clinic/patients/{patientId}/show"; pub=$false; d="Get patient details."; r=$null}
        @{a="medical-history"; m="GET"; u="/clinic/clinic/patients/{patientId}/medical-history"; pub=$false; d="Get patient medical history."; r=$null}
        @{a="update"; m="POST"; u="/clinic/clinic/patients/update"; pub=$false; d="Update patient info."; r=@('{','    "patient_id": 1,                // required, exists:patient_infos','    "clinic_id": 1,                 // optional, exists:clinics','    "fname": "Updated",             // optional','    "lname": "Name",                // optional','    "dob": "1990-01-01",            // optional, date, before:today','    "gender": "male",               // optional, in:male,female,other,unknown','    "nationality": null,            // optional','    "address": null,                // optional','    "marital_status": null,         // optional, in:married,single,divorced,widowed,other','    "emergency_phone": null,        // optional, digits_between:10,13','    "allergies": null,              // optional','    "chronic_conditions": null,     // optional','    "career": null,                 // optional','    "blood_type": "A+"              // optional, in:A+,A-,B+,B-,AB+,AB-,O+,O-','}')}
        @{a="destroy"; m="DELETE"; u="/clinic/clinic/patients/delete"; pub=$false; d="Soft-delete patient."; r=@('{','    "patient_id": "1"               // required, exists:patient_infos','}')}
        @{a="restore"; m="GET"; u="/clinic/clinic/patients/restore"; pub=$false; d="Restore soft-deleted patient."; q="?patient_id=1"}
    )
    "users" = @(
        @{a="info"; m="GET"; u="/clinic/clinic/users/info"; pub=$false; d="Get authenticated user info."; r=$null}
        @{a="image-url"; m="GET"; u="/clinic/clinic/users/image-url"; pub=$false; d="Get user profile image URL."; r=$null}
        @{a="update-image"; m="POST"; u="/clinic/clinic/users/update-image"; pub=$false; d="Update user profile image."; r=$null}
    )
    "doctors" = @(
        @{a="index"; m="GET"; u="/clinic/clinic/doctors/filter"; pub=$false; d="List doctors with filters."; q="?clinic_id=1"}
        @{a="info"; m="GET"; u="/clinic/clinic/doctors/{id}/info"; pub=$false; d="Get doctor info."; r=$null}
        @{a="update"; m="POST"; u="/clinic/clinic/doctors/update"; pub=$false; d="Update doctor info."; r=@('{','    "doctor_id": 1,                 // required, exists:doctors','    "fname": "Updated",             // optional','    "lname": "Doctor",              // optional','    "dob": "1985-05-15",            // optional, date, before:today','    "gender": "male",               // optional, in:male,female,unknown','    "appointment_duration": 30,     // optional, integer, min:5, max:120','    "bio": "Experienced",           // optional','    "consultation_fee": 200,        // optional, numeric, min:0','    "specialties": [1, 2]           // optional, array, each exists:specialties','}')}
        @{a="destroy"; m="DELETE"; u="/clinic/clinic/doctors/{id}/leave"; pub=$false; d="Soft-delete doctor."; r=$null}
        @{a="restore"; m="POST"; u="/clinic/clinic/doctors/{id}/restore"; pub=$false; d="Restore soft-deleted doctor."; r=$null}
        @{a="force-delete"; m="DELETE"; u="/clinic/clinic/doctors/{id}/force"; pub=$false; d="Force-delete doctor."; r=$null}
    )
    "appointments" = @(
        @{a="book"; m="POST"; u="/clinic/clinic/appointments/book"; pub=$false; d="Book a new appointment."; r=@('{','    "patient_id": 1,                // required, integer, exists:patient_infos','    "doctor_id": 1,                 // required, integer, exists:doctors','    "clinic_id": 1,                 // required, integer, exists:clinics','    "appointment_type_id": 1,       // required, integer, exists:appointment_types','    "start_time": "11:00",          // required, format:H:i','    "date": "2026-06-28",           // required, date, format:Y-m-d','    "visit_reason": "Routine checkup" // optional','}')}
        @{a="show"; m="GET"; u="/clinic/clinic/appointments/{id}"; pub=$false; d="Show appointment details."; r=$null}
        @{a="cancel"; m="POST"; u="/clinic/clinic/appointments/{id}/cancel"; pub=$false; d="Cancel an appointment."; r=@('{','    "cancel_reason": "Patient requested" // optional','}')}
        @{a="mark-confirmed"; m="POST"; u="/clinic/clinic/appointments/{id}/confirmed"; pub=$false; d="Mark appointment as confirmed."; r=$null}
        @{a="complete"; m="POST"; u="/clinic/clinic/appointments/{id}/complete"; pub=$false; d="Complete a confirmed appointment."; r=$null}
        @{a="reschedule"; m="POST"; u="/clinic/clinic/appointments/{id}/reschedule"; pub=$false; d="Reschedule an appointment."; r=@('{','    "start_time": "14:00",          // required, format:H:i','    "date": "2026-06-28",           // required, date, format:Y-m-d','    "type_id": 1                    // optional, exists:appointment_types','}')}
        @{a="patient-appointments"; m="GET"; u="/clinic/clinic/appointments/patient/{patientId}"; pub=$false; d="List patient appointments."; r=$null}
        @{a="doctor-appointments"; m="GET"; u="/clinic/clinic/appointments/doctor/{doctorId}"; pub=$false; d="List doctor appointments."; r=$null}
        @{a="clinic-appointments"; m="GET"; u="/clinic/clinic/appointments/clinic/{clinicId}"; pub=$false; d="List clinic appointments."; r=$null}
        @{a="room-appointments"; m="GET"; u="/clinic/clinic/appointments/room/appo"; pub=$false; d="List room appointments."; q="?roomIds[0]=1"}
        @{a="doctor-schedule"; m="GET"; u="/clinic/clinic/appointments/doctor/{doctorId}/schedule"; pub=$false; d="Get doctor schedule for a date."; q="?date=2026-06-28"}
        @{a="clinic-schedule"; m="GET"; u="/clinic/clinic/appointments/clinic/{clinicId}/schedule"; pub=$false; d="Get clinic schedule for a date."; q="?date=2026-06-28"}
        @{a="available-slots"; m="GET"; u="/clinic/clinic/appointments/get/available-slots"; pub=$false; d="Get available appointment slots."; q="?doctor_id=1&date=2026-06-28"}
    )
    "patient-records" = @(
        @{a="store"; m="POST"; u="/clinic/clinic/patient-records"; pub=$false; d="Create patient record."; r=@('{','    "patient_id": 1,                // required, exists:patient_infos','    "doctor_id": 1,                 // required, exists:doctors','    "clinic_id": 1,                 // required, exists:clinics','    "appointment_id": 1,            // required, exists:appointments','    "diagnosis_summary": "hypertension", // required, max:1000','    "description": null,            // optional, max:1000','    "status": "open",               // optional, in:open,closed,follow-up','    "notes": null,                  // optional, max:2000','    "diseases": [{                  // optional, array','        "id": 1,                    // required_without:code, exists:diseases','        "code": null,               // required_without:id','        "en_name": "Diabetes",       // required_without:id','        "ar_name": "...",           // required_without:id','        "disease_nature": "chronic", // required_without:id','        "description": null,        // optional','        "status": "active",         // optional, in:active,resolved,chronic','        "severity": "moderate"      // optional, in:mild,moderate,severe','    }],','    "prescription_items": [{        // optional, array','        "id": null,                 // required_without:api_medicine_id','        "api_medicine_id": null,    // required_without:id','        "en_name": "Paracetamol",   // required_without:id','        "ar_name": "...",           // required_without:id','        "generic_name_en": null,    // required_without:id','        "generic_name_ar": null,    // required_without:id','        "form": "tablet",           // required_without:id','        "strength": "500mg",        // required_without:id','        "dosage_instruction": null, // optional','        "frequency": null,          // optional','        "duration": null            // optional','    }]','}')}
        @{a="show"; m="GET"; u="/clinic/clinic/patient-records/show/{id}"; pub=$false; d="Show patient record."; r=$null}
        @{a="update"; m="PUT"; u="/clinic/clinic/patient-records/{id}"; pub=$false; d="Update patient record."; r=@('{','    "diagnosis_summary": "Updated", // optional, max:1000','    "description": null,            // optional, max:1000','    "status": "follow-up",          // optional, in:open,closed,follow-up','    "notes": null,                  // optional, max:2000','    "diseases": [{ ... }],          // optional (same structure as store)','    "preid": null,                  // required_with:prescription_items, exists:prescriptions','    "prescription_items": [{ ... }] // optional (same structure as store)','}')}
        @{a="destroy"; m="DELETE"; u="/clinic/clinic/patient-records/{id}"; pub=$false; d="Delete patient record."; r=$null}
        @{a="index"; m="GET"; u="/clinic/clinic/patient-records/filtered"; pub=$false; d="List filtered patient records."; q="?clinic_id=1&search=&status=open&date_from=&date_to=&disease_code="}
        @{a="history"; m="GET"; u="/clinic/clinic/patient-records/patient/{patientId}/history"; pub=$false; d="Get patient medical history records."; r=$null}
        @{a="get-by-doctor"; m="GET"; u="/clinic/clinic/patient-records/patient/{patientId}/doctor/{doctorId}"; pub=$false; d="Get records by patient and doctor."; r=$null}
        @{a="get-all-by-doctor"; m="GET"; u="/clinic/clinic/patient-records/doctor/{doctorId}/all"; pub=$false; d="Get all records for a doctor."; r=$null}
        @{a="get-by-room"; m="POST"; u="/clinic/clinic/patient-records/rooms/search"; pub=$false; d="Search records by rooms."; r=@('{','    "room_ids": [1]                 // required, array, min:1, each exists:rooms','}')}
    )
}

# Sort responses: success first, then by status code
function ResponseRank($sName) {
    if ($sName -match "-success$") { return 0 }
    $codes = @{ "200"=1; "201"=2; "204"=3; "400"=4; "401"=5; "403"=6; "404"=7; "422"=8; "500"=9 }
    # Extract status from fixture to rank
    return 99
}

# --- GENERATE ---
$lines = New-Object System.Collections.Generic.List[string]

$lines.Add('# Clinic System API - Full Reference')
$lines.Add('')
$lines.Add('**Base URL:** ' + $base)
$lines.Add('')
$lines.Add('**Standard Response Envelope:**')
$lines.Add('')
$lines.Add('```json')
$lines.Add('{')
$lines.Add('  "success": true|false,')
$lines.Add('  "message": "...",')
$lines.Add('  "data": { ... } | [ ... ] | null')
$lines.Add('}')
$lines.Add('```')
$lines.Add('')
$lines.Add('**Authentication:** Bearer token via auth:sanctum. Attach as:')
$lines.Add('')
$lines.Add('```')
$lines.Add('Authorization: Bearer {token}')
$lines.Add('Accept: application/json')
$lines.Add('```')
$lines.Add('')

# Sort order for HTTP methods
$methodOrder = @{ "GET" = 0; "POST" = 1; "PUT" = 2; "DELETE" = 3 }

# --- BUILD TOC ---
$tocLines = New-Object System.Collections.Generic.List[string]
$tocLines.Add('## Table of Contents')
$tocLines.Add('')
foreach ($tg in $groups) {
    $tgdName = $tg.key
    if (-not $defs.ContainsKey($tgdName)) { continue }
    $sortedToc = $defs[$tgdName] | Sort-Object { $methodOrder[$_.m] }
    $tocLines.Add('### ' + $tg.title)
    $tocLines.Add('')
    $tocLines.Add('| Method | Endpoint | Description |')
    $tocLines.Add('|--------|----------|-------------|')
    $tocAny = $false
    foreach ($tep in $sortedToc) {
        $tocAction = $tep.a
        $tocFixtureFiles = Get-ChildItem -Path "$FixturesDir/$tgdName" -Filter "$tocAction-*.json" -ErrorAction SilentlyContinue
        if ($null -eq $tocFixtureFiles -or $tocFixtureFiles.Count -eq 0) { continue }
        $tocAny = $true
        $tocAnchor = "#" + $tgdName + "-" + $tocAction
        $tocLines.Add('| ' + $tep.m + ' | [' + $tep.u + '](' + $tocAnchor + ') | ' + $tep.d + ' |')
    }
    if ($tocAny) { $tocLines.Add('') }
}
foreach ($tl in $tocLines) { $lines.Add($tl) }

$endpointCount = 0
$fixtureUsed = 0

foreach ($g in $groups) {
    $dName = $g.key
    if (-not $defs.ContainsKey($dName)) { continue }

    $lines.Add("---")
    $lines.Add("")
    $lines.Add("## " + $g.title)
    $lines.Add("")
    if ($g.sub) { $lines.Add($g.sub); $lines.Add("") }

    # Sort endpoints by method order
    $sortedEndpoints = $defs[$dName] | Sort-Object { $methodOrder[$_.m] }

    foreach ($ep in $sortedEndpoints) {
        $action = $ep.a
        # Find all fixture files matching {action}-*.json
        $fixtureFiles = Get-ChildItem -Path "$FixturesDir/$dName" -Filter "$action-*.json" -ErrorAction SilentlyContinue
        if ($null -eq $fixtureFiles -or $fixtureFiles.Count -eq 0) { continue }

        $endpointCount++
        $authLabel = if ($ep.pub) { "Public." } else { "Auth required." }
        $fullUri = $base + $ep.u

        $lines.Add('<div id="' + $dName + '-' + $action + '"></div>')
        $lines.Add('**`' + $ep.m + ' ' + $fullUri + '`**')
        $lines.Add("")
        $lines.Add($ep.d + " " + $authLabel)
        $lines.Add("")

        if ($ep.r) {
            $lines.Add("**Request Body:**")
            $lines.Add("")
            $lines.Add('```json')
            if ($ep.r -is [array]) { $ep.r | ForEach-Object { $lines.Add($_) } }
            else { $lines.Add($ep.r) }
            $lines.Add('```')
            $lines.Add("")
        } elseif ($ep.q) {
            $lines.Add("**Query Parameters:**")
            $lines.Add("")
            $lines.Add('```')
            $lines.Add($ep.q.Substring(1))
            $lines.Add('```')
            $lines.Add("")
        }

        # Sort fixture files: -success first, then by name
        $sortedFiles = $fixtureFiles | Sort-Object { $_.BaseName -notmatch '-success$' }, Name

        foreach ($f in $sortedFiles) {
            $scenarioName = $f.BaseName
            $json = Get-Content -Path $f.FullName -Raw -Encoding UTF8 | ConvertFrom-Json
            $status = $json.status
            $body = $json.body | ConvertTo-Json -Depth 10
            $fixtureUsed++

            # Generate a readable label from scenario name
            $label = $scenarioName -replace "^$action-", ""
            if ($label -eq "success") { $label = "Success" }
            elseif ($label -match "^error-") { $label = "Error: " + ($label -replace "^error-", "") }
            elseif ($label -match "^error$") { $label = "Error" }

            $lines.Add("**Response ($status) - $($label):**")
            $lines.Add("")
            $lines.Add('```json')
            $lines.Add($body)
            $lines.Add('```')
            $lines.Add("")
        }
    }
}

$lines.Add("---")
$lines.Add("")
$lines.Add('_Generated from test fixtures in `tests/Fixtures/api-responses/`. ' + $fixtureUsed + ' responses across ' + $endpointCount + ' endpoints._')

[System.IO.File]::WriteAllLines($OutputFile, $lines, [System.Text.UTF8Encoding]::new($false))
Write-Host ("Report generated: " + $OutputFile + " (" + $fixtureUsed + " responses across " + $endpointCount + " endpoints)")
