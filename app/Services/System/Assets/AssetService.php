<?php

declare(strict_types=1);

namespace App\Services\System\Assets;

use App\Helpers\System\Utilities;
use Illuminate\Support\Facades\DB;

use App\Models\System\Assets\Asset;
use App\Repositories\System\Assets\AssetRepository;

/**
 * Service class for managing Asset operations
 * Handles business logic for creating and updating assets
 */
class AssetService {

    /**
     * @var AssetRepository
     */
    private static $repository;

    /**
     * Get repository instance (lazy initialization)
     *
     * @return AssetRepository
     */
    private static function getRepository(): AssetRepository {

        if(self::$repository === null) {

            self::$repository = new AssetRepository();

        }

        return self::$repository;

    }

    /**
     * Get paginated list of assets with filters
     *
     * @param int $companyId Company ID
     * @param array $filters Filters array
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters, int $perPage) {

        return self::getRepository()->getPaginatedList($companyId, $filters, $perPage);

    }

    /**
     * Find asset by ID and company
     *
     * @param int $id Asset ID
     * @param int $companyId Company ID
     * @param array $relations Relations to eager load
     * @return Asset|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, array $relations = []): ?Asset {

        return self::getRepository()->findByIdAndCompany($id, $companyId, $relations);

    }

    /**
     * Check if internal code exists for company
     *
     * @param string $internalCode Internal code
     * @param int $companyId Company ID
     * @param int|null $excludeId Asset ID to exclude from check
     * @return bool
     */
    public static function internalCodeExists(string $internalCode, int $companyId, ?int $excludeId = null): bool {

        return self::getRepository()->internalCodeExists($internalCode, $companyId, $excludeId);

    }

    /**
     * Create a new asset
     *
     * @param array $data Asset data
     * @param int|null $userId User ID creating the asset
     * @return Asset|null
     */
    public static function create(array $data, ?int $userId = null): ?Asset {

        $asset = null;

        DB::transaction(function() use($data, $userId, &$asset) {

            $asset = new Asset();
            $asset->company_id    = $data["company_id"];
            $asset->internal_code = $data["internal_code"];
            $asset->name          = $data["name"];
            $asset->description   = $data["description"] ?? "";
            $asset->status        = $data["status"];
            $asset->created_at    = now();
            $asset->created_by    = $userId;
            $asset->save();

        });

        return $asset;

    }

    /**
     * Update an existing asset
     *
     * @param Asset $asset Asset instance
     * @param array $data Asset data
     * @param int|null $userId User ID updating the asset
     * @return Asset|null
     */
    public static function update(Asset $asset, array $data, ?int $userId = null): ?Asset {

        DB::transaction(function() use($asset, $data, $userId) {

            $asset->internal_code = $data["internal_code"];
            $asset->name          = $data["name"];
            $asset->description   = $data["description"] ?? "";
            $asset->status        = $data["status"];
            $asset->updated_at    = now();
            $asset->updated_by    = $userId;
            $asset->save();

        });

        return $asset->fresh();

    }

}

