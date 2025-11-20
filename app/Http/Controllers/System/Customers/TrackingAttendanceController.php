<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\TrackingAttendances\{CancelTrackingAttendanceRequest};
use App\Models\System\Customers\{Attendance, Customer};
use App\Models\System\Organizations\{Branch};
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class TrackingAttendanceController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->branches = new stdClass();
            $config->branches->records = Branch::getAll("tracking_attendance");

            $config->customers = new stdClass();
            $config->customers->records = Customer::getAll("tracking_attendance");

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

        $list = Attendance::when(Utilities::isDefined($request->customer_id), function($query) use($request) {

                            $query->where(function($query) use($request) {

                                $query->where("customer_id", $request->customer_id);

                            });

                          })
                          ->when(Utilities::isDefined($request->status), function($query) use($request) {

                            $query->where(function($query) use($request) {

                                $query->where("status", $request->status);

                            });

                          })
                          ->where("company_id", $userAuth->company_id)
                          ->where("branch_id", $branch->id)
                          ->whereDate("created_at", $request->start_date ?? date("Y-m-d"))
                          ->orderBy("id", "DESC")
                          ->with(["branch", "customer"])
                          ->paginate($request->per_page ?? Utilities::$per_page_max);

        return $list;

    }

    public function index() {

        return view("System/general/tracking_attendances/main");

    }

    public function create() {

        //

    }

    public function store(Request $request, AttendanceService $attendanceService) { // StoreTrackingAttendanceRequest

        $userAuth = Auth::user();

        $startDate = Utilities::isDefined($request->start_date) ? Carbon::parse(str_replace("T", " ", $request->start_date)) : now();
        $endDate   = Utilities::isDefined($request->end_date) ? Carbon::parse(str_replace("T", " ", $request->end_date)) : now();

        $attendances = collect();

        $result = $attendanceService->validateAndCreateAttendance([
            "company_id"  => $userAuth->company_id,
            "branch_id"   => $request->branch_id,
            "customer_id" => $request->customer_id,
            "start_date"  => $startDate,
            "end_date"    => $endDate,
            "observation" => $request->observation,
            "user_id"     => $userAuth->id,
            "type"        => "manual_form",
            "action"      => "automatic"
        ]);

        $attendances->push($result);

        $bool = count($attendances->where("bool", true)) > 0;
        $msg  = $bool ? "Asistencias creadas correctamente." : "No se han podido crear las asistencias.";

        return response()->json(["bool" => $bool, "msg" => $msg, "attendances" => $attendances], 200);

    }

    public function show(Attendance $attendance) {

        //

    }

    public function edit(Attendance $attendance) {

        //

    }

    public function update(Request $request, $id, AttendanceService $attendanceService) { // UpdateTrackingAttendanceRequest

        $userAuth = Auth::user();

        $endDate = Utilities::isDefined($request->end_date) ? Carbon::parse(str_replace("T", " ", $request->end_date)) : now();

        $attendances = collect();

        $result = $attendanceService->validateAndCreateAttendance([
            "company_id"  => $userAuth->company_id,
            "branch_id"   => $request->branch_id,
            "customer_id" => $request->customer_id,
            "start_date"  => null,
            "end_date"    => $endDate,
            "observation" => null,
            "user_id"     => $userAuth->id,
            "action"      => "checkout"
        ]);

        $attendances->push($result);

        $bool = count($attendances->where("bool", true)) > 0;
        $msg  = $bool ? "Asistencias concluidas correctamente." : "No se han podido concluir las asistencias.";

        return response()->json(["bool" => $bool, "msg" => $msg, "attendances" => $attendances], 200);

    }

    public function cancel(CancelTrackingAttendanceRequest $request, $id) {

        $userAuth = Auth::user();

        $attendance = Attendance::findOrFail($id);

        if(Utilities::isDefined($attendance) && in_array($attendance->status, ["active", "finalized"])) {

            if(Utilities::isDefined($attendance) && $attendance->company_id == $userAuth->company_id) {

                $attendance->motive      = $request->motive ?? "N/A";
                $attendance->status      = "canceled";
                $attendance->updated_at  = now();
                $attendance->updated_by  = $userAuth->id ?? null;
                $attendance->canceled_at = now();
                $attendance->canceled_by = $userAuth->id ?? null;
                $attendance->save();

            }

        }

        $bool = $attendance->wasChanged();
        $msg  = $bool ? "Asistencia anulada correctamente." : "No se ha podido anular la asistencia.";

        return response()->json(["bool" => $bool, "msg" => $msg, "attendance" => $attendance], 200);

    }

    public function destroy(Attendance $attendance) {

        //

    }

    public function qrCamera(Request $request, AttendanceService $attendanceService) { // StoreTrackingAttendanceRequest

        $userAuth = Auth::user();

        $startDate = Utilities::isDefined($request->start_date) ? Carbon::parse(str_replace("T", " ", $request->start_date)) : now();
        $endDate   = Utilities::isDefined($request->end_date) ? Carbon::parse(str_replace("T", " ", $request->end_date)) : now();

        $attendances = collect();

        foreach($request->customers as $customerRequest) {

            $result = $attendanceService->validateAndCreateAttendance([
                "company_id"  => $userAuth->company_id,
                "branch_id"   => $request->branch_id,
                "customer_id" => $customerRequest["customer_id"],
                "start_date"  => $startDate,
                "end_date"    => $endDate,
                "observation" => $request->observation,
                "user_id"     => $userAuth->id,
                "type"        => "qr_camera",
                "action"      => "automatic"
            ]);

            $attendances->push($result);

        }

        $bool = count($attendances->where("bool", true)) > 0;
        $msg  = $bool ? "Asistencias creadas correctamente." : "No se han podido crear las asistencias.";

        return response()->json(["bool" => $bool, "msg" => $msg, "attendances" => $attendances], 200);

    }

    public function qrScanner(Request $request, AttendanceService $attendanceService) { // StoreTrackingAttendanceRequest

        $userAuth = Auth::user();

        $startDate = Utilities::isDefined($request->start_date) ? Carbon::parse(str_replace("T", " ", $request->start_date)) : now();
        $endDate   = Utilities::isDefined($request->end_date) ? Carbon::parse(str_replace("T", " ", $request->end_date)) : now();

        $attendances = collect();

        foreach($request->customers as $customerRequest) {

            $result = $attendanceService->validateAndCreateAttendance([
                "company_id"  => $userAuth->company_id,
                "branch_id"   => $request->branch_id,
                "customer_id" => $customerRequest["customer_id"] ?? "",
                "customer_document_number" => $customerRequest["customer_document_number"] ?? "",
                "customer_attendance_type" => $customerRequest["customer_attendance_type"] ?? "",
                "start_date"  => $startDate,
                "end_date"    => $endDate,
                "observation" => $request->observation,
                "user_id"     => $userAuth->id,
                "type"        => "qr_scanner",
                "action"      => "automatic"
            ]);

            $attendances->push($result);

        }

        $bool = count($attendances->where("bool", true)) > 0;
        $msg  = $bool ? "Asistencias creadas correctamente." : "No se han podido crear las asistencias.";

        return response()->json(["bool" => $bool, "msg" => $msg, "attendances" => $attendances], 200);

    }

}
