<?php

declare(strict_types=1);

namespace App\Services\System\Assets;

use App\Helpers\System\Utilities;
use App\Models\System\Assets\{AssetAssignment, BranchAsset};
use App\Models\System\Organizations\{Branch};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service class for managing Asset Management operations
 * Handles business logic for asset assignments to branches and users
 */
class AssetManagementService {

    /**
     * Get paginated list of branch assets
     *
     * @param int $branchId Branch ID
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public static function getBranchAssetsList(int $branchId, int $perPage): LengthAwarePaginator {

        $branchAssets = BranchAsset::where("branch_id", $branchId)
                                   ->with(["asset"])
                                   ->paginate($perPage);

        return $branchAssets;

    }

    /**
     * Assign assets to a branch
     *
     * @param int $branchId Branch ID
     * @param array $branchAssets Array of branch assets data
     * @param int $companyId Company ID
     * @param int|null $userId User ID performing the action
     * @return array Information about success and error counters
     */
    public static function assignAssetsToBranch(int $branchId, array $branchAssets, int $companyId, ?int $userId = null): array {

        $information = [
            "success" => [
                "counter" => 0,
                "data"    => []
            ],
            "error" => [
                "counter" => 0,
                "data"    => []
            ]
        ];

        $branch = Branch::where("id", $branchId)
                        ->where("company_id", $companyId)
                        ->with("company")
                        ->first();

        if(!$branch) {

            return $information;

        }

        $company = $branch->company;

        DB::transaction(function() use($branchId, $branchAssets, $company, $userId, &$information) {

            foreach($branchAssets as $record) {

                $branchAsset = BranchAsset::where("branch_id", $branchId)
                                          ->where("asset_id", $record["asset_id"])
                                          ->first();

                if(!Utilities::isDefined($branchAsset)) {

                    $branchAsset = new BranchAsset();
                    $branchAsset->branch_id         = $branchId;
                    $branchAsset->asset_id          = $record["asset_id"];
                    $branchAsset->currency_id       = $company->currency_id;
                    $branchAsset->quantity          = $record["quantity"];
                    $branchAsset->acquisition_value = 0;
                    $branchAsset->acquisition_date  = null;
                    $branchAsset->note              = null;
                    $branchAsset->status            = "active";
                    $branchAsset->created_at         = now();
                    $branchAsset->created_by        = $userId;
                    $branchAsset->save();

                    $information["success"]["counter"]++;
                    $information["success"]["data"][] = ["asset_id" => $record["asset_id"]];

                }else {

                    if(Utilities::isDefined($branchAsset) && in_array($branchAsset->status, ["retired"])) {

                        $branchAsset->currency_id       = $company->currency_id;
                        $branchAsset->quantity          = $record["quantity"];
                        $branchAsset->acquisition_value = 0;
                        $branchAsset->acquisition_date  = null;
                        $branchAsset->note              = null;
                        $branchAsset->status            = "active";
                        $branchAsset->updated_at        = now();
                        $branchAsset->updated_by         = $userId;
                        $branchAsset->save();

                        $information["success"]["counter"]++;
                        $information["success"]["data"][] = ["asset_id" => $record["asset_id"]];

                    }else {

                        $information["error"]["counter"]++;
                        $information["error"]["data"][] = ["asset_id" => $record["asset_id"]];

                    }

                }

            }

        });

        return $information;

    }

