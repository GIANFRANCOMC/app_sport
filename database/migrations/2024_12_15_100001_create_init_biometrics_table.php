<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {

        Schema::create("biometric_devices", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id");
            $table->string("name");
            $table->enum("brand", ["ZKTeco"])->default("ZKTeco");
            $table->enum("model", ["K20 Pro"])->default("K20 Pro");
            $table->string("serial_number")->nullable();
            $table->string("ip_address");
            $table->integer("port")->default(4370);
            $table->integer("device_id")->nullable();
            $table->text("description")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
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
            $table->unique(["biometric_device_id", "device_user_id", "finger_index"]);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {

        Schema::dropIfExists("customer_biometric_fingerprints");
        Schema::dropIfExists("biometric_devices");

    }

};

