<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {

    public function up(): void {

        if(!Schema::hasTable("brands")) {

            Schema::create("brands", function(Blueprint $table) {

                $table->id();
                $table->unsignedBigInteger("company_id");
                $table->string("internal_code", 50);
                $table->string("name", 100);
                $table->text("description")->nullable();
                $table->enum("status", ["active", "inactive"])->default("active");

                $table->timestamp("created_at")->useCurrent()->nullable();
                $table->integer("created_by")->nullable();
                $table->timestamp("updated_at")->nullable();
                $table->integer("updated_by")->nullable();

                $table->foreign("company_id")
                      ->references("id")
                      ->on("companies")
                      ->cascadeOnDelete();
                $table->unique(["company_id", "internal_code"]);
                $table->unique(["company_id", "name"]);
                $table->index(["company_id", "status", "name"]);

            });

        }

        if(!Schema::hasColumn("items", "brand_id")) {

            Schema::table("items", function(Blueprint $table) {

                $table->unsignedBigInteger("brand_id")
                      ->nullable()
                      ->after("company_id");
                $table->foreign("brand_id")
                      ->references("id")
                      ->on("brands")
                      ->nullOnDelete();
                $table->index(["company_id", "brand_id"]);

            });

        }

        DB::table("sub_sections")
          ->where("id", 54)
          ->update(["order" => 6]);

        DB::table("sub_sections")->updateOrInsert(
            ["id" => 55],
            [
                "section_id" => 5,
                "slug" => "sc_items-brands",
                "name" => "items-brands",
                "description" => "Administra las marcas utilizadas para identificar y agrupar productos.",
                "order" => 5,
                "dom_id" => "menu-items-brands",
                "dom_label" => "Marcas",
                "dom_icon" => "",
                "dom_route" => "brands.index",
                "status" => "active",
                "updated_at" => now()
            ]
        );

        $companyIds = DB::table("companies")
                        ->where("status", "active")
                        ->pluck("id");

        foreach($companyIds as $companyId) {

            DB::table("companies_sub_sections")->updateOrInsert(
                [
                    "company_id" => $companyId,
                    "sub_section_id" => 55
                ],
                [
                    "status" => "active",
                    "updated_at" => now()
                ]
            );

        }

    }

    public function down(): void {

        DB::table("companies_sub_sections")
          ->where("sub_section_id", 55)
          ->delete();

        DB::table("sub_sections")
          ->where("id", 55)
          ->delete();

        DB::table("sub_sections")
          ->where("id", 54)
          ->update(["order" => 5]);

        if(Schema::hasColumn("items", "brand_id")) {

            Schema::table("items", function(Blueprint $table) {

                $table->dropForeign(["brand_id"]);
                $table->dropIndex(["company_id", "brand_id"]);
                $table->dropColumn("brand_id");

            });

        }

        Schema::dropIfExists("brands");

    }

};
