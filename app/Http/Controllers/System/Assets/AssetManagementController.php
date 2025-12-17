<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Assets;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};
use Illuminate\Pagination\LengthAwarePaginator;

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Http\Requests\System\Assets\AssetManagements\{AssignAssetToBranchRequest, UnassignAssetFromBranchRequest};
use App\Services\System\Assets\{AssetManagementConfigService, AssetManagementService};

class AssetManagementController extends Controller {

    use HandlesApiResponses;

    /**
     * Translation namespace for asset management module
     */
    private const TRANSLATION_NAMESPACE = "System.Assets.asset_management";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return AssetManagementConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of branch assets
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator {

        $userAuth = Auth::user();
        $branchId = intval($request->input("branch_id"));

        $branch = AssetManagementService::validateBranch($branchId, $userAuth->company_id);

        if(!Utilities::isDefined($branch)) {

            return new LengthAwarePaginator([], 0, 1, 1, ["path" => ""]);

        }

        $perPage = intval($request->input("per_page") ?? Utilities::$per_page_max);

        return AssetManagementService::getBranchAssetsList($branch->id, $perPage);

    }

    /**
     * Display the asset management index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Assets/assets_management/main");

    }

    /**
     * Show the form for creating a new asset management
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created asset management
     * (Not used, but kept for REST compliance)
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request): void {

        // Not implemented

    }

    /**
     * Display the specified asset management
     * (Not used, but kept for REST compliance)
     *
     * @param mixed $record
     * @return void
     */
    public function show($record): void {

        // Not implemented

    }

