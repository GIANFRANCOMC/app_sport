<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Items;

use App\Models\System\Catalogs\Item;

/**
 * Service class for managing Subscription operations
 * Extends ItemService with subscription-specific logic
 */
class SubscriptionService {

    /**
     * Create a new subscription
     *
     * @param array $data Subscription data from request
     * @param int|null $userId User ID creating the subscription
     * @return Item|null Created subscription instance or null on failure
     */
    public static function create(array $data, ?int $userId = null): ?Item {

        return ItemService::create($data, "subscription", $userId);

    }

    /**
     * Update an existing subscription
     *
     * @param Item $item Subscription instance to update
     * @param array $data Updated subscription data
     * @param int|null $userId User ID updating the subscription
     * @return Item Updated subscription instance
     */
    public static function update(Item $item, array $data, ?int $userId = null): Item {

        return ItemService::update($item, $data, $userId);

    }

    /**
     * Find subscription by ID and company ID
     *
     * @param int $id Subscription ID
     * @param int $companyId Company ID
     * @return Item|null
     */
    public static function findByIdAndCompany(int $id, int $companyId): ?Item {

        return ItemService::findByIdAndCompany($id, $companyId, "subscription");

    }

    /**
     * Get paginated list of subscriptions
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15) {

        return ItemService::getPaginatedList($companyId, "subscription", $filters, $perPage);

    }

}

