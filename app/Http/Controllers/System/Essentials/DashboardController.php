<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Models\System\Organizations\{Branch};
use App\Models\System\Sales\{SaleHeader};

class DashboardController extends Controller {

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

    public function index() {

        return view("System/general/dashboard/main");

    }

    public function initData(Request $request) {

        $userAuth = Auth::user();

        $date = Utilities::isDefined($request->date) && Utilities::isValidDateFormat($request->date) ? $request->date : date("Y-m-d");

        $branches = Branch::where("company_id", $userAuth->company_id)
                          ->with(["series"])
                          ->get();

        $serieIds = $branches->pluck("series.*.id")->flatten();

        $sales = SaleHeader::whereDate("created_at", $date)
                           ->whereIn("serie_id", $serieIds)
                           ->orderBy("created_at", "DESC")
                           ->with(["serie.documentType", "holder", "currency"])
                           ->get();

        $canceledSales = $sales->whereIn("status", ["canceled"])
                               ->values();

        // $users = User::where("company_id", $userAuth->company_id)
                      // ->whereIn("status", ["active"])
                      // ->get();

        $data = [
            "sales" => [
                "all" => [
                    "total"   => $sales->sum("total"),
                    "count"   => $sales->count(),
                    "records" => $sales
                ],
                "canceled" => [
                    "total" => $canceledSales->sum("total"),
                    "count" => $canceledSales->count()
                ]
            ],
            "branches" => [
                "valid" => [
                    "count" => $branches->whereIn("status", ["active"])->count()
                ]
            ],
            "users" => [
                "valid" => [
                    "count" => 0 // $users->count()
                ]
            ]
        ];

        return response()->json(["bool" => true, "msg" => "Data obtenida", "data" => $data], 200);

    }

}
