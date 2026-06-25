<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = 'landlord';

    public function up(): void {

        Schema::connection($this->connection)->create('tenant_databases', function(Blueprint $table): void {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('connection_name', 60)->default('tenant');
            $table->string('database_name', 180);
            $table->string('db_driver', 30)->default('mysql');
            $table->string('db_host', 180)->nullable();
            $table->string('db_port', 20)->nullable();
            $table->string('db_username', 180)->nullable();
            $table->text('db_password')->nullable();
            $table->enum('status', ['provisioning', 'active', 'inactive', 'suspended'])->default('provisioning');
            $table->timestamp('last_resolved_at')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('updated_by')->nullable();
        });

        Schema::connection($this->connection)->create('tenant_domains', function(Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_database_id');
            $table->string('domain', 255)->unique();
            $table->enum('type', ['subdomain', 'custom'])->default('subdomain');
            $table->boolean('is_primary')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('updated_by')->nullable();

            $table->foreign('tenant_database_id')->references('id')->on('tenant_databases')->onDelete('cascade');
        });

    }

    public function down(): void {

        Schema::connection($this->connection)->dropIfExists('tenant_domains');
        Schema::connection($this->connection)->dropIfExists('tenant_databases');

    }

};
