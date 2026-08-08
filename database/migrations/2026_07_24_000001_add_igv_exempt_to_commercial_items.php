<?php

use Illuminate\Database\Migrations\{Migration};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Schema};

return new class extends Migration {
    public function up(): void {

        if(!Schema::hasColumn("items", "igv_exempt")) {

            Schema::table("items", function(Blueprint $table) {

                $table->boolean("igv_exempt")->default(false)->after("price_includes_tax");

            });

        }

        if(!Schema::hasColumn("sales_body", "igv_exempt")) {

            Schema::table("sales_body", function(Blueprint $table) {

                $table->boolean("igv_exempt")->default(false)->after("price_includes_tax");

            });

        }

        if(!Schema::hasColumn("quotation_items", "igv_exempt")) {

            Schema::table("quotation_items", function(Blueprint $table) {

                $table->boolean("igv_exempt")->default(false)->after("price_includes_tax");

            });

        }

    }

    public function down(): void {

        if(Schema::hasColumn("quotation_items", "igv_exempt")) {

            Schema::table("quotation_items", function(Blueprint $table) {

                $table->dropColumn("igv_exempt");

            });

        }

        if(Schema::hasColumn("sales_body", "igv_exempt")) {

            Schema::table("sales_body", function(Blueprint $table) {

                $table->dropColumn("igv_exempt");

            });

        }

        if(Schema::hasColumn("items", "igv_exempt")) {

            Schema::table("items", function(Blueprint $table) {

                $table->dropColumn("igv_exempt");

            });

        }

    }
};
