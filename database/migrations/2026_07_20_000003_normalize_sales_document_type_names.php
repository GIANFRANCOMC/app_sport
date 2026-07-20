<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        if(!Schema::hasTable("document_types")) {

            return;

        }

        DB::table("document_types")
            ->where("code", "BV")
            ->update([
                "name"       => "BOLETA",
                "updated_at" => now()
            ]);

        DB::table("document_types")
            ->where("code", "FA")
            ->update([
                "name"       => "FACTURA",
                "updated_at" => now()
            ]);

    }

    public function down(): void {

        // Normalizing document labels is intentionally not reversible.

    }

};
