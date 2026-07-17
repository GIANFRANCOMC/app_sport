<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create("sales_header", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("serie_id");
            $table->integer("sequential");
            $table->unsignedBigInteger("holder_id");
            $table->unsignedBigInteger("seller_id");
            $table->unsignedBigInteger("currency_id");
            $table->unsignedBigInteger("warehouse_id")->nullable();
            $table->unsignedBigInteger("cash_session_id")->nullable();
            $table->date("issue_date");
            $table->enum("delivery_mode", ["immediate", "pending"])->default("immediate");
            $table->enum("delivery_status", ["pending", "partial", "delivered", "canceled"])->default("delivered");
            $table->timestamp("delivered_at")->nullable();
            $table->unsignedBigInteger("delivered_by")->nullable();
            $table->string("delivery_observation", 500)->nullable();
            $table->decimal("subtotal", 16, 4)->default(0);
            $table->decimal("tax", 16, 4)->default(0);
            $table->decimal("commission_total", 16, 4)->default(0);
            $table->decimal("total", 16, 4);
            $table->text("observation")->nullable();
            $table->enum("status", ["active", "canceled", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("canceled_at")->nullable();
            $table->integer("canceled_by")->nullable();

            $table->foreign("serie_id")->references("id")->on("series")->onDelete("cascade");
            $table->foreign("holder_id")->references("id")->on("customers")->onDelete("cascade");
            $table->foreign("seller_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("cascade");
            $table->foreign("warehouse_id")->references("id")->on("warehouses")->nullOnDelete();
            $table->foreign("cash_session_id")->references("id")->on("cash_sessions")->nullOnDelete();
            $table->foreign("delivered_by")->references("id")->on("users")->nullOnDelete();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->unique(["company_id", "serie_id", "sequential"]);
        });

        Schema::create("series_correlative_movements", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("serie_id");
            $table->unsignedBigInteger("sale_header_id");
            $table->unsignedBigInteger("user_id")->nullable();
            $table->integer("sequential");
            $table->enum("action", ["issued", "canceled"]);
            $table->enum("source", ["sale", "pos"])->default("sale");
            $table->string("note", 500)->nullable();
            $table->json("metadata")->nullable();
            $table->timestamp("occurred_at")->useCurrent();
            $table->timestamp("created_at")->useCurrent()->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("serie_id")->references("id")->on("series")->restrictOnDelete();
            $table->foreign("sale_header_id")->references("id")->on("sales_header")->restrictOnDelete();
            $table->foreign("user_id")->references("id")->on("users")->nullOnDelete();
            $table->unique(
                ["company_id", "serie_id", "sequential", "action"],
                "series_corr_company_serie_seq_action_uq"
            );
        });
        Schema::create("sales_body", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("sale_header_id");
            $table->unsignedBigInteger("item_id");
            $table->unsignedBigInteger("currency_id");
            $table->string("name", 255);
            $table->decimal("quantity", 16, 4);
            $table->decimal("price", 16, 4);
            $table->boolean("price_includes_tax")->default(true);
            $table->decimal("total", 16, 4);
            $table->enum("commission_type", ["none", "percentage", "fixed"])->default("none");
            $table->decimal("commission_value", 16, 4)->default(0);
            $table->decimal("commission_amount", 16, 4)->default(0);
            $table->unsignedBigInteger("customer_id");
            $table->enum("type", ["product", "service", "subscription"])->default("product");
            $table->text("observation")->nullable();
            $table->text("extras");
            $table->enum("status", ["active", "canceled", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("canceled_at")->nullable();
            $table->integer("canceled_by")->nullable();

            $table->foreign("sale_header_id")->references("id")->on("sales_header")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("cascade");
            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("cascade");
            $table->foreign("customer_id")->references("id")->on("customers")->onDelete("cascade");
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        Schema::create("sale_taxes", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("sale_header_id");
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
            $table->enum("status", ["active", "canceled", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("sale_header_id")->references("id")->on("sales_header")->onDelete("cascade");
            $table->foreign("tax_id")->references("id")->on("taxes")->nullOnDelete();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        Schema::create("sale_payments", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("sale_header_id");
            $table->unsignedBigInteger("payment_method_id")->nullable();
            $table->string("name", 255);
            $table->decimal("amount", 16, 4)->default(0);
            $table->string("reference", 100)->nullable();
            $table->text("note")->nullable();
            $table->enum("status", ["active", "canceled", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("sale_header_id")->references("id")->on("sales_header")->onDelete("cascade");
            $table->foreign("payment_method_id")->references("id")->on("payment_methods")->nullOnDelete();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {

        Schema::dropIfExists("sale_payments");
        Schema::dropIfExists("sale_taxes");
        Schema::dropIfExists("sales_body");
        Schema::dropIfExists("series_correlative_movements");
        Schema::dropIfExists("sales_header");

    }

};

