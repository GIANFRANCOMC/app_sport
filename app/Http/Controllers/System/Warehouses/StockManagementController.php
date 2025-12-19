<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Warehouses;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};

use App\Services\System\Warehouses\{StockManagementConfigService, StockManagementService};

class StockManagementController extends BaseController {

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

        $page = $this->getPage($request);
        return StockManagementConfigService::getInitParams($this->getCompanyId(), $page);

    }

    /**
     * Get paginated list of items with stock information
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $warehouseId = intval($request->input("warehouse_id"));
        $perPage     = $this->getPerPage($request, Utilities::$per_page_max);

        return StockManagementService::getPaginatedList($this->getCompanyId(), $warehouseId, $perPage);

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

            $warehouseId = intval($request->input("warehouse_id"));

            $warehouse = StockManagementService::validateWarehouse($warehouseId, $this->getCompanyId());

            if(!Utilities::isDefined($warehouse)) {

                return $this->errorResponse("warehouse_not_available");

            }

            $items = $request->input("items", []);

            $success = StockManagementService::updateStock($warehouse->id, $items, $this->getUserId());

            if(!$success) {

                return $this->errorResponse("update_failed");

            }

            StockManagementConfigService::clearAllCache($this->getCompanyId());

            return $this->successResponse(null, "stock_updated_successfully");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Display the specified stock management record
     * (Not used, but kept for REST compliance)
     *
     * @param mixed $record
     * @return JsonResponse
     */
    public function show($record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified stock management
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param mixed $record
     * @return void
     */
    public function edit($record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified stock management
     * (Not used, but kept for REST compliance)
     *
     * @param Request $request
     * @param mixed $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Remove the specified stock management
     * (Not used, but kept for REST compliance)
     *
     * @param mixed $record
     * @return JsonResponse
     */
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
