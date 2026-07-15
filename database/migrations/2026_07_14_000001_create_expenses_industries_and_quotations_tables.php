<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        Schema::create("business_industries", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("slug", 120);
            $table->string("name", 255);
            $table->string("description", 500)->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->unique(["company_id", "slug"]);
        });

        Schema::table("companies", function(Blueprint $table) {
            $table->unsignedBigInteger("business_industry_id")->nullable()->after("currency_id");
            $table->foreign("business_industry_id")->references("id")->on("business_industries")->nullOnDelete();
        });

        Schema::create("business_industry_module_sets", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("business_industry_id");
            $table->unsignedBigInteger("sub_section_id");
            $table->boolean("is_enabled_by_default")->default(true);
            $table->string("reason", 500)->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("business_industry_id")->references("id")->on("business_industries")->onDelete("cascade");
            $table->foreign("sub_section_id")->references("id")->on("sub_sections")->onDelete("cascade");
            $table->unique(["company_id", "business_industry_id", "sub_section_id"], "business_industry_module_set_unique");
        });

        Schema::create("misc_expense_categories", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("name", 150);
            $table->string("description", 500)->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        Schema::create("misc_expenses", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id")->nullable();
            $table->unsignedBigInteger("cash_session_id")->nullable();
            $table->unsignedBigInteger("payment_method_id")->nullable();
            $table->unsignedBigInteger("currency_id");
            $table->unsignedBigInteger("misc_expense_category_id")->nullable();
            $table->unsignedBigInteger("responsible_user_id")->nullable();
            $table->date("expense_date");
            $table->string("reference", 100)->nullable();
            $table->string("concept", 255);
            $table->decimal("amount", 16, 4);
            $table->text("description")->nullable();
            $table->text("observation")->nullable();
            $table->enum("status", ["active", "canceled"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("canceled_at")->nullable();
            $table->integer("canceled_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->nullOnDelete();
            $table->foreign("cash_session_id")->references("id")->on("cash_sessions")->nullOnDelete();
            $table->foreign("payment_method_id")->references("id")->on("payment_methods")->nullOnDelete();
            $table->foreign("currency_id")->references("id")->on("currencies")->restrictOnDelete();
            $table->foreign("misc_expense_category_id")->references("id")->on("misc_expense_categories")->nullOnDelete();
            $table->foreign("responsible_user_id")->references("id")->on("users")->nullOnDelete();
        });

        Schema::create("quotation_headers", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id")->nullable();
            $table->unsignedBigInteger("holder_id");
            $table->unsignedBigInteger("seller_id");
            $table->unsignedBigInteger("currency_id");
            $table->unsignedBigInteger("sale_header_id")->nullable();
            $table->string("reference", 100);
            $table->date("issue_date");
            $table->date("valid_until")->nullable();
            $table->decimal("subtotal", 16, 4)->default(0);
            $table->decimal("tax", 16, 4)->default(0);
            $table->decimal("total", 16, 4)->default(0);
            $table->text("observation")->nullable();
            $table->enum("status", ["draft", "sent", "accepted", "converted", "canceled", "expired"])->default("draft");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("converted_at")->nullable();
            $table->integer("converted_by")->nullable();
            $table->timestamp("canceled_at")->nullable();
            $table->integer("canceled_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->nullOnDelete();
            $table->foreign("holder_id")->references("id")->on("customers")->restrictOnDelete();
            $table->foreign("seller_id")->references("id")->on("users")->restrictOnDelete();
            $table->foreign("currency_id")->references("id")->on("currencies")->restrictOnDelete();
            $table->foreign("sale_header_id")->references("id")->on("sales_header")->nullOnDelete();
            $table->unique(["company_id", "reference"]);
        });

        Schema::create("quotation_items", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("quotation_header_id");
            $table->unsignedBigInteger("item_id");
            $table->unsignedBigInteger("currency_id");
            $table->string("name", 255);
            $table->enum("type", ["product", "service", "subscription"])->default("product");
            $table->decimal("quantity", 16, 4);
            $table->decimal("price", 16, 4);
            $table->boolean("price_includes_tax")->default(true);
            $table->decimal("total", 16, 4);
            $table->text("observation")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("quotation_header_id")->references("id")->on("quotation_headers")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->restrictOnDelete();
            $table->foreign("currency_id")->references("id")->on("currencies")->restrictOnDelete();
        });

        Schema::create("quotation_taxes", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("quotation_header_id");
            $table->unsignedBigInteger("tax_id")->nullable();
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->decimal("rate", 16, 4)->default(0);
            $table->enum("calculation_type", ["percentage", "fixed"])->default("percentage");
            $table->enum("operation_type", ["addition", "subtraction"])->default("addition");
            $table->boolean("is_required")->default(true);
            $table->unsignedInteger("quantity")->default(1);
            $table->decimal("base_amount", 16, 4)->default(0);
            $table->decimal("amount", 16, 4)->default(0);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("quotation_header_id")->references("id")->on("quotation_headers")->onDelete("cascade");
            $table->foreign("tax_id")->references("id")->on("taxes")->nullOnDelete();
        });

        Schema::table("sales_header", function(Blueprint $table) {
            $table->unsignedBigInteger("quotation_header_id")->nullable()->after("cash_session_id");
            $table->foreign("quotation_header_id")->references("id")->on("quotation_headers")->nullOnDelete();
        });

        $this->registerModules();
        $this->registerMiscExpenseCategories();
        $this->registerBusinessProfiles();

    }

    public function down(): void {

        Schema::table("sales_header", function(Blueprint $table) {
            $table->dropForeign(["quotation_header_id"]);
            $table->dropColumn("quotation_header_id");
        });

        Schema::dropIfExists("quotation_taxes");
        Schema::dropIfExists("quotation_items");
        Schema::dropIfExists("quotation_headers");
        Schema::dropIfExists("misc_expenses");
        Schema::dropIfExists("misc_expense_categories");
        Schema::dropIfExists("business_industry_module_sets");

        Schema::table("companies", function(Blueprint $table) {
            $table->dropForeign(["business_industry_id"]);
            $table->dropColumn("business_industry_id");
        });

        Schema::dropIfExists("business_industries");
    }

    private function registerModules(): void {

        $modules = [
            33 => [
                "section_id" => 3,
                "slug" => "sc_sales-quotations",
                "name" => "sales-quotations",
                "description" => "Registra propuestas comerciales y conviértelas en ventas recalculando precios vigentes.",
                "order" => 3,
                "dom_id" => "menu-sales-quotations",
                "dom_label" => "Cotizaciones",
                "dom_route" => "quotations.index",
                "section_order" => 4
            ],
            109 => [
                "section_id" => 10,
                "slug" => "sc_misc-expenses",
                "name" => "misc-expenses",
                "description" => "Registra gastos no ligados a compras de inventario, con responsable y contexto financiero.",
                "order" => 9,
                "dom_id" => "menu-misc-expenses",
                "dom_label" => "Gastos varios",
                "dom_route" => "misc_expenses.index",
                "section_order" => 3
            ],
            74 => [
                "section_id" => 7,
                "slug" => "sc_configuration-business-profile",
                "name" => "configuration-business-profile",
                "description" => "Configura el rubro de la empresa y el set base de módulos sugeridos.",
                "order" => 5,
                "dom_id" => "menu-configuration-business-profile",
                "dom_label" => "Rubro y módulos",
                "dom_route" => "business_profile.index",
                "section_order" => 10
            ]
        ];

        foreach($modules as $id => $module) {
            DB::table("sub_sections")->updateOrInsert(
                ["id" => $id],
                collect($module)->except(["section_order"])->all()
            );
        }

        DB::table("companies")->pluck("id")->each(function($companyId) use($modules) {
            foreach($modules as $id => $module) {
                DB::table("companies_sub_sections")->updateOrInsert(
                    ["company_id" => $companyId, "sub_section_id" => $id],
                    [
                        "section_order" => $module["section_order"],
                        "sub_section_order" => $module["order"],
                        "status" => "active"
                    ]
                );
            }
        });

    }

    private function registerBusinessProfiles(): void {

        $profiles = [
            "gym" => [
                "name" => "Gimnasio y membresías",
                "description" => "Base para gimnasios, estudios deportivos y negocios con membresías, asistencias y servicios recurrentes.",
                "disable" => [59, 105]
            ],
            "restaurant" => [
                "name" => "Restaurante y comida",
                "description" => "Base para restaurantes, cafeterías y negocios de comida con POS, mesas, recetas y cocina.",
                "disable" => [42, 52, 43]
            ],
            "retail" => [
                "name" => "Comercio y retail",
                "description" => "Base para tiendas con productos, inventario, compras, caja y ventas rápidas.",
                "disable" => [42, 52, 43, 59, 105, 106]
            ]
        ];

        $enabledModules = DB::table("sub_sections")->pluck("id")->map(fn($id) => (int) $id)->all();

        DB::table("companies")->pluck("id")->each(function($companyId) use($profiles, $enabledModules) {
            foreach($profiles as $slug => $profile) {
                DB::table("business_industries")->updateOrInsert(
                    ["company_id" => $companyId, "slug" => $slug],
                    [
                        "name" => $profile["name"],
                        "description" => $profile["description"],
                        "status" => "active",
                        "created_at" => now(),
                        "updated_at" => now()
                    ]
                );

                $industryId = (int) DB::table("business_industries")
                    ->where("company_id", $companyId)
                    ->where("slug", $slug)
                    ->value("id");

                foreach($enabledModules as $subSectionId) {
                    $isEnabled = !in_array($subSectionId, $profile["disable"], true);
                    DB::table("business_industry_module_sets")->updateOrInsert(
                        [
                            "company_id" => $companyId,
                            "business_industry_id" => $industryId,
                            "sub_section_id" => $subSectionId
                        ],
                        [
                            "is_enabled_by_default" => $isEnabled,
                            "reason" => $isEnabled
                                ? "Módulo sugerido para el rubro {$profile["name"]}."
                                : "Módulo normalmente no usado por el rubro {$profile["name"]}; puede habilitarse manualmente si aplica.",
                            "status" => "active",
                            "created_at" => now(),
                            "updated_at" => now()
                        ]
                    );
                }
            }

            $defaultIndustryId = (int) DB::table("business_industries")
                ->where("company_id", $companyId)
                ->where("slug", "gym")
                ->value("id");

            if($defaultIndustryId > 0) {
                DB::table("companies")
                    ->where("id", $companyId)
                    ->whereNull("business_industry_id")
                    ->update(["business_industry_id" => $defaultIndustryId]);
            }
        });

    }

    private function registerMiscExpenseCategories(): void {

        $categories = [
            ["name" => "Mantenimiento", "description" => "Gastos de conservación, ajustes menores y revisiones operativas."],
            ["name" => "Reparación", "description" => "Gastos por arreglo de equipos, mobiliario, infraestructura o herramientas."],
            ["name" => "Servicios básicos", "description" => "Pagos de luz, agua, internet, telefonía u otros servicios de operación."],
            ["name" => "Suministros menores", "description" => "Compras pequeñas que no ingresan como inventario comercial."],
            ["name" => "Otros gastos", "description" => "Gastos operativos que no encajan en una categoría específica."]
        ];

        DB::table("companies")->pluck("id")->each(function($companyId) use($categories) {
            foreach($categories as $category) {
                DB::table("misc_expense_categories")->updateOrInsert(
                    ["company_id" => $companyId, "name" => $category["name"]],
                    [
                        "description" => $category["description"],
                        "status" => "active",
                        "created_at" => now(),
                        "updated_at" => now()
                    ]
                );
            }
        });

    }

};
