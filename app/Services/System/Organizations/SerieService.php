<?php

namespace App\Services\System\Organizations;

use App\Models\System\General\DocumentType;
use App\Models\System\Organizations\Serie;

/**
 * Service class for managing Serie operations
 * Handles business logic for creating document series for branches
 */
class SerieService {

    /**
     * Create series for all active document types for a given branch
     * Uses bulk insert for better performance
     *
     * @param int $branchId Branch ID
     * @param int $companyId Company ID
     * @param int|null $userId User ID creating the series
     * @return array Collection of created series
     */
    public static function createForBranch(int $branchId, int $companyId, ?int $userId = null): array {

        // Get new sequential number for the branch
        $newSequential = Serie::getNewSequential($companyId);

        // Get all active document types (only needed fields)
        $documentTypes = DocumentType::whereIn("status", ["active"])
                                     ->select("id", "code")
                                     ->get();

        if($documentTypes->isEmpty()) {
            return [];
        }

        // Prepare bulk insert data
        $now = now();

        $seriesData = $documentTypes->map(function($documentType) use($branchId, $newSequential, $userId, $now) {

            return [
                "branch_id"        => $branchId,
                "document_type_id" => $documentType->id,
                "code"             => $documentType->code,
                "number"           => $newSequential,
                "init"             => 1,
                "status"           => "active",
                "created_at"       => $now,
                "created_by"       => $userId
            ];

        })->toArray();

        // Bulk insert for better performance
        Serie::insert($seriesData);

        // Return count instead of fetching (more efficient)
        return $seriesData;
    }

    /**
     * Get all series for a branch
     *
     * @param int $branchId Branch ID
     * @param bool $activeOnly Only return active series
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByBranch(int $branchId, bool $activeOnly = true) {

        $query = Serie::where("branch_id", $branchId);

        if($activeOnly) {

            $query->whereIn("status", ["active"]);

        }

        return $query->with("documentType")->get();

    }

}

