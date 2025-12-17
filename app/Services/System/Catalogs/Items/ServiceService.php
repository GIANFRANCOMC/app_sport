<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Items;

use App\Models\System\Catalogs\Item;

/**
 * Service class for managing Service operations
 * Extends ItemService with service-specific logic
 */
class ServiceService {

    /**
     * Create a new service
     *
     * @param array $data Service data from request
     * @param int|null $userId User ID creating the service
     * @return Item|null Created service instance or null on failure
     */
    public static function create(array $data, ?int $userId = null): ?Item {

        return ItemService::create($data, "service", $userId);

    }

    /**
     * Update an existing service
     *
     * @param Item $item Service instance to update
     * @param array $data Updated service data
     * @param int|null $userId User ID updating the service
     * @return Item Updated service instance
     */
    public static function update(Item $item, array $data, ?int $userId = null): Item {

        return ItemService::update($item, $data, $userId);

    }

    /**
     * Find service by ID and company ID
     *
     * @param int $id Service ID
     * @param int $companyId Company ID
     * @return Item|null
     */
    public static function findByIdAndCompany(int $id, int $companyId): ?Item {

        return ItemService::findByIdAndCompany($id, $companyId, "service");

    }

    /**
     * Get paginated list of services
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15) {

        return ItemService::getPaginatedList($companyId, "service", $filters, $perPage);

    }

}

