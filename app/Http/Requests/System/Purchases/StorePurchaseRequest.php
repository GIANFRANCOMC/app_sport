<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Purchases;

use Illuminate\Foundation\Http\FormRequest;

final class StorePurchaseRequest extends FormRequest {

    public function authorize(): bool {

        return true;

    }

    public function rules(): array {

        return [
            "supplier_id" => ["required", "integer"],
            "warehouse_id" => ["required", "integer"],
            "currency_id" => ["required", "integer"],
            "document_type" => ["required", "in:order,invoice"],
            "document_number" => ["nullable", "string", "max:50"],
            "issue_date" => ["required", "date"],
            "expected_date" => ["nullable", "date", "after_or_equal:issue_date"],
            "tax" => ["nullable", "numeric", "min:0"],
            "observation" => ["nullable", "string", "max:1000"],
            "items" => ["required", "array", "min:1", "max:100"],
            "items.*.item_id" => ["required", "integer", "distinct"],
            "items.*.quantity" => ["required", "numeric", "gt:0"],
            "items.*.unit_cost" => ["required", "numeric", "min:0"]
        ];

    }

    public function messages(): array {

        return [
            "required" => "Campo obligatorio.",
            "items.min" => "Agrega al menos un producto.",
            "items.max" => "Puedes agregar hasta 100 productos.",
            "distinct" => "No repitas un producto.",
            "integer" => "Selecciona una opción válida.",
            "numeric" => "Ingresa un número válido.",
            "gt" => "Debe ser mayor que cero.",
            "min" => "No puede ser menor que cero.",
            "after_or_equal" => "No puede ser anterior a la fecha de emisión.",
            "max" => "Supera la longitud permitida."
        ];

    }

}
