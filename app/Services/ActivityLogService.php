<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

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
        // $user = auth()->user();
        // $userId = auth()->id() ?? ($user->getAuthIdentifier() ?? null);
        // $userName = $user->fname ?? 'System';
        // $ip = request()->ip() ?? 'System';

        // $old = null;
        // $new = null;
        // if ($subject instanceof Model) {
        //     $old = $subject->getOriginal();
        //     $new = $subject->getAttributes();
        // }

        // $log = activity($logName)
        //     ->performedOn($subject)
        //     ->causedBy($causer)
        //     ->withProperties(array_merge(
        //         $details,
        //         [
        //             'user_id' => $userId,
        //             'user_name' => $userName,
        //             'ip' => $ip,
        //             'old_value' => $old,
        //             'new_value' => $new,
        //         ]
        //     ));

        // if ($event !== null) {
        //     $log->event($event);
        // }

        // $log->log($description);
    }
}
