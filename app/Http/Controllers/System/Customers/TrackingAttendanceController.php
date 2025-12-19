<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Carbon\Carbon;

use App\Http\Requests\System\Customers\TrackingAttendances\{CancelTrackingAttendanceRequest};
use App\Services\System\Customers\Tracking\{TrackingAttendanceConfigService, TrackingAttendanceService, TrackingAttendanceBusinessService};
use App\Models\System\Customers\{Attendance};

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
        return TrackingAttendanceConfigService::getInitParams($this->getCompanyId(), $page);

    }

    /**
     * Get paginated list of attendances with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $filters = [
            "branch_id"   => $request->input("branch_id"),
            "customer_id" => $request->input("customer_id"),
            "status"      => $request->input("status"),
            "start_date"  => $request->input("start_date")
        ];
        $perPage = $this->getPerPage($request, Utilities::$per_page_max);

        return TrackingAttendanceService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    public function index() {

        return view("System/general/Customers/tracking_attendances/main");

    }

    public function create() {

        //

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

            return $this->errorResponse("create_failed", [], 422);

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    public function show(Attendance $attendance): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    public function edit(Attendance $attendance): void {

        // Form is handled by frontend SPA

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

            return $this->errorResponse("update_failed", [], 422);

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

            TrackingAttendanceConfigService::clearAllCache($this->getCompanyId());

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

    public function destroy(Attendance $attendance): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

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

}
