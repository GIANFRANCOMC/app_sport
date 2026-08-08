<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\{Migration};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    protected $connection = "landlord";

    public function up(): void {

        $schema = Schema::connection($this->connection);
        $sensitiveColumns = [
            "connection_name",
            "db_driver",
            "db_host",
            "db_port",
            "db_username",
            "db_password",
        ];

        $existingColumns = array_values(array_filter(
            $sensitiveColumns,
            static fn(string $column): bool => $schema->hasColumn("tenant_databases", $column)
        ));

        if($existingColumns !== []) {

            $schema->table("tenant_databases", function(Blueprint $table) use ($existingColumns): void {

                $table->dropColumn($existingColumns);

            });

        }

        DB::connection($this->connection)
            ->table("tenant_domains")
            ->where("type", "custom")
            ->update(["status" => "inactive", "updated_at" => now()]);

    }

    public function down(): void {

        // Las credenciales no vuelven al registry por seguridad.

    }
};
