<?php

namespace App\Http\Resources\Schedule;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleOverrideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'doctor'         => [
                'id'   => $this->doctor?->id,
                'name' => $this->doctor?->user ? $this->doctor->user->fname . ' ' . $this->doctor->user->lname : 'Unknown',
            ],
            'override_date'  => $this->override_date,
            'override_type'  => $this->override_type,
            'start_time'     => Carbon::parse($this->start_time)?->format('H:i'),
            'end_time'       => Carbon::parse($this->end_time)?->format('H:i'),
            'reason'         => $this->reason,
            'is_closed'      => $this->is_closed,
            'created_at'     => Carbon::parse($this->created_at)?->format('Y-m-d'),
        ];
    }
}
