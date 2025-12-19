<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Catalogs;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};

use App\Http\Requests\System\Catalogs\Subscriptions\{StoreSubscriptionRequest, UpdateSubscriptionRequest};
use App\Services\System\Catalogs\Items\{SubscriptionConfigService, SubscriptionService};
use App\Models\System\Catalogs\{Item};

class SubscriptionController extends BaseController {

    /**
     * Translation namespace for subscription module
     */
    private const TRANSLATION_NAMESPACE = "System.Catalogs.subscription";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);
        return SubscriptionConfigService::getInitParams($this->getCompanyId(), $page);

    }

    /**
     * Get paginated list of subscriptions with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $filters = $this->getFilters($request);
        $perPage = $this->getPerPage($request, Utilities::$per_page_default);

        return SubscriptionService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Display the subscriptions index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Catalogs/subscriptions/main");

    }

    /**
     * Show the form for creating a new subscription
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created subscription
     *
     * @param StoreSubscriptionRequest $request
     * @return JsonResponse
     */
    public function store(StoreSubscriptionRequest $request): JsonResponse {

        try {

            $data = $this->prepareSubscriptionData($request);
            $item = SubscriptionService::create($data, $this->getUserId());

            if(!Utilities::isDefined($item)) {

                return $this->errorResponse("create_failed");

            }

            SubscriptionConfigService::clearAllCache($this->getCompanyId());

            return $this->createdResponse($item, "created", "item");

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    /**
     * Display the specified subscription
     * (Not used, but kept for REST compliance)
     *
     * @param Item $record
     * @return JsonResponse
     */
    public function show(Item $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified subscription
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param Item $record
     * @return void
     */
    public function edit(Item $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified subscription
     *
     * @param UpdateSubscriptionRequest $request
     * @param int $id Subscription ID
     * @return JsonResponse
     */
    public function update(UpdateSubscriptionRequest $request, int $id): JsonResponse {

        try {

            $item = SubscriptionService::findByIdAndCompany($id, $this->getCompanyId());

            if(!Utilities::isDefined($item)) {

                return $this->notFoundResponse();

            }

            $data = $this->prepareSubscriptionData($request);
            $item = SubscriptionService::update($item, $data, $this->getUserId());

            if(!Utilities::isDefined($item)) {

                return $this->errorResponse("update_failed");

            }

            SubscriptionConfigService::clearAllCache($this->getCompanyId());

            return $this->updatedResponse($item, "updated", "item");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Remove the specified subscription
     * (Not used, but kept for REST compliance)
     *
     * @param Item $record
     * @return JsonResponse
     */
    public function destroy(Item $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare subscription data from request
     *
     * @param StoreSubscriptionRequest|UpdateSubscriptionRequest $request
     * @return array
     */
    private function prepareSubscriptionData($request): array {

        return [
            "company_id"         => $this->getCompanyId(),
            "internal_code"      => $request->internal_code,
            "name"               => $request->name,
            "description"        => $request->description ?? "",
            "price"              => $request->price,
            "min_price"          => $request->min_price,
            "max_price"          => $request->max_price,
            "currency_id"        => $request->currency_id,
            "duration_type"      => $request->duration_type,
            "duration_value"     => $request->duration_value,
            "see_my_web"         => $request->see_my_web ?? false,
            "see_my_web_price"   => $request->see_my_web_price ?? false,
            "status"             => $request->status,
            "categories"          => $request->categories ?? []
        ];

    }

    /**
     * Get translation namespace for subscription module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
