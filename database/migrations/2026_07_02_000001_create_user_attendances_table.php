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

        Schema::create("user_biometric_fingerprints", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("biometric_device_id");
            $table->integer("device_user_id");
            $table->unsignedTinyInteger("finger_index")->default(0);
            $table->longText("fingerprint_template")->nullable();
            $table->string("description", 500)->nullable();
            $table->string("status", 20)->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->restrictOnDelete();
            $table->foreign("biometric_device_id")->references("id")->on("biometric_devices")->restrictOnDelete();
            $table->unique(
                ["company_id", "biometric_device_id", "device_user_id", "finger_index"],
                "ubf_company_device_user_finger_uq"
            );
        });

    }

    public function down(): void {

        Schema::dropIfExists("user_biometric_fingerprints");
        Schema::dropIfExists("user_attendances");

    }

};
