<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Recipes;

use App\Http\Requests\System\Base\CompanyFormRequest;
use App\Rules\System\Defaults\BelongsToCompany;

class StoreRecipeRequest extends CompanyFormRequest {

    public function rules(): array {

        $productFromCompany = fn(string $message) => new BelongsToCompany(
            "items",
            ["type" => "product"],
            $message
        );

        return [
            "item_id" => ["required", "integer", new BelongsToCompany("items", [], "El item principal no pertenece a la empresa.")],
            "yield_quantity" => ["required", "numeric", "min:0.01", "decimal:0,4"],
            "waste_percentage" => ["nullable", "numeric", "min:0", "max:100", "decimal:0,4"],
            "preparation_notes" => ["nullable", "string", "max:1000"],
            "status" => ["required", "in:active,inactive"],

            "components" => ["nullable", "array", "max:200"],
            "components.*.item_id" => ["required_with:components.*.quantity", "nullable", "integer", $productFromCompany("Un insumo no pertenece a la empresa.")],
            "components.*.quantity" => ["required_with:components.*.item_id", "nullable", "numeric", "min:0.0001", "decimal:0,4"],
            "components.*.waste_percentage" => ["nullable", "numeric", "min:0", "max:100", "decimal:0,4"],
            "components.*.note" => ["nullable", "string", "max:255"],

            "toppings" => ["nullable", "array", "max:100"],
            "toppings.*.name" => ["nullable", "string", "max:100"],
            "toppings.*.description" => ["nullable", "string", "max:255"],
            "toppings.*.price" => ["nullable", "numeric", "min:0", "decimal:0,4"],
            "toppings.*.currency_id" => ["nullable", "integer", new BelongsToCompany("currencies", ["status" => "active"], "Una moneda del topping no pertenece a la empresa.")],
            "toppings.*.item_id" => ["nullable", "integer", new BelongsToCompany("items", [], "Un topping vinculado no pertenece a la empresa.")],
            "toppings.*.max_quantity" => ["nullable", "integer", "min:1"],
            "toppings.*.status" => ["nullable", "in:active,inactive"],
            "toppings.*.components" => ["nullable", "array", "max:100"],
            "toppings.*.components.*.item_id" => ["required_with:toppings.*.components.*.quantity", "nullable", "integer", $productFromCompany("Un insumo del topping no pertenece a la empresa.")],
            "toppings.*.components.*.quantity" => ["required_with:toppings.*.components.*.item_id", "nullable", "numeric", "min:0.0001", "decimal:0,4"],
            "toppings.*.components.*.waste_percentage" => ["nullable", "numeric", "min:0", "max:100", "decimal:0,4"],
            "toppings.*.components.*.note" => ["nullable", "string", "max:255"],

            "options" => ["nullable", "array", "max:100"],
            "options.*.name" => ["nullable", "string", "max:100"],
            "options.*.description" => ["nullable", "string", "max:255"],
            "options.*.max_portions" => ["nullable", "integer", "min:1"],
            "options.*.status" => ["nullable", "in:active,inactive"],
            "options.*.components" => ["nullable", "array", "max:100"],
            "options.*.components.*.item_id" => ["required_with:options.*.components.*.quantity", "nullable", "integer", $productFromCompany("Un insumo de la opcion no pertenece a la empresa.")],
            "options.*.components.*.quantity" => ["required_with:options.*.components.*.item_id", "nullable", "numeric", "min:0.0001", "decimal:0,4"],
            "options.*.components.*.waste_percentage" => ["nullable", "numeric", "min:0", "max:100", "decimal:0,4"],
            "options.*.components.*.note" => ["nullable", "string", "max:255"]
        ];

    }

    public function messages(): array {

        return array_merge(parent::messages(), [
            "min.numeric" => "Debe ser mayor que cero.",
            "max.array" => "Supera el limite de registros permitido."
        ]);

    }

    protected function normalizedStringFields(): array {

        return ["preparation_notes"];

    }

}
