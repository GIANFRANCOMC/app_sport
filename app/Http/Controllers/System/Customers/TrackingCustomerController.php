<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\TrackingAttendances\{CancelTrackingAttendanceRequest};
use App\Models\System\Customers\{Attendance, Customer};
use App\Services\TrackingCustomerService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class TrackingCustomerController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->customers = new stdClass();
            $config->customers->records = Customer::getAll();

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $list = [];

        return $list;

    }

    public function index() {

        return view("System/general/tracking_customers/main");

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

    public function getTracking(Request $request, $id, TrackingCustomerService $trackingCustomer) {

        $userAuth = Auth::user();

        return $trackingCustomer->get(["company_id" => $userAuth->company_id, "customer_id" => $id, "period_type" => $request->period_type, "options" => $request->options]);

    }

}
