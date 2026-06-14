<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Warehouses;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Validator;

use App\Services\System\Warehouses\StockManagement\{
    StockManagementConfigService,
    StockManagementService
};

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

            return $this->successResponse(null, "stock_updated_successfully");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    public function movements(Request $request) {

        $perPage = $this->getPerPage($request, Utilities::$per_page_max);

        return StockManagementService::getKardex(
            $this->getCompanyId(),
            $request->only([
                "warehouse_id",
                "item_id",
                "movement_type",
                "date_from",
                "date_to"
            ]),
            $perPage
        );

    }

    public function storeMovement(Request $request): JsonResponse {

        $validator = Validator::make($request->all(), [
            "warehouse_id"     => ["required", "integer"],
            "item_id"          => ["required", "integer"],
            "movement_type"    => ["required", "in:entry,exit,correction"],
            "quantity"         => ["nullable", "numeric", "gt:0"],
            "resulting_balance" => ["nullable", "numeric", "min:0"],
            "reason"           => ["required", "string", "max:255"]
        ], [
            "required" => "Campo obligatorio.",
            "in"       => "Selecciona una opción válida.",
            "numeric"  => "Ingresa un número válido.",
            "gt"       => "La cantidad debe ser mayor que cero.",
            "min"      => "El saldo no puede ser negativo.",
            "max"      => "El motivo no debe superar 255 caracteres."
        ]);

        $validator->after(function($validator) use($request) {

            if($request->input("movement_type") === "correction"
                && !$request->filled("resulting_balance")) {

                $validator->errors()->add("resulting_balance", "Campo obligatorio.");

            }

            if(in_array($request->input("movement_type"), ["entry", "exit"], true)
                && !$request->filled("quantity")) {

                $validator->errors()->add("quantity", "Campo obligatorio.");

            }

        });

        if($validator->fails()) {

            return response()->json([
                "bool"   => false,
                "msg"    => "Revisa los datos del movimiento.",
                "errors" => $validator->errors()
            ], 422);

        }

        try {

            $warehouse = StockManagementService::validateWarehouse(
                (int) $request->input("warehouse_id"),
                $this->getCompanyId()
            );

            if(!$warehouse) {

                return $this->errorResponse("warehouse_not_available");

            }

            $movement = StockManagementService::createManualMovement(
                $this->getCompanyId(),
                (int) $warehouse->id,
                (int) $request->input("item_id"),
                (string) $request->input("movement_type"),
                $request->filled("quantity") ? (float) $request->input("quantity") : null,
                $request->filled("resulting_balance")
                    ? (float) $request->input("resulting_balance")
                    : null,
                (string) $request->input("reason"),
                $this->getUserId()
            );

            return response()->json([
                "bool" => true,
                "msg"  => "Movimiento registrado correctamente.",
                "data" => $movement
            ]);

        }catch(\Throwable $e) {

            return response()->json([
                "bool" => false,
                "msg"  => $e->getMessage()
            ], 422);

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
