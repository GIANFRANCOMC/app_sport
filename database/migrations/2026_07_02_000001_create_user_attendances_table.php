<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        Schema::create("user_attendances", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("user_id");
            $table->date("work_date");
            $table->dateTime("checked_in_at");
            $table->dateTime("checked_out_at")->nullable();
            $table->unsignedInteger("worked_minutes")->default(0);
            $table->string("source_type", 30)->default("manual_form");
            $table->string("source_reference", 100)->nullable();
            $table->string("observation", 500)->nullable();
            $table->string("motive", 500)->nullable();
            $table->string("status", 20)->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("canceled_at")->nullable();
            $table->integer("canceled_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->restrictOnDelete();
            $table->foreign("user_id")->references("id")->on("users")->restrictOnDelete();
        });

    }

    public function down(): void {

        Schema::dropIfExists("user_attendances");

    }

};
