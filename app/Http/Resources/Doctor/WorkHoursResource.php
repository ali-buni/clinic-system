<?php

namespace App\Http\Resources\Doctor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkHoursResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'doctor' => [
                'id' => $this->doctor?->id,
                'name' => $this->getDoctorName(),
            ],
            'day_of_week' => $this->day_of_week,
            'day_name' => $this->getDayName(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'break_start' => $this->break_start,
            'break_end' => $this->break_end,
            'max_patients_per_day' => $this->max_patients_per_day,
            'duration_minutes' => $this->getDuration(),
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }

    /**
     * Get the day name from day_of_week number.
     */
    protected function getDayName(): string
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return $days[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Calculate total working duration in minutes.
     */
    protected function getDuration(): int
    {
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);

        return ($end - $start) / 60;
    }

    /**
     * Get doctor's full name.
     */
    protected function getDoctorName(): string
    {
        if (!$this->doctor?->user) {
            return 'Unknown Doctor';
        }

        return $this->doctor->user->fname . ' ' . $this->doctor->user->lname;
    }
}
