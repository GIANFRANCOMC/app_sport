<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use App\Http\Controllers\System\Base\BaseController;
use Illuminate\Http\{JsonResponse, Request};

use App\Services\System\Customers\Tracking\{TrackingCustomerConfigService, TrackingCustomerBusinessService};

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
        return TrackingCustomerConfigService::getInitParams($this->getCompanyId(), $page);

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $list = [];

        return $list;

    }

    public function index() {

        return view("System/general/Customers/tracking_customers/main");

    }

    public function create() {

        //

    }

    public function store(Request $request) { // StoreTrackingAttendanceRequest

        //

    }

    public function show(Attendance $attendance): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    public function edit(Attendance $attendance): void {

        // Form is handled by frontend SPA

    }

    public function update(Request $request, $id): JsonResponse { // UpdateTrackingAttendanceRequest

        return $this->errorResponse("not_implemented", [], 501);

    }

    public function destroy(Attendance $attendance): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

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
