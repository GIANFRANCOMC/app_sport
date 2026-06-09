<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Catalogs;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\System\Catalogs\Categories\{StoreCategoryRequest, UpdateCategoryRequest};
use App\Services\System\Base\{InitParamsCacheInvalidationService};
use App\Services\System\Catalogs\Categories\{CategoryConfigService, CategoryService};
use App\Models\System\Catalogs\{Category};

class CategoryController extends BaseController {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Catalogs.category";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);

        return CategoryConfigService::getInitParams($this->getCompanyId(), $page);

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

        return CategoryService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Display the module index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Catalogs/categories/main");

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
     * @param StoreCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request): JsonResponse {

        try {

            $data     = $this->prepareCategoryData($request);
            $category = CategoryService::create($data, $this->getUserId());

            if(!Utilities::isDefined($category)) {

                return $this->errorResponse("create_failed");

            }

            InitParamsCacheInvalidationService::invalidate(
                InitParamsCacheInvalidationService::CATEGORIES,
                $this->getCompanyId()
            );

            return $this->createdResponse($category, "created", "category");

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    /**
     * Display the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param Category $record
     * @return JsonResponse
     */
    public function show(Category $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified record
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param Category $record
     * @return void
     */
    public function edit(Category $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified record
     *
     * @param UpdateCategoryRequest $request
     * @param int $id Category ID
     * @return JsonResponse
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse {

        try {

            $category = CategoryService::findByIdAndCompany($id, $this->getCompanyId(), null);

            if(!Utilities::isDefined($category)) {

                return $this->notFoundResponse();

            }

            $data     = $this->prepareCategoryData($request);
            $category = CategoryService::update($category, $data, $this->getUserId());

            if(!Utilities::isDefined($category)) {

                return $this->errorResponse("update_failed");

            }

            InitParamsCacheInvalidationService::invalidate(
                InitParamsCacheInvalidationService::CATEGORIES,
                $this->getCompanyId()
            );

            return $this->updatedResponse($category, "updated", "category");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Remove the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param Category $record
     * @return JsonResponse
     */
    public function destroy(Category $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare record data from request
     *
     * @param StoreCategoryRequest|UpdateCategoryRequest $request
     * @return array
     */
    private function prepareCategoryData($request): array {

        return [
            "company_id"    => $this->getCompanyId(),
            "internal_code" => $request->input("internal_code"),
            "name"          => $request->input("name"),
            "description"   => $request->input("description"),
            "status"        => $request->input("status")
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
