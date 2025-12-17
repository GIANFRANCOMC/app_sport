<?php

declare(strict_types=1);

namespace App\Services\System\Customers\Tracking;

use App\Helpers\System\{TranslationHelper, Utilities};
use App\Models\System\Customers\Subscription;
use App\Models\System\Organizations\Branch;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service class for managing Tracking Subscription operations
 * Handles business logic for listing and managing subscriptions
 */
class TrackingSubscriptionService {

    /**
     * Translation namespace for tracking subscription module
     */
    private const TRANSLATION_NAMESPACE = "System.Customers.tracking_subscription";

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
     * Get paginated list of subscriptions
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

        $query = Subscription::where("company_id", $companyId)
                             ->where("branch_id", $branch->id);

        // Apply filters
        self::applyFilters($query, $filters);

        // Apply ordering
        $query->orderBy("id", "DESC")
              ->with(["branch", "saleHeader", "customer"]);

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

        if(Utilities::isDefined($filters["start_date"])) {

            $query->where("start_date", ">=", $filters["start_date"]." 00:00:00");

        }

        if(Utilities::isDefined($filters["end_date"])) {

            $query->where("end_date", "<=", $filters["end_date"]." 23:59:59");

        }

        if(Utilities::isDefined($filters["status"])) {

            $query->where("status", $filters["status"]);

        }

    }

    /**
     * Cancel a subscription
     *
     * @param Subscription $subscription Subscription instance
     * @param string|null $motive Cancellation motive
     * @param int|null $userId User ID
     * @return Subscription
     * @throws \Exception
     */
    public static function cancel(Subscription $subscription, ?string $motive = null, ?int $userId = null): Subscription {

        if(!in_array($subscription->status, ["active"])) {

            throw new \Exception("La membresía no puede ser anulada.");

        }

        $subscription->motive      = $motive ?? "N/A";
        $subscription->status      = "canceled";
        $subscription->updated_at  = now();
        $subscription->updated_by  = $userId;
        $subscription->canceled_at = now();
        $subscription->canceled_by = $userId;
        $subscription->save();

        return $subscription;

    }

}

