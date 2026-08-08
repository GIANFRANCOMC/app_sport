<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\ProvisionsSystemDatabase;
use Tests\TestCase;

final class SystemNavigationProvisioningTest extends TestCase {
    use ProvisionsSystemDatabase;
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->provisionSystemDatabase();
    }

    public function test_every_catalog_module_is_enabled_for_the_company_and_admin_role(): void {
        $catalogCount = DB::table("sub_sections")->where("status", "active")->count();
        $companyCount = DB::table("companies_sub_sections")
            ->where("company_id", 1)
            ->where("status", "active")
            ->count();
        $adminRoleId = DB::table("roles")
            ->where("company_id", 1)
            ->where("is_full_access", true)
            ->value("id");
        $adminCount = DB::table("role_sub_sections")
            ->where("company_id", 1)
            ->where("role_id", $adminRoleId)
            ->where("status", "active")
            ->count();

        $this->assertSame(47, $catalogCount);
        $this->assertSame($catalogCount, $companyCount);
        $this->assertSame($catalogCount, $adminCount);
    }

    public function test_customer_attendance_belongs_to_the_membership_group(): void {
        $attendance = DB::table("sub_sections")
            ->join("sections", "sections.id", "=", "sub_sections.section_id")
            ->join("menu_groups", "menu_groups.id", "=", "sub_sections.menu_group_id")
            ->where("sub_sections.dom_route", "tracking_attendances.index")
            ->first([
                "sections.dom_label as section_name",
                "menu_groups.name as group_name",
                "sub_sections.order",
            ]);

        $this->assertNotNull($attendance);
        $this->assertSame("Clientes", $attendance->section_name);
        $this->assertSame("Membresías", $attendance->group_name);
        $this->assertSame(2, (int) $attendance->order);
    }
}
