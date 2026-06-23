<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Recipes;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\System\Defaults\BelongsToCompany;

class StoreRecipeRequest extends FormRequest {

    public function authorize(): bool {

        return true;

    }

    public function rules(): array {

        return [
            "item_id" => ["required", "integer"],
            "yield_quantity" => ["required", "numeric", "min:0.01"],
            "waste_percentage" => ["nullable", "numeric", "min:0", "max:100"],
            "preparation_notes" => ["nullable", "string", "max:1000"],
            "status" => ["required", "in:active,inactive"],
            "components" => ["nullable", "array"],
            "components.*.item_id" => ["nullable", "integer"],
            "components.*.quantity" => ["nullable", "numeric", "min:0.0001"],
            "components.*.waste_percentage" => ["nullable", "numeric", "min:0", "max:100"],
            "components.*.note" => ["nullable", "string", "max:255"],
            "toppings" => ["nullable", "array"],
            "toppings.*.name" => ["nullable", "string", "max:100"],
            "toppings.*.description" => ["nullable", "string", "max:255"],
            "toppings.*.price" => ["nullable", "numeric", "min:0"],
            "toppings.*.currency_id" => ["nullable", "integer", new BelongsToCompany("currencies", ["status" => "active"], "Una moneda del topping no pertenece a la empresa.")],
            "toppings.*.max_quantity" => ["nullable", "integer", "min:1"],
            "toppings.*.components" => ["nullable", "array"],
            "options" => ["nullable", "array"],
            "options.*.name" => ["nullable", "string", "max:100"],
            "options.*.description" => ["nullable", "string", "max:255"],
            "options.*.max_portions" => ["nullable", "integer", "min:1"],
            "options.*.components" => ["nullable", "array"]
        ];

    }

    public function messages(): array {

        return [
            "required" => "Campo obligatorio.",
            "numeric" => "Debe ser un número válido.",
            "integer" => "Debe ser un número entero.",
            "min" => "Debe ser mayor a cero.",
            "max" => "Supera el límite permitido.",
            "in" => "Selecciona una opción válida."
        ];

    }

}
