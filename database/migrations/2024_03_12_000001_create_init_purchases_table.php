<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        Schema::create("suppliers", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("document_type", 20)->nullable();
            $table->string("document_number", 30)->nullable();
            $table->string("name", 255);
            $table->string("contact_name", 255)->nullable();
            $table->string("telephone", 30)->nullable();
            $table->string("email", 255)->nullable();
            $table->string("address", 255)->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        Schema::create("purchase_headers", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("supplier_id");
            $table->unsignedBigInteger("warehouse_id");
            $table->unsignedBigInteger("currency_id");
            $table->enum("document_type", ["order", "invoice"])->default("order");
            $table->string("document_number", 50)->nullable();
            $table->date("issue_date");
            $table->date("expected_date")->nullable();
            $table->decimal("subtotal", 16, 4)->default(0);
            $table->decimal("tax", 16, 4)->default(0);
            $table->decimal("total", 16, 4)->default(0);
            $table->text("observation")->nullable();
            $table->enum("status", [
                "confirmed",
                "partial",
                "received",
                "canceled"
            ])->default("confirmed");
            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("canceled_at")->nullable();
            $table->integer("canceled_by")->nullable();
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("supplier_id")->references("id")->on("suppliers")->onDelete("restrict");
            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("restrict");
            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("restrict");
        });

        Schema::create("purchase_items", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("purchase_header_id");
            $table->unsignedBigInteger("item_id");
            $table->string("name", 255);
            $table->decimal("quantity", 16, 4);
            $table->decimal("received_quantity", 16, 4)->default(0);
            $table->decimal("unit_cost", 16, 4);
            $table->decimal("subtotal", 16, 4);
            $table->enum("status", ["pending", "partial", "received", "canceled"])->default("pending");
            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->foreign("purchase_header_id")->references("id")->on("purchase_headers")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("restrict");
            $table->foreign("company_id")->references("id")->on("companies")->nullOnDelete();
        });

        Schema::create("purchase_receipts", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("purchase_header_id");
            $table->unsignedBigInteger("warehouse_id");
            $table->string("reference", 40);
            $table->dateTime("received_at");
            $table->text("observation")->nullable();
            $table->enum("status", ["received", "canceled"])->default("received");
            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("canceled_at")->nullable();
            $table->integer("canceled_by")->nullable();
            $table->foreign("purchase_header_id")->references("id")->on("purchase_headers")->onDelete("cascade");
            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("restrict");
            $table->foreign("company_id")->references("id")->on("companies")->nullOnDelete();
        });

        Schema::create("purchase_receipt_items", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("purchase_receipt_id");
            $table->unsignedBigInteger("purchase_item_id");
            $table->unsignedBigInteger("item_id");
            $table->unsignedBigInteger("inventory_movement_id")->nullable();
            $table->decimal("quantity", 16, 4);
            $table->decimal("unit_cost", 16, 4);
            $table->decimal("total_cost", 16, 4);
            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->foreign("purchase_receipt_id")->references("id")->on("purchase_receipts")->onDelete("cascade");
            $table->foreign("purchase_item_id")->references("id")->on("purchase_items")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->onDelete("restrict");
            $table->foreign("inventory_movement_id")->references("id")->on("inventory_movements")->nullOnDelete();
            $table->foreign("company_id")->references("id")->on("companies")->nullOnDelete();
        });

        Schema::create("purchase_taxes", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("purchase_header_id");
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
            $table->foreign("purchase_header_id")->references("id")->on("purchase_headers")->onDelete("cascade");
            $table->foreign("tax_id")->references("id")->on("taxes")->nullOnDelete();
            $table->foreign("company_id")->references("id")->on("companies")->nullOnDelete();
        });

        Schema::create("purchase_payments", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("purchase_header_id");
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
            $table->foreign("purchase_header_id")->references("id")->on("purchase_headers")->onDelete("cascade");
            $table->foreign("payment_method_id")->references("id")->on("payment_methods")->nullOnDelete();
            $table->foreign("company_id")->references("id")->on("companies")->nullOnDelete();
        });

    }

    public function down(): void {

        Schema::dropIfExists("purchase_payments");
        Schema::dropIfExists("purchase_taxes");
        Schema::dropIfExists("purchase_receipt_items");
        Schema::dropIfExists("purchase_receipts");
        Schema::dropIfExists("purchase_items");
        Schema::dropIfExists("purchase_headers");
        Schema::dropIfExists("suppliers");

    }

};
