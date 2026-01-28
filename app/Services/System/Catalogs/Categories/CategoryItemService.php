<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Categories;

use App\Models\System\Catalogs\{CategoryItem, Item};

class CategoryItemService {

    /**
     * Sincroniza categorías para un item.
     *
     * Formato esperado:
     * - $categories = [
     *   ["category_id" => 1],
     *   ["category_id" => 2],
     * ]
     */
    public static function sync(Item $item, array $categories, int $userId): void {

        CategoryItem::where("item_id", $item->id)
                    ->where("status", "active")
                    ->update([
                        "status"     => "inactive",
                        "updated_at" => now(),
                        "updated_by" => $userId
                    ]);

        foreach($categories as $category) {

            if(!isset($category["category_id"])) {

                continue;

            }

            CategoryItem::updateOrInsert(
                [
                    "category_id" => $category["category_id"],
                    "item_id"     => $item->id
                ],
                [
                    "status"      => "active",
                    "updated_at"  => now(),
                    "updated_by"  => $userId
                ]
            );

        }

    }

}


