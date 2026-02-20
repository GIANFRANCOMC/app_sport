<?php

declare(strict_types=1);

namespace App\Services\System\Assets\Assets;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

use App\Models\System\Assets\{Asset};

/**
 * Service class for managing module operations
 * Handles business logic for creating and updating records
 */
class AssetService {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Assets.asset";

    /**
     * Allowed fields for record creation and update
     */
    private const ALLOWED_FIELDS = [
        "internal_code",
        "name",
        "description",
        "status"
    ];

    /**
     * Searchable fields for filtering
     */
    private const SEARCHABLE_FIELDS = [
        "internal_code",
        "name",
        "description"
    ];

    /**
     * Get translation with fallback
     *
     * @param string $key Translation key
     * @param array $replace Replacements
     * @return string
     */
    private static function trans(string $key, array $replace = []): string {

        return TranslationHelper::getWithFallback(self::TRANSLATION_NAMESPACE, $key, $replace);

    }

    /**
     * Prepare data for creation
     *
     * @param array $data Input data
     * @param int $companyId Company
     * @param int $userId User
     * @return array
     */
    private static function prepareAssetDataForCreate(array $data, int $companyId, int $userId): array {

        $assetData = [
            "company_id"      => $companyId,
            "management_type" => "stock",
            "status"          => $data["status"] ?? "active",
            "created_at"      => now(),
            "created_by"      => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field])) {

                $assetData[$field] = $data[$field];

            }

        }

        return $assetData;

    }

    /**
     * Prepare data for update (only changed fields)
     *
     * @param Asset $asset Record instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareAssetDataForUpdate(Asset $asset, array $data): array {

        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field])) {

                if($data[$field] !== $asset->$field) {

                    $updateData[$field] = $data[$field];

                }

            }

        }

        return $updateData;

    }

    /**
     * Create a new record
     *
     * @param array $data Input data
     * @param int|null $userId User creating the record
     * @return Asset|null Created record instance or null on failure
     * @throws Exception
     */
    public static function create(array $data, ?int $userId = null): ?Asset {

        $asset = null;

        DB::transaction(function() use($data, $userId, &$asset) {

            $userAuth  = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {

                throw new Exception(self::trans("company_id_required"));

            }

            $userId = $userId ?? $userAuth->id ?? null;

            // Prepare data with only allowed fields
            $assetData = self::prepareAssetDataForCreate($data, $companyId, $userId);

            // Create the record
            $asset = Asset::create($assetData);

        });

        return $asset;

    }

    /**
     * Update an existing record
     *
     * @param Asset $asset Record instance to update
     * @param array $data Input data
     * @param int|null $userId User updating the record
     * @return Asset Updated record instance
     */
    public static function update(Asset $asset, array $data, ?int $userId = null): Asset {

        DB::transaction(function() use($asset, $data, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id ?? null;

            // Prepare update data with only changed fields
            $updateData = self::prepareAssetDataForUpdate($asset, $data);

            // Only update if there are changes
            if(!empty($updateData)) {

                $updateData["updated_at"] = now();
                $updateData["updated_by"] = $userId;
                $asset->update($updateData);

            }

        });

        return $asset->fresh();

    }

    /**
     * Find record by ID and company ID
     *
     * @param int $id Record
     * @param int $companyId Company
     * @param array|null $statuses Filter by statuses (e.g. ["active"], ["active", "inactive"])
     * @param array $relations Relations to eager load
     * @return Asset|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, ?array $statuses = ["active"], array $relations = []): ?Asset {

        $query = Asset::where("id", $id)
                      ->where("company_id", $companyId);

        if($statuses !== null && !empty($statuses)) {

            $query->whereIn("status", $statuses);

        }

        if($relations !== null && !empty($relations)) {

            $query->with($relations);

        }

        return $query->first();

    }

    /**
     * Get paginated list of records with filters
     *
     * @param int $companyId Company
     * @param array $filters Filter parameters (filter_by, word)
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator {

        $query = Asset::where("company_id", $companyId);

        // Apply filters
        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        if(Utilities::isDefined($word) && Utilities::isDefined($filterBy)) {

            $searchTerm = Utilities::getWordSearch($word);

            if($filterBy === "all") {

                // Search across all searchable fields
                $query->where(function(Builder $q) use($searchTerm) {

                    $searchableFields = self::SEARCHABLE_FIELDS;
                    $firstField       = array_shift($searchableFields);

                    if($firstField) {

                        $q->where($firstField, "like", $searchTerm);

                    }

                    foreach($searchableFields as $field) {

                        $q->orWhere($field, "like", $searchTerm);

                    }

                });

            }elseif(in_array($filterBy, self::SEARCHABLE_FIELDS, true)) {

                // Search in specific field
                $query->where($filterBy, "like", $searchTerm);

            }

        }

        return $query->orderBy("name", "ASC")
                     ->paginate($perPage);

    }

}
