<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use Exception;
use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};

use App\Http\Requests\System\Customers\TrackingSubscriptions\{CancelTrackingSubscriptionRequest};
use App\Services\System\Customers\Tracking\{TrackingSubscriptionConfigService, TrackingSubscriptionService};
use App\Models\System\Customers\Subscription;

class TrackingSubscriptionController extends BaseController {

    /**
     * Translation namespace for tracking subscription module
     */
    private const TRANSLATION_NAMESPACE = "System.Customers.tracking_subscription";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);
        return TrackingSubscriptionConfigService::getInitParams($this->getCompanyId(), $page);

    }

    /**
     * Get paginated list of subscriptions with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $filters = [
            "branch_id"   => $request->input("branch_id"),
            "customer_id" => $request->input("customer_id"),
            "start_date"  => $request->input("start_date"),
            "end_date"    => $request->input("end_date"),
            "status"      => $request->input("status")
        ];
        $perPage = $this->getPerPage($request, Utilities::$per_page_default);

        return TrackingSubscriptionService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    public function index() {

        return view("System/general/Customers/tracking_subscriptions/main");

    }

    public function create() {

        //

    }

    public function store(Request $request) { // StoreTrackingSubscriptionRequest

        //

    }

    public function show(Subscription $subscription): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    public function edit(Subscription $subscription): void {

        // Form is handled by frontend SPA

    }

    public function update(Request $request, $id): JsonResponse { // UpdateTrackingSubscriptionRequest

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Cancel the specified subscription
     *
     * @param CancelTrackingSubscriptionRequest $request
     * @param int $id Subscription ID
     * @return JsonResponse
     */
    public function cancel(CancelTrackingSubscriptionRequest $request, int $id): JsonResponse {

        try {

            $subscription = Subscription::findOrFail($id);

            if(!Utilities::isDefined($subscription) || $subscription->company_id !== $this->getCompanyId()) {

                return $this->notFoundResponse();

            }

            $subscription = TrackingSubscriptionService::cancel($subscription, $request->motive, $this->getUserId());

            TrackingSubscriptionConfigService::clearAllCache($this->getCompanyId());

            return $this->updatedResponse($subscription, "canceled", "subscription");

        }catch(Exception $e) {

            return $this->handleException($e, "cancel");

        }

    }

    /**
     * Get translation namespace for tracking subscription module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

    public function destroy(Subscription $subscription): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

}
