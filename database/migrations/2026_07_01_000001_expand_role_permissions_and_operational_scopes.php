<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        Schema::table("role_sub_sections", function (Blueprint $table): void {
            $table->json("actions")->nullable()->after("sub_section_id");
        });

        Schema::table("roles", function (Blueprint $table): void {
            $table->enum("branch_scope_mode", ["all", "restricted"])->default("all")->after("is_full_access");
            $table->enum("cash_register_scope_mode", ["all", "restricted"])->default("all")->after("branch_scope_mode");
            $table->enum("warehouse_scope_mode", ["all", "restricted"])->default("all")->after("cash_register_scope_mode");
        });

        Schema::table("users", function (Blueprint $table): void {
            $table->enum("branch_scope_mode", ["inherit", "restricted"])->default("inherit")->after("role_id");
            $table->enum("cash_register_scope_mode", ["inherit", "restricted"])->default("inherit")->after("branch_scope_mode");
            $table->enum("warehouse_scope_mode", ["inherit", "restricted"])->default("inherit")->after("cash_register_scope_mode");
        });

        $this->createRoleBranches();
        $this->createRoleCashRegisters();
        $this->createRoleWarehouses();
        $this->createUserCashRegisters();
        $this->createUserWarehouses();

        DB::table("users")
            ->whereIn("id", DB::table("user_branches")->where("status", "active")->select("user_id"))
            ->update(["branch_scope_mode" => "restricted"]);

    }

    public function down(): void {

        Schema::dropIfExists("user_warehouses");
        Schema::dropIfExists("user_cash_registers");
        Schema::dropIfExists("role_warehouses");
        Schema::dropIfExists("role_cash_registers");
        Schema::dropIfExists("role_branches");

        Schema::table("users", function (Blueprint $table): void {
            $table->dropColumn(["branch_scope_mode", "cash_register_scope_mode", "warehouse_scope_mode"]);
        });

        Schema::table("roles", function (Blueprint $table): void {
            $table->dropColumn(["branch_scope_mode", "cash_register_scope_mode", "warehouse_scope_mode"]);
        });

        Schema::table("role_sub_sections", function (Blueprint $table): void {
            $table->dropColumn("actions");
        });

    }

    private function createRoleBranches(): void {

        Schema::create("role_branches", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("role_id");
            $table->unsignedBigInteger("branch_id");
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->timestamps();
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("role_id")->references("id")->on("roles")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->unique(["company_id", "role_id", "branch_id"]);
        });

    }

    private function createRoleCashRegisters(): void {

        Schema::create("role_cash_registers", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("role_id");
            $table->unsignedBigInteger("cash_register_id");
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->timestamps();
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("role_id")->references("id")->on("roles")->onDelete("cascade");
            $table->foreign("cash_register_id")->references("id")->on("cash_registers")->onDelete("cascade");
            $table->unique(["company_id", "role_id", "cash_register_id"]);
        });

    }

    private function createRoleWarehouses(): void {

        Schema::create("role_warehouses", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("role_id");
            $table->unsignedBigInteger("warehouse_id");
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->timestamps();
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("role_id")->references("id")->on("roles")->onDelete("cascade");
            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("cascade");
            $table->unique(["company_id", "role_id", "warehouse_id"]);
        });

    }

    private function createUserCashRegisters(): void {

        Schema::create("user_cash_registers", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("cash_register_id");
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->timestamps();
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("cash_register_id")->references("id")->on("cash_registers")->onDelete("cascade");
            $table->unique(["company_id", "user_id", "cash_register_id"]);
        });

    }

    private function createUserWarehouses(): void {

        Schema::create("user_warehouses", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("warehouse_id");
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->timestamps();
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("cascade");
            $table->unique(["company_id", "user_id", "warehouse_id"]);
        });

    }
};
