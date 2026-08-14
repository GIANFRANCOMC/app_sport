<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\System\Organizations\{User};
use App\Services\System\Essentials\{UserNavigationService};
use Illuminate\Foundation\Testing\{RefreshDatabase};
use Illuminate\Support\Facades\{Auth, DB};
use Tests\Concerns\{ProvisionsSystemDatabase};
use Tests\{TestCase};

final class UserWorkspaceTest extends TestCase {
    use ProvisionsSystemDatabase;
    use RefreshDatabase;

    private User $user;

    private UserNavigationService $navigationService;

    protected function setUp(): void {

        parent::setUp();
        $this->provisionSystemDatabase();

        $this->user = User::query()
            ->where("company_id", 1)
            ->where("email", "admin@example.test")
            ->firstOrFail();
        $this->navigationService = app(UserNavigationService::class);

    }

    public function test_visits_are_aggregated_by_user_and_route_without_event_rows(): void {

        $this->navigationService->record($this->user, "dashboard.index");
        $this->navigationService->record($this->user, "dashboard.index");
        $this->navigationService->record($this->user, "home.index");

        $dashboardId = DB::table("sub_sections")->where("dom_route", "dashboard.index")->value("id");

        $this->assertDatabaseCount("user_navigation_metrics", 2);
        $this->assertDatabaseHas("user_navigation_metrics", [
            "company_id" => 1,
            "user_id" => $this->user->id,
            "sub_section_id" => $dashboardId,
            "visit_count" => 2,
            "recent_rank" => 2,
        ]);

    }

    public function test_only_ten_routes_keep_a_recent_rank_while_counts_remain_aggregated(): void {

        $routes = DB::table("sub_sections")
            ->where("dom_route", "!=", "workspace.index")
            ->orderBy("id")
            ->limit(11)
            ->pluck("dom_route");

        foreach($routes as $routeName) {

            $this->navigationService->record($this->user, $routeName);

        }

        $metrics = DB::table("user_navigation_metrics")
            ->where("company_id", 1)
            ->where("user_id", $this->user->id);

        $this->assertSame(11, (clone $metrics)->count());
        $this->assertSame(10, (clone $metrics)->whereNotNull("recent_rank")->count());
        $this->assertSame(10, (int) (clone $metrics)->max("recent_rank"));

    }

    public function test_revisiting_a_recent_route_keeps_the_ranks_consecutive(): void {

        $routes = DB::table("sub_sections")
            ->where("dom_route", "!=", "workspace.index")
            ->orderBy("id")
            ->limit(10)
            ->pluck("dom_route")
            ->values();

        foreach($routes as $routeName) {

            $this->navigationService->record($this->user, $routeName);

        }

        $this->navigationService->record($this->user, $routes[5]);

        $ranks = DB::table("user_navigation_metrics")
            ->where("company_id", 1)
            ->where("user_id", $this->user->id)
            ->whereNotNull("recent_rank")
            ->orderBy("recent_rank")
            ->pluck("recent_rank")
            ->map(fn($rank) => (int) $rank)
            ->all();

        $this->assertSame(range(1, 10), $ranks);

    }

    public function test_workspace_is_the_authenticated_landing_page_and_renders_recommendations(): void {

        $this->navigationService->record($this->user, "dashboard.index");
        Auth::login($this->user);

        $response = $this->withoutMiddleware([
            \App\Http\Middleware\ResolveTenant::class,
            \App\Http\Middleware\TrustHosts::class,
            \App\Http\Middleware\EnsureTenantSession::class,
            \App\Http\Middleware\EnsureAuthenticatedSession::class,
            \App\Http\Middleware\EnsureModulePermission::class,
            \App\Http\Middleware\EnsureOperationalScope::class,
        ])->get(route("workspace.index"));

        $response->assertOk();
        $response->assertSee("Mi espacio de trabajo");
        $response->assertSee("Vistos recientemente");
        $response->assertSee("Más utilizados");
        $response->assertSee("Dashboard");
        $this->assertSame("/workspace", \App\Providers\RouteServiceProvider::HOME);

    }
}
