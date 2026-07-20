<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        if(!Schema::hasTable("warehouses") || !Schema::hasTable("branches")) {

            return;

        }

        $branches = DB::table("branches")
            ->select(["id", "name"])
            ->get()
            ->keyBy("id");

        DB::table("warehouses")
            ->select(["id", "branch_id", "name"])
            ->orderBy("branch_id")
            ->orderBy("id")
            ->get()
            ->groupBy("branch_id")
            ->each(function($warehouses, $branchId) use($branches) {

                $branchName = trim((string) ($branches->get($branchId)->name ?? ""));
                $sequence = 1;

                foreach($warehouses as $warehouse) {

                    $name = trim((string) $warehouse->name);
                    $cleanName = $this->cleanWarehouseName($name, $branchName, $sequence);

                    if($cleanName !== $name) {

                        DB::table("warehouses")
                            ->where("id", $warehouse->id)
                            ->update([
                                "name"       => $cleanName,
                                "updated_at" => now()
                            ]);

                    }

                    $sequence++;

                }

            });

    }

    public function down(): void {

        // Data normalization is intentionally not reversible.

    }

    private function cleanWarehouseName(string $name, string $branchName, int $sequence): string {

        if($name === "") {

            return "Almacén {$sequence}";

        }

        if($branchName !== "" && str_starts_with($name, "{$branchName} - ")) {

            return trim(substr($name, strlen("{$branchName} - "))) ?: "Almacén {$sequence}";

        }

        if(preg_match('/^.+\s-\s(Almac(?:én|Ã©n)\s+\d+)$/u', $name, $matches)) {

            return trim($matches[1]);

        }

        if(preg_match('/^Almac(?:én|Ã©n)\s-\s.+$/u', $name)) {

            return "Almacén {$sequence}";

        }

        return $name;

    }

};
