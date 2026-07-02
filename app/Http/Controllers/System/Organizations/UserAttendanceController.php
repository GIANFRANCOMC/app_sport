<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Organizations;

use App\Http\Controllers\System\Base\BaseController;
use App\Http\Requests\System\Organizations\UserAttendances\{
    CheckInUserAttendanceRequest,
    CheckOutUserAttendanceRequest
};
use App\Services\System\Organizations\Users\UserAttendanceService;
use Illuminate\Http\{JsonResponse, Request};

final class UserAttendanceController extends BaseController {

    private const TRANSLATION_NAMESPACE = "System.Organizations.user_attendance";

    public function list(Request $request) {

        return UserAttendanceService::getPaginatedList(
            $this->getCompanyId(),
            [
                "branch_id" => $request->input("branch_id"),
                "user_id" => $request->input("user_id"),
                "status" => $request->input("status"),
                "date_from" => $request->input("date_from"),
                "date_to" => $request->input("date_to")
            ],
            $this->getPerPage($request)
        );

    }

    public function checkIn(CheckInUserAttendanceRequest $request): JsonResponse {

        try {

            $attendance = UserAttendanceService::checkIn([
                ...$request->validated(),
                "company_id" => $this->getCompanyId(),
                "actor_id" => $this->getUserId()
            ]);

            return response()->json([
                "bool" => true,
                "msg" => "Ingreso del colaborador registrado correctamente.",
                "attendance" => $attendance->load(["branch", "user"])
            ]);

        }catch(\DomainException $exception) {

            return response()->json([
                "bool" => false,
                "msg" => $exception->getMessage()
            ], 422);

        }catch(\Exception $exception) {

            return $this->handleException($exception, "check_in");

        }

    }

    public function checkOut(CheckOutUserAttendanceRequest $request): JsonResponse {

        try {

            $attendance = UserAttendanceService::checkOut([
                ...$request->validated(),
                "company_id" => $this->getCompanyId(),
                "actor_id" => $this->getUserId()
            ]);

            return response()->json([
                "bool" => true,
                "msg" => "Salida registrada. La jornada quedó finalizada.",
                "attendance" => $attendance
            ]);

        }catch(\DomainException $exception) {

            return response()->json([
                "bool" => false,
                "msg" => $exception->getMessage()
            ], 422);

        }catch(\Exception $exception) {

            return $this->handleException($exception, "check_out");

        }

    }

    public function weeklySummary(Request $request): JsonResponse {

        $request->validate([
            "user_id" => ["required", "integer", new \App\Rules\System\Defaults\BelongsToCompany("users")],
            "branch_id" => ["nullable", "integer", new \App\Rules\System\Defaults\BelongsToCompany("branches")],
            "week_start" => ["nullable", "date"]
        ]);

        return response()->json([
            "bool" => true,
            "summary" => UserAttendanceService::weeklySummary(
                $this->getCompanyId(),
                (int) $request->input("user_id"),
                $request->input("week_start"),
                $request->filled("branch_id") ? (int) $request->input("branch_id") : null
            )
        ]);

    }

    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
