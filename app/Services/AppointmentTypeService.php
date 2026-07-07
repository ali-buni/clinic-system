<?php

namespace App\Services;

use App\Models\Appointment_type;

class AppointmentTypeService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    /**
     * Return list of appointment.
     */
    public function index()
    {
        $q = Appointment_type::query()->orderBy('id', 'desc');

        return $q->get();
    }

    /**
     * Create a new appointment type.
     */
    public function add(array $data): Appointment_type
    {
        $type = Appointment_type::create($data);

        $this->activityLog->log('appointment_type', 'appointment type created', $type, null, [
            'name' => $data['en_name'] ?? null,
        ], 'created');

        return $type;
    }

    /**
     * Delete an appointment type by id.
     */
    public function delete(int $id): bool
    {
        $type = Appointment_type::findOrFail($id);

        $this->activityLog->log('appointment_type', 'appointment type deleted', $type, null, [
            'name' => $type->en_name ?? null,
        ], 'deleted');

        return $type->delete();
    }
}
