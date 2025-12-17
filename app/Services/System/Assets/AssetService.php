<?php

declare(strict_types=1);

namespace App\Services\System\Assets;

use App\Helpers\System\Utilities;
use Illuminate\Support\Facades\DB;

use App\Models\System\Assets\Asset;

/**
 * Service class for managing Asset operations
 * Handles business logic for creating and updating assets
 */
class AssetService {

    /**
     * Get paginated list of assets with filters
     *
     * @param int $companyId Company ID
     * @param array $filters Filters array
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters, int $perPage) {

        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        return Asset::when(Utilities::isDefined($filterBy), function($query) use($filterBy, $word) {

                            $filter = Utilities::getWordSearch($word);

                            if(in_array($filterBy, ["all"])) {

                                $query->where(function($query) use($filter) {

                                    $query->where("internal_code", "like", $filter)
                                          ->orWhere("name", "like", $filter)
                                          ->orWhere("description", "like", $filter);

                                });

                            }else if(in_array($filterBy, ["internal_code", "name", "description"])) {

                                $query->where(function($query) use($filterBy, $filter) {

                                    $query->where($filterBy, "like", $filter);

                                });

                            }

                      })
                      ->where("company_id", $companyId)
                      ->orderBy("name", "ASC")
                      ->paginate($perPage);

    }

    /**
     * Find asset by ID and company
     *
     * @param int $id Asset ID
     * @param int $companyId Company ID
     * @return Asset|null
     */
    public static function findByIdAndCompany(int $id, int $companyId): ?Asset {

        return Asset::where("id", $id)
                    ->where("company_id", $companyId)
                    ->first();

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

        $query = Asset::where("company_id", $companyId)
                      ->where("internal_code", $internalCode);

        if($excludeId) {

            $query->whereNot("id", $excludeId);

        }

        return $query->exists();

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

