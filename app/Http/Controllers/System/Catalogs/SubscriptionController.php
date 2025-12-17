<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Catalogs;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Http\Requests\System\Catalogs\Subscriptions\{StoreSubscriptionRequest, UpdateSubscriptionRequest};
use App\Services\System\Catalogs\Items\{SubscriptionConfigService, SubscriptionService};
use App\Models\System\Catalogs\{Item};

class SubscriptionController extends Controller {

    use HandlesApiResponses;

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

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return SubscriptionConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of subscriptions with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $userAuth = Auth::user();
        $filters  = ["filter_by" => $request->input("filter_by"), "word" => $request->input("word")];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_default);

        return SubscriptionService::getPaginatedList($userAuth->company_id, $filters, $perPage);

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

            $userAuth = Auth::user();
            $data     = $this->prepareSubscriptionData($request, $userAuth);
            $item     = SubscriptionService::create($data, $userAuth->id);

            if(!Utilities::isDefined($item)) {

                return $this->errorResponse("create_failed");

            }

            SubscriptionConfigService::clearAllCache($userAuth->company_id);

            return $this->createdResponse($item, "created", "item");

        }catch(Exception $e) {

            return $this->errorResponse("exception_create", ["message" => $e->getMessage()]);

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

            $userAuth = Auth::user();
            $item     = SubscriptionService::findByIdAndCompany($id, $userAuth->company_id);

            if(!Utilities::isDefined($item)) {

                return $this->notFoundResponse();

            }

            $data = $this->prepareSubscriptionData($request, $userAuth);
            $item = SubscriptionService::update($item, $data, $userAuth->id);

            if(!Utilities::isDefined($item)) {

                return $this->errorResponse("update_failed");

            }

            SubscriptionConfigService::clearAllCache($userAuth->company_id);

            return $this->updatedResponse($item, "updated", "item");

        }catch(Exception $e) {

            return $this->errorResponse("exception_update", ["message" => $e->getMessage()]);

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
     * @param object|null $userAuth
     * @return array
     */
    private function prepareSubscriptionData($request, ?object $userAuth = null): array {

        $data = [
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

        if($userAuth) {

            $data["company_id"] = $userAuth->company_id;

        }

        return $data;

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
