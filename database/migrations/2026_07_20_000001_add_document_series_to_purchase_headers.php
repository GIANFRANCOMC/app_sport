<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\{Migration};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Schema};

return new class extends Migration {
    public function up(): void {

        if(!Schema::hasTable("purchase_headers") || Schema::hasColumn("purchase_headers", "document_series")) {

            return;

        }

        Schema::table("purchase_headers", function(Blueprint $table) {

            $table->string("document_series", 20)->nullable()->after("document_type");

        });

    }

    public function down(): void {

        if(!Schema::hasTable("purchase_headers") || !Schema::hasColumn("purchase_headers", "document_series")) {

            return;

        }

        Schema::table("purchase_headers", function(Blueprint $table) {

            $table->dropColumn("document_series");

        });

    }
};
