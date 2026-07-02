<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\System\Organizations\UserAttendance;
use App\Services\System\Customers\Tracking\TrackingAttendanceBusinessService;
use App\Services\System\Organizations\Users\UserAttendanceService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AttendanceFlowsTest extends TestCase {

    use RefreshDatabase;

    public function test_customer_can_check_in_and_check_out(): void {

        $this->createCustomerSubscription(2, 1);
        $service = app(TrackingAttendanceBusinessService::class);
        $checkInAt = Carbon::parse("2026-07-02 08:00:00");

        $checkIn = $service->validateAndCreateAttendance([
            "company_id" => 1,
            "branch_id" => 1,
            "customer_id" => 2,
            "start_date" => $checkInAt,
            "end_date" => null,
            "user_id" => 1,
            "type" => "manual_form",
            "action" => "checkin"
        ]);

        $this->assertTrue($checkIn["bool"]);
        $this->assertDatabaseHas("attendances", [
            "company_id" => 1,
            "branch_id" => 1,
            "customer_id" => 2,
            "status" => "active"
        ]);

        $checkOut = $service->validateAndCreateAttendance([
            "company_id" => 1,
            "branch_id" => 1,
            "customer_id" => 2,
            "start_date" => null,
            "end_date" => $checkInAt->copy()->addHours(2),
            "user_id" => 1,
            "action" => "checkout"
        ]);

        $this->assertTrue($checkOut["bool"]);
        $this->assertDatabaseHas("attendances", [
            "company_id" => 1,
            "branch_id" => 1,
            "customer_id" => 2,
            "status" => "finalized"
        ]);

    }

    public function test_customer_daily_limit_blocks_an_extra_check_in(): void {

        $this->createCustomerSubscription(2, 1);
        $date = Carbon::parse("2026-07-02 10:00:00");

        DB::table("attendances")->insert([
            "company_id" => 1,
            "branch_id" => 1,
            "customer_id" => 2,
            "start_date" => $date->copy()->subHours(2),
            "end_date" => $date->copy()->subHour(),
            "type" => "manual_form",
            "status" => "finalized",
            "created_at" => now(),
            "created_by" => 1
        ]);

        $result = app(TrackingAttendanceBusinessService::class)->validateAndCreateAttendance([
            "company_id" => 1,
            "branch_id" => 1,
            "customer_id" => 2,
            "start_date" => $date,
            "end_date" => null,
            "user_id" => 1,
            "type" => "manual_form",
            "action" => "checkin"
        ]);

        $this->assertFalse($result["bool"]);
        $this->assertStringContainsString("límite diario", $result["msg"]);
        $this->assertDatabaseCount("attendances", 1);

    }

    public function test_collaborator_check_in_check_out_and_weekly_hours(): void {

        $checkInAt = Carbon::parse("2026-07-02 08:00:00");

        $attendance = UserAttendanceService::checkIn([
            "company_id" => 1,
            "branch_id" => 1,
            "user_id" => 1,
            "actor_id" => 1,
            "checked_in_at" => $checkInAt
        ]);

        $this->assertSame(UserAttendanceService::STATUS_ACTIVE, $attendance->status);

        $attendance = UserAttendanceService::checkOut([
            "company_id" => 1,
            "branch_id" => 1,
            "user_id" => 1,
            "actor_id" => 1,
            "checked_out_at" => $checkInAt->copy()->addHours(8)
        ]);

        $this->assertSame(UserAttendanceService::STATUS_FINALIZED, $attendance->status);
        $this->assertSame(480, $attendance->worked_minutes);

        $summary = UserAttendanceService::weeklySummary(1, 1, "2026-06-29");

        $this->assertSame(480, $summary["total_minutes"]);
        $this->assertSame(8.0, $summary["total_hours"]);
        $this->assertCount(1, $summary["days"]);

    }

    public function test_collaborator_cannot_open_simultaneous_shifts_in_two_branches(): void {

        DB::table("branches")->insert([
            "company_id" => 1,
            "internal_code" => "SUC-TEST-2",
            "name" => "Sede secundaria",
            "status" => "active",
            "created_at" => now()
        ]);

        $secondBranchId = (int) DB::table("branches")->where("internal_code", "SUC-TEST-2")->value("id");

        UserAttendanceService::checkIn([
            "company_id" => 1,
            "branch_id" => 1,
            "user_id" => 1,
            "actor_id" => 1,
            "checked_in_at" => "2026-07-02 08:00:00"
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("jornada en curso");

        UserAttendanceService::checkIn([
            "company_id" => 1,
            "branch_id" => $secondBranchId,
            "user_id" => 1,
            "actor_id" => 1,
            "checked_in_at" => "2026-07-02 09:00:00"
        ]);

    }

    private function createCustomerSubscription(int $customerId, int $limit): void {

        $itemId = (int) DB::table("items")->insertGetId([
            "company_id" => 1,
            "internal_code" => "MEM-ATT-TEST",
            "name" => "Membresía de prueba",
            "price" => 10,
            "currency_id" => 1,
            "type" => "subscription",
            "status" => "active",
            "created_at" => now()
        ]);

        $saleId = (int) DB::table("sales_header")->insertGetId([
            "company_id" => 1,
            "serie_id" => 1,
            "sequential" => 900001,
            "holder_id" => $customerId,
            "seller_id" => 1,
            "currency_id" => 1,
            "warehouse_id" => 1,
            "issue_date" => "2026-07-01",
            "subtotal" => 10,
            "tax" => 0,
            "total" => 10,
            "status" => "active",
            "created_at" => now()
        ]);

        $saleBodyId = (int) DB::table("sales_body")->insertGetId([
            "company_id" => 1,
            "sale_header_id" => $saleId,
            "item_id" => $itemId,
            "currency_id" => 1,
            "name" => "Membresía de prueba",
            "quantity" => 1,
            "price" => 10,
            "price_includes_tax" => true,
            "total" => 10,
            "customer_id" => $customerId,
            "type" => "subscription",
            "extras" => "{}",
            "status" => "active",
            "created_at" => now()
        ]);

        DB::table("subscriptions")->insert([
            "company_id" => 1,
            "branch_id" => 1,
            "sale_header_id" => $saleId,
            "sale_body_id" => $saleBodyId,
            "customer_id" => $customerId,
            "duration_type" => "month",
            "duration_value" => 1,
            "start_date" => "2026-07-01 00:00:00",
            "end_date" => "2026-07-31 23:59:59",
            "attendance_limit_per_day" => $limit,
            "type" => "sale",
            "status" => "active",
            "created_at" => now()
        ]);

    }

}
