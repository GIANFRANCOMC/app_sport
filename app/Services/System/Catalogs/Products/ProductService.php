<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Products;

use App\Models\System\Catalogs\Item;

/**
 * Service class for managing Product operations
 * Extends ItemService with product-specific logic
 */
class ProductService {

    /**
     * Create a new product
     *
     * @param array $data Product data from request
     * @param int|null $userId User ID creating the product
     * @return Item|null Created product instance or null on failure
     */
    public static function create(array $data, ?int $userId = null): ?Item {

        return ItemService::create($data, "product", $userId);

    }

    /**
     * Update an existing product
     *
     * @param Item $item Product instance to update
     * @param array $data Updated product data
     * @param int|null $userId User ID updating the product
     * @return Item Updated product instance
     */
    public static function update(Item $item, array $data, ?int $userId = null): Item {

        return ItemService::update($item, $data, $userId);

    }

    /**
     * Find product by ID and company ID
     *
     * @param int $id Product ID
     * @param int $companyId Company ID
     * @return Item|null
     */
    public static function findByIdAndCompany(int $id, int $companyId): ?Item {

        return ItemService::findByIdAndCompany($id, $companyId, "product");

    }

    /**
     * Get paginated list of products
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15) {

        return ItemService::getPaginatedList($companyId, "product", $filters, $perPage);

    }

}

