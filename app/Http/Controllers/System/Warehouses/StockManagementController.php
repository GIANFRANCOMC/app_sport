<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Warehouses;

use App\Exports\System\Warehouses\InventoryReportExport;
use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        if(!StockManagementService::validateWarehouse($warehouseId, $this->getCompanyId())) {

            return response()->json([
                "data" => [],
                "total" => 0
            ]);

        }

        return StockManagementService::getPaginatedList(
            $this->getCompanyId(),
            $warehouseId,
            $perPage,
            (string) $request->input("product_search", "")
        );

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
                "origin_types",
                "product_search",
                "date_from",
                "date_to"
            ]),
            $perPage
        );

    }

    public function storeOperations(Request $request): JsonResponse {

        $validator = Validator::make($request->all(), [
            "warehouse_id"             => ["required", "integer"],
            "movement_type"            => ["required", "in:entry,exit,correction"],
            "origin_type"              => [
                "required",
                "in:manual,replenishment,customer_return,supplier_return,physical_count"
            ],
            "items"                    => ["required", "array", "min:1", "max:100"],
            "items.*.item_id"          => ["required", "integer", "distinct"],
            "items.*.quantity"         => ["nullable", "numeric", "gt:0"],
            "items.*.resulting_balance" => ["nullable", "numeric", "min:0"],
            "items.*.unit_cost"        => ["nullable", "numeric", "min:0"],
            "reason"                   => ["required", "string", "max:255"]
        ], [
            "required" => "Campo obligatorio.",
            "items.min" => "Agrega al menos un producto.",
            "items.max" => "Puedes registrar hasta 100 productos por operación.",
            "distinct" => "No repitas un producto en la misma operación.",
            "in" => "Selecciona una opción válida.",
            "numeric" => "Ingresa un número válido.",
            "gt" => "La cantidad debe ser mayor que cero.",
            "min" => "El saldo no puede ser negativo.",
            "max" => "El motivo no debe superar 255 caracteres."
        ]);

        $validator->after(function($validator) use($request) {

            foreach($request->input("items", []) as $index => $item) {

                $field = $request->input("movement_type") === "correction"
                    ? "resulting_balance"
                    : "quantity";

                if(!array_key_exists($field, $item) || $item[$field] === null || $item[$field] === "") {

                    $validator->errors()->add("items.{$index}.{$field}", "Campo obligatorio.");

                }

            }

        });

        if($validator->fails()) {

            return response()->json([
                "bool" => false,
                "msg" => "Revisa los datos de la operación.",
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

            $movements = StockManagementService::createManualMovements(
                $this->getCompanyId(),
                (int) $warehouse->id,
                (string) $request->input("movement_type"),
                (string) $request->input("origin_type"),
                $request->input("items", []),
                (string) $request->input("reason"),
                $this->getUserId()
            );

            return response()->json([
                "bool" => true,
                "msg" => count($movements) === 1
                    ? "Operación registrada correctamente."
                    : "Operación registrada para todos los productos.",
                "data" => $movements
            ]);

        }catch(\Throwable $e) {

            return response()->json([
                "bool" => false,
                "msg" => $e->getMessage()
            ], 422);

        }

    }

    public function export(Request $request): BinaryFileResponse {

        $view = in_array($request->input("view"), ["stock", "kardex", "transfers", "valued"], true)
            ? (string) $request->input("view")
            : "stock";
        $filters = $request->only([
            "warehouse_id",
            "item_id",
            "movement_type",
            "origin_types",
            "product_search",
            "date_from",
            "date_to"
        ]);

        StockManagementService::validateWarehouse(
            (int) ($filters["warehouse_id"] ?? 0),
            $this->getCompanyId()
        ) ?? abort(404, "El almacén seleccionado no está disponible.");
        $fileName = "inventario_{$view}_" . now()->format("Y-m-d_His") . ".xlsx";

        return Excel::download(
            new InventoryReportExport($this->getCompanyId(), $view, $filters),
            $fileName
        );

    }

    public function storeMovement(Request $request): JsonResponse {

        $validator = Validator::make($request->all(), [
            "warehouse_id"     => ["required", "integer"],
            "item_id"          => ["required", "integer"],
            "movement_type"    => ["required", "in:entry,exit,correction"],
            "origin_type"      => [
                "required",
                "in:manual,replenishment,customer_return,supplier_return,physical_count"
            ],
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
                (string) $request->input("origin_type"),
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

    public function storeTransfer(Request $request): JsonResponse {

        $validator = Validator::make($request->all(), [
            "source_warehouse_id"      => ["required", "integer", "different:destination_warehouse_id"],
            "destination_warehouse_id" => ["required", "integer"],
            "items"                    => ["required", "array", "min:1", "max:100"],
            "items.*.item_id"          => ["required", "integer", "distinct"],
            "items.*.quantity"         => ["required", "numeric", "gt:0"],
            "reason"                   => ["required", "string", "max:255"]
        ], [
            "required"  => "Campo obligatorio.",
            "array"     => "Agrega al menos un producto.",
            "items.min" => "Agrega al menos un producto.",
            "items.max" => "Puedes trasladar hasta 100 productos por operación.",
            "distinct"  => "No repitas un producto en el mismo traslado.",
            "integer"   => "Selecciona una opción válida.",
            "different" => "Selecciona un almacén de destino diferente.",
            "numeric"   => "Ingresa un número válido.",
            "gt"        => "La cantidad debe ser mayor que cero.",
            "max"       => "El motivo no debe superar 255 caracteres."
        ]);

        if($validator->fails()) {

            return response()->json([
                "bool"   => false,
                "msg"    => "Revisa los datos del traslado.",
                "errors" => $validator->errors()
            ], 422);

        }

        try {

            $transfer = StockManagementService::transfer([
                "company_id"              => $this->getCompanyId(),
                "source_warehouse_id"     => (int) $request->input("source_warehouse_id"),
                "destination_warehouse_id" => (int) $request->input("destination_warehouse_id"),
                "items"                   => $request->input("items", []),
                "reason"                  => (string) $request->input("reason"),
                "user_id"                 => $this->getUserId()
            ]);

            return response()->json([
                "bool" => true,
                "msg"  => count($request->input("items", [])) === 1
                    ? "Traslado registrado correctamente."
                    : "Productos trasladados correctamente.",
                "data" => $transfer
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
