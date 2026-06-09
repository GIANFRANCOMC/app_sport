<?php

declare(strict_types=1);

namespace App\Services\Guest;

use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

use App\Models\Guest\Item;

/**
 * Provides the public catalog exposed to a company's visitors.
 */
final class GuestCatalogService {

    public static function publicItems(int $companyId): Collection {

        if($companyId <= 0) {

            throw new InvalidArgumentException("Company ID must be greater than zero.");

        }

        return Item::query()
                   ->where("company_id", $companyId)
                   ->where("see_my_web", true)
                   ->where("status", "active")
                   ->with("currency")
                   ->orderByDesc("type")
                   ->orderBy("name")
                   ->get();

    }

}
