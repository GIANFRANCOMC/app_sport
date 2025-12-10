<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Hash, Schema};

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {

        // ✔️
        Schema::create("book_complaints", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("branch_id")->nullable();
            $table->unsignedBigInteger("identity_document_type_id");
            $table->string("document_number");
            $table->string("name");
            $table->string("email")->nullable();
            $table->string("phone_number")->nullable();
            $table->enum("type", ["complaint", "claim", "suggestion"]);
            $table->text("description");
            $table->text("request")->nullable();
            $table->string("evidence")->nullable();
            $table->text("admin_response")->nullable();
            $table->string("submitted_ip", 45)->nullable();
            $table->text("submitted_user_agent")->nullable();
            $table->string("submitted_platform", 100)->nullable();
            $table->string("submitted_browser", 100)->nullable();
            $table->enum("status", ["pending", "in_progress", "resolved"])->default("pending");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("deleted_at")->nullable();
            $table->integer("deleted_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("branch_id")->references("id")->on("branches")->onDelete("cascade");
            $table->foreign("identity_document_type_id")->references("id")->on("identity_document_types")->onDelete("cascade");
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {

        Schema::dropIfExists("book_complaints");

    }

};