    /**
     * Unassign assets from a branch
     *
     * @param int $branchId Branch ID
     * @param array $branchAssets Array of branch assets data
     * @param int|null $userId User ID performing the action
     * @return array Information about success and error counters
     */
    public static function unassignAssetsFromBranch(int $branchId, array $branchAssets, ?int $userId = null): array {

        $information = [
            "success" => [
                "counter" => 0,
                "data"    => []
            ],
            "error" => [
                "counter" => 0,
                "data"    => []
            ]
        ];

        DB::transaction(function() use($branchId, $branchAssets, $userId, &$information) {

            foreach($branchAssets as $record) {

                $branchAsset = BranchAsset::where("id", $record["id"])
                                          ->where("branch_id", $branchId)
                                          ->where("asset_id", $record["asset_id"])
                                          ->whereIn("status", ["active", "maintenance"])
                                          ->first();

                if(Utilities::isDefined($branchAsset)) {

                    $branchAsset->status     = "retired";
                    $branchAsset->updated_at = now();
                    $branchAsset->updated_by = $userId;
                    $branchAsset->save();

                    $information["success"]["counter"]++;
                    $information["success"]["data"][] = ["asset_id" => $record["asset_id"]];

                }else {

                    $information["error"]["counter"]++;
                    $information["error"]["data"][] = ["asset_id" => $record["asset_id"]];

                }

            }

        });

        return $information;

    }

    /**
     * Update asset in branch
     *
     * @param int $branchId Branch ID
     * @param int $branchAssetId Branch Asset ID
     * @param int $assetId Asset ID
     * @param array $data Asset data
     * @param int|null $userId User ID performing the action
     * @return BranchAsset|null
     */
    public static function updateAssetInBranch(int $branchId, int $branchAssetId, int $assetId, array $data, ?int $userId = null): ?BranchAsset {

        $branchAsset = BranchAsset::where("id", $branchAssetId)
                                  ->where("branch_id", $branchId)
                                  ->where("asset_id", $assetId)
                                  ->whereIn("status", ["active", "maintenance"])
                                  ->first();

        if(!Utilities::isDefined($branchAsset)) {

            return null;

        }

        DB::transaction(function() use($branchAsset, $data, $userId) {

            $branchAsset->quantity          = $data["quantity"] ?? 0;
            $branchAsset->acquisition_value = $data["acquisition_value"] ?? 0;
            $branchAsset->acquisition_date  = $data["acquisition_date"] ?? null;
            $branchAsset->note              = $data["note"] ?? null;
            $branchAsset->updated_at        = now();
            $branchAsset->updated_by        = $userId;
            $branchAsset->save();

        });

        return $branchAsset->fresh();

    }

    /**
     * Get asset assignments for a branch asset
     *
     * @param int $branchId Branch ID
     * @param int $assetId Asset ID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAssetAssignments(int $branchId, int $assetId) {

        return AssetAssignment::where("branch_id", $branchId)
                              ->where("asset_id", $assetId)
                              ->whereIn("status", ["active", "maintenance"])
                              ->with(["user"])
                              ->get();

    }

    /**
     * Assign asset to users
     *
     * @param int $branchId Branch ID
     * @param int $branchAssetId Branch Asset ID
     * @param int $assetId Asset ID
     * @param array $assignments Array of assignments data
     * @param int|null $userId User ID performing the action
     * @return array Information about success and error counters
     */
    public static function assignAssetToUsers(int $branchId, int $branchAssetId, int $assetId, array $assignments, ?int $userId = null): array {

        $information = [
            "success" => [
                "counter" => 0,
                "data"    => []
            ],
            "error" => [
                "counter" => 0,
                "data"    => []
            ]
        ];

        $branchAsset = BranchAsset::where("id", $branchAssetId)
                                  ->where("branch_id", $branchId)
                                  ->where("asset_id", $assetId)
                                  ->whereIn("status", ["active", "maintenance"])
                                  ->first();

        if(!Utilities::isDefined($branchAsset)) {

            return $information;

        }

        $assetQuantity = floatval($branchAsset->quantity);
        $totalQuantity = array_reduce($assignments, function($accumulator, $currentValue) {

            return $accumulator + floatval($currentValue["quantity"] ?? 0);

        }, 0);

        if($totalQuantity > $assetQuantity) {

            return $information;

        }

        DB::transaction(function() use($branchAsset, $assignments, $userId, &$information) {

            foreach($assignments as $record) {

                $data = [
                    "user_id"           => $record["user_id"],
                    "branch_id"         => $branchAsset->branch_id,
                    "asset_id"          => $branchAsset->asset_id,
                    "currency_id"       => $branchAsset->currency_id,
                    "quantity"          => $record["quantity"] ?? 0,
                    "acquisition_value" => 0,
                    "acquisition_date"  => null,
                    "note"              => null,
                    "status"            => "active",
                    "updated_at"        => now(),
                    "updated_by"        => $userId
                ];

                if(is_numeric($record["id"] ?? null)) {

                    $assetAssignment = "check";

                }else {

                    $data["created_at"] = now();
                    $data["created_by"] = $userId;

                    $assetAssignment = AssetAssignment::create($data);

                }

                if(Utilities::isDefined($assetAssignment)) {

                    $information["success"]["counter"]++;
                    $information["success"]["data"][] = ["asset_id" => $branchAsset->asset_id];

                }else {

                    $information["error"]["counter"]++;
                    $information["error"]["data"][] = ["asset_id" => $branchAsset->asset_id];

                }

            }

        });

        return $information;

    }

