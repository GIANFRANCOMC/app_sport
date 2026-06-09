<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use InvalidArgumentException;
use Tests\TestCase;

use App\Services\Guest\GuestCatalogService;
use App\Services\System\Base\CompanyReferenceDataService;

class ReferenceDataServiceTest extends TestCase {

    public function test_company_reference_data_rejects_an_invalid_company_id(): void {

        $this->expectException(InvalidArgumentException::class);

        CompanyReferenceDataService::for(0);

    }

    public function test_guest_catalog_rejects_an_invalid_company_id(): void {

        $this->expectException(InvalidArgumentException::class);

        GuestCatalogService::publicItems(0);

    }

}
