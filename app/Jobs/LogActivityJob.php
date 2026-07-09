<?php

namespace App\Jobs;

use App\Services\ActivityLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public readonly string $logName,
        public readonly string $description,
        public readonly string $subjectType,
        public readonly int $subjectId,
        public readonly ?int $causerId,
        public readonly array $extra = [],
        public readonly string $eventName = 'updated',
    ) {}

    public function handle(ActivityLogService $activityLog): void
    {
        $subject = $this->subjectType::find($this->subjectId);
        if (! $subject) {
            return;
        }

        $causer = $this->causerId ? \App\Models\User::find($this->causerId) : null;

        $activityLog->log(
            $this->logName,
            $this->description,
            $subject,
            $causer,
            $this->extra,
            $this->eventName,
        );
    }
}
