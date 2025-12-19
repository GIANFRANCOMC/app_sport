<?php

declare(strict_types=1);

namespace App\Repositories\System\Assets;

use App\Models\System\Assets\Asset;
use App\Repositories\System\Base\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Repository class for Asset data access
 * Implements Repository Pattern for centralized query logic
 */
class AssetRepository extends BaseRepository {

    /**
     * Get the model class name
     *
     * @return string
     */
    protected static function getModelClass(): string {

        return Asset::class;

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
            "description"
        ];

    }

    /**
     * Get paginated list of assets with filters
     * Uses BaseRepository with asset-specific defaults
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
     * @param int|null $excludeId Asset ID to exclude (for updates)
     * @return bool
     */
    public function internalCodeExists(string $internalCode, int $companyId, ?int $excludeId = null): bool {

        return $this->fieldExists("internal_code", $internalCode, $companyId, $excludeId);

    }

}

