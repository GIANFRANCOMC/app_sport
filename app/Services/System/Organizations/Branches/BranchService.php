<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Branches;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};

use App\Services\System\Organizations\Branches\SerieService;
use App\Services\System\Warehouses\WarehouseService;
use App\Repositories\System\Organizations\BranchRepository;
use App\Models\System\Organizations\Branch;


/**
 * Service class for managing Branch operations
 * Handles business logic for creating and updating branches
 */
class BranchService {

    /**
     * Translation namespace for branch module
     */
    private const TRANSLATION_NAMESPACE = "System.Organizations.branch";

    /**
     * Allowed fields for branch creation and update
     */
    private const ALLOWED_FIELDS = [
        "internal_code",
        "name",
        "address",
        "reference",
        "telephone",
        "email",
        "capacity",
        "map_url",
        "status"
    ];

    /**
     * @var BranchRepository
     */
    private static $repository;

    /**
     * Get repository instance (lazy initialization)
     *
     * @return BranchRepository
     */
    private static function getRepository(): BranchRepository {

        if(self::$repository === null) {

            self::$repository = new BranchRepository();

        }

        return self::$repository;

    }

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
     * Prepare branch data for creation
     *
     * @param array $data Input data
     * @param int $companyId Company ID
     * @param int $userId User ID
     * @return array
     */
    private static function prepareBranchDataForCreate(array $data, int $companyId, int $userId): array {

        $branchData = [
            "company_id" => $companyId,
            "status"      => $data["status"] ?? "active",
            "created_at"  => now(),
            "created_by"  => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {

            $branchData[$field] = $data[$field] ?? null;

        }

        return $branchData;

    }

    /**
     * Prepare branch data for update (only changed fields)
     *
     * @param Branch $branch Branch instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareBranchDataForUpdate(Branch $branch, array $data): array {

        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field]) && $data[$field] !== $branch->$field) {

                $updateData[$field] = $data[$field];

            }

        }

        return $updateData;

    }

    /**
     * Create a new branch with related series and warehouse
     *
     * @param array $data Branch data from request
     * @param int|null $userId User ID creating the branch
     * @return Branch|null Created branch instance or null on failure
     * @throws \Exception
     */
    public static function create(array $data, ?int $userId = null): ?Branch {

        $branch = null;

        DB::transaction(function() use($data, $userId, &$branch) {

            $userAuth  = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {

                throw new Exception(self::trans("company_id_required"));

            }

            $userId = $userId ?? $userAuth->id;

            // Prepare branch data with only allowed fields
            $branchData = self::prepareBranchDataForCreate($data, $companyId, $userId);

            // Create the branch
            $branch = Branch::create($branchData);

            // Create related series for document types
            SerieService::createForBranch($branch->id, $companyId, $userId);

            // Create default warehouse
            WarehouseService::createDefaultForBranch($branch->id, $branch->name, $userId);

        });

        return $branch;

    }

    /**
     * Update an existing branch and related warehouses
     *
     * @param Branch $branch Branch instance to update
     * @param array $data Updated branch data
     * @param int|null $userId User ID updating the branch
     * @return Branch Updated branch instance
     */
    public static function update(Branch $branch, array $data, ?int $userId = null): Branch {

        DB::transaction(function() use($branch, $data, $userId) {

            $userAuth = Auth::user();
            $userId = $userId ?? $userAuth->id;

            // Prepare update data with only changed fields
            $updateData = self::prepareBranchDataForUpdate($branch, $data);
            $nameChanged = isset($updateData["name"]);

            // Only update if there are changes
            if(!empty($updateData)) {

                $updateData["updated_at"] = now();
                $updateData["updated_by"] = $userId;
                $branch->update($updateData);

                // Update related warehouses names if branch name changed
                if($nameChanged) {

                    WarehouseService::updateNamesForBranch($branch->fresh(), $userId);

                }

            }

        });

        return $branch->fresh(['warehousesAll']);

    }

    /**
     * Find branch by ID and company ID
     *
     * @param int $id Branch ID
     * @param int $companyId Company ID
     * @param array $relations Relations to eager load
     * @return Branch|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, array $relations = ["warehousesAll"]): ?Branch {

        return self::getRepository()->findByIdAndCompany($id, $companyId, $relations);

    }

    /**
     * Get paginated list of branches
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15) {

        return self::getRepository()->getPaginatedList($companyId, $filters, $perPage);

    }

    /**
     * Check if internal code exists
     *
     * @param string $internalCode Internal code
     * @param int $companyId Company ID
     * @param int|null $excludeId Branch ID to exclude
     * @return bool
     */
    public static function internalCodeExists(string $internalCode, int $companyId, ?int $excludeId = null): bool {

        return self::getRepository()->internalCodeExists($internalCode, $companyId, $excludeId);

    }

}

