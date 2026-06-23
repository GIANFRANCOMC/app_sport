<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {

        Schema::create("biometric_device_brands", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("slug", 120);
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->unique(["company_id", "slug"]);
        });

        Schema::create("biometric_device_models", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("biometric_device_brand_id");
            $table->string("slug", 120);
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("biometric_device_brand_id")->references("id")->on("biometric_device_brands")->onDelete("cascade");
            $table->unique(["company_id", "biometric_device_brand_id", "slug"], "bdm_company_brand_slug_unique");
        });

        Schema::create("biometric_devices", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("biometric_device_model_id");
            $table->string("name", 255);
            $table->string("serial_number", 255)->nullable();
            $table->string("ip_address", 255);
            $table->integer("port")->default(4370);
            $table->string("device_id", 255)->nullable();
            $table->text("description")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("biometric_device_model_id")->references("id")->on("biometric_device_models")->onDelete("restrict");
        });

        Schema::create("customer_biometric_fingerprints", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("customer_id");
            $table->unsignedBigInteger("biometric_device_id");
            $table->integer("device_user_id")->comment("ID del usuario en el dispositivo biométrico");
            $table->integer("finger_index")->default(0)->comment("Índice del dedo (0-9 típicamente)");
            $table->text("fingerprint_template")->nullable()->comment("Template de la huella (opcional, puede estar solo en el dispositivo)");
            $table->text("description")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("customer_id")->references("id")->on("customers")->onDelete("cascade");
            $table->foreign("biometric_device_id")->references("id")->on("biometric_devices")->onDelete("cascade");
            $table->unique(["company_id", "biometric_device_id", "device_user_id", "finger_index"], "cbf_company_device_user_finger_unique");
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {

        Schema::dropIfExists("customer_biometric_fingerprints");
        Schema::dropIfExists("biometric_devices");
        Schema::dropIfExists("biometric_device_models");
        Schema::dropIfExists("biometric_device_brands");

    }

};

