<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\System\Defaults\{UniqueInCompany};
use Illuminate\Support\Facades\{Auth, DB};
use Mockery;
use Tests\{TestCase};

class UniqueInCompanyTest extends TestCase {
    public function test_it_rejects_a_value_already_used_by_the_company(): void {

        Auth::shouldReceive("user")
            ->once()
            ->andReturn((object) ["company_id" => 7]);

        $query = $this->mockQuery(exists: true);

        DB::shouldReceive("table")
            ->once()
            ->with("items")
            ->andReturn($query);

        $query->shouldReceive("where")->once()->with("barcode", "2001234567893")->andReturnSelf();
        $query->shouldReceive("where")->once()->with("company_id", 7)->andReturnSelf();

        $rule = new UniqueInCompany("items", "barcode", null, [], "código de barras");

        $this->assertNotSame([], $this->validateRule($rule, "barcode", "2001234567893"));

    }

    public function test_it_accepts_a_value_not_used_by_the_company(): void {

        Auth::shouldReceive("user")
            ->once()
            ->andReturn((object) ["company_id" => 7]);

        $query = $this->mockQuery(exists: false);

        DB::shouldReceive("table")
            ->once()
            ->with("items")
            ->andReturn($query);

        $query->shouldReceive("where")->once()->with("barcode", "2001234567893")->andReturnSelf();
        $query->shouldReceive("where")->once()->with("company_id", 7)->andReturnSelf();

        $rule = new UniqueInCompany("items", "barcode");

        $this->assertSame([], $this->validateRule($rule, "barcode", "2001234567893"));

    }

    public function test_it_rejects_a_duplicate_brand_name_within_the_company(): void {

        Auth::shouldReceive("user")
            ->once()
            ->andReturn((object) ["company_id" => 7]);

        $query = $this->mockQuery(exists: true);

        DB::shouldReceive("table")
            ->once()
            ->with("brands")
            ->andReturn($query);

        $query->shouldReceive("where")->once()->with("name", "HP")->andReturnSelf();
        $query->shouldReceive("where")->once()->with("company_id", 7)->andReturnSelf();

        $rule = new UniqueInCompany("brands", "name", null, [], "nombre");

        $this->assertNotSame([], $this->validateRule($rule, "name", "HP"));

    }

    public function test_it_supports_type_scopes_and_excludes_the_current_record(): void {

        Auth::shouldReceive("user")
            ->once()
            ->andReturn((object) ["company_id" => 7]);

        $query = $this->mockQuery(exists: false);

        DB::shouldReceive("table")
            ->once()
            ->with("items")
            ->andReturn($query);

        $query->shouldReceive("where")->once()->with("internal_code", "PROD-001")->andReturnSelf();
        $query->shouldReceive("where")->once()->with("company_id", 7)->andReturnSelf();
        $query->shouldReceive("where")->once()->with("type", "product")->andReturnSelf();
        $query->shouldReceive("where")->once()->with("id", "!=", 25)->andReturnSelf();

        $rule = new UniqueInCompany(
            "items",
            "internal_code",
            25,
            ["type" => "product"],
            "código interno"
        );

        $this->assertSame([], $this->validateRule($rule, "internal_code", "PROD-001"));

    }

    public function test_it_fails_closed_without_an_authenticated_company(): void {

        Auth::shouldReceive("user")
            ->once()
            ->andReturn(null);

        DB::shouldReceive("table")->never();

        $rule = new UniqueInCompany("items", "barcode");

        $this->assertNotSame([], $this->validateRule($rule, "barcode", "2001234567893"));

    }

    private function mockQuery(bool $exists) {

        $query = Mockery::mock();
        $query->shouldReceive("exists")->once()->andReturn($exists);

        return $query;

    }

    private function validateRule(UniqueInCompany $rule, string $attribute, string $value): array {

        $errors = [];

        $rule->validate($attribute, $value, function(string $message) use (&$errors) {

            $errors[] = $message;

        });

        return $errors;

    }
}
