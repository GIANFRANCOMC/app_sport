<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {

        Schema::create("identity_document_types", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("code", 255);
            $table->string("name", 255);
            $table->boolean("is_searchable")->default(true);
            $table->integer("min_length")->default(1);
            $table->integer("max_length")->default(50);

            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->unique(["company_id", "code"]);
        });
        Schema::create("document_types", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("code", 255);
            $table->string("name", 255);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->unique(["company_id", "code"]);
        });
        Schema::create("currencies", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("code", 255);
            $table->string("sign", 255);
            $table->string("singular_name", 255);
            $table->string("plural_name", 255);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->unique(["company_id", "code"]);
        });
        Schema::create("companies", function(Blueprint $table) {
            $table->id();
            $table->string("slug", 255)->unique();
            $table->string("internal_code", 255);
            $table->unsignedBigInteger("identity_document_type_id")->nullable();
            $table->string("document_number", 255);
            $table->string("legal_name", 255);
            $table->string("commercial_name", 255);
            $table->unsignedBigInteger("currency_id")->nullable();
            $table->string("tagline", 255)->nullable();
            $table->string("description", 500)->nullable();
            $table->string("address", 255)->nullable();
            $table->string("telephone", 255)->nullable();
            $table->string("email", 255)->nullable();
            $table->string("token_api_misc", 255)->nullable();
            $table->string("logotype", 255)->nullable();
            $table->string("combinationmark", 255)->nullable();
            $table->string("logomark", 255)->nullable();
            $table->string("login_image", 255)->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("identity_document_type_id")->references("id")->on("identity_document_types")->restrictOnDelete();
            $table->foreign("currency_id")->references("id")->on("currencies")->restrictOnDelete();
        });
        Schema::table("identity_document_types", function(Blueprint $table) {
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });
        Schema::table("document_types", function(Blueprint $table) {
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });
        Schema::table("currencies", function(Blueprint $table) {
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });
        Schema::create("sections", function(Blueprint $table) {
            $table->id();
            $table->string("slug", 255);
            $table->string("name", 255);
            $table->integer("order")->nullable();
            $table->string("dom_id", 255)->default("");
            $table->string("dom_label", 255)->default("");
            $table->string("dom_icon", 255)->default("");
            $table->boolean("has_sub_menu")->default(false);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
        });
        Schema::create("sub_sections", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("section_id");
            $table->string("slug", 255);
            $table->string("name", 255);
            $table->string("description", 255)->nullable();
            $table->integer("order")->nullable();
            $table->string("dom_id", 255)->default("");
            $table->string("dom_label", 255)->default("");
            $table->string("dom_icon", 255)->default("");
            $table->string("dom_route", 255)->default("");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("section_id")->references("id")->on("sections")->onDelete("cascade");
        });
        Schema::create("companies_sub_sections", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("sub_section_id");
            $table->integer("section_order")->nullable();
            $table->integer("sub_section_order")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("sub_section_id")->references("id")->on("sub_sections")->onDelete("cascade");
        });
        Schema::create("roles", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("slug", 255);
            $table->string("name", 255);
            $table->boolean("is_full_access")->default(false);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });
        Schema::create("role_sub_sections", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("role_id");
            $table->unsignedBigInteger("sub_section_id");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("role_id")->references("id")->on("roles")->onDelete("cascade");
            $table->foreign("sub_section_id")->references("id")->on("sub_sections")->onDelete("cascade");
            $table->unique(["company_id", "role_id", "sub_section_id"]);
        });
        Schema::create("users", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("role_id")->nullable();
            $table->unsignedBigInteger("identity_document_type_id");
            $table->string("document_number", 255);
            $table->string("name", 255);
            $table->string("email", 255);
            $table->timestamp("email_verified_at")->nullable();
            $table->string("password", 255);
            $table->rememberToken();
            $table->string("phone_number", 255)->nullable();
            $table->enum("gender", ["male", "female", "other"])->nullable();
            $table->date("birthdate")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("role_id")->references("id")->on("roles")->onDelete("cascade");
            $table->foreign("identity_document_type_id")->references("id")->on("identity_document_types")->restrictOnDelete();
            $table->unique(["email", "company_id"]);
        });
        Schema::create("user_preferences", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("user_id");
            $table->string("slug", 255);
            $table->text("value")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
        });
        // Initial data lives in 2024_12_31_235959_insert_initial_system_data.php.

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {

        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists("user_preferences");
        Schema::dropIfExists("users");
        Schema::dropIfExists("role_sub_sections");
        Schema::dropIfExists("roles");
        Schema::dropIfExists("companies_sub_sections");
        Schema::dropIfExists("sub_sections");
        Schema::dropIfExists("sections");
        Schema::dropIfExists("companies");
        Schema::dropIfExists("currencies");
        Schema::dropIfExists("document_types");
        Schema::dropIfExists("identity_document_types");

        Schema::enableForeignKeyConstraints();

    }
};


