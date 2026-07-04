<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Assets;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Pagination\LengthAwarePaginator;

use App\Http\Requests\System\Assets\AssetManagements\{AssignAssetToBranchRequest, UnassignAssetFromBranchRequest};
use App\Services\System\Assets\{AssetManagementConfigService, AssetManagementService};

class AssetManagementController extends BaseController {

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

        $page = $this->getPage($request);
        return AssetManagementConfigService::getInitParams($this->getCompanyId(), $page, $this->getUserId());

    }

    /**
     * Get paginated list of branch assets
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator {

        $branchId = intval($request->input("branch_id"));

        $branch = AssetManagementService::validateBranch($branchId, $this->getCompanyId());

        if(!Utilities::isDefined($branch)) {

            return new LengthAwarePaginator([], 0, 1, 1, ["path" => ""]);

        }

        $perPage = $this->getPerPage($request, Utilities::$per_page_max);

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
     * Assign assets to a branch
     *
     * @param AssignAssetToBranchRequest $request
     * @return JsonResponse
     */
    public function assignAssetToBranch(AssignAssetToBranchRequest $request): JsonResponse {

        try {

            $branchId = intval($request->branch_id);

            $branch = AssetManagementService::validateBranch($branchId, $this->getCompanyId());

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("branch_not_found");

            }

            $information = AssetManagementService::assignAssetsToBranch(
                $branch->id,
                $request->branch_assets ?? [],
                $this->getCompanyId(),
                $this->getUserId()
            );

            $bool = $information["success"]["counter"] > 0;

            if(!$bool) {

                return $this->errorResponse("assign_failed");

            }

            return $this->successResponse($information, "assigned_successfully");

        }catch(\Exception $e) {

            return $this->handleException($e, "assign");

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

            $branchId = intval($request->branch_id);

            $branch = AssetManagementService::validateBranch($branchId, $this->getCompanyId());

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("branch_not_found");

            }

            $information = AssetManagementService::unassignAssetsFromBranch(
                $branch->id,
                $request->branch_assets ?? [],
                $this->getUserId()
            );

            $bool = $information["success"]["counter"] > 0;

            if(!$bool) {

                return $this->errorResponse("unassign_failed");

            }

            return $this->successResponse($information, "unassigned_successfully");

        }catch(\Exception $e) {

            return $this->handleException($e, "unassign");

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

            $branchId = intval($request->input("branch_id"));

            $branch = AssetManagementService::validateBranch($branchId, $this->getCompanyId());

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
                $this->getUserId()
            );

            if(!Utilities::isDefined($branchAsset)) {

                return $this->errorResponse("asset_not_found");

            }

            return $this->successResponse($branchAsset, "updated_successfully");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

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

            $branchId = intval($request->input("branch_id"));

            $branch = AssetManagementService::validateBranch($branchId, $this->getCompanyId());

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("branch_not_found");

            }

            $assetId = intval($request->input("asset_id"));

            $assignments = AssetManagementService::getAssetAssignments($branch->id, $assetId);

            return $this->successResponse($assignments, "assignments_found");

        }catch(\Exception $e) {

            return $this->handleException($e, "get_assignments");

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

            $branchId = intval($request->input("branch_id"));

            $branch = AssetManagementService::validateBranch($branchId, $this->getCompanyId());

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
                $this->getUserId()
            );

            $bool = $information["success"]["counter"] > 0;

            if(!$bool) {

                return $this->errorResponse("assign_failed");

            }

            return $this->successResponse($information, "assigned_to_users_successfully");

        }catch(\Exception $e) {

            return $this->handleException($e, "assign_to_users");

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

            $branchId = intval($request->input("branch_id"));

            $branch = AssetManagementService::validateBranch($branchId, $this->getCompanyId());

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
                $this->getUserId()
            );

            $bool = $information["success"]["counter"] > 0;

            if(!$bool) {

                return $this->errorResponse("unassign_failed");

            }

            return $this->successResponse($information, "unassigned_from_users_successfully");

        }catch(\Exception $e) {

            return $this->handleException($e, "unassign_from_users");

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