    /**
     * Show the form for editing the specified asset management
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param mixed $record
     * @return void
     */
    public function edit($record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified asset management
     * (Not used, but kept for REST compliance)
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function update(Request $request, int $id): void {

        // Not implemented

    }

    /**
     * Remove the specified asset management
     * (Not used, but kept for REST compliance)
     *
     * @param mixed $record
     * @return void
     */
    public function destroy($record): void {

        // Not implemented

    }

    /**
     * Assign assets to a branch
     *
     * @param AssignAssetToBranchRequest $request
     * @return JsonResponse
     */
    public function assignAssetToBranch(AssignAssetToBranchRequest $request): JsonResponse {

        try {

            $userAuth = Auth::user();
            $branchId = intval($request->branch_id);

            $branch = AssetManagementService::validateBranch($branchId, $userAuth->company_id);

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("branch_not_found");

            }

            $information = AssetManagementService::assignAssetsToBranch(
                $branch->id,
                $request->branch_assets ?? [],
                $userAuth->company_id,
                $userAuth->id
            );

            $bool = $information["success"]["counter"] > 0;

            if(!$bool) {

                return $this->errorResponse("assign_failed");

            }

            AssetManagementConfigService::clearAllCache($userAuth->company_id);

            return $this->successResponse($information, "assigned_successfully");

        }catch(Exception $e) {

            return $this->errorResponse("exception_assign", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Unassign assets from a branch
     *
     * @param UnassignAssetFromBranchRequest $request
     * @return JsonResponse
     */
    public function unassignAssetFromBranch(UnassignAssetFromBranchRequest $request): JsonResponse {

        try {

            $userAuth = Auth::user();
            $branchId = intval($request->branch_id);

            $branch = AssetManagementService::validateBranch($branchId, $userAuth->company_id);

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("branch_not_found");

            }

            $information = AssetManagementService::unassignAssetsFromBranch(
                $branch->id,
                $request->branch_assets ?? [],
                $userAuth->id
            );

            $bool = $information["success"]["counter"] > 0;

            if(!$bool) {

                return $this->errorResponse("unassign_failed");

            }

            AssetManagementConfigService::clearAllCache($userAuth->company_id);

            return $this->successResponse($information, "unassigned_successfully");

        }catch(Exception $e) {

            return $this->errorResponse("exception_unassign", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Update asset in branch
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function assetInBranch(Request $request): JsonResponse {

        try {

            $userAuth = Auth::user();
            $branchId = intval($request->input("branch_id"));

            $branch = AssetManagementService::validateBranch($branchId, $userAuth->company_id);

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("branch_not_found");

            }

            $branchAssetId = intval($request->input("id"));
            $assetId       = intval($request->input("asset_id"));

            $data = [
                "quantity"          => $request->input("quantity"),
                "acquisition_value" => $request->input("acquisition_value"),
                "acquisition_date"  => $request->input("acquisition_date"),
                "note"              => $request->input("note")
            ];

            $branchAsset = AssetManagementService::updateAssetInBranch(
                $branch->id,
                $branchAssetId,
                $assetId,
                $data,
                $userAuth->id
            );

            if(!Utilities::isDefined($branchAsset)) {

                return $this->errorResponse("asset_not_found");

            }

            AssetManagementConfigService::clearAllCache($userAuth->company_id);

            return $this->successResponse($branchAsset, "updated_successfully");

        }catch(Exception $e) {

            return $this->errorResponse("exception_update", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Get asset assignments for a branch asset
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAssetAssignments(Request $request): JsonResponse {

        try {

            $userAuth = Auth::user();
            $branchId = intval($request->input("branch_id"));

            $branch = AssetManagementService::validateBranch($branchId, $userAuth->company_id);

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("branch_not_found");

            }

            $assetId = intval($request->input("asset_id"));

            $assignments = AssetManagementService::getAssetAssignments($branch->id, $assetId);

            return $this->successResponse($assignments, "assignments_found");

        }catch(Exception $e) {

            return $this->errorResponse("exception_get_assignments", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Assign asset to users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function assignToUser(Request $request): JsonResponse {

        try {

            $userAuth = Auth::user();
            $branchId = intval($request->input("branch_id"));

            $branch = AssetManagementService::validateBranch($branchId, $userAuth->company_id);

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("branch_not_found");

            }

            $branchAssetId = intval($request->input("branch_asset_id"));
            $assetId       = intval($request->input("asset_id"));

            $branchAsset = AssetManagementService::validateBranchAsset($branch->id, $branchAssetId, $assetId);

            if(!Utilities::isDefined($branchAsset)) {

                return $this->errorResponse("asset_not_found");

            }

            $assetQuantity = floatval($branchAsset->quantity);
            $totalQuantity = array_reduce($request->assignments ?? [], function($accumulator, $currentValue) {

                return $accumulator + floatval($currentValue["quantity"] ?? 0);

            }, 0);

            if($totalQuantity > $assetQuantity) {

                return $this->errorResponse("quantity_exceeds_limit");

            }

            $information = AssetManagementService::assignAssetToUsers(
                $branch->id,
                $branchAssetId,
                $assetId,
                $request->assignments ?? [],
                $userAuth->id
            );

            $bool = $information["success"]["counter"] > 0;

            if(!$bool) {

                return $this->errorResponse("assign_failed");

            }

            AssetManagementConfigService::clearAllCache($userAuth->company_id);

            return $this->successResponse($information, "assigned_to_users_successfully");

        }catch(Exception $e) {

            return $this->errorResponse("exception_assign_to_users", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Unassign asset from users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unassignToUser(Request $request): JsonResponse {

        try {

            $userAuth = Auth::user();
            $branchId = intval($request->input("branch_id"));

            $branch = AssetManagementService::validateBranch($branchId, $userAuth->company_id);

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("branch_not_found");

            }

            $branchAssetId = intval($request->input("branch_asset_id"));
            $assetId       = intval($request->input("asset_id"));

            $branchAsset = AssetManagementService::validateBranchAsset($branch->id, $branchAssetId, $assetId);

            if(!Utilities::isDefined($branchAsset)) {

                return $this->errorResponse("asset_not_found");

            }

            $information = AssetManagementService::unassignAssetFromUsers(
                $branch->id,
                $branchAssetId,
                $assetId,
                $request->assignments ?? [],
                $userAuth->id
            );

            $bool = $information["success"]["counter"] > 0;

            if(!$bool) {

                return $this->errorResponse("unassign_failed");

            }

            AssetManagementConfigService::clearAllCache($userAuth->company_id);

            return $this->successResponse($information, "unassigned_from_users_successfully");

        }catch(Exception $e) {

            return $this->errorResponse("exception_unassign_from_users", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Get translation namespace for asset management module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
