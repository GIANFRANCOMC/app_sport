<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Assets;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Http\Requests\System\Assets\Assets\{StoreAssetRequest, UpdateAssetRequest};
use App\Services\System\Assets\{AssetConfigService, AssetService};
use App\Models\System\Assets\{Asset};

class AssetController extends Controller {

    use HandlesApiResponses;

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

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return AssetConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of assets with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $userAuth = Auth::user();
        $filters  = ["filter_by" => $request->input("filter_by"), "word" => $request->input("word")];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_default);

        return AssetService::getPaginatedList($userAuth->company_id, $filters, $perPage);

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

            $userAuth = Auth::user();

            if(AssetService::internalCodeExists($request->internal_code, $userAuth->company_id)) {

                return $this->errorResponse("internal_code_exists");

            }

            $data  = $this->prepareAssetData($request, $userAuth);
            $asset = AssetService::create($data, $userAuth->id);

            if(!Utilities::isDefined($asset)) {

                return $this->errorResponse("create_failed");

            }

            AssetConfigService::clearAllCache($userAuth->company_id);

            return $this->createdResponse($asset, "created", "asset");

        }catch(Exception $e) {

            return $this->errorResponse("exception_create", ["message" => $e->getMessage()]);

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

            $userAuth = Auth::user();
            $asset    = AssetService::findByIdAndCompany($id, $userAuth->company_id);

            if(!Utilities::isDefined($asset)) {

                return $this->notFoundResponse();

            }

            if(AssetService::internalCodeExists($request->internal_code, $userAuth->company_id, $asset->id)) {

                return $this->errorResponse("internal_code_exists");

            }

            $data  = $this->prepareAssetData($request, $userAuth);
            $asset = AssetService::update($asset, $data, $userAuth->id);

            if(!Utilities::isDefined($asset)) {

                return $this->errorResponse("update_failed");

            }

            AssetConfigService::clearAllCache($userAuth->company_id);

            return $this->updatedResponse($asset, "updated", "asset");

        }catch(Exception $e) {

            return $this->errorResponse("exception_update", ["message" => $e->getMessage()]);

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
    private function prepareAssetData($request, ?object $userAuth = null): array {

        $data = [
            "internal_code" => $request->internal_code,
            "name"          => $request->name,
            "description"   => $request->description ?? "",
            "status"        => $request->status
        ];

        if($userAuth) {

            $data["company_id"] = $userAuth->company_id;

        }

        return $data;

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
