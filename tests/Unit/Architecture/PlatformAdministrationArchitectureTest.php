<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use Tests\{TestCase};

final class PlatformAdministrationArchitectureTest extends TestCase {
    public function test_authenticated_platform_uses_one_asynchronous_shell(): void {

        $routes = file_get_contents(base_path("routes/platform.php"));
        $layout = file_get_contents(resource_path("views/Platform/layouts/app.blade.php"));
        $shell = file_get_contents(resource_path("views/Platform/shell.blade.php"));

        $this->assertStringContainsString("PlatformShellController", $routes);
        $this->assertStringContainsString("/api/tenants", $routes);
        $this->assertStringContainsString("@stack(\"scripts\")", str_replace("'", "\"", $layout));
        $this->assertStringContainsString("resources/js/Platform/app.js", $shell);
        $this->assertFileDoesNotExist(resource_path("views/Platform/tenants/index.blade.php"));
        $this->assertFileDoesNotExist(resource_path("views/Platform/tenants/show.blade.php"));

    }

    public function test_module_configuration_uses_one_bulk_write(): void {

        $service = file_get_contents(app_path("Services/System/Tenancy/PlatformTenantService.php"));
        $migration = file_get_contents(database_path("migrations/2024_01_11_223124_create_init_masters_table.php"));

        $this->assertStringContainsString("->upsert(", $service);
        $this->assertStringNotContainsString("->updateOrInsert(", $service);
        $this->assertStringContainsString("companies_sub_sections_company_module_unique", $migration);

    }

    public function test_landlord_schema_has_a_single_migration_source(): void {

        $schemaService = file_get_contents(app_path("Services/System/Tenancy/LandlordSchemaService.php"));

        $this->assertStringNotContainsString("->create(", $schemaService);
        $this->assertStringContainsString("platform:install", $schemaService);

    }

    public function test_platform_provisioning_is_locked_and_uses_a_blocking_modal(): void {

        $provisioner = file_get_contents(app_path("Services/System/Tenancy/PlatformTenantProvisioner.php"));
        $tenantIndex = file_get_contents(resource_path("js/Platform/pages/TenantIndex.vue"));
        $tenantDetail = file_get_contents(resource_path("js/Platform/pages/TenantDetail.vue"));

        $this->assertStringContainsString("Cache::lock(", $provisioner);
        $this->assertStringContainsString("platform-provisioning-lock", $tenantIndex);
        $this->assertStringContainsString("beforeunload", $tenantIndex);
        $this->assertStringContainsString("platform-check__box", $tenantDetail);

    }
}
