<?php

namespace App\Observers;

use App\Models\Item;
use App\Services\ActivityLogService;

class ItemObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(Item $item): void
    {
        $this->activityLog->log(
            'item',
            'created item',
            $item,
            auth()->user(),
            ['item_name' => $item->item_name, 'clinic_id' => $item->clinic_id],
            'created'
        );
    }

    public function updated(Item $item): void
    {
        $changes = $item->getChanges();
        unset($changes['updated_at']);

        $this->activityLog->log(
            'item',
            'updated item',
            $item,
            auth()->user(),
            ['changed_fields' => array_keys($changes)],
            'updated'
        );
    }

    public function deleted(Item $item): void
    {
        $this->activityLog->log(
            'item',
            'deleted item',
            $item,
            auth()->user(),
            [],
            'deleted'
        );
    }
}
