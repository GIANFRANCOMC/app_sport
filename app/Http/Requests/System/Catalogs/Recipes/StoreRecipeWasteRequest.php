<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Recipes;

use App\Http\Requests\System\Base\CompanyFormRequest;
use App\Rules\System\Defaults\BelongsToCompany;

final class StoreRecipeWasteRequest extends CompanyFormRequest {
    public function rules(): array {

        $round = $this->decimalPrecision();
        $maxValue = $this->numericMaxValue();

        return [
            "warehouse_id" => [
                "bail",
                "required",
                "integer",
                new BelongsToCompany("warehouses", ["status" => "active"], "El almacen seleccionado no esta disponible."),
            ],
            "item_id" => [
                "bail",
                "required",
                "integer",
                new BelongsToCompany("items", ["type" => "product", "status" => "active"], "El insumo seleccionado no esta disponible."),
            ],
            "quantity" => ["required", "numeric", "gt:0", "max:{$maxValue}", "decimal:0,{$round}"],
            "reason" => ["required", "string", "max:500"],
            "occurred_at" => ["nullable", "date"],
            "allow_negative" => ["nullable", "boolean"],
        ];

    }

    protected function normalizedStringFields(): array {

        return ["reason", "occurred_at"];

    }
}
