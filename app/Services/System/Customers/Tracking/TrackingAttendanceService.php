<?php

declare(strict_types=1);

namespace App\Services\System\Customers\Tracking;

use App\Helpers\System\{TranslationHelper, Utilities};
use App\Models\System\Customers\Attendance;
use App\Models\System\Organizations\Branch;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service class for managing Tracking Attendance operations
 * Handles business logic for listing and managing attendances
 */
class TrackingAttendanceService {

    /**
     * Translation namespace for tracking attendance module
     */
    private const TRANSLATION_NAMESPACE = "System.Customers.tracking_attendance";

    /**
     * Get translation with fallback
     *
     * @param string $key Translation key
     * @param array $replace Replacements
     * @return string
     */
    private static function trans(string $key, array $replace = []): string {

        return TranslationHelper::getWithFallback(self::TRANSLATION_NAMESPACE, $key, $replace);

    }

    /**
     * Get paginated list of attendances
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator {

        $branch = Branch::where("id", $filters["branch_id"] ?? null)
                        ->where("company_id", $companyId)
                        ->first();

        if(!Utilities::isDefined($branch)) {

            return new LengthAwarePaginator([], 0, 1, 1, ["path" => ""]);

        }

        $query = Attendance::where("company_id", $companyId)
                           ->where("branch_id", $branch->id)
                           ->whereDate("created_at", $filters["start_date"] ?? date("Y-m-d"));

        // Apply filters
        self::applyFilters($query, $filters);

        // Apply ordering
        $query->orderBy("id", "DESC")
              ->with(["branch", "customer"]);

        return $query->paginate($perPage);

    }

    /**
     * Apply filters to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    private static function applyFilters($query, array $filters): void {

        if(Utilities::isDefined($filters["customer_id"])) {

            $query->where("customer_id", $filters["customer_id"]);

        }

        if(Utilities::isDefined($filters["status"])) {

            $query->where("status", $filters["status"]);

        }

    }

    /**
     * Cancel an attendance
     *
     * @param Attendance $attendance Attendance instance
     * @param string|null $motive Cancellation motive
     * @param int|null $userId User ID
     * @return Attendance
     * @throws \Exception
     */
    public static function cancel(Attendance $attendance, ?string $motive = null, ?int $userId = null): Attendance {

        if(!in_array($attendance->status, ["active", "finalized"])) {

            throw new \Exception("La asistencia no puede ser anulada.");

        }

        $attendance->motive      = $motive ?? "N/A";
        $attendance->status      = "canceled";
        $attendance->updated_at  = now();
        $attendance->updated_by  = $userId;
        $attendance->canceled_at = now();
        $attendance->canceled_by = $userId;
        $attendance->save();

        return $attendance;

    }

}

