<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\BookComplaints;

use App\Helpers\System\Utilities;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

use App\Models\System\Organizations\BookComplaint;
use App\Repositories\System\Organizations\BookComplaints\BookComplaintRepository;

/**
 * Service class for managing BookComplaint operations
 * Handles business logic for listing and updating book complaints
 */
class BookComplaintService {

    /**
     * @var BookComplaintRepository
     */
    private static $repository;

    /**
     * Get repository instance (lazy initialization)
     *
     * @return BookComplaintRepository
     */
    private static function getRepository(): BookComplaintRepository {

        if(self::$repository === null) {

            self::$repository = new BookComplaintRepository();

        }

        return self::$repository;

    }

    /**
     * Allowed fields for book complaint update
     */
    private const ALLOWED_UPDATE_FIELDS = [
        "admin_response",
        "status"
    ];


    /**
     * Get paginated list of book complaints with filters
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator {

        return self::getRepository()->getPaginatedList($companyId, $filters, $perPage);

    }

    /**
     * Find book complaint by ID and company ID
     *
     * @param int $id Book complaint ID
     * @param int $companyId Company ID
     * @param array $relations Relations to eager load
     * @return BookComplaint|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, array $relations = ["branch", "identityDocumentType"]): ?BookComplaint {

        return self::getRepository()->findByIdAndCompany($id, $companyId, $relations);

    }

    /**
     * Update an existing book complaint
     * Only allows updating admin_response and status
     *
     * @param BookComplaint $bookComplaint Book complaint instance to update
     * @param array $data Updated book complaint data
     * @param int|null $userId User ID updating the book complaint
     * @return BookComplaint Updated book complaint instance
     */
    public static function update(BookComplaint $bookComplaint, array $data, ?int $userId = null): BookComplaint {

        DB::transaction(function() use($bookComplaint, $data, $userId) {

            $updateData = [];

            // Only allow updating specific fields
            foreach(self::ALLOWED_UPDATE_FIELDS as $field) {

                if(isset($data[$field])) {

                    $updateData[$field] = $data[$field];

                }

            }

            // Only update if there are changes
            if(!empty($updateData)) {

                $updateData["updated_at"] = now();
                $updateData["updated_by"] = $userId;

                $bookComplaint->update($updateData);

            }

        });

        return $bookComplaint->fresh(["branch", "identityDocumentType"]);

    }

}

