<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Organizations;

use App\Http\Controllers\System\Base\BaseController;
use App\Http\Requests\System\Organizations\UserAttendances\{
    CheckInUserAttendanceRequest,
    CheckOutUserAttendanceRequest
};
use App\Services\System\Organizations\Users\{UserAttendanceConfigService, UserAttendanceService};
use App\Services\System\Base\CompanyReferenceDataService;
use App\Services\System\Organizations\Companies\CompanySettingService;
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\Validation\ValidationException;

final class UserAttendanceController extends BaseController {

    private const TRANSLATION_NAMESPACE = "System.Organizations.user_attendance";

    public function index() {

        return view("System/general/Organizations/user_attendances/main");

    }

    public function initParams(Request $request): JsonResponse {

        return response()->json(
            UserAttendanceConfigService::getInitParams(
                $this->getCompanyId(),
                (string) $request->input("page", "main")
            )
        );

    }

    public function list(Request $request) {

        return response()->json([
            "bool" => true,
            "data" => UserAttendanceService::getPaginatedList(
                $this->getCompanyId(),
                [
                    "branch_id" => $request->input("branch_id"),
                    "user_id" => $request->input("user_id"),
                    "status" => $request->input("status"),
                    "date_from" => $request->input("date_from"),
                    "date_to" => $request->input("date_to")
                ],
                $this->getPerPage($request),
                $this->allowedBranchIds()
            )
        ]);

    }

    public function export(Request $request): Response {

        $filters = [
            "branch_id" => $request->input("branch_id"),
            "user_id" => $request->input("user_id"),
            "status" => $request->input("status"),
            "date_from" => $request->input("date_from"),
            "date_to" => $request->input("date_to")
        ];
        $query = UserAttendanceService::getFilteredQuery(
            $this->getCompanyId(),
            $filters,
            $this->allowedBranchIds()
        );
        $limit = max(100, (int) CompanySettingService::value(
            $this->getCompanyId(),
            "reports",
            "export_max_rows",
            25000
        ));

        if((clone $query)->limit($limit + 1)->count() > $limit) {
            throw ValidationException::withMessages([
                "filters" => "El reporte supera {$limit} registros. Reduce el rango o aplica más filtros."
            ]);
        }

        $handle = fopen("php://temp", "r+");
        fputcsv($handle, [
            "Fecha", "Colaborador", "Sucursal", "Ingreso", "Salida", "Horas trabajadas",
            "Minutos ordinarios", "Tardanza", "Horas extra", "Pausas", "Estado"
        ], ";");

        foreach($query->orderByDesc("checked_in_at")->get() as $attendance) {
            fputcsv($handle, [
                $attendance->work_date?->format("Y-m-d"),
                $attendance->user?->name,
                $attendance->branch?->name,
                $attendance->checked_in_at?->format("Y-m-d H:i:s"),
                $attendance->checked_out_at?->format("Y-m-d H:i:s"),
                number_format((float) $attendance->worked_hours, 2, ".", ""),
                (int) $attendance->ordinary_minutes,
                (int) $attendance->late_minutes,
                (int) $attendance->overtime_minutes,
                (int) $attendance->break_minutes,
                $attendance->status
            ], ";");
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response("\xEF\xBB\xBF".$csv, 200, [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=gympe-asistencia-laboral-".now()->format("Ymd-His").".csv"
        ]);

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

    public function biometricCheckIn(Request $request): JsonResponse {

        $data = $request->validate([
            "branch_id" => ["required", "integer", new \App\Rules\System\Defaults\BelongsToCompany("branches", ["status" => "active"])],
            "device_id" => ["required", "integer", new \App\Rules\System\Defaults\BelongsToCompany("biometric_devices", ["status" => "active"])],
            "device_user_id" => ["required", "integer", "min:1"],
            "checked_in_at" => ["nullable", "date"]
        ], [
            "required" => "Campo obligatorio.",
            "integer" => "Debe ser un número entero.",
            "min" => "El valor es menor al permitido.",
            "date" => "La fecha y hora no son válidas."
        ]);

        try {

            $attendance = UserAttendanceService::checkInFromBiometric([
                ...$data,
                "company_id" => $this->getCompanyId(),
                "actor_id" => $this->getUserId()
            ]);

            return response()->json([
                "bool" => true,
                "msg" => "Ingreso biométrico registrado correctamente.",
                "attendance" => $attendance->load(["branch", "user"])
            ]);

        }catch(\DomainException $exception) {

            return response()->json(["bool" => false, "msg" => $exception->getMessage()], 422);

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
                $request->filled("branch_id") ? (int) $request->input("branch_id") : null,
                $this->allowedBranchIds()
            )
        ]);

    }

    public function startBreak(Request $request, int $attendanceId): JsonResponse {

        $data = $request->validate(["reason" => ["nullable", "string", "max:500"]]);

        return $this->domainResponse(function() use($attendanceId, $data) {
            $break = UserAttendanceService::startBreak(
                $this->getCompanyId(),
                $attendanceId,
                $this->getUserId(),
                $data["reason"] ?? null
            );

            return ["msg" => "Pausa iniciada correctamente.", "break" => $break];
        });

    }

    public function endBreak(int $attendanceId): JsonResponse {

        return $this->domainResponse(function() use($attendanceId) {
            $break = UserAttendanceService::endBreak(
                $this->getCompanyId(),
                $attendanceId,
                $this->getUserId()
            );

            return ["msg" => "Pausa finalizada correctamente.", "break" => $break];
        });

    }

    public function requestCorrection(Request $request, int $attendanceId): JsonResponse {

        $data = $request->validate([
            "checked_in_at" => ["nullable", "date", "required_without:checked_out_at"],
            "checked_out_at" => ["nullable", "date", "after:checked_in_at", "required_without:checked_in_at"],
            "reason" => ["required", "string", "max:500"]
        ]);

        return $this->domainResponse(function() use($attendanceId, $data) {
            $correction = UserAttendanceService::requestCorrection(
                $this->getCompanyId(),
                $attendanceId,
                $this->getUserId(),
                $data
            );

            return ["msg" => "Solicitud de corrección registrada.", "correction" => $correction];
        });

    }

    public function reviewCorrection(Request $request, int $correctionId): JsonResponse {

        $data = $request->validate([
            "approve" => ["required", "boolean"],
            "note" => ["nullable", "string", "max:500"]
        ]);

        return $this->domainResponse(function() use($correctionId, $data) {
            $correction = UserAttendanceService::reviewCorrection(
                $this->getCompanyId(),
                $correctionId,
                $this->getUserId(),
                (bool) $data["approve"],
                $data["note"] ?? null
            );

            return ["msg" => "Solicitud de corrección revisada.", "correction" => $correction];
        });

    }

    private function domainResponse(callable $callback): JsonResponse {

        try {
            return response()->json(["bool" => true, ...$callback()]);
        }catch(\DomainException $exception) {
            return response()->json(["bool" => false, "msg" => $exception->getMessage()], 422);
        }

    }

    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

    private function allowedBranchIds(): ?array {

        return CompanyReferenceDataService::for($this->getCompanyId(), $this->getUserId())
            ->allowedBranchIds();

    }

}
