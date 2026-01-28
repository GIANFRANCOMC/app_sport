<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Categories;

use App\Models\System\Catalogs\{CategoryItem};

class CategoryItemService {

    public static function sync(int $itemId, array $categories, int $userId): void {

        CategoryItem::where("item_id", $itemId)
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
                    "item_id"     => $itemId
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


