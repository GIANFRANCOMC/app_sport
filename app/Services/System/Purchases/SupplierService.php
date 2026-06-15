<?php

declare(strict_types=1);

namespace App\Services\System\Purchases;

use App\Models\System\Purchases\Supplier;

final class SupplierService {

    public static function query(int $companyId, string $word = "") {

        $query = Supplier::query()->where("company_id", $companyId);
        $word = trim($word);

        if($word !== "") {

            $query->where(function($query) use($word) {

                $query->where("name", "like", "%{$word}%")
                    ->orWhere("document_number", "like", "%{$word}%")
                    ->orWhere("contact_name", "like", "%{$word}%");

            });

        }

        return $query->orderBy("name");

    }

    public static function create(int $companyId, int $userId, array $data): Supplier {

        return Supplier::create([
            ...$data,
            "company_id" => $companyId,
            "created_at" => now(),
            "created_by" => $userId
        ]);

    }

    public static function update(
        int $companyId,
        int $supplierId,
        int $userId,
        array $data
    ): Supplier {

        $supplier = Supplier::query()
            ->where("company_id", $companyId)
            ->findOrFail($supplierId);
        $supplier->update([
            ...$data,
            "updated_at" => now(),
            "updated_by" => $userId
        ]);

        return $supplier->refresh();

    }

}
