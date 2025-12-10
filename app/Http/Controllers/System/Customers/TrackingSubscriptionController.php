<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\TrackingSubscriptions\{CancelTrackingSubscriptionRequest, StoreTrackingSubscriptionRequest, UpdateTrackingSubscriptionRequest};
use App\Models\System\Customers\{Customer, Subscription};
use App\Models\System\Organizations\{Branch};
use Illuminate\Pagination\LengthAwarePaginator;

class TrackingSubscriptionController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->branches = new stdClass();
            $config->branches->records = Branch::getAll("tracking_subscription");

            $config->customers = new stdClass();
            $config->customers->records = Customer::getAll("tracking_subscription");

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $branch = Branch::where("id", $request->branch_id)
                        ->where("company_id", $userAuth->company_id)
                        ->first();

        if(!Utilities::isDefined($branch)) {

            return new LengthAwarePaginator([], 0, 1, 1, ["path" => ""]);

        }

        $list = Subscription::when(Utilities::isDefined($request->customer_id), function($query) use($request) {

                                $query->where(function($query) use($request) {

                                    $query->where("customer_id", $request->customer_id);

                                });

                            })
                            ->when(Utilities::isDefined($request->start_date), function($query) use($request) {

                                $query->where(function($query) use($request) {

                                    $query->where("start_date", ">=", $request->start_date." 00:00:00");

                                });

                            })
                            ->when(Utilities::isDefined($request->end_date), function($query) use($request) {

                                $query->where(function($query) use($request) {

                                    $query->where("end_date", "<=", $request->end_date." 23:59:59");

                                });

                            })
                            ->when(Utilities::isDefined($request->status), function($query) use($request) {

                                $query->where(function($query) use($request) {

                                    $query->where("status", $request->status);

                                });

                            })
                            ->where("company_id", $userAuth->company_id)
                            ->where("branch_id", $branch->id)
                            ->orderBy("id", "DESC")
                            ->with(["branch", "saleHeader", "customer"])
                            ->paginate($request->per_page ?? Utilities::$per_page_default);

        return $list;

    }

    public function index() {

        return view("System/general/tracking_subscriptions/main");

    }

    public function create() {

        //

    }

    public function store(Request $request) { // StoreTrackingSubscriptionRequest

        //

    }

    public function show(Subscription $subscription) {

        //

    }

    public function edit(Subscription $subscription) {

        //

    }

    public function update(Request $request, $id) { // UpdateTrackingSubscriptionRequest

        //

    }

    public function cancel(CancelTrackingSubscriptionRequest $request, $id) {

        $userAuth = Auth::user();

        $subscription = Subscription::findOrFail($id);

        if(Utilities::isDefined($subscription) && in_array($subscription->status, ["active"])) {

            if(Utilities::isDefined($subscription) && $subscription->company_id == $userAuth->company_id) {

                $subscription->motive      = $request->motive ?? "N/A";
                $subscription->status      = "canceled";
                $subscription->updated_at  = now();
                $subscription->updated_by  = $userAuth->id ?? null;
                $subscription->canceled_at = now();
                $subscription->canceled_by = $userAuth->id ?? null;
                $subscription->save();

            }

        }

        $bool = $subscription->wasChanged();
        $msg  = $bool ? "Membresía anulada correctamente." : "No se ha podido anular la membresía.";

        return response()->json(["bool" => $bool, "msg" => $msg, "subscription" => $subscription], 200);

    }

    public function destroy(Subscription $subscription) {

        //

    }

}
