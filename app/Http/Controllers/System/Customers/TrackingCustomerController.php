<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use App\Http\Controllers\System\Base\BaseController;
use Illuminate\Http\{JsonResponse, Request};

use App\Services\System\Customers\Tracking\{TrackingCustomerConfigService, TrackingCustomerBusinessService};
use App\Services\System\Organizations\AccessScopeService;

class TrackingCustomerController extends BaseController {

    /**
     * Translation namespace for tracking customer module
     */
    private const TRANSLATION_NAMESPACE = "System.Customers.tracking_customer";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);
        return TrackingCustomerConfigService::getInitParams($this->getCompanyId(), $page, $this->getUserId());

    }


    /**
     * Display the tracking customers index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Customers/tracking_customers/main");

    }







    /**
     * Get tracking information for a customer
     *
     * @param Request $request
     * @param int $id Customer ID
     * @param TrackingCustomerBusinessService $businessService
     * @return JsonResponse
     */
    public function getTracking(Request $request, int $id, TrackingCustomerBusinessService $businessService): JsonResponse {

        try {

            $result = $businessService->get([
                "company_id"  => $this->getCompanyId(),
                "customer_id" => $id,
                "period_type" => $request->input("period_type"),
                "start_date" => $request->input("start_date"),
                "end_date" => $request->input("end_date"),
                "allowed_branch_ids" => AccessScopeService::allowedIds(
                    $this->getAuthUser(),
                    AccessScopeService::BRANCH
                ),
                "options"     => $request->input("options")
            ]);

            if($result["bool"]) {

                return $this->successResponse($result["tracking"], "retrieved");

            }

            return $this->errorResponse($result["msg"] ?? "retrieve_failed", [], 422);

        }catch(\Exception $e) {

            return $this->handleException($e, "retrieve");

        }

    }

    /**
     * Get translation namespace for tracking customer module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
