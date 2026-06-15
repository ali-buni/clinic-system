<?php

namespace App\Services;

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
     *
     * @param array $data
     * @return Appointment_type
     */
    public function add(array $data): Appointment_type
    {
        return Appointment_type::create($data);
    }

    /**
     * Delete an appointment type by id.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $type = Appointment_type::findOrFail($id);
        return $type->delete();
    }
}
