<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        Schema::create("service_stations", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->string("code", 50);
            $table->string("name", 150);
            $table->string("station_type", 30)->default("table");
            $table->unsignedSmallInteger("capacity")->default(1);
            $table->string("description", 500)->nullable();
            $table->string("status", 20)->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->restrictOnDelete();
            $table->unique(["company_id", "branch_id", "code"]);
        });

        Schema::create("service_sessions", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("service_station_id")->nullable();
            $table->unsignedBigInteger("customer_id")->nullable();
            $table->unsignedBigInteger("assigned_user_id")->nullable();
            $table->unsignedBigInteger("sale_header_id")->nullable();
            $table->unsignedBigInteger("opened_by");
            $table->unsignedBigInteger("closed_by")->nullable();
            $table->string("reference", 50);
            $table->string("session_type", 30)->default("catalog_service");
            $table->string("status", 20)->default("pending");
            $table->dateTime("started_at")->nullable();
            $table->dateTime("ended_at")->nullable();
            $table->unsignedInteger("duration_minutes")->default(0);
            $table->string("observation", 500)->nullable();
            $table->string("cancellation_reason", 500)->nullable();

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("canceled_at")->nullable();
            $table->integer("canceled_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->restrictOnDelete();
            $table->foreign("service_station_id")->references("id")->on("service_stations")->restrictOnDelete();
            $table->foreign("customer_id")->references("id")->on("customers")->nullOnDelete();
            $table->foreign("assigned_user_id")->references("id")->on("users")->nullOnDelete();
            $table->foreign("sale_header_id")->references("id")->on("sales_header")->restrictOnDelete();
            $table->foreign("opened_by")->references("id")->on("users")->restrictOnDelete();
            $table->foreign("closed_by")->references("id")->on("users")->nullOnDelete();
            $table->unique(["company_id", "reference"]);
        });

        Schema::create("service_session_items", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("service_session_id");
            $table->unsignedBigInteger("item_id");
            $table->unsignedBigInteger("assigned_user_id")->nullable();
            $table->string("name", 255);
            $table->string("item_type", 30);
            $table->decimal("quantity", 16, 4)->default(1);
            $table->decimal("unit_price", 16, 4)->default(0);
            $table->string("status", 20)->default("pending");
            $table->dateTime("started_at")->nullable();
            $table->dateTime("ended_at")->nullable();
            $table->unsignedInteger("duration_minutes")->default(0);
            $table->string("observation", 500)->nullable();

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("canceled_at")->nullable();
            $table->integer("canceled_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("service_session_id")->references("id")->on("service_sessions")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("items")->restrictOnDelete();
            $table->foreign("assigned_user_id")->references("id")->on("users")->nullOnDelete();
        });

    }

    public function down(): void {

        Schema::dropIfExists("service_session_items");
        Schema::dropIfExists("service_sessions");
        Schema::dropIfExists("service_stations");

    }

};
