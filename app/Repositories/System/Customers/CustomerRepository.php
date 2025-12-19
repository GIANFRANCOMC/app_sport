<?php

declare(strict_types=1);

namespace App\Repositories\System\Customers;

use App\Models\System\Customers\Customer;
use App\Repositories\System\Base\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Repository class for Customer data access
 * Implements Repository Pattern for centralized query logic
 */
class CustomerRepository extends BaseRepository {

    /**
     * Get the model class name
     *
     * @return string
     */
    protected static function getModelClass(): string {

        return Customer::class;

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
     * Get paginated list of customers with filters
     * Uses BaseRepository with customer-specific defaults
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
        array $relations = ["identityDocumentType"],
        string $orderBy = "name",
        string $orderDirection = "ASC"
    ): LengthAwarePaginator {

        return parent::getPaginatedList($companyId, $filters, $perPage, $relations, $orderBy, $orderDirection);

    }

}

