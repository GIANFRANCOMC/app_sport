<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use App\Http\Controllers\{Controller};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Services\System\Customers\Tracking\TrackingCustomerConfigService;
use App\Services\TrackingCustomerService;

class TrackingCustomerController extends Controller {

    use HandlesApiResponses;

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

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return TrackingCustomerConfigService::getInitParams($userAuth->company_id, $page);

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

    public function show(Attendance $attendance) {

        //

    }

    public function edit(Attendance $attendance) {

        //

    }

    public function update(Request $request, $id) { // UpdateTrackingAttendanceRequest

        //

    }

    public function destroy(Attendance $attendance) {

        //

    }

    /**
     * Get tracking information for a customer
     *
     * @param Request $request
     * @param int $id Customer ID
     * @param TrackingCustomerService $trackingCustomer
     * @return mixed
     */
    public function getTracking(Request $request, int $id, TrackingCustomerService $trackingCustomer) {

        $userAuth = Auth::user();

        return $trackingCustomer->get([
            "company_id"  => $userAuth->company_id,
            "customer_id" => $id,
            "period_type" => $request->input("period_type"),
            "options"     => $request->input("options")
        ]);

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
