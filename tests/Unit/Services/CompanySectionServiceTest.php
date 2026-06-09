<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Tests\TestCase;

use App\Services\System\Organizations\Companies\CompanySectionService;

class CompanySectionServiceTest extends TestCase {

    public function test_it_returns_cached_sections_without_requerying(): void {

        $companyId = 201;
        $sections = new Collection([(object) ["id" => 1]]);

        Cache::put(CompanySectionService::cacheKey($companyId), $sections, 1800);

        $result = CompanySectionService::getSections($companyId);

        $this->assertCount(1, $result);
        $this->assertSame(1, $result->first()->id);

    }

    public function test_clear_cache_forgets_company_sections(): void {

        $companyId = 202;
        $cacheKey = CompanySectionService::cacheKey($companyId);

        Cache::put($cacheKey, new Collection(), 1800);
        CompanySectionService::clearCache($companyId);

        $this->assertFalse(Cache::has($cacheKey));

    }

    public function test_invalid_company_id_is_rejected(): void {

        $this->expectException(InvalidArgumentException::class);

        CompanySectionService::getSections(0);

    }

}
