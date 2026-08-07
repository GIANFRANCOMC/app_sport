<?php

use App\Services\System\Organizations\Companies\CompanySectionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {

    private const SUB_SECTION_ID = 36;

    public function up(): void {

        if(!Schema::hasTable("sub_sections")) return;

        DB::table("sub_sections")->updateOrInsert(
            ["id" => self::SUB_SECTION_ID],
            [
                "section_id" => 3,
                "slug" => "sc_sales-accounts-receivable",
                "name" => "sales-accounts-receivable",
                "description" => "Consulta los saldos financiados, vencimientos y cronogramas de las ventas a crédito.",
                "order" => 4,
                "dom_id" => "menu-sales-accounts-receivable",
                "dom_label" => "Cuentas por cobrar",
                "dom_route" => "accounts_receivable.index",
                "status" => "active",
                "updated_at" => now()
            ]
        );

        if(Schema::hasTable("companies") && Schema::hasTable("companies_sub_sections")) {

            DB::table("companies")->pluck("id")->each(function($companyId) {

                DB::table("companies_sub_sections")->updateOrInsert(
                    ["company_id" => $companyId, "sub_section_id" => self::SUB_SECTION_ID],
                    [
                        "section_order" => 4,
                        "sub_section_order" => 4,
                        "status" => "active",
                        "updated_at" => now()
                    ]
                );

            });

        }

        if(Schema::hasTable("role_sub_sections")) {

            DB::table("role_sub_sections")
                ->where("sub_section_id", 30)
                ->where("status", "active")
                ->get()
                ->each(function($permission) {

                    DB::table("role_sub_sections")->updateOrInsert(
                        [
                            "company_id" => $permission->company_id,
                            "role_id" => $permission->role_id,
                            "sub_section_id" => self::SUB_SECTION_ID
                        ],
                        [
                            "actions" => $permission->actions ?? null,
                            "status" => "active",
                            "updated_at" => now()
                        ]
                    );

                });

        }

        if(Schema::hasTable("business_industry_module_sets") && Schema::hasTable("business_industries")) {

            DB::table("business_industries")
                ->where("status", "active")
                ->get(["id", "company_id", "name"])
                ->each(function($industry) {

                    DB::table("business_industry_module_sets")->updateOrInsert(
                        [
                            "company_id" => $industry->company_id,
                            "business_industry_id" => $industry->id,
                            "sub_section_id" => self::SUB_SECTION_ID
                        ],
                        [
                            "is_enabled_by_default" => true,
                            "reason" => "Módulo financiero para controlar ventas a crédito y sus vencimientos.",
                            "status" => "active",
                            "updated_at" => now()
                        ]
                    );

                });

        }

        $this->clearMenuCache();

    }

    public function down(): void {

        if(Schema::hasTable("business_industry_module_sets")) {

            DB::table("business_industry_module_sets")->where("sub_section_id", self::SUB_SECTION_ID)->delete();

        }

        if(Schema::hasTable("role_sub_sections")) {

            DB::table("role_sub_sections")->where("sub_section_id", self::SUB_SECTION_ID)->delete();

        }

        if(Schema::hasTable("companies_sub_sections")) {

            DB::table("companies_sub_sections")->where("sub_section_id", self::SUB_SECTION_ID)->delete();

        }

        if(Schema::hasTable("sub_sections")) {

            DB::table("sub_sections")->where("id", self::SUB_SECTION_ID)->delete();

        }

        $this->clearMenuCache();

    }

    private function clearMenuCache(): void {

        if(!Schema::hasTable("companies")) return;

        DB::table("companies")->pluck("id")->each(function($companyId) {

            CompanySectionService::clearCompanyCache((int) $companyId);

        });

    }

};
