<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Catalogs;

use App\Exports\System\Catalogs\Products\ProductListExport;
use App\Exports\System\Catalogs\Products\ProductImportTemplateExport;
use App\Imports\System\Catalogs\Products\ProductBasicImport;
use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Requests\System\Catalogs\Products\{StoreProductRequest, UpdateProductRequest};
use App\Services\System\Base\{InitParamsCacheInvalidationService};
use App\Services\System\Catalogs\Products\{ProductConfigService, ProductService};
use App\Models\System\Catalogs\{Item};
use App\Models\System\Organizations\Company;
use App\Services\System\Warehouses\StockManagement\StockManagementService;
use Illuminate\Validation\ValidationException;

class ProductController extends BaseController {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Catalogs.product";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);

        return ProductConfigService::getInitParams($this->getCompanyId(), $page);

    }

    /**
     * Get paginated list with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $filters = $this->getFilters($request);
        $perPage = $this->getPerPage($request, Utilities::$per_page_default);

        return ProductService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Download every product matching the current list filters.
     */
    public function export(Request $request): BinaryFileResponse {

        $fileName = "productos_" . now()->format("Y-m-d_His") . ".xlsx";

        return Excel::download(
            new ProductListExport($this->getCompanyId(), $this->getFilters($request)),
            $fileName
        );

    }

    public function importTemplate(): BinaryFileResponse {

        return Excel::download(
            new ProductImportTemplateExport(),
            "plantilla_productos.xlsx"
        );

    }

    public function import(Request $request): JsonResponse {

        $request->validate([
            "file"         => ["required", "file", "mimes:xlsx,xls,csv", "max:5120"],
            "warehouse_id" => ["required", "integer"]
        ], [
            "required" => "Campo obligatorio.",
            "file"     => "Selecciona un archivo válido.",
            "mimes"    => "Usa un archivo Excel o CSV.",
            "max"      => "El archivo no debe superar 5 MB."
        ]);

        try {

            $warehouse = StockManagementService::validateWarehouse(
                (int) $request->input("warehouse_id"),
                $this->getCompanyId()
            );

            if(!$warehouse) {

                return response()->json([
                    "bool" => false,
                    "msg"  => "El almacén seleccionado no está disponible."
                ], 422);

            }

            $currencyId = (int) Company::whereKey($this->getCompanyId())->value("currency_id");
            $import = new ProductBasicImport(
                $this->getCompanyId(),
                $currencyId,
                (int) $warehouse->id,
                $this->getUserId()
            );

            Excel::import($import, $request->file("file"));

            InitParamsCacheInvalidationService::invalidate(
                InitParamsCacheInvalidationService::ITEMS,
                $this->getCompanyId()
            );

            return response()->json([
                "bool" => true,
                "msg"  => "{$import->importedCount()} productos importados correctamente.",
                "data" => ["imported" => $import->importedCount()]
            ]);

        }catch(ValidationException $e) {

            throw $e;

        }catch(\Throwable $e) {

            return response()->json([
                "bool" => false,
                "msg"  => $e->getMessage()
            ], 422);

        }

    }

    /**
     * Display the module index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Catalogs/products/main");

    }

    /**
     * Show the form for creating a new record
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created record
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse {

        try {

            $data = $this->prepareProductData($request);
            $item = ProductService::create($data, $this->getUserId());

            if(!Utilities::isDefined($item)) {

                return $this->errorResponse("create_failed");

            }

            InitParamsCacheInvalidationService::invalidate(
                InitParamsCacheInvalidationService::ITEMS,
                $this->getCompanyId()
            );

            return $this->createdResponse($item, "created", "item");

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    /**
     * Display the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param Item $record
     * @return JsonResponse
     */
    public function show(Item $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified record
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param Item $record
     * @return void
     */
    public function edit(Item $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified record
     *
     * @param UpdateProductRequest $request
     * @param int $id Product ID
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse {

        try {

            $item = ProductService::findByIdAndCompany($id, $this->getCompanyId(), null);

            if(!Utilities::isDefined($item)) {

                return $this->notFoundResponse();

            }

            $data = $this->prepareProductData($request);
            $item = ProductService::update($item, $data, $this->getUserId());

            if(!Utilities::isDefined($item)) {

                return $this->errorResponse("update_failed");

            }

            InitParamsCacheInvalidationService::invalidate(
                InitParamsCacheInvalidationService::ITEMS,
                $this->getCompanyId()
            );

            return $this->updatedResponse($item, "updated", "item");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Remove the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param Item $record
     * @return JsonResponse
     */
    public function destroy(Item $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare record data from request
     *
     * @param StoreProductRequest|UpdateProductRequest $request
     * @return array
     */
    private function prepareProductData($request): array {

        return [
            "company_id"       => $this->getCompanyId(),
            "brand_id"         => $request->input("brand_id"),
            "internal_code"    => $request->input("internal_code"),
            "barcode"          => $request->input("barcode"),
            "name"             => $request->input("name"),
            "description"      => $request->input("description"),
            "price"            => $request->input("price"),
            "min_price"        => $request->input("min_price"),
            "max_price"        => $request->input("max_price"),
            "currency_id"      => $request->input("currency_id"),
            "see_my_web"       => $request->input("see_my_web"),
            "see_my_web_price" => $request->input("see_my_web_price"),
            "status"           => $request->input("status"),
            "categories"       => $request->input("categories"),
            "inventory"        => $request->input("inventory", [])
        ];

    }

    /**
     * Get translation namespace for module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
