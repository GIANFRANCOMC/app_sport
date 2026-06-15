<?php

declare(strict_types=1);

namespace App\Services\System\Base;

use InvalidArgumentException;

use App\Services\System\Assets\{AssetManagementConfigService};
use App\Services\System\Assets\Assets\{AssetConfigService};
use App\Services\System\Catalogs\Categories\{CategoryConfigService};
use App\Services\System\Catalogs\Brands\{BrandConfigService};
use App\Services\System\Catalogs\Products\{ProductConfigService};
use App\Services\System\Catalogs\Services\{ServiceConfigService};
use App\Services\System\Catalogs\Subscriptions\{SubscriptionConfigService};
use App\Services\System\Customers\Customers\{CustomerConfigService};
use App\Services\System\Customers\Tracking\{
    TrackingAttendanceConfigService,
    TrackingCustomerConfigService,
    TrackingSubscriptionConfigService
};
use App\Services\System\Devices\BiometricDevices\{BiometricDeviceConfigService};
use App\Services\System\Organizations\Branches\{BranchConfigService};
use App\Services\System\Organizations\Users\{UserConfigService};
use App\Services\System\Sales\{SaleConfigService};
use App\Services\System\Purchases\{PurchaseConfigService};
use App\Services\System\Warehouses\StockManagement\{StockManagementConfigService};

/**
 * Invalidates initParams caches according to shared domain dependencies.
 */
final class InitParamsCacheInvalidationService {

    public const ASSETS            = "assets";
    public const BIOMETRIC_DEVICES = "biometric_devices";
    public const BRANCHES          = "branches";
    public const BRANDS            = "brands";
    public const CATEGORIES        = "categories";
    public const CUSTOMERS         = "customers";
    public const ITEMS             = "items";
    public const USERS             = "users";
    public const SUPPLIERS         = "suppliers";

    /**
     * Config services affected when a shared resource changes.
     *
     * @var array<string, array<class-string>>
     */
    private const DEPENDENCIES = [
        self::BRANDS => [
            BrandConfigService::class,
            ProductConfigService::class
        ],
        self::CATEGORIES => [
            CategoryConfigService::class,
            ProductConfigService::class,
            ServiceConfigService::class,
            SubscriptionConfigService::class
        ],
        self::ITEMS => [
            ProductConfigService::class,
            ServiceConfigService::class,
            SubscriptionConfigService::class,
            SaleConfigService::class,
            StockManagementConfigService::class,
            PurchaseConfigService::class
        ],
        self::CUSTOMERS => [
            CustomerConfigService::class,
            SaleConfigService::class,
            TrackingAttendanceConfigService::class,
            TrackingCustomerConfigService::class,
            TrackingSubscriptionConfigService::class
        ],
        self::BRANCHES => [
            BranchConfigService::class,
            ProductConfigService::class,
            SaleConfigService::class,
            TrackingAttendanceConfigService::class,
            TrackingSubscriptionConfigService::class,
            BiometricDeviceConfigService::class,
            AssetManagementConfigService::class,
            StockManagementConfigService::class,
            PurchaseConfigService::class
        ],
        self::ASSETS => [
            AssetConfigService::class,
            AssetManagementConfigService::class
        ],
        self::USERS => [
            UserConfigService::class,
            AssetManagementConfigService::class
        ],
        self::BIOMETRIC_DEVICES => [
            BiometricDeviceConfigService::class,
            CustomerConfigService::class
        ],
        self::SUPPLIERS => [
            PurchaseConfigService::class
        ]
    ];

    public static function invalidate(string $resource, int $companyId): void {

        $services = self::DEPENDENCIES[$resource] ?? null;

        if($services === null) {

            throw new InvalidArgumentException("Unknown initParams cache resource: {$resource}");

        }

        foreach(array_unique($services) as $service) {

            $service::clearAllCache($companyId);

        }

    }

}
