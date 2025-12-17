<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Catalogs;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Http\Requests\System\Catalogs\Categories\{StoreCategoryRequest, UpdateCategoryRequest};
use App\Services\System\Catalogs\Categories\{CategoryConfigService, CategoryService};
use App\Models\System\Catalogs\{Category};

class CategoryController extends Controller {

    use HandlesApiResponses;

    /**
     * Translation namespace for category module
     */
    private const TRANSLATION_NAMESPACE = "System.Catalogs.category";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return CategoryConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of categories with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $userAuth = Auth::user();
        $filters  = ["filter_by" => $request->input("filter_by"), "word" => $request->input("word")];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_default);

        return CategoryService::getPaginatedList($userAuth->company_id, $filters, $perPage);

    }

    /**
     * Display the categories index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Catalogs/categories/main");

    }

    /**
     * Show the form for creating a new category
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created category
     *
     * @param StoreCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request): JsonResponse {

        try {

            $userAuth = Auth::user();
            $data     = $this->prepareCategoryData($request, $userAuth);
            $category = CategoryService::create($data, $userAuth->id);

            if(!Utilities::isDefined($category)) {

                return $this->errorResponse("create_failed");

            }

            CategoryConfigService::clearAllCache($userAuth->company_id);

            return $this->createdResponse($category, "created", "category");

        }catch(Exception $e) {

            return $this->errorResponse("exception_create", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Display the specified category
     * (Not used, but kept for REST compliance)
     *
     * @param Category $record
     * @return JsonResponse
     */
    public function show(Category $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified category
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param Category $record
     * @return void
     */
    public function edit(Category $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified category
     *
     * @param UpdateCategoryRequest $request
     * @param int $id Category ID
     * @return JsonResponse
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse {

        try {

            $userAuth = Auth::user();
            $category = CategoryService::findByIdAndCompany($id, $userAuth->company_id);

            if(!Utilities::isDefined($category)) {

                return $this->notFoundResponse();

            }

            $data     = $this->prepareCategoryData($request, $userAuth);
            $category = CategoryService::update($category, $data, $userAuth->id);

            if(!Utilities::isDefined($category)) {

                return $this->errorResponse("update_failed");

            }

            CategoryConfigService::clearAllCache($userAuth->company_id);

            return $this->updatedResponse($category, "updated", "category");

        }catch(Exception $e) {

            return $this->errorResponse("exception_update", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Remove the specified category
     * (Not used, but kept for REST compliance)
     *
     * @param Category $record
     * @return JsonResponse
     */
    public function destroy(Category $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare category data from request
     *
     * @param StoreCategoryRequest|UpdateCategoryRequest $request
     * @param object|null $userAuth
     * @return array
     */
    private function prepareCategoryData($request, ?object $userAuth = null): array {

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
     * Get translation namespace for category module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
