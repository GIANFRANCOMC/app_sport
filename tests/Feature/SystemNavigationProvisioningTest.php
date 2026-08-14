<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{RefreshDatabase};
use Illuminate\Support\Facades\{Auth, DB};
use Tests\Concerns\{ProvisionsSystemDatabase};
use Tests\{TestCase};

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

        $this->assertSame(48, $catalogCount);
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

    public function test_tenant_navigation_renders_only_allowed_modules_and_marks_the_current_route(): void {

        $user = \App\Models\System\Organizations\User::query()
            ->where("company_id", 1)
            ->where("email", "admin@example.test")
            ->firstOrFail();

        Auth::login($user);

        $response = $this->withoutMiddleware([
            \App\Http\Middleware\ResolveTenant::class,
            \App\Http\Middleware\TrustHosts::class,
            \App\Http\Middleware\EnsureTenantSession::class,
            \App\Http\Middleware\EnsureAuthenticatedSession::class,
            \App\Http\Middleware\EnsureModulePermission::class,
            \App\Http\Middleware\EnsureOperationalScope::class,
        ])->get(route("dashboard.index"));

        $response->assertOk();
        $response->assertSee("br-navigation__rail", false);
        $response->assertSee("br-navigation has-context", false);
        $response->assertSee("id=\"menu-parent-workspace\"", false);
        $response->assertSee("id=\"brNavigationContext\"", false);
        $response->assertSee("Mi espacio de trabajo", false);
        $response->assertDontSee("br-menu-category__full", false);

    }

    public function test_workspace_groups_home_dashboard_and_reports_in_one_primary_module(): void {

        $routes = DB::table("sub_sections")
            ->whereIn("dom_route", ["workspace.index", "home.index", "dashboard.index", "reports.index"])
            ->orderBy("order")
            ->get(["section_id", "dom_route"]);

        $this->assertCount(4, $routes);
        $this->assertSame([1], $routes->pluck("section_id")->unique()->values()->all());
        $this->assertSame(
            ["workspace.index", "home.index", "dashboard.index", "reports.index"],
            $routes->pluck("dom_route")->all()
        );

    }

    public function test_customer_attention_and_company_resources_are_grouped_in_their_parent_modules(): void {

        $customerSections = DB::table("sub_sections")
            ->join("sections", "sections.id", "=", "sub_sections.section_id")
            ->whereIn("sub_sections.dom_route", [
                "service_sessions.index",
                "tracking_notifications.index",
                "book_complaints.index",
            ])
            ->pluck("sections.dom_label")
            ->unique()
            ->values()
            ->all();

        $organizationSections = DB::table("sub_sections")
            ->join("sections", "sections.id", "=", "sub_sections.section_id")
            ->whereIn("sub_sections.dom_route", [
                "users.index",
                "user_attendances.index",
                "companies.index",
            ])
            ->pluck("sections.dom_label")
            ->unique()
            ->values()
            ->all();

        $this->assertSame(["Clientes"], $customerSections);
        $this->assertSame(["Mi organización"], $organizationSections);
        $this->assertFalse(DB::table("sections")->where("dom_label", "Atención al cliente")->exists());
        $this->assertFalse(DB::table("sections")->where("dom_label", "Colaboradores")->exists());

    }
}
