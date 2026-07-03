<?php

namespace App\Http\Controllers\Guest;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Guest\PublicAttendanceRequest;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

// use App\Http\Requests\Guest\TrackingAttendances\{CancelTrackingAttendanceRequest};
use App\Models\Guest\{Attendance, Branch, Customer, Subscription};
use App\Services\System\Customers\Tracking\TrackingAttendanceBusinessService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class TrackingAttendanceController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $initParams->bool = true;

        return $initParams;

    }

    public function list(Request $request) {

        //

    }

    public function index(Request $request) {

        $company = $request->get("company");

        $branchId = base64_decode($request->branch ?? "");

        $branch = Branch::where("id", $branchId)
                        ->where("company_id", $company->id)
                        ->first();

        if(Utilities::isDefined($branch)) {

            return view("Guest/general/tracking_attendances/main", ["company" => $company, "branch" => $branch, "withMenu" => false]);

        }else {

            return response()->view("errors.500", ["msg" => "La sucursal no es válida"], 500);

        }

    }

    public function signedIndex(Request $request, int $branch) {

        $company = $request->get("company");
        $record = Branch::query()
            ->where("company_id", $company->id)
            ->where("status", "active")
            ->find($branch);

        abort_unless($record, 404, "La sucursal no está disponible.");

        return view("Guest/general/tracking_attendances/main", [
            "company" => $company,
            "branch" => $record,
            "withMenu" => false
        ]);

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

    public function qrCamera(PublicAttendanceRequest $request, TrackingAttendanceBusinessService $attendanceService) {

        $company = $request->get("company");

        $startDate = now();
        $endDate   = now();

        $attendances = collect();

        foreach($request->validated("customers") as $customerRequest) {

            $result = $attendanceService->validateAndCreateAttendance([
                "company_id"  => $company->id,
                "branch_id"   => $request->branch_id,
                "customer_id" => $customerRequest["customer_id"],
                "start_date"  => $startDate,
                "end_date"    => $endDate,
                "observation" => null,
                "user_id"     => null,
                "type"        => "qr_public",
                "action"      => "automatic"
            ]);

            $attendances->push($result);

        }

        $bool = count($attendances->where("bool", true)) > 0;
        $msg  = $bool ? "Asistencias creadas correctamente." : "No se han podido crear las asistencias.";

        return response()->json(["bool" => $bool, "msg" => $msg, "attendances" => $attendances], 200);

    }

}
