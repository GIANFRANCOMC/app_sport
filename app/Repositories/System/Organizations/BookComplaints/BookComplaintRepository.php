<?php

declare(strict_types=1);

namespace App\Repositories\System\Organizations\BookComplaints;

use App\Models\System\Organizations\BookComplaint;
use App\Repositories\System\Base\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Repository class for BookComplaint data access
 * Implements Repository Pattern for centralized query logic
 */
class BookComplaintRepository extends BaseRepository {

    /**
     * Get the model class name
     *
     * @return string
     */
    protected static function getModelClass(): string {

        return BookComplaint::class;

    }

    /**
     * Get searchable fields for filtering
     *
     * @return array
     */
    protected static function getSearchableFields(): array {

        return [
            "document_number",
            "name",
            "email",
            "phone_number"
        ];

    }

    /**
     * Get paginated list of book complaints with filters
     * Uses BaseRepository with book complaint-specific defaults
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @param array $relations Relations to eager load
     * @param string $orderBy Order by field
     * @param string $orderDirection Order direction
     * @return LengthAwarePaginator
     */
    public function getPaginatedList(
        int $companyId,
        array $filters = [],
        int $perPage = 15,
        array $relations = ["branch", "identityDocumentType"],
        string $orderBy = "id",
        string $orderDirection = "DESC"
    ): LengthAwarePaginator {

        return parent::getPaginatedList($companyId, $filters, $perPage, $relations, $orderBy, $orderDirection);

    }

}

