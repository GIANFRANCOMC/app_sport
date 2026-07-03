<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Users;

use App\Models\System\Organizations\{Branch, User, UserAttendance};
use App\Services\System\Devices\BiometricDevices\BiometricDeviceService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class UserAttendanceService {

    public const SOURCE_MANUAL = "manual_form";
    public const SOURCE_QR_CAMERA = "qr_camera";
    public const SOURCE_QR_SCANNER = "qr_scanner";
    public const SOURCE_BIOMETRIC = "biometric";
    public const SOURCE_SYSTEM = "system";

    public const STATUS_ACTIVE = "active";
    public const STATUS_FINALIZED = "finalized";
    public const STATUS_CANCELED = "canceled";

    public static function sourceTypes(): array {

        return [
            self::SOURCE_MANUAL,
            self::SOURCE_QR_CAMERA,
            self::SOURCE_QR_SCANNER,
            self::SOURCE_BIOMETRIC,
            self::SOURCE_SYSTEM
        ];

    }

    public static function checkIn(array $data): UserAttendance {

        return DB::transaction(function() use($data) {

            $companyId = (int) $data["company_id"];
            $branchId = (int) $data["branch_id"];
            $userId = (int) $data["user_id"];
            $actorId = isset($data["actor_id"]) ? (int) $data["actor_id"] : null;
            $checkedInAt = Carbon::parse($data["checked_in_at"] ?? now());

            self::lockActiveUser($companyId, $userId);
            self::requireActiveBranch($companyId, $branchId);

            $activeAttendance = UserAttendance::query()
                ->where("company_id", $companyId)
                ->where("user_id", $userId)
                ->where("status", self::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if($activeAttendance) {

                $branchName = $activeAttendance->branch()->value("name") ?? "otra sucursal";
                throw new DomainException("El colaborador ya tiene una jornada en curso en {$branchName}.");

            }

            return UserAttendance::create([
                "company_id" => $companyId,
                "branch_id" => $branchId,
                "user_id" => $userId,
                "work_date" => $checkedInAt->toDateString(),
                "checked_in_at" => $checkedInAt,
                "worked_minutes" => 0,
                "source_type" => $data["source_type"] ?? self::SOURCE_MANUAL,
                "source_reference" => $data["source_reference"] ?? null,
                "observation" => $data["observation"] ?? null,
                "status" => self::STATUS_ACTIVE,
                "created_at" => now(),
                "created_by" => $actorId
            ]);

        });

    }

    public static function checkInFromBiometric(array $data): UserAttendance {

        $companyId = (int) $data["company_id"];
        $deviceId = (int) $data["device_id"];
        $deviceUserId = (int) $data["device_user_id"];
        $branchId = (int) $data["branch_id"];
        $device = BiometricDeviceService::findByIdAndCompany($deviceId, $companyId, ["active"]);

        if(!$device || (int) $device->branch_id !== $branchId) {
            throw new DomainException("El dispositivo biométrico no está activo en la sucursal seleccionada.");
        }

        $user = BiometricDeviceService::findUserByDeviceUserId($deviceId, $deviceUserId, $companyId);

        if(!$user) {

            throw new DomainException("No se encontró un colaborador activo para la identidad biométrica.");

        }

        return self::checkIn([
            ...$data,
            "user_id" => (int) $user->id,
            "source_type" => self::SOURCE_BIOMETRIC,
            "source_reference" => "device:{$deviceId};user:{$deviceUserId}"
        ]);

    }

    public static function checkOut(array $data): UserAttendance {

        return DB::transaction(function() use($data) {

            $companyId = (int) $data["company_id"];
            $branchId = (int) $data["branch_id"];
            $userId = (int) $data["user_id"];
            $actorId = isset($data["actor_id"]) ? (int) $data["actor_id"] : null;
            $checkedOutAt = Carbon::parse($data["checked_out_at"] ?? now());

            self::lockActiveUser($companyId, $userId);
            self::requireActiveBranch($companyId, $branchId);

            $attendance = UserAttendance::query()
                ->where("company_id", $companyId)
                ->where("user_id", $userId)
                ->where("status", self::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if(!$attendance) {

                throw new DomainException("El colaborador no tiene una jornada en curso.");

            }

            if((int) $attendance->branch_id !== $branchId) {

                throw new DomainException("La jornada en curso pertenece a otra sucursal.");

            }

            $checkedInAt = Carbon::parse($attendance->checked_in_at);

            if($checkedOutAt->lessThanOrEqualTo($checkedInAt)) {

                throw new DomainException("La salida debe ser posterior al ingreso.");

            }

            $attendance->checked_out_at = $checkedOutAt;
            $attendance->worked_minutes = $checkedInAt->diffInMinutes($checkedOutAt);
            $attendance->status = self::STATUS_FINALIZED;
            $attendance->updated_at = now();
            $attendance->updated_by = $actorId;
            $attendance->save();

            return $attendance->fresh(["branch", "user"]);

        });

    }

    public static function getPaginatedList(
        int $companyId,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {

        $query = UserAttendance::query()
            ->where("company_id", $companyId)
            ->with(["branch", "user"]);

        if(!empty($filters["branch_id"])) {
            $query->where("branch_id", (int) $filters["branch_id"]);
        }

        if(!empty($filters["user_id"])) {
            $query->where("user_id", (int) $filters["user_id"]);
        }

        if(!empty($filters["status"])) {
            $query->where("status", (string) $filters["status"]);
        }

        if(!empty($filters["date_from"])) {
            $query->whereDate("work_date", ">=", $filters["date_from"]);
        }

        if(!empty($filters["date_to"])) {
            $query->whereDate("work_date", "<=", $filters["date_to"]);
        }

        return $query->orderByDesc("checked_in_at")->paginate($perPage);

    }

    public static function weeklySummary(
        int $companyId,
        int $userId,
        ?string $weekStart = null,
        ?int $branchId = null
    ): array {

        $start = Carbon::parse($weekStart ?? now())->startOfWeek(Carbon::MONDAY);
        $end = $start->copy()->endOfWeek(Carbon::SUNDAY);

        $query = UserAttendance::query()
            ->where("company_id", $companyId)
            ->where("user_id", $userId)
            ->where("status", self::STATUS_FINALIZED)
            ->whereBetween("work_date", [$start->toDateString(), $end->toDateString()]);

        if($branchId) {
            $query->where("branch_id", $branchId);
        }

        $records = $query->orderBy("work_date")->get();
        $totalMinutes = (int) $records->sum("worked_minutes");

        return [
            "week_start" => $start->toDateString(),
            "week_end" => $end->toDateString(),
            "total_minutes" => $totalMinutes,
            "total_hours" => round($totalMinutes / 60, 2),
            "days" => $records
                ->groupBy(fn(UserAttendance $attendance) => $attendance->work_date->toDateString())
                ->map(fn($dayRecords, $date) => [
                    "date" => $date,
                    "worked_minutes" => (int) $dayRecords->sum("worked_minutes"),
                    "worked_hours" => round(((int) $dayRecords->sum("worked_minutes")) / 60, 2)
                ])
                ->values()
                ->all()
        ];

    }

    private static function lockActiveUser(int $companyId, int $userId): User {

        $user = User::query()
            ->where("company_id", $companyId)
            ->where("status", "active")
            ->lockForUpdate()
            ->find($userId);

        if(!$user) {

            throw new DomainException("El colaborador no está activo o no pertenece a la empresa.");

        }

        return $user;

    }

    private static function requireActiveBranch(int $companyId, int $branchId): Branch {

        $branch = Branch::query()
            ->where("company_id", $companyId)
            ->where("status", "active")
            ->find($branchId);

        if(!$branch) {

            throw new DomainException("La sucursal no está activa o no pertenece a la empresa.");

        }

        return $branch;

    }

}
