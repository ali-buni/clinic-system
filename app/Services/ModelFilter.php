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
     * Supports search, sort, pagination, and multi-column search.
     *
     * Example usages:
     * ModelFilter::filter(new User(), [ 'search' => 'John', 'column' => 'name' ]);
     * ModelFilter::filter(new User(), [ 'search' => 'John', 'column' => 'name,email,phone' ]);
     * ModelFilter::filter(User::query()->whereActive(1), [ 'per_page' => 25 ]);
     * ModelFilter::filter(new User(), [
     *    'search' => 'John',
     *    'column' => 'name,email',
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

        // Handle search with multiple columns
        if ($search && $column) {
            // Split columns by comma and trim whitespace
            $searchColumns = array_map('trim', explode(',', $column));

            // Filter to only valid columns that exist in the table
            $validColumns = array_filter($searchColumns, function ($col) use ($columns) {
                return in_array($col, $columns);
            });

            if (!empty($validColumns)) {
                $query->where(function ($q) use ($validColumns, $search) {
                    foreach ($validColumns as $index => $col) {
                        if ($index === 0) {
                            $q->where($col, 'LIKE', "%{$search}%");
                        } else {
                            $q->orWhere($col, 'LIKE', "%{$search}%");
                        }
                    }
                });
            }
        }

        // Handle sorting (also supports multiple columns)
        $sort = $filters['sort'] ?? 'id';
        $direction = strtolower($filters['direction'] ?? 'asc');
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        // Check if sort contains multiple columns (comma-separated)
        if (str_contains($sort, ',')) {
            $sortColumns = array_map('trim', explode(',', $sort));

            foreach ($sortColumns as $sortColumn) {
                if (in_array($sortColumn, $columns)) {
                    $query->orderBy($sortColumn, $direction);
                }
            }
        } else {
            // Single column sort
            if (in_array($sort, $columns)) {
                $query->orderBy($sort, $direction);
            }
        }

        return $query->paginate(
            perPage: $filters['per_page'] ?? 15,
            page: $filters['page'] ?? null
        );
    }
}
