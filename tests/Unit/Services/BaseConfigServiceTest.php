<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use stdClass;
use Tests\TestCase;

use App\Services\System\Base\BaseConfigService;

final class TestConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "test";

    }

    protected static function cachePages(): array {

        return ["main", "list"];

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "company_id" => $companyId,
            "page"       => $page
        ]);

    }

}

class BaseConfigServiceTest extends TestCase {

    public function test_cache_is_isolated_by_company_and_page(): void {

        $main = TestConfigService::getInitParams(101, "main");
        $list = TestConfigService::getInitParams(101, "list");
        $otherCompany = TestConfigService::getInitParams(102, "main");

        $this->assertSame("main", $main->config->page);
        $this->assertSame("list", $list->config->page);
        $this->assertSame(102, $otherCompany->config->company_id);
        $this->assertNotSame(
            TestConfigService::cacheKey(101, "main"),
            TestConfigService::cacheKey(101, "list")
        );

    }

    public function test_empty_or_unknown_page_falls_back_to_first_supported_page(): void {

        $empty = TestConfigService::getInitParams(103, "");
        $unknown = TestConfigService::getInitParams(103, "unknown");

        $this->assertSame("main", $empty->config->page);
        $this->assertSame("main", $unknown->config->page);

    }

    public function test_clear_all_cache_forgets_every_supported_page(): void {

        TestConfigService::getInitParams(104, "main");
        TestConfigService::getInitParams(104, "list");

        TestConfigService::clearAllCache(104);

        $this->assertFalse(cache()->has(TestConfigService::cacheKey(104, "main")));
        $this->assertFalse(cache()->has(TestConfigService::cacheKey(104, "list")));

    }

}
