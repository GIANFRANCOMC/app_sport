<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

// use App\Http\Requests\System\TrackingNotifications\{CancelTrackingNotificationRequest, StoreTrackingNotificationRequest, UpdateTrackingNotificationRequest};
use App\Models\System\Customers\{SubscriptionEmail};
use Illuminate\Pagination\LengthAwarePaginator;

class TrackingNotificationController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            //

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $list = SubscriptionEmail::when(Utilities::isDefined($request->status), function($query) use($request) {

                                    $query->where(function($query) use($request) {

                                        $query->where("status", $request->status);

                                    });

                                 })
                                 ->where("company_id", $userAuth->company_id)
                                 ->orderBy("id", "DESC")
                                 ->paginate($request->per_page ?? Utilities::$per_page_default);

        return $list;

    }

    public function index() {

        return view("System/general/tracking_notifications/main");

    }

    public function create() {

        //

    }

    public function store(Request $request) { // StoreTrackingNotificationRequest

        //

    }

    public function show(SubscriptionEmail $email) {

        //

    }

    public function edit(SubscriptionEmail $email) {

        //

    }

    public function update(Request $request, $id) { // UpdateTrackingNotificationRequest

        //

    }

    public function destroy(SubscriptionEmail $email) {

        //

    }

}
