<?php

declare(strict_types=1);

namespace App\Services\System\Essentials;

use App\Helpers\System\Utilities;
use App\Models\System\Organizations\Branch;
use App\Models\System\Sales\SaleHeader;

/**
 * Service class for managing Dashboard operations
 * Handles business logic for dashboard data
 */
class DashboardService {

    /**
     * Get dashboard data for a specific date
     *
     * @param int $companyId Company ID
     * @param string $date Date in Y-m-d format
     * @return array
     */
    public static function getDashboardData(int $companyId, string $date): array {

        $branches = Branch::where("company_id", $companyId)
                          ->with(["series"])
                          ->get();

        $serieIds = $branches->pluck("series.*.id")->flatten();

        $sales = SaleHeader::whereDate("created_at", $date)
                           ->whereIn("serie_id", $serieIds)
                           ->orderBy("created_at", "DESC")
                           ->with(["serie.documentType", "holder", "currency"])
                           ->get();

        $canceledSales = $sales->whereIn("status", ["canceled"])
                               ->values();

        $data = [
            "sales" => [
                "all" => [
                    "total"   => $sales->sum("total"),
                    "count"   => $sales->count(),
                    "records" => $sales
                ],
                "canceled" => [
                    "total" => $canceledSales->sum("total"),
                    "count" => $canceledSales->count()
                ]
            ],
            "branches" => [
                "valid" => [
                    "count" => $branches->whereIn("status", ["active"])->count()
                ]
            ],
            "users" => [
                "valid" => [
                    "count" => 0 // Can be implemented later if needed
                ]
            ]
        ];

        return $data;

    }

}

