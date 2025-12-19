<?php

declare(strict_types=1);

namespace App\Services\System\Customers\Tracking;

use App\Helpers\System\Utilities;
use App\Models\System\Customers\SubscriptionEmail;

/**
 * Service class for managing Tracking Notification operations
 * Handles business logic for listing and managing tracking notifications
 */
class TrackingNotificationService {

    /**
     * Get paginated list of subscription emails with filters
     *
     * @param int $companyId Company ID
     * @param array $filters Filters array
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters, int $perPage) {

        $status = $filters["status"] ?? null;

        return SubscriptionEmail::when(Utilities::isDefined($status), function($query) use($status) {

                                    $query->where(function($query) use($status) {

                                        $query->where("status", $status);

                                    });

                                 })
                                 ->where("company_id", $companyId)
                                 ->orderBy("id", "DESC")
                                 ->paginate($perPage);

    }

}