    /**
     * Unassign asset from users
     *
     * @param int $branchId Branch ID
     * @param int $branchAssetId Branch Asset ID
     * @param int $assetId Asset ID
     * @param array $assignments Array of assignments data
     * @param int|null $userId User ID performing the action
     * @return array Information about success and error counters
     */
    public static function unassignAssetFromUsers(int $branchId, int $branchAssetId, int $assetId, array $assignments, ?int $userId = null): array {

        $information = [
            "success" => [
                "counter" => 0,
                "data"    => []
            ],
            "error" => [
                "counter" => 0,
                "data"    => []
            ]
        ];

        $branchAsset = BranchAsset::where("id", $branchAssetId)
                                  ->where("branch_id", $branchId)
                                  ->where("asset_id", $assetId)
                                  ->whereIn("status", ["active", "maintenance"])
                                  ->first();

        if(!Utilities::isDefined($branchAsset)) {

            return $information;

        }

        DB::transaction(function() use($branchAsset, $assignments, $userId, &$information) {

            foreach($assignments as $record) {

                $assetAssignment = AssetAssignment::where("id", $record["id"])
                                                  ->where("user_id", $record["user_id"])
                                                  ->where("branch_id", $branchAsset->branch_id)
                                                  ->where("asset_id", $branchAsset->asset_id)
                                                  ->whereIn("status", ["active", "maintenance"])
                                                  ->first();

                if(Utilities::isDefined($assetAssignment)) {

                    $assetAssignment->status     = "retired";
                    $assetAssignment->updated_at = now();
                    $assetAssignment->updated_by = $userId;
                    $assetAssignment->save();

                    $information["success"]["counter"]++;
                    $information["success"]["data"][] = ["asset_id" => $branchAsset->asset_id];

                }else {

                    $information["error"]["counter"]++;
                    $information["error"]["data"][] = ["asset_id" => $branchAsset->asset_id];

                }

            }

        });

        return $information;

    }

    /**
     * Validate branch belongs to company
     *
     * @param int $branchId Branch ID
     * @param int $companyId Company ID
     * @return Branch|null
     */
    public static function validateBranch(int $branchId, int $companyId): ?Branch {

        return Branch::where("id", $branchId)
                     ->where("company_id", $companyId)
                     ->first();

    }

    /**
     * Validate branch asset exists
     *
     * @param int $branchId Branch ID
     * @param int $branchAssetId Branch Asset ID
     * @param int $assetId Asset ID
     * @return BranchAsset|null
     */
    public static function validateBranchAsset(int $branchId, int $branchAssetId, int $assetId): ?BranchAsset {

        return BranchAsset::where("id", $branchAssetId)
                          ->where("branch_id", $branchId)
                          ->where("asset_id", $assetId)
                          ->whereIn("status", ["active", "maintenance"])
                          ->first();

    }

}

