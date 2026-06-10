<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    private const TABLE = "items";
    private const UNIQUE_INDEX = "items_company_id_barcode_unique";
    private const LOOKUP_INDEX = "items_company_id_barcode_index";

    public function up(): void {

        if(!Schema::hasTable(self::TABLE)) {

            return;

        }

        if($this->hasIndex(self::UNIQUE_INDEX)) {

            Schema::table(self::TABLE, function(Blueprint $table) {

                $table->dropUnique(self::UNIQUE_INDEX);

            });

        }

        if(!$this->hasIndex(self::LOOKUP_INDEX)) {

            Schema::table(self::TABLE, function(Blueprint $table) {

                $table->index(["company_id", "barcode"], self::LOOKUP_INDEX);

            });

        }

    }

    public function down(): void {

        if(!Schema::hasTable(self::TABLE)) {

            return;

        }

        if($this->hasIndex(self::LOOKUP_INDEX)) {

            Schema::table(self::TABLE, function(Blueprint $table) {

                $table->dropIndex(self::LOOKUP_INDEX);

            });

        }

        if(!$this->hasIndex(self::UNIQUE_INDEX)) {

            Schema::table(self::TABLE, function(Blueprint $table) {

                $table->unique(["company_id", "barcode"], self::UNIQUE_INDEX);

            });

        }

    }

    private function hasIndex(string $indexName): bool {

        return collect(Schema::getIndexes(self::TABLE))
            ->contains(fn(array $index): bool => strtolower($index["name"]) === strtolower($indexName));

    }

};
