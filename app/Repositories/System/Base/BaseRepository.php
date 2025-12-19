<?php

declare(strict_types=1);

namespace App\Repositories\System\Base;

use App\Helpers\System\Utilities;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Repository Class
 * Provides common functionality for all repository classes
 */
abstract class BaseRepository {

    /**
     * Get the model class name
     * Must be defined in child classes
     *
     * @return string
     */
    abstract protected static function getModelClass(): string;

    /**
     * Get searchable fields
     * Override in child classes if needed
     *
     * @return array
     */
    protected static function getSearchableFields(): array {

        return [];

    }

    /**
     * Get base query builder
     *
     * @return Builder
     */
    protected static function query(): Builder {

        $modelClass = static::getModelClass();

        return $modelClass::query();

    }

    /**
     * Find by ID and company ID
     *
     * @param int $id Record ID
     * @param int $companyId Company ID
     * @param array $relations Relations to eager load
     * @return Model|null
     */
    public function findByIdAndCompany(int $id, int $companyId, array $relations = []): ?Model {

        $query = static::query()
                       ->where("id", $id)
                       ->where("company_id", $companyId);

        if(!empty($relations)) {

            $query->with($relations);

        }

        return $query->first();

    }

    /**
     * Get all records for company
     *
     * @param int $companyId Company ID
     * @param string $type Filter type
     * @param array $relations Relations to eager load
     * @return Collection
     */
    public function getAllByCompany(int $companyId, string $type = "default", array $relations = []): Collection {

        $query = static::query()
                       ->where("company_id", $companyId);

        // Apply type filters
        $this->applyTypeFilters($query, $type);

        if(!empty($relations)) {

            $query->with($relations);

        }

        return $query->get();

    }

    /**
     * Get paginated list with filters
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @param array $relations Relations to eager load
     * @param string $orderBy Order by field
     * @param string $orderDirection Order direction
     * @return LengthAwarePaginator
     */
    public function getPaginatedList(
        int $companyId,
        array $filters = [],
        int $perPage = 15,
        array $relations = [],
        string $orderBy = "id",
        string $orderDirection = "DESC"
    ): LengthAwarePaginator {

        $query = static::query()
                       ->where("company_id", $companyId);

        // Apply search filters
        $this->applySearchFilters($query, $filters);

        // Apply relations
        if(!empty($relations)) {

            $query->with($relations);

        }

        // Apply ordering
        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($perPage);

    }

    /**
     * Apply search filters to query
     *
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    protected function applySearchFilters(Builder $query, array $filters): void {

        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        if(!Utilities::isDefined($filterBy) || !Utilities::isDefined($word)) {

            return;

        }

        $searchableFields = static::getSearchableFields();

        if(empty($searchableFields)) {

            return;

        }

        $searchTerm = Utilities::getWordSearch($word);

        if($filterBy === "all") {

            // Search across all searchable fields
            $query->where(function($q) use($searchTerm, $searchableFields) {

                foreach($searchableFields as $field) {

                    $q->orWhere($field, "like", $searchTerm);

                }

            });

        }elseif(in_array($filterBy, $searchableFields, true)) {

            // Search in specific field
            $query->where($filterBy, "like", $searchTerm);

        }

    }

    /**
     * Apply type-based filters
     * Override in child classes for specific type filters
     *
     * @param Builder $query
     * @param string $type
     * @return void
     */
    protected function applyTypeFilters(Builder $query, string $type): void {

        // Default implementation - override in child classes
        $typeFilters = [
            "active" => ["active"],
            // Add more type filters as needed
        ];

        if(isset($typeFilters[$type])) {

            $query->whereIn("status", $typeFilters[$type]);

        }

    }

    /**
     * Check if field value exists for company
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param int $companyId Company ID
     * @param int|null $excludeId Record ID to exclude (for updates)
     * @return bool
     */
    public function fieldExists(string $field, $value, int $companyId, ?int $excludeId = null): bool {

        $query = static::query()
                       ->where("company_id", $companyId)
                       ->where($field, $value);

        if($excludeId !== null) {

            $query->where("id", "!=", $excludeId);

        }

        return $query->exists();

    }

}

