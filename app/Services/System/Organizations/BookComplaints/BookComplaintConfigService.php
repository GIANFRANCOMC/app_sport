<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\BookComplaints;

use stdClass;

use App\Models\System\Organizations\BookComplaint;
use App\Services\System\Base\{
    BaseConfigService,
    MasterReferenceDataService
};

final class BookComplaintConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "book_complaint";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "identity_document_types" => self::data([
                "records" => MasterReferenceDataService::customerIdentityDocuments($companyId)
            ]),
            "book_complaints" => self::data([
                "types"    => BookComplaint::getTypes(),
                "statuses" => BookComplaint::getStatuses()
            ])
        ]);

    }

}
