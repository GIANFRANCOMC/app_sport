<?php

declare(strict_types=1);

namespace App\Repositories\System\Catalogs\Items;

use App\Models\System\Catalogs\Item;
use App\Repositories\System\Base\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Repository class for Item data access
 * Implements Repository Pattern for centralized query logic
 */
class ItemRepository extends BaseRepository {

    /**
     * Get the model class name
     *
     * @return string
     */
    protected static function getModelClass(): string {

        return Item::class;

    }

    /**
     * Get searchable fields for filtering
     *
     * @return array
     */
    protected static function getSearchableFields(): array {

        return [
            "internal_code",
            "name",
            "description",
            "price"
        ];

    }

    /**
     * Get paginated list of items with filters by type
     *
     * @param int $companyId Company ID
     * @param string $type Item type (product, service, subscription)
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @param array $relations Relations to eager load
     * @param string $orderBy Order by field
     * @param string $orderDirection Order direction
     * @return LengthAwarePaginator
     */
    public function getPaginatedListByType(
        int $companyId,
        string $type,
        array $filters = [],
        int $perPage = 15,
        array $relations = ["currency", "categoryItems"],
        string $orderBy = "name",
        string $orderDirection = "ASC"
    ): LengthAwarePaginator {

        $query = static::query()
                       ->where("company_id", $companyId)
                       ->where("type", $type);

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
     * Get paginated list of items with filters
     * Uses BaseRepository with item-specific defaults
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
        array $relations = ["currency", "categoryItems"],
        string $orderBy = "name",
        string $orderDirection = "ASC"
    ): LengthAwarePaginator {

        return parent::getPaginatedList($companyId, $filters, $perPage, $relations, $orderBy, $orderDirection);

    }

    /**
     * Check if internal code exists for company
     *
     * @param string $internalCode Internal code
     * @param int $companyId Company ID
     * @param int|null $excludeId Item ID to exclude (for updates)
     * @return bool
     */
    public function internalCodeExists(string $internalCode, int $companyId, ?int $excludeId = null): bool {

        return $this->fieldExists("internal_code", $internalCode, $companyId, $excludeId);

    }

}

