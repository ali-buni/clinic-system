<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class ModelFilter
{
     /**
     * Filter a model based on the provided filters.
     *
     * e.a. search, sort, pagination, etc.
     *
     * example usage:
     * ModelFilter::filter(new User(), [
     *    'search' => 'John',
     *    'column' => 'name',
     *    'sort' => 'created_at',
     *    'direction' => 'desc',
     *    'per_page' => 15,
     *    'page' => 1
     * ]);
     * @param Model|Builder $queryOrModel
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public static function filter(
        $queryOrModel,
        array $filters = []
    ): LengthAwarePaginator {
        $query = $queryOrModel instanceof Builder ? $queryOrModel : $queryOrModel->newQuery();

        $table = $query->getModel()->getTable();
        $columns = Schema::getColumnListing($table);

        $search = $filters['search'] ?? null;
        $column = $filters['column'] ?? null;

        if ($search && $column && in_array($column, $columns)) {
            $query->where($column, 'LIKE', "%{$search}%");
        }

        $sort = $filters['sort'] ?? 'id';
        $direction = strtolower($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $columns)) {
            $query->orderBy($sort, $direction);
        }

        return $query->paginate(
            perPage: $filters['per_page'] ?? 15,
            page: $filters['page'] ?? null
        );
    }
}
