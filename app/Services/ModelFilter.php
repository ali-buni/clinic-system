<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ModelFilter
{
    /**
     * Filter a model or query builder based on the provided filters.
     *
     * Supports search, sort, pagination, etc.
     *
     * Example usages:
     * ModelFilter::filter(new User(), [ 'search' => 'John', 'column' => 'name' ]);
     * ModelFilter::filter(User::query()->whereActive(1), [ 'per_page' => 25 ]);
     *      * ModelFilter::filter(new User(), [
     *    'search' => 'John',
     *    'column' => 'name',
     *    'sort' => 'created_at',
     *    'direction' => 'desc',
     *    'per_page' => 15,
     *    'page' => 1
     * ]);
     *
     * @param Model|Builder $model  Model instance or Eloquent query builder
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public static function filter(
        Model|Builder $model,
        array $filters = []
    ): LengthAwarePaginator {
        $query = $model instanceof Model
            ? $model->newQuery()
            : $model;

        $table = $model instanceof Model
            ? $model->getTable()
            : $model->getModel()->getTable();

        // Get all columns dynamically from table
        $columns = Schema::getColumnListing($table);

        $search = $filters['search'] ?? null;
        $column = $filters['column'] ?? null;

        if (
            $search &&
            $column &&
            in_array($column, $columns)
        ) {
            $query->where(
                $column,
                'LIKE',
                "%{$search}%"
            );
        }

        $sort = $filters['sort'] ?? 'id';
        $direction = strtolower($filters['direction'] ?? 'asc');

        $direction = $direction == 'desc'
            ? 'desc'
            : 'asc';

        if (in_array($sort, $columns)) {
            $query->orderBy($sort, $direction);
        }

        return $query->paginate(
            perPage: $filters['per_page'] ?? 15,
            page: $filters['page'] ?? null
        );
    }
}
