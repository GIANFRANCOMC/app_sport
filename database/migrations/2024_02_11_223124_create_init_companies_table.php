<?php

use App\Helpers\System\Utilities;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Hash, Schema};

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {

        Schema::create("company_settings", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("group");
            $table->string("key");
            $table->text("value")->nullable();
            $table->enum("value_type", ["string", "boolean", "integer", "decimal", "json"])->default("string");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        Schema::create("taxes", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("code", 30);
            $table->string("name");
            $table->decimal("rate", 7, 4)->default(0);
            $table->enum("scope", ["sale", "purchase", "both"])->default("both");
            $table->boolean("is_default")->default(false);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        Schema::create("payment_methods", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("code", 30);
            $table->string("name");
            $table->enum("scope", ["sale", "purchase", "both"])->default("both");
            $table->boolean("requires_reference")->default(false);
            $table->boolean("is_default")->default(false);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        // ✅
        Schema::create("company_socials_media", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->enum("type", ["web", "facebook", "instagram", "tiktok", "whatsapp", "other"])->default("other");
            $table->text("link");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        // ✔️
        Schema::create("branches", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("internal_code");
            $table->string("name");
            $table->string("address")->nullable();
            $table->string("reference")->nullable();
            $table->string("telephone")->nullable();
            $table->string("email")->nullable();
            $table->integer("capacity")->nullable();
            $table->text("map_url")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("deleted_at")->nullable();
            $table->integer("deleted_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->unique(["company_id", "internal_code"]);
        });

        // ✅
        Schema::create("user_branches", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("branch_id");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
        });

        Schema::create("series", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("document_type_id");
            $table->string("code");
            $table->integer("number");
            $table->integer("init")->default(1);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("document_type_id")->references("id")->on("document_types")->onDelete("cascade");
        });

        // ✅
        Schema::create("brands", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("internal_code");
            $table->string("name");
            $table->text("description")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        // ✅
        Schema::create("items", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("brand_id")->nullable();
            $table->string("internal_code");
            $table->string("barcode", 13)->nullable();
            $table->string("name");
            $table->text("description")->nullable();
            $table->decimal("price", 10, 2);
            $table->boolean("price_includes_tax")->default(true);
            $table->decimal("min_price", 10, 2)->nullable();
            $table->decimal("max_price", 10, 2)->nullable();
            $table->unsignedBigInteger("currency_id");
            $table->enum("type", ["product", "service", "subscription"])->default("product");
            $table->enum("duration_type", ["hour", "day", "today", "month", "year"])->nullable();
            $table->integer("duration_value")->nullable();
            $table->boolean("see_my_web")->nullable()->default(true);
            $table->boolean("see_my_web_price")->nullable()->default(false);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("brand_id")->references("id")->on("brands")->nullOnDelete();
            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("cascade");
        });

        // ✅
        Schema::create("assets", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("internal_code");
            $table->string("name");
            $table->text("description")->nullable();
            $table->enum("management_type", ["unit", "stock"])->default("stock");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        // ✅
        Schema::create("categories", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("internal_code");
            $table->string("name");
            $table->text("description")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        // ✅
        Schema::create("category_items", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("category_id");
            $table->unsignedBigInteger("item_id");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("category_id")->references("id")->on("categories")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
        });

        // ✅
        Schema::create("customers", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("identity_document_type_id");
            $table->string("document_number");
            $table->string("name");
            $table->string("email")->nullable();
            $table->string("phone_number")->nullable();
            $table->enum("gender", ["male", "female", "other"])->nullable();
            $table->date("birthdate")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("identity_document_type_id")->references("id")->on("identity_document_types")->onDelete("cascade");
        });

        // ✅
        Schema::create("warehouses", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("branch_id");
            $table->string("name");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
        });

        // ✅
        Schema::create("cash_registers", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->string("code", 30)->nullable();
            $table->string("name");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
        });

        Schema::create("cash_sessions", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("cash_register_id");
            $table->unsignedBigInteger("opened_by");
            $table->unsignedBigInteger("closed_by")->nullable();
            $table->timestamp("opened_at")->useCurrent();
            $table->timestamp("closed_at")->nullable();
            $table->decimal("opening_amount", 10, 2)->default(0);
            $table->decimal("expected_amount", 10, 2)->default(0);
            $table->decimal("counted_amount", 10, 2)->default(0);
            $table->decimal("difference_amount", 10, 2)->default(0);
            $table->text("observation")->nullable();
            $table->enum("status", ["open", "closed", "canceled"])->default("open");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("cash_register_id")->references("id")->on("cash_registers")->onDelete("cascade");
            $table->foreign("opened_by")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("closed_by")->references("id")->on("users")->nullOnDelete();
        });

        Schema::create("cash_session_payments", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("cash_session_id");
            $table->unsignedBigInteger("payment_method_id")->nullable();
            $table->string("payment_method_name");
            $table->decimal("expected_amount", 10, 2)->default(0);
            $table->decimal("counted_amount", 10, 2)->default(0);
            $table->decimal("difference_amount", 10, 2)->default(0);
            $table->text("note")->nullable();
            $table->enum("status", ["active", "canceled", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("cash_session_id")->references("id")->on("cash_sessions")->onDelete("cascade");
            $table->foreign("payment_method_id")->references("id")->on("payment_methods")->nullOnDelete();
        });

        Schema::create("cash_movements", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("cash_session_id")->nullable();
            $table->unsignedBigInteger("payment_method_id")->nullable();
            $table->unsignedBigInteger("user_id");
            $table->enum("movement_type", ["opening", "sale", "purchase", "expense", "income", "withdrawal", "adjustment", "closing"])->default("sale");
            $table->string("origin_type", 60)->nullable();
            $table->unsignedBigInteger("origin_id")->nullable();
            $table->decimal("amount", 10, 2)->default(0);
            $table->string("reference", 100)->nullable();
            $table->text("note")->nullable();
            $table->timestamp("occurred_at")->useCurrent();
            $table->enum("status", ["active", "canceled", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("cash_session_id")->references("id")->on("cash_sessions")->nullOnDelete();
            $table->foreign("payment_method_id")->references("id")->on("payment_methods")->nullOnDelete();
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");

            $table->index(["company_id", "branch_id", "occurred_at"]);
            $table->index(["origin_type", "origin_id"]);
        });

        Schema::create("warehouse_items", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("warehouse_id");
            $table->unsignedBigInteger("item_id");
            $table->decimal("quantity", 12, 2)->default(0);
            $table->decimal("minimum_stock", 12, 2)->default(0);
            $table->decimal("average_cost", 14, 4)->default(0);
            $table->decimal("inventory_value", 14, 2)->default(0);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
            $table->unique(["warehouse_id", "item_id"]);
        });

        // Historial inmutable de entradas, salidas y correcciones de inventario.
        Schema::create("inventory_movements", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("warehouse_id");
            $table->unsignedBigInteger("item_id");
            $table->unsignedBigInteger("user_id")->nullable();
            $table->string("movement_type", 30);
            $table->string("origin_type", 50);
            $table->unsignedBigInteger("origin_id")->nullable();
            $table->decimal("quantity_before", 12, 2);
            $table->decimal("quantity_change", 12, 2);
            $table->decimal("quantity_after", 12, 2);
            $table->decimal("unit_cost", 14, 4)->default(0);
            $table->decimal("value_before", 14, 2)->default(0);
            $table->decimal("value_change", 14, 2)->default(0);
            $table->decimal("value_after", 14, 2)->default(0);
            $table->string("reason", 255);
            $table->json("metadata")->nullable();
            $table->timestamp("created_at")->useCurrent();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->nullOnDelete();

            $table->index(["company_id", "created_at"]);
            $table->index(["warehouse_id", "item_id", "created_at"]);
            $table->index(["origin_type", "origin_id"]);
        });

        // ✅
        Schema::create("branch_assets", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("asset_id");
            $table->unsignedBigInteger("currency_id");
            $table->decimal("quantity", 10, 2)->nullable()->default(0);
            $table->decimal("acquisition_value", 10, 2)->nullable()->default(0);
            $table->date("acquisition_date")->nullable();
            $table->text("note")->nullable();
            $table->enum("status", ["active", "maintenance", "retired"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("asset_id")->references("id")->on("assets")->onDelete("cascade");
            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("cascade");
            $table->unique(["branch_id", "asset_id"]);
        });

        // ✅
        Schema::create("asset_assignments", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("asset_id");
            $table->unsignedBigInteger("currency_id");
            $table->decimal("quantity", 10, 2)->nullable()->default(0);
            $table->decimal("acquisition_value", 10, 2)->nullable()->default(0);
            $table->date("acquisition_date")->nullable();
            $table->text("note")->nullable();
            $table->enum("status", ["active", "maintenance", "retired"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("asset_id")->references("id")->on("assets")->onDelete("cascade");
            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("cascade");
        });

        // ✅
        Schema::create("asset_assignment_logs", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("action_by");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("asset_id");
            $table->enum("action_type", ["assigned", "transferred", "returned", "retired"]);
            $table->decimal("quantity", 10, 2);
            $table->text("note")->nullable();
            $table->timestamp("action_at")->useCurrent();

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("action_by")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("asset_id")->references("id")->on("assets")->onDelete("cascade");
        });

        // Inserts
        DB::table("company_settings")->insert([
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "product", "value" => "PRO", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "service", "value" => "SER", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "subscription", "value" => "MEM", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "brand", "value" => "MAR", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "category", "value" => "CAT", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "branch", "value" => "SUC", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "asset", "value" => "ACT", "value_type" => "string"],
            [
                "company_id" => 1,
                "group" => "inventory",
                "key" => "restore_stock_on_sale_cancellation",
                "value" => "false",
                "value_type" => "boolean"
            ],
            [
                "company_id" => 1,
                "group" => "inventory",
                "key" => "valuation_method",
                "value" => "weighted_average",
                "value_type" => "string"
            ]
        ]);

        DB::table("taxes")->insert([
            ["company_id" => 1, "code" => "SALE-GEN", "name" => "Impuesto venta general", "rate" => 10, "scope" => "sale", "is_default" => false],
            ["company_id" => 1, "code" => "PUR-GEN", "name" => "Impuesto compra general", "rate" => 8, "scope" => "purchase", "is_default" => false]
        ]);

        DB::table("payment_methods")->insert([
            ["company_id" => 1, "code" => "CASH", "name" => "Efectivo", "scope" => "both", "requires_reference" => false, "is_default" => true],
            ["company_id" => 1, "code" => "CARD", "name" => "Tarjeta", "scope" => "sale", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "TRANSFER", "name" => "Transferencia", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "DIGITAL_WALLET", "name" => "Billetera digital", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "YAPE", "name" => "Yape", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "PLIN", "name" => "Plin", "scope" => "both", "requires_reference" => true, "is_default" => false]
        ]);

        DB::table("company_socials_media")->insert([
            ["company_id" => 1, "type" => "facebook", "link" => "https://www.facebook.com/GianfrancoMC"],
            ["company_id" => 1, "type" => "instagram", "link" => "https://www.instagram.com/gianfrancomc"],
            ["company_id" => 1, "type" => "whatsapp", "link" => "https://wa.me/987057624"]
        ]);

        DB::table("branches")->insert([
            ["id" => 1, "internal_code" => "SUC-" . Utilities::generateCode(5), "company_id" => 1, "name" => "Sede Principal"]
        ]);

        DB::table("series")->insert([
            ["branch_id" => 1, "document_type_id" => 1, "code" => "BV", "number" => 1, "init" => 1],
            ["branch_id" => 1, "document_type_id" => 2, "code" => "FA", "number" => 1, "init" => 1]
        ]);

        DB::table("customers")->insert([
            ["company_id" => 1, "identity_document_type_id" => 1, "document_number" => "999999999", "name" => "Cliente varios", "phone_number" => ""],
            ["company_id" => 1, "identity_document_type_id" => 2, "document_number" => "71883137", "name" => "Gianfranco Mejia Carhuajulca", "phone_number" => "51987057624"],
            ["company_id" => 1, "identity_document_type_id" => 2, "document_number" => "71883136", "name" => "Andy Paolo Mejia Carhuajulca", "phone_number" => "51987634253"]
        ]);

        DB::table("warehouses")->insert([
            ["branch_id" => 1, "name" => "Almacén - Sede principal"]
        ]);

        DB::table("cash_registers")->insert([
            ["company_id" => 1, "branch_id" => 1, "code" => "CAJ-" . Utilities::generateCode(5), "name" => "Caja principal"]
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {

        Schema::dropIfExists("asset_assignment_logs");
        Schema::dropIfExists("asset_assignments");
        Schema::dropIfExists("branch_assets");
        Schema::dropIfExists("inventory_movements");
        Schema::dropIfExists("warehouse_items");
        Schema::dropIfExists("cash_movements");
        Schema::dropIfExists("cash_session_payments");
        Schema::dropIfExists("cash_sessions");
        Schema::dropIfExists("cash_registers");
        Schema::dropIfExists("warehouses");
        Schema::dropIfExists("customers");
        Schema::dropIfExists("category_items");
        Schema::dropIfExists("categories");
        Schema::dropIfExists("assets");
        Schema::dropIfExists("items");
        Schema::dropIfExists("brands");
        Schema::dropIfExists("series");
        Schema::dropIfExists("user_branches");
        Schema::dropIfExists("branches");
        Schema::dropIfExists("company_socials_media");
        Schema::dropIfExists("payment_methods");
        Schema::dropIfExists("taxes");
        Schema::dropIfExists("company_settings");

    }

};
