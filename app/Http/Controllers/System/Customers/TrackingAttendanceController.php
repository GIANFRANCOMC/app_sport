<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};
use Carbon\Carbon;

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Http\Requests\System\Customers\TrackingAttendances\{CancelTrackingAttendanceRequest};
use App\Services\System\Customers\Tracking\{TrackingAttendanceConfigService, TrackingAttendanceService};
use App\Services\AttendanceService;
use App\Models\System\Customers\{Attendance};

class TrackingAttendanceController extends Controller {

    use HandlesApiResponses;

    /**
     * Translation namespace for tracking attendance module
     */
    private const TRANSLATION_NAMESPACE = "System.Customers.tracking_attendance";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return TrackingAttendanceConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of attendances with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $userAuth = Auth::user();
        $filters  = [
            "branch_id"   => $request->input("branch_id"),
            "customer_id" => $request->input("customer_id"),
            "status"      => $request->input("status"),
            "start_date"  => $request->input("start_date")
        ];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_max);

        return TrackingAttendanceService::getPaginatedList($userAuth->company_id, $filters, $perPage);

    }

    public function index() {

        return view("System/general/Customers/tracking_attendances/main");

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

    /**
     * Cancel the specified attendance
     *
     * @param CancelTrackingAttendanceRequest $request
     * @param int $id Attendance ID
     * @return JsonResponse
     */
    public function cancel(CancelTrackingAttendanceRequest $request, int $id): JsonResponse {

        try {

            $userAuth = Auth::user();
            $attendance = Attendance::findOrFail($id);

            if(!Utilities::isDefined($attendance) || $attendance->company_id != $userAuth->company_id) {

                return $this->notFoundResponse();

            }

            $attendance = TrackingAttendanceService::cancel($attendance, $request->motive, $userAuth->id);

            TrackingAttendanceConfigService::clearAllCache($userAuth->company_id);

            return $this->updatedResponse($attendance, "canceled", "attendance");

        }catch(Exception $e) {

            return response()->json(["bool" => false, "msg" => $e->getMessage()], 200);

        }

    }

    /**
     * Get translation namespace for tracking attendance module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

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
