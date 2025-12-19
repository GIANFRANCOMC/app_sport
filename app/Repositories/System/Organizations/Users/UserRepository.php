<?php

declare(strict_types=1);

namespace App\Repositories\System\Organizations\Users;

use App\Models\System\Organizations\User;
use App\Repositories\System\Base\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Repository class for User data access
 * Implements Repository Pattern for centralized query logic
 */
class UserRepository extends BaseRepository {

    /**
     * Get the model class name
     *
     * @return string
     */
    protected static function getModelClass(): string {

        return User::class;

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
     * Get paginated list of users with filters
     * Uses BaseRepository with user-specific defaults
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
        array $relations = ["identityDocumentType", "role"],
        string $orderBy = "name",
        string $orderDirection = "ASC"
    ): LengthAwarePaginator {

        return parent::getPaginatedList($companyId, $filters, $perPage, $relations, $orderBy, $orderDirection);

    }

}

