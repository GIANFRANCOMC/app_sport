<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Warehouses;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Services\System\Warehouses\{StockManagementConfigService, StockManagementService};

class StockManagementController extends Controller {

    use HandlesApiResponses;

    /**
     * Translation namespace for stock management module
     */
    private const TRANSLATION_NAMESPACE = "System.Warehouses.stock_management";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return StockManagementConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of items with stock information
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $userAuth   = Auth::user();
        $warehouseId = intval($request->input("warehouse_id"));
        $perPage    = intval($request->input("per_page") ?? Utilities::$per_page_max);

        return StockManagementService::getPaginatedList($userAuth->company_id, $warehouseId, $perPage);

    }

    /**
     * Display the stock management index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Warehouses/stocks_management/main");

    }

    /**
     * Show the form for creating a new stock management
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store/Update stock for items in a warehouse
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse {

        try {

            $userAuth   = Auth::user();
            $warehouseId = intval($request->input("warehouse_id"));

            $warehouse = StockManagementService::validateWarehouse($warehouseId, $userAuth->company_id);

            if(!Utilities::isDefined($warehouse)) {

                return $this->errorResponse("warehouse_not_available");

            }

            $items = $request->input("items", []);

            $success = StockManagementService::updateStock($warehouse->id, $items, $userAuth->id);

            if(!$success) {

                return $this->errorResponse("update_failed");

            }

            StockManagementConfigService::clearAllCache($userAuth->company_id);

            return $this->successResponse(null, "stock_updated_successfully");

        }catch(Exception $e) {

            return $this->errorResponse("exception_update", ["message" => $e->getMessage()]);

        }

    }

    public function show($record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    public function edit($record): void {

        // Form is handled by frontend SPA

    }

    public function update(Request $request, $id): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    public function destroy($record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Get translation namespace for stock management module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
