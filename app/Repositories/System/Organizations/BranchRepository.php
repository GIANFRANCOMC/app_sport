<?php

declare(strict_types=1);

namespace App\Repositories\System\Organizations;

use App\Helpers\System\Utilities;
use App\Models\System\Organizations\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository class for Branch data access
 * Implements Repository Pattern for centralized query logic
 */
class BranchRepository {

    /**
     * Searchable fields for filtering
     */
    private const SEARCHABLE_FIELDS = [
        "internal_code",
        "name",
        "address",
        "reference",
        "telephone",
        "email"
    ];

    /**
     * Get paginated list of branches with filters
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator {

        $query = Branch::query()
                       ->where("company_id", $companyId)
                       ->with(["series.documentType", "warehouses"]);

        // Apply search filters
        $this->applySearchFilters($query, $filters);

        // Apply ordering
        $query->orderBy("name", "ASC");

        return $query->paginate($perPage);

    }

    /**
     * Apply search filters to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    private function applySearchFilters($query, array $filters): void {

        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        if(!Utilities::isDefined($filterBy) || !Utilities::isDefined($word)) {

            return;

        }

        $searchTerm = Utilities::getWordSearch($word);

        if($filterBy === "all") {

            // Search across all searchable fields
            $query->where(function($q) use($searchTerm) {

                foreach(self::SEARCHABLE_FIELDS as $field) {

                    $q->orWhere($field, "like", $searchTerm);

                }

            });

        }elseif(in_array($filterBy, self::SEARCHABLE_FIELDS, true)) {

            // Search in specific field
            $query->where($filterBy, "like", $searchTerm);

        }

    }

    /**
     * Find branch by ID and company ID
     *
     * @param int $id Branch ID
     * @param int $companyId Company ID
     * @param array $relations Relations to eager load
     * @return Branch|null
     */
    public function findByIdAndCompany(int $id, int $companyId, array $relations = []): ?Branch {

        $query = Branch::where("id", $id)
                       ->where("company_id", $companyId);

        if(!empty($relations)) {

            $query->with($relations);

        }

        return $query->first();

    }

    /**
     * Get all branches for a company
     *
     * @param int $companyId Company ID
     * @param string $type Filter type
     * @param array $relations Relations to eager load
     * @return Collection
     */
    public function getAllByCompany(int $companyId, string $type = "default", array $relations = []): Collection {

        $query = Branch::where("company_id", $companyId);

        // Apply type filters
        $this->applyTypeFilters($query, $type);

        if(!empty($relations)) {

            $query->with($relations);

        }

        return $query->get();

    }

    /**
     * Apply type-based filters
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return void
     */
    private function applyTypeFilters($query, string $type): void {

        $typeFilters = [
            "sale" => ["active"],
            // Add more type filters as needed
        ];

        if(isset($typeFilters[$type])) {

            $query->whereIn("status", $typeFilters[$type]);

        }

    }

    /**
     * Check if internal code exists for company
     *
     * @param string $internalCode Internal code
     * @param int $companyId Company ID
     * @param int|null $excludeId Branch ID to exclude (for updates)
     * @return bool
     */
    public function internalCodeExists(string $internalCode, int $companyId, ?int $excludeId = null): bool {

        $query = Branch::where("company_id", $companyId)
                       ->where("internal_code", $internalCode);

        if($excludeId !== null) {

            $query->where("id", "!=", $excludeId);

        }

        return $query->exists();

    }

}
