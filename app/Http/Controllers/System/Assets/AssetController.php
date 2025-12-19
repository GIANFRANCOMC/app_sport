<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Assets;

use Exception;
use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};

use App\Http\Requests\System\Assets\Assets\{StoreAssetRequest, UpdateAssetRequest};
use App\Services\System\Assets\{AssetConfigService, AssetService};
use App\Models\System\Assets\{Asset};

class AssetController extends BaseController {

    /**
     * Translation namespace for asset module
     */
    private const TRANSLATION_NAMESPACE = "System.Assets.asset";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);
        return AssetConfigService::getInitParams($this->getCompanyId(), $page);

    }

    /**
     * Get paginated list of assets with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $filters = $this->getFilters($request);
        $perPage = $this->getPerPage($request, Utilities::$per_page_default);

        return AssetService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Display the assets index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Assets/assets/main");

    }

    /**
     * Show the form for creating a new asset
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created asset
     *
     * @param StoreAssetRequest $request
     * @return JsonResponse
     */
    public function store(StoreAssetRequest $request): JsonResponse {

        try {

            if(AssetService::internalCodeExists($request->internal_code, $this->getCompanyId())) {

                return $this->errorResponse("internal_code_exists");

            }

            $data  = $this->prepareAssetData($request);
            $asset = AssetService::create($data, $this->getUserId());

            if(!Utilities::isDefined($asset)) {

                return $this->errorResponse("create_failed");

            }

            AssetConfigService::clearAllCache($this->getCompanyId());

            return $this->createdResponse($asset, "created", "asset");

        }catch(Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    /**
     * Display the specified asset
     * (Not used, but kept for REST compliance)
     *
     * @param Asset $record
     * @return JsonResponse
     */
    public function show(Asset $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified asset
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param Asset $record
     * @return void
     */
    public function edit(Asset $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified asset
     *
     * @param UpdateAssetRequest $request
     * @param int $id Asset ID
     * @return JsonResponse
     */
    public function update(UpdateAssetRequest $request, int $id): JsonResponse {

        try {

            $asset = AssetService::findByIdAndCompany($id, $this->getCompanyId());

            if(!Utilities::isDefined($asset)) {

                return $this->notFoundResponse();

            }

            if(AssetService::internalCodeExists($request->internal_code, $this->getCompanyId(), $asset->id)) {

                return $this->errorResponse("internal_code_exists");

            }

            $data  = $this->prepareAssetData($request);
            $asset = AssetService::update($asset, $data, $this->getUserId());

            if(!Utilities::isDefined($asset)) {

                return $this->errorResponse("update_failed");

            }

            AssetConfigService::clearAllCache($this->getCompanyId());

            return $this->updatedResponse($asset, "updated", "asset");

        }catch(Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Remove the specified asset
     * (Not used, but kept for REST compliance)
     *
     * @param Asset $record
     * @return JsonResponse
     */
    public function destroy(Asset $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare asset data from request
     *
     * @param StoreAssetRequest|UpdateAssetRequest $request
     * @param object|null $userAuth
     * @return array
     */
    private function prepareAssetData($request): array {

        return [
            "company_id"    => $this->getCompanyId(),
            "internal_code" => $request->internal_code,
            "name"          => $request->name,
            "description"   => $request->description ?? "",
            "status"        => $request->status
        ];

    }

    /**
     * Get translation namespace for asset module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
