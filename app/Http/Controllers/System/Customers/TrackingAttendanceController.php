<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request, Response};
use Carbon\Carbon;
use DomainException;

use App\Http\Requests\System\Customers\TrackingAttendances\{
    CancelTrackingAttendanceRequest,
    ReviewAttendanceCorrectionRequest,
    StoreAttendanceCorrectionRequest
};
use App\Services\System\Customers\Tracking\{TrackingAttendanceConfigService, TrackingAttendanceService, TrackingAttendanceBusinessService};
use App\Models\System\Customers\Attendance;
use App\Services\System\Organizations\AccessScopeService;

class TrackingAttendanceController extends BaseController {

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

        $page = $this->getPage($request);
        return TrackingAttendanceConfigService::getInitParams($this->getCompanyId(), $page, $this->getUserId());

    }

    /**
     * Get paginated list of attendances with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $perPage = $this->getPerPage($request, Utilities::$per_page_max);

        return TrackingAttendanceService::getPaginatedList(
            $this->getCompanyId(),
            $this->filters($request),
            $perPage,
            AccessScopeService::allowedIds($this->getAuthUser(), AccessScopeService::BRANCH)
        );

    }

    public function export(Request $request): Response|JsonResponse {

        try {
            $records = TrackingAttendanceService::getForExport(
                $this->getCompanyId(),
                $this->filters($request),
                AccessScopeService::allowedIds($this->getAuthUser(), AccessScopeService::BRANCH)
            );
        }catch(DomainException $exception) {
            return response()->json([
                "bool" => false,
                "msg" => $exception->getMessage()
            ], 422);
        }
        $handle = fopen("php://temp", "r+");

        fputcsv($handle, [
            "Inicio",
            "Fin",
            "Sucursal",
            "Cliente",
            "Estado",
            "Origen",
            "Dispositivo biométrico",
            "Observación"
        ], ";");

        foreach($records as $record) {
            fputcsv($handle, [
                $record->start_date,
                $record->end_date,
                $record->branch?->name,
                $record->customer?->name,
                $record->formatted_status,
                $record->type,
                $record->biometricDevice?->name,
                $record->observation
            ], ";");
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response("\xEF\xBB\xBF".$csv, 200, [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=asistencias-clientes-".now()->format("Ymd-His").".csv"
        ]);

    }

    /**
     * Display the tracking attendances index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Customers/tracking_attendances/main");

    }


    public function store(Request $request, TrackingAttendanceBusinessService $businessService): JsonResponse {

        try {

            $startDate = Utilities::isDefined($request->start_date)
                ? Carbon::parse(str_replace("T", " ", $request->start_date))
                : now();
            $endDate = Utilities::isDefined($request->end_date)
                ? Carbon::parse(str_replace("T", " ", $request->end_date))
                : now();

            $result = $businessService->validateAndCreateAttendance([
                "company_id"  => $this->getCompanyId(),
                "branch_id"   => $request->branch_id,
                "customer_id" => $request->customer_id,
                "start_date"  => $startDate,
                "end_date"    => $endDate,
                "observation" => $request->observation,
                "user_id"     => $this->getUserId(),
                "type"        => "manual_form",
                "action"      => "automatic"
            ]);

            if($result["bool"]) {

                return $this->successResponse(
                    ["attendances" => [$result]],
                    "created"
                );

            }

            return response()->json([
                "bool" => false,
                "msg" => $result["msg"] ?? "No fue posible registrar la asistencia.",
                "attendances" => [$result]
            ], 422);

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }



    public function update(Request $request, int $id, TrackingAttendanceBusinessService $businessService): JsonResponse {

        try {

            $endDate = Utilities::isDefined($request->end_date)
                ? Carbon::parse(str_replace("T", " ", $request->end_date))
                : now();

            $result = $businessService->validateAndCreateAttendance([
                "company_id"  => $this->getCompanyId(),
                "branch_id"   => $request->branch_id,
                "customer_id" => $request->customer_id,
                "start_date"  => null,
                "end_date"    => $endDate,
                "observation" => null,
                "user_id"     => $this->getUserId(),
                "action"      => "checkout"
            ]);

            if($result["bool"]) {

                return $this->successResponse(
                    ["attendances" => [$result]],
                    "updated"
                );

            }

            return response()->json([
                "bool" => false,
                "msg" => $result["msg"] ?? "No fue posible registrar la salida.",
                "attendances" => [$result]
            ], 422);

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

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

            $attendance = Attendance::findOrFail($id);

            if(!Utilities::isDefined($attendance) || $attendance->company_id !== $this->getCompanyId()) {

                return $this->notFoundResponse();

            }

            $attendance = TrackingAttendanceService::cancel($attendance, $request->motive, $this->getUserId());

            return $this->updatedResponse($attendance, "canceled", "attendance");

        }catch(\Exception $e) {

            return $this->handleException($e, "cancel");

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


    public function requestCorrection(StoreAttendanceCorrectionRequest $request, int $id): JsonResponse {

        try {

            $attendance = Attendance::query()
                ->where("company_id", $this->getCompanyId())
                ->find($id);

            if(!$attendance
                || !AccessScopeService::canAccess(auth()->user(), AccessScopeService::BRANCH, (int) $attendance->branch_id)) {

                return $this->notFoundResponse();

            }

            $correction = TrackingAttendanceService::requestCorrection(
                $attendance,
                $request->validated(),
                $this->getUserId()
            );

            return $this->createdResponse($correction, "created", "attendanceCorrection");

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    public function reviewCorrection(ReviewAttendanceCorrectionRequest $request, int $id): JsonResponse {

        try {

            $correction = AttendanceCorrection::query()
                ->where("company_id", $this->getCompanyId())
                ->with("attendance")
                ->find($id);

            if(!$correction
                || !AccessScopeService::canAccess(auth()->user(), AccessScopeService::BRANCH, (int) $correction->attendance->branch_id)) {

                return $this->notFoundResponse();

            }

            $correction = TrackingAttendanceService::reviewCorrection(
                $correction,
                $request->decision,
                $request->note,
                $this->getUserId()
            );

            return $this->updatedResponse($correction, "updated", "attendanceCorrection");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    public function qrCamera(Request $request, TrackingAttendanceBusinessService $businessService): JsonResponse {

        try {

            $startDate = Utilities::isDefined($request->start_date)
                ? Carbon::parse(str_replace("T", " ", $request->start_date))
                : now();
            $endDate = Utilities::isDefined($request->end_date)
                ? Carbon::parse(str_replace("T", " ", $request->end_date))
                : now();

            $attendances = collect();

            foreach($request->customers as $customerRequest) {

                $result = $businessService->validateAndCreateAttendance([
                    "company_id"  => $this->getCompanyId(),
                    "branch_id"   => $request->branch_id,
                    "customer_id" => $customerRequest["customer_id"],
                    "start_date"  => $startDate,
                    "end_date"    => $endDate,
                    "observation" => $request->observation,
                    "user_id"     => $this->getUserId(),
                    "type"        => "qr_camera",
                    "action"      => "automatic"
                ]);

                $attendances->push($result);

            }

            $successCount = $attendances->where("bool", true)->count();

            if($successCount > 0) {

                return $this->successResponse(
                    ["attendances" => $attendances->all()],
                    "created"
                );

            }

            return $this->errorResponse("create_failed", [], 422);

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    public function qrScanner(Request $request, TrackingAttendanceBusinessService $businessService): JsonResponse {

        try {

            $startDate = Utilities::isDefined($request->start_date)
                ? Carbon::parse(str_replace("T", " ", $request->start_date))
                : now();
            $endDate = Utilities::isDefined($request->end_date)
                ? Carbon::parse(str_replace("T", " ", $request->end_date))
                : now();

            $attendances = collect();

            foreach($request->customers as $customerRequest) {

                $result = $businessService->validateAndCreateAttendance([
                    "company_id"  => $this->getCompanyId(),
                    "branch_id"   => $request->branch_id,
                    "customer_id" => $customerRequest["customer_id"] ?? "",
                    "customer_document_number" => $customerRequest["customer_document_number"] ?? "",
                    "customer_attendance_type" => $customerRequest["customer_attendance_type"] ?? "",
                    "start_date"  => $startDate,
                    "end_date"    => $endDate,
                    "observation" => $request->observation,
                    "user_id"     => $this->getUserId(),
                    "type"        => "qr_scanner",
                    "action"      => "automatic"
                ]);

                $attendances->push($result);

            }

            $successCount = $attendances->where("bool", true)->count();

            if($successCount > 0) {

                return $this->successResponse(
                    ["attendances" => $attendances->all()],
                    "created"
                );

            }

            return $this->errorResponse("create_failed", [], 422);

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    private function filters(Request $request): array {

        return $request->only(["branch_id", "customer_id", "status", "start_date", "end_date"]);

    }

}
