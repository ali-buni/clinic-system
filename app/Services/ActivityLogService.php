<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * @param string $logName
     * @param string $description
     * @param Model|null $subject
     * @param Authenticatable|null $causer
     * @param array $details
     * @param string|null $event
     */
    public function log(
        string $logName,
        string $description,
        ?Model $subject = null,
        ?Authenticatable $causer = null,
        array $details = [],
        ?string $event = null
    ) {
        $user = auth()->user();
        $userId = auth()->id() ?? ($user?->getAuthIdentifier() ?? null);
        $userName = $user->fname ?? 'System';
        $ip = request()->ip() ?? 'System';
        $correlationId = request()->header('X-Correlation-ID');

        $old = null;
        $new = null;
        if ($subject instanceof Model) {
            $old = $subject->getOriginal();
            $new = $subject->getAttributes();
        }

        $properties = array_merge(
            $details,
            [
                'user_id' => $userId,
                'user_name' => $userName,
                'ip' => $ip,
                'correlation_id' => $correlationId,
                'old_value' => $old,
                'new_value' => $new,
            ]
        );

        $log = activity($logName)
            ->performedOn($subject)
            ->causedBy($causer)
            ->withProperties($properties);

        if ($event !== null) {
            $log->event($event);
        }

        $log->log($description);

        $structuredContext = [
            'log_name' => $logName,
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'causer_id' => $causer?->getKey(),
            'description' => $description,
            'properties' => $details,
        ];

        if ($event === 'deleted') {
            Log::channel('structured')->warning($description, $structuredContext);
        } else {
            Log::channel('structured')->info($description, $structuredContext);
        }
    }
}
