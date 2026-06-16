<?php

declare(strict_types=1);

namespace App\Observers\System\Organizations;

use App\Models\System\Organizations\CompanySubSection;
use App\Services\System\Organizations\Companies\CompanySectionService;

final class CompanySubSectionObserver {

    public function saved(CompanySubSection $companySubSection): void {

        $companyIds = [
            (int) $companySubSection->company_id,
            (int) $companySubSection->getOriginal("company_id")
        ];

        foreach(array_unique(array_filter($companyIds)) as $companyId) {

            CompanySectionService::clearCompanyCache($companyId);

        }

    }

    public function deleted(CompanySubSection $companySubSection): void {

        CompanySectionService::clearCompanyCache((int) $companySubSection->company_id);

    }

}
