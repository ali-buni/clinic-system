<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemService
{
    public function index(array $filters): LengthAwarePaginator
    {
        $query = Item::query();

        if (! empty($filters['item_name'])) {
            $query->where('item_name', 'like', '%' . $filters['item_name'] . '%');
        }

        if (isset($filters['clinic_id'])) {
            $clinicId = (int) $filters['clinic_id'];

            $query->where(function ($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId)
                    ->orWhereNull('clinic_id');
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->orderBy('item_name')->paginate(
            perPage: $perPage,
            page: $filters['page'] ?? null
        );
    }

    public function create(array $data): Item
    {
        return Item::create($data);
    }

    public function delete(Item $item, ?int $ownerClinicId, bool $isAdmin): bool
    {
        if ($isAdmin) {
            return (bool) $item->delete();
        }

        if ($item->clinic_id === null) {
            return false;
        }

        if ($ownerClinicId === null || $ownerClinicId !== $item->clinic_id) {
            return false;
        }

        return (bool) $item->delete();
    }
}
