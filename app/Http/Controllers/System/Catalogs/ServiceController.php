<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Catalogs;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\System\Catalogs\Services\{StoreServiceRequest, UpdateServiceRequest};
use App\Services\System\Base\{InitParamsCacheInvalidationService};
use App\Services\System\Catalogs\Services\{ServiceConfigService, ServiceService};
use App\Models\System\Catalogs\{Item};

class ServiceController extends BaseController {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Catalogs.service";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);

        return ServiceConfigService::getInitParams($this->getCompanyId(), $page);

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

        return ServiceService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Display the module index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Catalogs/services/main");

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
     * @param StoreServiceRequest $request
     * @return JsonResponse
     */
    public function store(StoreServiceRequest $request): JsonResponse {

        try {

            $data = $this->prepareServiceData($request);
            $item = ServiceService::create($data, $this->getCompanyId(), $this->getUserId());

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
     * @param UpdateServiceRequest $request
     * @param int $id Service ID
     * @return JsonResponse
     */
    public function update(UpdateServiceRequest $request, int $id): JsonResponse {

        try {

            $item = ServiceService::findByIdAndCompany($id, $this->getCompanyId(), null);

            if(!Utilities::isDefined($item)) {

                return $this->notFoundResponse();

            }

            $data = $this->prepareServiceData($request);
            $item = ServiceService::update($item, $data, $this->getUserId());

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
     * @param StoreServiceRequest|UpdateServiceRequest $request
     * @return array
     */
    private function prepareServiceData($request): array {

        return [
            "company_id"       => $this->getCompanyId(),
            "internal_code"    => $request->input("internal_code"),
            "name"             => $request->input("name"),
            "description"      => $request->input("description"),
            "price"            => $request->input("price"),
            "min_price"        => $request->input("min_price"),
            "max_price"        => $request->input("max_price"),
            "currency_id"      => $request->input("currency_id"),
            "estimated_duration_minutes" => $request->input("estimated_duration_minutes"),
            "commission_rate"  => $request->input("commission_rate"),
            "see_my_web"       => $request->input("see_my_web"),
            "see_my_web_price" => $request->input("see_my_web_price"),
            "status"           => $request->input("status"),
            "categories"       => $request->input("categories")
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
