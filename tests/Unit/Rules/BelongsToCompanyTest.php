<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\System\Defaults\BelongsToCompany;
use Illuminate\Support\Facades\{Auth, DB};
use Mockery;
use Tests\TestCase;

class BelongsToCompanyTest extends TestCase {

    protected function setUp(): void {

        parent::setUp();

        Auth::shouldReceive("user")
            ->andReturn((object) ["company_id" => 7]);

    }

    public function test_it_accepts_a_direct_company_owned_record(): void {

        $query = $this->mockQuery(exists: true);

        DB::shouldReceive("table")
            ->once()
            ->with("brands")
            ->andReturn($query);

        $query->shouldReceive("where")->once()->with("id", 1)->andReturnSelf();
        $query->shouldReceive("where")->once()->with("company_id", 7)->andReturnSelf();
        $query->shouldReceive("where")->once()->with("status", "active")->andReturnSelf();

        $rule = new BelongsToCompany("brands", ["status" => "active"]);

        $this->assertSame([], $this->validateRule($rule, 1));

    }

    public function test_it_rejects_a_record_owned_by_another_company(): void {

        $query = $this->mockQuery(exists: false);

        DB::shouldReceive("table")
            ->once()
            ->with("brands")
            ->andReturn($query);

        $query->shouldReceive("where")->times(3)->andReturnSelf();

        $rule = new BelongsToCompany("brands", ["status" => "active"]);

        $this->assertNotSame([], $this->validateRule($rule, 2));

    }

    public function test_it_supports_company_ownership_through_a_join(): void {

        $query = $this->mockQuery(exists: true);

        DB::shouldReceive("table")
            ->once()
            ->with("warehouses")
            ->andReturn($query);

        $query->shouldReceive("join")
              ->once()
              ->with("branches", "warehouses.branch_id", "=", "branches.id")
              ->andReturnSelf();
        $query->shouldReceive("where")->once()->with("warehouses.id", 20)->andReturnSelf();
        $query->shouldReceive("where")->once()->with("branches.company_id", 7)->andReturnSelf();
        $query->shouldReceive("where")->once()->with("warehouses.status", "active")->andReturnSelf();
        $query->shouldReceive("where")->once()->with("branches.status", "active")->andReturnSelf();

        $rule = new BelongsToCompany(
            "warehouses",
            [
                "warehouses.status" => "active",
                "branches.status" => "active"
            ],
            null,
            [
                ["branches", "warehouses.branch_id", "=", "branches.id"]
            ],
            "branches.company_id",
            "warehouses.id"
        );

        $this->assertSame([], $this->validateRule($rule, 20));

    }

    private function mockQuery(bool $exists) {

        $query = Mockery::mock();
        $query->shouldReceive("exists")->once()->andReturn($exists);

        return $query;

    }

    private function validateRule(BelongsToCompany $rule, int $value): array {

        $errors = [];

        $rule->validate("record_id", $value, function(string $message) use(&$errors) {

            $errors[] = $message;

        });

        return $errors;

    }

}
