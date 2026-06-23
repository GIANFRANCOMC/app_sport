<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Branches;

use Exception;

use App\Models\System\General\{DocumentType};
use App\Models\System\Organizations\{Branch, Serie};

/**
 * Service class for managing Serie operations
 * Handles business logic for creating document series for branches
 */
class SerieService {

    /**
     * Get new sequential number for branch (based on company branch count)
     *
     * @param int $companyId Company ID
     * @return int
     */
    public static function getNewSequential(int $companyId, int $branchId): int {

        $newSequential = 0;

        try {

            $maxSequential = Branch::where("company_id", $companyId)
                                   ->where("id", "!=", $branchId)
                                   ->count();

            $newSequential = intval($maxSequential) + 1;

        }catch(Exception $e) {

            $newSequential = 0;

        }

        return $newSequential;

    }

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
        $newSequential = self::getNewSequential($companyId, $branchId);

        // Get all active document types for the company (only needed fields)
        $documentTypes = DocumentType::where("company_id", $companyId)
                                     ->whereIn("status", ["active"])
                                     ->select("id", "code")
                                     ->get();

        if($documentTypes->isEmpty()) {

            return [];

        }

        // Prepare bulk insert data
        $now = now();

        $seriesData = $documentTypes->map(function($documentType) use($companyId, $branchId, $newSequential, $userId, $now) {

            return [
                "company_id"       => $companyId,
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

}
