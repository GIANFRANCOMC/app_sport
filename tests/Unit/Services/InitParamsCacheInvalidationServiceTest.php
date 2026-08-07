<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Tests\TestCase;

use App\Services\System\Assets\AssetManagementConfigService;
use App\Services\System\Base\InitParamsCacheInvalidationService;
use App\Services\System\Catalogs\Brands\BrandConfigService;
use App\Services\System\Catalogs\Categories\CategoryConfigService;
use App\Services\System\Catalogs\Products\ProductConfigService;
use App\Services\System\Catalogs\Services\ServiceConfigService;
use App\Services\System\Catalogs\Subscriptions\SubscriptionConfigService;
use App\Services\System\Customers\Tracking\{
    TrackingAttendanceConfigService,
    TrackingSubscriptionConfigService
};
use App\Services\System\Devices\BiometricDevices\BiometricDeviceConfigService;
use App\Services\System\Organizations\Branches\BranchConfigService;
use App\Services\System\Sales\SaleConfigService;
use App\Services\System\Warehouses\StockManagement\StockManagementConfigService;

class InitParamsCacheInvalidationServiceTest extends TestCase {

    public function test_category_changes_clear_all_dependent_config_caches(): void {

        $companyId = 91;
        $keys = [
            CategoryConfigService::cacheKey($companyId),
            ProductConfigService::cacheKey($companyId, 'main', $this->userId($companyId)),
            ServiceConfigService::cacheKey($companyId, 'main', $this->userId($companyId)),
            SubscriptionConfigService::cacheKey($companyId, 'main', $this->userId($companyId))
        ];

        $this->seedCache($keys, $companyId);

        InitParamsCacheInvalidationService::invalidate(
            InitParamsCacheInvalidationService::CATEGORIES,
            $companyId
        );

        $this->assertCacheKeysWereForgotten($keys);

    }

    public function test_brand_changes_clear_brand_and_product_config_caches(): void {

        $companyId = 90;
        $keys = [
            BrandConfigService::cacheKey($companyId),
            ProductConfigService::cacheKey($companyId, 'main', $this->userId($companyId))
        ];

        $this->seedCache($keys, $companyId);

        InitParamsCacheInvalidationService::invalidate(
            InitParamsCacheInvalidationService::BRANDS,
            $companyId
        );

        $this->assertCacheKeysWereForgotten($keys);

    }

    public function test_item_changes_clear_sale_pages_and_catalog_config_caches(): void {

        $companyId = 92;
        $keys = [
            ProductConfigService::cacheKey($companyId, 'main', $this->userId($companyId)),
            ServiceConfigService::cacheKey($companyId, 'main', $this->userId($companyId)),
            SubscriptionConfigService::cacheKey($companyId, 'main', $this->userId($companyId)),
            SaleConfigService::cacheKey($companyId, "list", $this->userId($companyId)),
            SaleConfigService::cacheKey($companyId, "main", $this->userId($companyId))
        ];

        $this->seedCache($keys, $companyId);

        InitParamsCacheInvalidationService::invalidate(
            InitParamsCacheInvalidationService::ITEMS,
            $companyId
        );

        $this->assertCacheKeysWereForgotten($keys);

    }

    public function test_branch_changes_clear_product_warehouse_options_and_other_dependents(): void {

        $companyId = 93;
        $keys = [
            BranchConfigService::cacheKey($companyId),
            ProductConfigService::cacheKey($companyId, 'main', $this->userId($companyId)),
            SaleConfigService::cacheKey($companyId, "main", $this->userId($companyId)),
            SaleConfigService::cacheKey($companyId, "list", $this->userId($companyId)),
            TrackingAttendanceConfigService::cacheKey($companyId, 'main', $this->userId($companyId)),
            TrackingSubscriptionConfigService::cacheKey($companyId, 'main', $this->userId($companyId)),
            BiometricDeviceConfigService::cacheKey($companyId, 'main', $this->userId($companyId)),
            AssetManagementConfigService::cacheKey($companyId, 'main', $this->userId($companyId)),
            StockManagementConfigService::cacheKey($companyId, 'main', $this->userId($companyId))
        ];

        $this->seedCache($keys, $companyId);

        InitParamsCacheInvalidationService::invalidate(
            InitParamsCacheInvalidationService::BRANCHES,
            $companyId
        );

        $this->assertCacheKeysWereForgotten($keys);

    }

    public function test_unknown_resource_is_rejected(): void {

        $this->expectException(InvalidArgumentException::class);

        InitParamsCacheInvalidationService::invalidate("unknown", 1);

    }

    public function test_all_registered_resources_can_be_invalidated(): void {

        ProductConfigService::registerUserCacheScope(94, $this->userId(94));

        $resources = [
            InitParamsCacheInvalidationService::ASSETS,
            InitParamsCacheInvalidationService::BIOMETRIC_DEVICES,
            InitParamsCacheInvalidationService::BRANCHES,
            InitParamsCacheInvalidationService::BRANDS,
            InitParamsCacheInvalidationService::CATEGORIES,
            InitParamsCacheInvalidationService::CUSTOMERS,
            InitParamsCacheInvalidationService::ITEMS,
            InitParamsCacheInvalidationService::USERS
        ];

        foreach($resources as $resource) {

            InitParamsCacheInvalidationService::invalidate($resource, 94);

        }

        $this->addToAssertionCount(count($resources));

    }

    private function seedCache(array $keys, int $companyId): void {

        ProductConfigService::registerUserCacheScope($companyId, $this->userId($companyId));

        foreach($keys as $key) {

            Cache::put($key, "cached", 3600);

        }

    }

    private function userId(int $companyId): int {

        return $companyId * 10;

    }

    private function assertCacheKeysWereForgotten(array $keys): void {

        foreach($keys as $key) {

            $this->assertFalse(Cache::has($key), "Cache key {$key} was not invalidated.");

        }

    }

}
