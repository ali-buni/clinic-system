<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\Item;

class ItemObserver
{
    public function created(Item $item): void
    {
        LogActivityJob::dispatch(
            'item',
            'created item',
            get_class($item),
            $item->id,
            auth()->id(),
            ['item_name' => $item->item_name, 'clinic_id' => $item->clinic_id],
            'created'
        );
    }

    public function updated(Item $item): void
    {
        $changes = $item->getChanges();
        unset($changes['updated_at']);

        LogActivityJob::dispatch(
            'item',
            'updated item',
            get_class($item),
            $item->id,
            auth()->id(),
            ['changed_fields' => array_keys($changes)],
            'updated'
        );
    }

    public function deleted(Item $item): void
    {
        LogActivityJob::dispatch(
            'item',
            'deleted item',
            get_class($item),
            $item->id,
            auth()->id(),
            [],
            'deleted'
        );
    }
}
