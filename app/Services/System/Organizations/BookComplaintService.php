<?php

declare(strict_types=1);

namespace App\Services\System\Organizations;

use App\Helpers\System\Utilities;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

use App\Models\System\Organizations\BookComplaint;

/**
 * Service class for managing BookComplaint operations
 * Handles business logic for listing and updating book complaints
 */
class BookComplaintService {

    /**
     * Allowed fields for book complaint update
     */
    private const ALLOWED_UPDATE_FIELDS = [
        "admin_response",
        "status"
    ];

    /**
     * Searchable fields for filtering
     */
    private const SEARCHABLE_FIELDS = [
        "document_number",
        "name",
        "email",
        "phone_number"
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

        $query = BookComplaint::query()
                              ->where("company_id", $companyId)
                              ->with(["branch", "identityDocumentType"]);

        // Apply search filters
        self::applySearchFilters($query, $filters);

        // Apply ordering
        $query->orderBy("id", "DESC");

        return $query->paginate($perPage);

    }

    /**
     * Apply search filters to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    private static function applySearchFilters($query, array $filters): void {

        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        if(!Utilities::isDefined($filterBy) || !Utilities::isDefined($word)) {

            return;

        }

        $searchTerm = Utilities::getWordSearch($word);

        if($filterBy === "all") {

            // Search across all searchable fields
            $query->where(function($q) use($searchTerm) {

                foreach(self::SEARCHABLE_FIELDS as $field) {

                    $q->orWhere($field, "like", $searchTerm);

                }

            });

        }elseif(in_array($filterBy, self::SEARCHABLE_FIELDS, true)) {

            // Search in specific field
            $query->where($filterBy, "like", $searchTerm);

        }

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

        $query = BookComplaint::where("id", $id)
                              ->where("company_id", $companyId);

        if(!empty($relations)) {

            $query->with($relations);

        }

        return $query->first();

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

