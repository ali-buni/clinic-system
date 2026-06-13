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
     * Supports search, sort, pagination, multi-column search, and relationship columns.
     *
     * Example usages:
     * ModelFilter::filter(new User(), [ 'search' => 'John', 'column' => 'name' ]);
     * ModelFilter::filter(new User(), [ 'search' => 'John', 'column' => 'name,email,phone' ]);
     * ModelFilter::filter(User::query()->whereActive(1), [ 'per_page' => 25 ]);
     * ModelFilter::filter(new Appointment(), [
     *    'search' => 'John',
     *    'column' => 'doctor.name,patient.fname', // Relationship columns
     *    'sort' => 'doctor.id',
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

        $baseModel = $model instanceof Model
            ? $model
            : $model->getModel();

        $table = $baseModel->getTable();

        // Get all columns dynamically from base table
        $baseColumns = Schema::getColumnListing($table);

        $search = $filters['search'] ?? null;
        $column = $filters['column'] ?? null;

        // Handle search with multiple columns (including relationships)
        if ($search && $column) {
            // Split columns by comma and trim whitespace
            $searchColumns = array_map('trim', explode(',', $column));

            $query->where(function ($q) use ($searchColumns, $search, $baseColumns, $baseModel) {
                foreach ($searchColumns as $searchColumn) {
                    // Check if it's a relationship column (contains dot)
                    if (str_contains($searchColumn, '.')) {
                        [$relation, $relationColumn] = explode('.', $searchColumn, 2);

                        // Check if relation exists
                        if (method_exists($baseModel, $relation)) {
                            $q->orWhereHas($relation, function ($relQuery) use ($relationColumn, $search) {
                                $relQuery->where($relationColumn, 'LIKE', "%{$search}%");
                            });
                        }
                    } else {
                        // Regular column - check if exists in base table
                        if (in_array($searchColumn, $baseColumns)) {
                            $q->orWhere($searchColumn, 'LIKE', "%{$search}%");
                        }
                    }
                }
            });
        }


        // Handle sorting (also supports multiple columns)
        $sort = $filters['sort'] ?? 'id';
        $direction = strtolower($filters['direction'] ?? 'asc');
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        // Check if sort contains multiple columns (comma-separated)
        if (str_contains($sort, ',')) {
            $sortColumns = array_map('trim', explode(',', $sort));

            foreach ($sortColumns as $sortColumn) {
                if (in_array($sortColumn, $baseColumns)) {
                    $query->orderBy($sortColumn, $direction);
                }
            }
        } else {
            // Single column sort
            if (in_array($sort, $baseColumns)) {
                $query->orderBy($sort, $direction);
            }
        }

        return $query->paginate(
            perPage: $filters['per_page'] ?? 15,
            page: $filters['page'] ?? null
        );
    }
}
