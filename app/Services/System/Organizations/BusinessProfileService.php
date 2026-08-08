<?php

declare(strict_types=1);

namespace App\Services\System\Organizations;

use App\Models\System\Organizations\{BusinessIndustry, BusinessIndustryModuleSet};
use App\Services\System\Organizations\Companies\{CompanySectionService};
use Illuminate\Support\Facades\{DB};

final class BusinessProfileService {
    public static function industries(int $companyId) {

        return BusinessIndustry::query()
            ->where("company_id", $companyId)
            ->where("status", "active")
            ->with("moduleSets.subSection:id,dom_label,dom_route")
            ->orderBy("name")
            ->get();

    }

    public static function applyIndustry(int $companyId, int $industryId, int $userId): void {

        DB::transaction(function() use ($companyId, $industryId, $userId) {

            $industry = BusinessIndustry::query()
                ->where("company_id", $companyId)
                ->whereKey($industryId)
                ->firstOrFail();

            $sets = BusinessIndustryModuleSet::query()
                ->where("company_id", $companyId)
                ->where("business_industry_id", $industry->id)
                ->where("status", "active")
                ->get();

            foreach($sets as $set) {

                DB::table("companies_sub_sections")->updateOrInsert(
                    ["company_id" => $companyId, "sub_section_id" => $set->sub_section_id],
                    [
                        "status" => $set->is_enabled_by_default ? "active" : "inactive",
                        "updated_at" => now(),
                        "updated_by" => $userId,
                    ]
                );

            }

            DB::table("companies")
                ->where("id", $companyId)
                ->update([
                    "business_industry_id" => $industry->id,
                    "updated_at" => now(),
                    "updated_by" => $userId,
                ]);

            CompanySectionService::clearCompanyCache($companyId);

        });

    }
}
