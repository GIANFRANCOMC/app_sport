<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {

        Schema::create("company_settings", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("group", 255);
            $table->string("key", 255);
            $table->text("value")->nullable();
            $table->text("description")->nullable();
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
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->decimal("rate", 16, 4)->default(0);
            $table->enum("calculation_type", ["percentage", "fixed"])->default("percentage");
            $table->enum("operation_type", ["addition", "subtraction"])->default("addition");
            $table->unsignedInteger("min_apply_quantity")->nullable();
            $table->unsignedInteger("max_apply_quantity")->nullable();
            $table->enum("scope", ["sale", "purchase", "both"])->default("both");
            $table->boolean("is_required")->default(true);
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
            $table->string("name", 255);
            $table->string("sunat_code", 10)->nullable();
            $table->string("image_path", 500)->nullable();
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
        Schema::create("branches", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("internal_code", 255);
            $table->string("name", 255);
            $table->string("address", 255)->nullable();
            $table->string("reference", 255)->nullable();
            $table->string("telephone", 255)->nullable();
            $table->string("email", 255)->nullable();
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
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("document_type_id");
            $table->string("code", 255);
            $table->integer("number");
            $table->integer("init")->default(1);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("document_type_id")->references("id")->on("document_types")->onDelete("cascade");
        });
        Schema::create("brands", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("internal_code", 255);
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });
        Schema::create("items", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("brand_id")->nullable();
            $table->string("internal_code", 255);
            $table->string("barcode", 13)->nullable();
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->decimal("price", 16, 4);
            $table->boolean("price_includes_tax")->default(true);
            $table->decimal("min_price", 16, 4)->nullable();
            $table->decimal("max_price", 16, 4)->nullable();
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
        Schema::create("assets", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("internal_code", 255);
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->enum("management_type", ["unit", "stock"])->default("stock");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });
        Schema::create("categories", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("internal_code", 255);
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });
        Schema::create("category_items", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("category_id");
            $table->unsignedBigInteger("item_id");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("category_id")->references("id")->on("categories")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
        });
        Schema::create("customers", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("identity_document_type_id");
            $table->string("document_number", 255);
            $table->string("name", 255);
            $table->string("email", 255)->nullable();
            $table->string("phone_number", 255)->nullable();
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
        Schema::create("warehouses", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->string("name", 255);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
        });
        Schema::create("cash_registers", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->string("code", 30)->nullable();
            $table->string("name", 255);
            $table->boolean("is_main")->default(false);
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
            $table->decimal("opening_amount", 16, 4)->default(0);
            $table->decimal("expected_amount", 16, 4)->default(0);
            $table->decimal("counted_amount", 16, 4)->default(0);
            $table->decimal("difference_amount", 16, 4)->default(0);
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
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("cash_session_id");
            $table->unsignedBigInteger("payment_method_id")->nullable();
            $table->string("payment_method_name", 255);
            $table->decimal("expected_amount", 16, 4)->default(0);
            $table->decimal("counted_amount", 16, 4)->default(0);
            $table->decimal("difference_amount", 16, 4)->default(0);
            $table->text("note")->nullable();
            $table->enum("status", ["active", "canceled", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

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
            $table->decimal("amount", 16, 4)->default(0);
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

        });

        Schema::create("warehouse_items", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("warehouse_id");
            $table->unsignedBigInteger("item_id");
            $table->decimal("quantity", 16, 4)->default(0);
            $table->decimal("minimum_stock", 16, 4)->default(0);
            $table->decimal("average_cost", 16, 4)->default(0);
            $table->decimal("inventory_value", 16, 4)->default(0);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
            $table->unique(["company_id", "warehouse_id", "item_id"]);
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
            $table->decimal("quantity_before", 16, 4);
            $table->decimal("quantity_change", 16, 4);
            $table->decimal("quantity_after", 16, 4);
            $table->decimal("unit_cost", 16, 4)->default(0);
            $table->decimal("value_before", 16, 4)->default(0);
            $table->decimal("value_change", 16, 4)->default(0);
            $table->decimal("value_after", 16, 4)->default(0);
            $table->string("reason", 255);
            $table->json("metadata")->nullable();
            $table->timestamp("created_at")->useCurrent();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->nullOnDelete();

        });

        Schema::create("recipe_dishes", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("item_id");
            $table->decimal("yield_quantity", 16, 4)->default(1);
            $table->decimal("waste_percentage", 16, 4)->default(0);
            $table->text("preparation_notes")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
        });

        Schema::create("recipe_dish_components", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("recipe_dish_id");
            $table->unsignedBigInteger("item_id");
            $table->decimal("quantity", 16, 4);
            $table->decimal("waste_percentage", 16, 4)->default(0);
            $table->string("note", 255)->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("recipe_dish_id")->references("id")->on("recipe_dishes")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
        });

        Schema::create("recipe_toppings", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("currency_id");
            $table->unsignedBigInteger("item_id")->nullable();
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->decimal("price", 16, 4)->default(0);
            $table->unsignedInteger("max_quantity")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->nullOnDelete();
        });

        Schema::create("recipe_dish_toppings", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("recipe_dish_id");
            $table->unsignedBigInteger("recipe_topping_id");
            $table->boolean("is_default")->default(false);
            $table->unsignedInteger("min_quantity")->default(0);
            $table->unsignedInteger("max_quantity")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("recipe_dish_id")->references("id")->on("recipe_dishes")->onDelete("cascade");
            $table->foreign("recipe_topping_id")->references("id")->on("recipe_toppings")->onDelete("cascade");
        });

        Schema::create("recipe_topping_components", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("recipe_topping_id");
            $table->unsignedBigInteger("item_id");
            $table->decimal("quantity", 16, 4);
            $table->decimal("waste_percentage", 16, 4)->default(0);
            $table->string("note", 255)->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("recipe_topping_id")->references("id")->on("recipe_toppings")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
        });

        Schema::create("recipe_dish_options", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("recipe_dish_id");
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->unsignedInteger("max_portions")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("recipe_dish_id")->references("id")->on("recipe_dishes")->onDelete("cascade");
        });

        Schema::create("recipe_dish_option_components", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("recipe_dish_option_id");
            $table->unsignedBigInteger("item_id");
            $table->decimal("quantity", 16, 4);
            $table->decimal("waste_percentage", 16, 4)->default(0);
            $table->string("note", 255)->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("recipe_dish_option_id")->references("id")->on("recipe_dish_options")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
        });

        Schema::create("cash_session_inventory_counts", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("cash_session_id");
            $table->unsignedBigInteger("warehouse_id");
            $table->unsignedBigInteger("item_id");
            $table->unsignedBigInteger("inventory_movement_id")->nullable();
            $table->decimal("system_quantity", 16, 4)->default(0);
            $table->decimal("counted_quantity", 16, 4)->default(0);
            $table->decimal("difference_quantity", 16, 4)->default(0);
            $table->text("observation")->nullable();
            $table->enum("status", ["pending", "adjusted", "ignored", "canceled"])->default("pending");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("cash_session_id")->references("id")->on("cash_sessions")->onDelete("cascade");
            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
            $table->foreign("inventory_movement_id")->references("id")->on("inventory_movements")->nullOnDelete();
        });
        Schema::create("branch_assets", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("asset_id");
            $table->unsignedBigInteger("currency_id");
            $table->decimal("quantity", 16, 4)->nullable()->default(0);
            $table->decimal("acquisition_value", 16, 4)->nullable()->default(0);
            $table->date("acquisition_date")->nullable();
            $table->text("note")->nullable();
            $table->enum("status", ["active", "maintenance", "retired"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("asset_id")->references("id")->on("assets")->onDelete("cascade");
            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("cascade");
            $table->unique(["company_id", "branch_id", "asset_id"]);
        });
        Schema::create("asset_assignments", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("asset_id");
            $table->unsignedBigInteger("currency_id");
            $table->decimal("quantity", 16, 4)->nullable()->default(0);
            $table->decimal("acquisition_value", 16, 4)->nullable()->default(0);
            $table->date("acquisition_date")->nullable();
            $table->text("note")->nullable();
            $table->enum("status", ["active", "maintenance", "retired"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("asset_id")->references("id")->on("assets")->onDelete("cascade");
            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("cascade");
        });
        Schema::create("asset_assignment_logs", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("action_by");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("asset_id");
            $table->enum("action_type", ["assigned", "transferred", "returned", "retired"]);
            $table->decimal("quantity", 16, 4);
            $table->text("note")->nullable();
            $table->timestamp("action_at")->useCurrent();

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");

            $table->foreign("action_by")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("asset_id")->references("id")->on("assets")->onDelete("cascade");
        });
        // Initial data lives in 2024_12_31_235959_insert_initial_system_data.php.

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {

        Schema::dropIfExists("asset_assignment_logs");
        Schema::dropIfExists("asset_assignments");
        Schema::dropIfExists("branch_assets");
        Schema::dropIfExists("cash_session_inventory_counts");
        Schema::dropIfExists("recipe_dish_option_components");
        Schema::dropIfExists("recipe_dish_options");
        Schema::dropIfExists("recipe_topping_components");
        Schema::dropIfExists("recipe_dish_toppings");
        Schema::dropIfExists("recipe_toppings");
        Schema::dropIfExists("recipe_dish_components");
        Schema::dropIfExists("recipe_dishes");
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
