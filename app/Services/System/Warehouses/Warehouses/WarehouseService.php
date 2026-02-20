<?php

declare(strict_types=1);

namespace App\Services\System\Warehouses\Warehouses;

use App\Models\System\Organizations\Branch;
use App\Models\System\Warehouses\Warehouse;

/**
 * Service class for managing Warehouse operations
 * Handles business logic for creating and updating warehouses related to branches
 */
class WarehouseService {

    /**
     * Create default warehouse for a new branch
     *
     * @param int $branchId Branch ID
     * @param string $branchName Branch name for warehouse naming
     * @param int|null $userId User ID creating the warehouse
     * @return Warehouse Created warehouse instance
     */
    public static function createDefaultForBranch(int $branchId, string $branchName, ?int $userId = null): Warehouse {

        return Warehouse::create([
            "branch_id"  => $branchId,
            "name"       => self::generateWarehouseName($branchName, 1),
            "status"     => "active",
            "created_at" => now(),
            "created_by" => $userId
        ]);

    }

    /**
     * Update warehouse names for a branch when branch name changes
     * Uses bulk update for better performance
     *
     * @param Branch $branch Branch instance
     * @param int|null $userId User ID updating the warehouses
     * @return int Number of updated warehouses
     */
    public static function updateNamesForBranch(Branch $branch, ?int $userId = null): int {

        $warehouses = $branch->warehousesAll;

        if($warehouses->isEmpty()) {

            return 0;

        }

        $now = now();
        $seq = 1;

        // Update warehouses efficiently
        foreach($warehouses as $warehouse) {

            $warehouse->name       = self::generateWarehouseName($branch->name, $seq);
            $warehouse->updated_at = $now;
            $warehouse->updated_by = $userId;
            $warehouse->save();

            $seq++;

        }

        return $warehouses->count();

    }

    /**
     * Generate warehouse name based on branch name and sequence
     *
     * @param string $branchName Branch name
     * @param int $sequence Sequence number
     * @return string Generated warehouse name
     */
    public static function generateWarehouseName(string $branchName, int $sequence): string {

        return "{$branchName} - Almacén {$sequence}";

    }

}

