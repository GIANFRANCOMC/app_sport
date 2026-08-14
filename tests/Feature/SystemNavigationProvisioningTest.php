<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\System\Organizations\{BusinessProfileService};
use Illuminate\Foundation\Testing\{RefreshDatabase};
use Illuminate\Support\Facades\{Auth, DB, Route};
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

        $this->assertSame(49, $catalogCount);
        $this->assertSame($catalogCount, $companyCount);
        $this->assertSame($catalogCount, $adminCount);

    }

    public function test_operational_expense_and_business_profile_defaults_are_provisioned_per_company(): void {

        $this->assertSame(5, DB::table("misc_expense_categories")->where("company_id", 1)->count());
        $this->assertSame(3, DB::table("business_industries")->where("company_id", 1)->count());
        $this->assertGreaterThan(
            0,
            DB::table("business_industry_module_sets")->where("company_id", 1)->count()
        );
        $this->assertNotNull(DB::table("companies")->where("id", 1)->value("business_industry_id"));

    }

    public function test_company_can_customize_modules_without_disabling_essential_access(): void {

        $administratorId = (int) DB::table("users")
            ->where("company_id", 1)
            ->where("email", "admin@example.test")
            ->value("id");

        BusinessProfileService::updateModules(1, [], $administratorId);

        $protectedRoutes = ["workspace.index", "home.index", "account.index", "business_profile.index"];
        $protectedIds = DB::table("sub_sections")->whereIn("dom_route", $protectedRoutes)->pluck("id");

        $this->assertSame(
            count($protectedRoutes),
            DB::table("companies_sub_sections")
                ->where("company_id", 1)
                ->whereIn("sub_section_id", $protectedIds)
                ->where("status", "active")
                ->count()
        );
        $this->assertSame(
            "inactive",
            DB::table("companies_sub_sections")
                ->where("company_id", 1)
                ->where("sub_section_id", DB::table("sub_sections")->where("dom_route", "sales.create")->value("id"))
                ->value("status")
        );

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
            ->join("menu_groups", "menu_groups.id", "=", "sub_sections.menu_group_id")
            ->whereIn("dom_route", ["workspace.index", "home.index", "account.index", "dashboard.index", "reports.index"])
            ->orderBy("menu_groups.order")
            ->orderBy("sub_sections.order")
            ->get(["sub_sections.section_id", "sub_sections.dom_route"]);

        $this->assertCount(5, $routes);
        $this->assertSame([1], $routes->pluck("section_id")->unique()->values()->all());
        $this->assertSame(
            ["workspace.index", "home.index", "account.index", "dashboard.index", "reports.index"],
            $routes->pluck("dom_route")->all()
        );

    }

    public function test_cash_pages_use_independent_routes_without_legacy_page_paths(): void {

        $expectedPaths = [
            "cash_registers.index" => "/cash_registers",
            "cash_sessions.index" => "/cash-sessions",
            "cash_movements.index" => "/cash-movements",
            "cash_summary.index" => "/cash-summary",
        ];

        foreach($expectedPaths as $routeName => $path) {

            $this->assertTrue(Route::has($routeName));
            $this->assertSame($path, parse_url(route($routeName), PHP_URL_PATH));

        }

        $this->assertFalse(Route::has("cash_registers.registers.index"));
        $this->assertFalse(Route::has("cash_registers.sessions.index"));
        $this->assertFalse(Route::has("cash_registers.movements.index"));
        $this->assertFalse(Route::has("cash_registers.summary.index"));
        $this->assertTrue(Route::has("cash_sessions.list"));
        $this->assertTrue(Route::has("cash_sessions.open"));
        $this->assertTrue(Route::has("cash_sessions.close"));
        $this->assertTrue(Route::has("cash_movements.list"));
        $this->assertTrue(Route::has("cash_movements.store"));
        $this->assertTrue(Route::has("cash_movements.export"));
        $this->assertTrue(Route::has("cash_summary.data"));
        $this->assertFalse(Route::has("cash_registers.sessions"));
        $this->assertFalse(Route::has("cash_registers.movements"));
        $this->assertFalse(Route::has("cash_registers.summary"));

    }

    public function test_misc_expenses_and_business_profile_initialization_are_available(): void {

        $user = \App\Models\System\Organizations\User::query()
            ->where("company_id", 1)
            ->where("email", "admin@example.test")
            ->firstOrFail();

        Auth::login($user);
        $withoutTenantMiddleware = [
            \App\Http\Middleware\ResolveTenant::class,
            \App\Http\Middleware\TrustHosts::class,
            \App\Http\Middleware\EnsureTenantSession::class,
            \App\Http\Middleware\EnsureAuthenticatedSession::class,
            \App\Http\Middleware\EnsureModulePermission::class,
            \App\Http\Middleware\EnsureOperationalScope::class,
        ];

        $this->withoutMiddleware($withoutTenantMiddleware)
            ->get(route("misc_expenses.initParams"))
            ->assertOk()
            ->assertJsonPath("bool", true)
            ->assertJsonCount(5, "data.categories")
            ->assertJsonCount(1, "data.currencies");

        $this->withoutMiddleware($withoutTenantMiddleware)
            ->get(route("business_profile.initParams"))
            ->assertOk()
            ->assertJsonPath("bool", true)
            ->assertJsonCount(3, "industries")
            ->assertJsonCount(49, "modules");

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
