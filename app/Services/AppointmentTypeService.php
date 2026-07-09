<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
use App\Models\Appointment_type;

class AppointmentTypeService
{
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

        LogActivityJob::dispatch('appointment_type', 'appointment type created', get_class($type), $type->id, null, [
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

        LogActivityJob::dispatch('appointment_type', 'appointment type deleted', get_class($type), $type->id, null, [
            'name' => $type->en_name ?? null,
        ], 'deleted');

        return $type->delete();
    }
}
