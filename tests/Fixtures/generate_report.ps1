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
    @{key="patient-records"; title="Patient Records"; sub="Patient medical records (auth required)."}
)

# Define endpoints. Each entry: action prefix, method, uri, auth, description, request body
# Fixtures named {action}-*.json in the domain directory will be matched.
$defs = @{
    "auth" = @(
        @{a="login"; m="POST"; u="/login"; pub=$true;  d="Authenticate user credentials."; r='{"email":"patient@test.com","password":"password"}'}
        @{a="register"; m="POST"; u="/register"; pub=$true;  d="Register a new user."; r='{"email":"new@test.com","password":"password","phone":"0900000005"}'}
        @{a="forgot-password"; m="POST"; u="/forgot-password"; pub=$true;  d="Send password reset link."; r='{"email":"patient@test.com"}'}
        @{a="signout"; m="POST"; u="/signout"; pub=$false; d="Revoke current token."; r=$null}
        @{a="refresh-token"; m="POST"; u="/refresh-token"; pub=$false; d="Refresh authentication token."; r='{"refresh_token":"..."}'}
        @{a="reset-password"; m="POST"; u="/reset-password"; pub=$false; d="Reset password (authenticated)."; r='{"current_password":"password","new_password":"newpass123"}'}
        @{a="reset-with-code"; m="POST"; u="/reset-password-with-code"; pub=$true;  d="Reset password using verification code."; r='{"email":"","code":"","password":""}'}
    )
    "verification" = @(
        @{a="verify-code"; m="POST"; u="/verify-code"; pub=$true;  d="Verify email verification code."; r='{"email":"...","code":"..."}'}
        @{a="resend-code"; m="POST"; u="/resend-code"; pub=$true;  d="Resend verification code."; r='{"email":"..."}'}
    )
    "appointment-types" = @(
        @{a="index"; m="GET"; u="/appointment-types"; pub=$true;  d="List all appointment types."; r=$null; q=$null}
        @{a="add"; m="POST"; u="/appointment-types"; pub=$true;  d="Create a new appointment type."; r='{"types":1,"ar_name":"...","en_name":"..."}'}
        @{a="delete"; m="DELETE"; u="/appointment-types/{id}"; pub=$true;  d="Delete an appointment type."; r=$null}
    )
    "devices" = @(
        @{a="register-token"; m="POST"; u="/devices/register-token"; pub=$false; d="Register FCM device token."; r='{"fcm_token":"..."}'}
    )
    "specialties" = @(
        @{a="index"; m="GET"; u="/clinic/specialty/index"; pub=$true;  d="List all specialties."; r=$null}
        @{a="attach-specialties"; m="POST"; u="/clinic/specialty/add"; pub=$false; d="Attach specialties to doctor."; r='{"doctor_id":1,"specialties":[1,2]}'}; @{a="detach-specialty"; m="DELETE"; u="/clinic/specialty/delete/{specialId}"; pub=$false; d="Detach specialty from doctor."; r=$null}
        @{a="change-primary"; m="POST"; u="/clinic/specialty/changePrimary/{specialtyId}"; pub=$false; d="Change primary specialty."; r='{"doctor_id":1}'}
        @{a="show-primary"; m="GET"; u="/clinic/specialty/showPrimary/{doctorId}"; pub=$false; d="Show primary specialty."; r=$null}
        @{a="get-all-specialties"; m="GET"; u="/clinic/specialty/getAll"; pub=$false; d="Show all doctor specialties."; r=$null}
    )
    "schedules" = @(
        @{a="store"; m="POST"; u="/clinic/schedule/add"; pub=$false; d="Create work hour entry."; r='{"doctor_id":1,"day_of_week":0,"start_time":"09:00","end_time":"17:00"}'}
        @{a="update"; m="PUT"; u="/clinic/schedule/edit"; pub=$false; d="Update work hour."; r='{"doctor_id":1,"day_of_week":0,"start_time":"09:00","end_time":"18:00"}'}
        @{a="destroy"; m="DELETE"; u="/clinic/schedule/delete/{dayOfWeek}/{doctorId}"; pub=$false; d="Delete work hour."; r=$null}
        @{a="get-weekly"; m="GET"; u="/clinic/schedule/get-weekly/{doctorId}"; pub=$true;  d="Get weekly schedule for a doctor."; r=$null}
        @{a="work-hour-by-date"; m="GET"; u="/clinic/schedule/work-hour/{doctorId}"; pub=$true;  d="Get work hours for a specific date."; q="?date=2026-06-28"}
    )
    "medicines" = @(
        @{a="search"; m="GET"; u="/clinic/medicines/search"; pub=$true;  d="Search medicines by name."; q="?query=para"}
        @{a="store"; m="POST"; u="/clinic/medicines/store"; pub=$false; d="Create custom medicine."; r='{"ar_name":"...","en_name":"Paracetamol","form":"tablet","strength":"500mg","is_custom":true}'}
    )
    "diseases" = @(
        @{a="search"; m="GET"; u="/clinic/diseases/search"; pub=$true;  d="Search diseases by name."; q="?query=dia"}
        @{a="store"; m="POST"; u="/clinic/diseases/store"; pub=$false; d="Create custom disease."; r='{"ar_name":"...","en_name":"Diabetes","disease_nature":"chronic","is_custom":true}'}
    )
    "clinic" = @(
        @{a="clinic-info"; m="GET"; u="/clinic/clinic/info"; pub=$false; d="Get clinic info."; r=$null}
        @{a="update-clinic"; m="POST"; u="/clinic/clinic/update/{clinicId}"; pub=$false; d="Update clinic details."; r='{"name":"Updated Clinic"}'}
        @{a="create-doctor"; m="POST"; u="/clinic/clinic/doctor/register"; pub=$false; d="Create doctor (owner only)."; r='{"user_id":1,"clinic_id":1}'}
        @{a="create-secretary"; m="POST"; u="/clinic/clinic/secretary/register"; pub=$false; d="Create secretary (owner only)."; r='{"user_id":1,"clinic_id":1}'}
    )
    "rooms" = @(
        @{a="index"; m="GET"; u="/clinic/clinic/rooms/{clinicId}"; pub=$false; d="List rooms in a clinic."; r=$null}
        @{a="index-with-info"; m="GET"; u="/clinic/clinic/rooms/{clinicId}/info"; pub=$false; d="List rooms with additional info."; r=$null}
        @{a="get-room-details"; m="GET"; u="/clinic/clinic/rooms/{roomId}/details"; pub=$false; d="Get room details."; r=$null}
        @{a="user-rooms"; m="GET"; u="/clinic/clinic/rooms/userRooms/get"; pub=$false; d="Get current user's rooms."; r=$null}
        @{a="create-room"; m="POST"; u="/clinic/clinic/rooms/"; pub=$false; d="Create a new room."; r='{"name":"New Room","clinic_id":1}'}
        @{a="update-room"; m="POST"; u="/clinic/clinic/rooms/{roomId}"; pub=$false; d="Update a room."; r='{"name":"Updated Room Name","clinic_id":1}'}
        @{a="destroy-room"; m="DELETE"; u="/clinic/clinic/rooms/{roomId}"; pub=$false; d="Delete a room."; r=$null}
        @{a="add-doctor-to-room"; m="POST"; u="/clinic/clinic/rooms/sync/doctorRoom"; pub=$false; d="Add doctor to room."; r='{"room_id":1,"doctor_id":1}'}
        @{a="add-sec-to-room"; m="POST"; u="/clinic/clinic/rooms/sync/secRooms"; pub=$false; d="Add secretary to room."; r='{"room_id":1,"secretary_id":1}'}
        @{a="del-doctor-from-room"; m="DELETE"; u="/clinic/clinic/rooms/detach/doctorRoom"; pub=$false; d="Remove doctor from room."; r='{"room_id":1,"doctor_id":1}'}
        @{a="del-sec-from-room"; m="DELETE"; u="/clinic/clinic/rooms/detach/secRooms"; pub=$false; d="Remove secretary from room."; r='{"room_id":1,"secretary_id":1}'}
    )
    "secretaries" = @(
        @{a="info"; m="GET"; u="/clinic/clinic/secretaries/{id}"; pub=$false; d="Get secretary info."; r=$null}
        @{a="update"; m="POST"; u="/clinic/clinic/secretaries/update"; pub=$false; d="Update secretary info."; r='{"secretary_id":1,"fname":"...","lname":"..."}'}
    )
    "patients" = @(
        @{a="index"; m="GET"; u="/clinic/clinic/patients"; pub=$false; d="List patients (requires clinic_id)."; q="?clinic_id=1"}
        @{a="index-trashed"; m="GET"; u="/clinic/clinic/patients/trashed"; pub=$false; d="List soft-deleted patients."; q="?clinic_id=1"}
        @{a="show"; m="GET"; u="/clinic/clinic/patients/{patientId}/show"; pub=$false; d="Get patient details."; r=$null}
        @{a="medical-history"; m="GET"; u="/clinic/clinic/patients/{patientId}/medical-history"; pub=$false; d="Get patient medical history."; r=$null}
        @{a="update"; m="POST"; u="/clinic/clinic/patients/update"; pub=$false; d="Update patient info."; r='{"patient_id":1,"fname":"Updated","blood_type":"A+"}'}
        @{a="destroy"; m="DELETE"; u="/clinic/clinic/patients/delete"; pub=$false; d="Soft-delete patient (patient_id must be string)."; r='{"patient_id":"1"}'}
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
        @{a="update"; m="POST"; u="/clinic/clinic/doctors/update"; pub=$false; d="Update doctor info."; r='{"doctor_id":1,"consultation_fee":200}'}
        @{a="destroy"; m="DELETE"; u="/clinic/clinic/doctors/{id}/leave"; pub=$false; d="Soft-delete doctor."; r=$null}
        @{a="restore"; m="POST"; u="/clinic/clinic/doctors/{id}/restore"; pub=$false; d="Restore soft-deleted doctor."; r=$null}
        @{a="force-delete"; m="DELETE"; u="/clinic/clinic/doctors/{id}/force"; pub=$false; d="Force-delete doctor."; r=$null}
    )
    "appointments" = @(
        @{a="book"; m="POST"; u="/clinic/clinic/appointments/book"; pub=$false; d="Book a new appointment."; r='{"patient_id":1,"doctor_id":1,"clinic_id":1,"appointment_type_id":1,"start_time":"11:00","date":"2026-06-28","visit_reason":"Routine checkup"}'}
        @{a="show"; m="GET"; u="/clinic/clinic/appointments/{id}"; pub=$false; d="Show appointment details."; r=$null}
        @{a="cancel"; m="POST"; u="/clinic/clinic/appointments/{id}/cancel"; pub=$false; d="Cancel an appointment."; r='{"cancel_reason":"Patient requested"}'}
        @{a="mark-confirmed"; m="POST"; u="/clinic/clinic/appointments/{id}/confirmed"; pub=$false; d="Mark appointment as confirmed."; r=$null}
        @{a="complete"; m="POST"; u="/clinic/clinic/appointments/{id}/complete"; pub=$false; d="Complete a confirmed appointment."; r=$null}
        @{a="reschedule"; m="POST"; u="/clinic/clinic/appointments/{id}/reschedule"; pub=$false; d="Reschedule an appointment."; r='{"start_time":"14:00","date":"2026-06-28"}'}
        @{a="patient-appointments"; m="GET"; u="/clinic/clinic/appointments/patient/{patientId}"; pub=$false; d="List patient appointments."; r=$null}
        @{a="doctor-appointments"; m="GET"; u="/clinic/clinic/appointments/doctor/{doctorId}"; pub=$false; d="List doctor appointments."; r=$null}
        @{a="clinic-appointments"; m="GET"; u="/clinic/clinic/appointments/clinic/{clinicId}"; pub=$false; d="List clinic appointments."; r=$null}
        @{a="room-appointments"; m="GET"; u="/clinic/clinic/appointments/room/appo"; pub=$false; d="List room appointments."; q="?roomIds[0]=1"}
        @{a="doctor-schedule"; m="GET"; u="/clinic/clinic/appointments/doctor/{doctorId}/schedule"; pub=$false; d="Get doctor schedule for a date."; q="?date=2026-06-28"}
        @{a="clinic-schedule"; m="GET"; u="/clinic/clinic/appointments/clinic/{clinicId}/schedule"; pub=$false; d="Get clinic schedule for a date."; q="?date=2026-06-28"}
        @{a="available-slots"; m="GET"; u="/clinic/clinic/appointments/get/available-slots"; pub=$false; d="Get available appointment slots."; q="?doctor_id=1&date=2026-06-28"}
    )
    "patient-records" = @(
        @{a="store"; m="POST"; u="/clinic/clinic/patient-records"; pub=$false; d="Create patient record."; r='{"patient_id":1,"doctor_id":1,"clinic_id":1,"appointment_id":1,"diagnosis_summary":"hypertension","status":"open"}'}
        @{a="show"; m="GET"; u="/clinic/clinic/patient-records/show/{id}"; pub=$false; d="Show patient record."; r=$null}
        @{a="update"; m="PUT"; u="/clinic/clinic/patient-records/{id}"; pub=$false; d="Update patient record."; r='{"diagnosis_summary":"Updated","status":"follow-up"}'}
        @{a="destroy"; m="DELETE"; u="/clinic/clinic/patient-records/{id}"; pub=$false; d="Delete patient record."; r=$null}
        @{a="index"; m="GET"; u="/clinic/clinic/patient-records/filtered"; pub=$false; d="List filtered patient records."; q="?clinic_id=1"}
        @{a="history"; m="GET"; u="/clinic/clinic/patient-records/patient/{patientId}/history"; pub=$false; d="Get patient's medical history records."; r=$null}
        @{a="get-by-doctor"; m="GET"; u="/clinic/clinic/patient-records/patient/{patientId}/doctor/{doctorId}"; pub=$false; d="Get records by patient and doctor."; r=$null}
        @{a="get-all-by-doctor"; m="GET"; u="/clinic/clinic/patient-records/doctor/{doctorId}/all"; pub=$false; d="Get all records for a doctor."; r=$null}
        @{a="get-by-room"; m="POST"; u="/clinic/clinic/patient-records/rooms/search"; pub=$false; d="Search records by rooms."; r='{"room_ids":[1]}'}
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
    $methodOrder = @{ "GET" = 0; "POST" = 1; "PUT" = 2; "DELETE" = 3 }
    $sortedEndpoints = $defs[$dName] | Sort-Object { $methodOrder[$_.m] }

    foreach ($ep in $sortedEndpoints) {
        $action = $ep.a
        # Find all fixture files matching {action}-*.json
        $fixtureFiles = Get-ChildItem -Path "$FixturesDir/$dName" -Filter "$action-*.json" -ErrorAction SilentlyContinue
        if ($null -eq $fixtureFiles -or $fixtureFiles.Count -eq 0) { continue }

        $endpointCount++
        $authLabel = if ($ep.pub) { "Public." } else { "Auth required." }
        $fullUri = $base + $ep.u

        $lines.Add('**`' + $ep.m + ' ' + $fullUri + '`**')
        $lines.Add("")
        $lines.Add($ep.d + " " + $authLabel)
        $lines.Add("")

        if ($ep.r) {
            $lines.Add("**Request Body:**")
            $lines.Add("")
            $lines.Add('```json')
            $lines.Add($ep.r)
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
