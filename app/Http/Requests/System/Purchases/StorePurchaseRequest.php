<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Purchases;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\System\Defaults\BelongsToCompany;

final class StorePurchaseRequest extends FormRequest {

    public function authorize(): bool {

        return true;

    }

    public function rules(): array {

        return [
            "supplier_id" => ["required", "integer"],
            "warehouse_id" => ["required", "integer"],
            "currency_id" => ["required", "integer", new BelongsToCompany("currencies", ["status" => "active"], "La moneda seleccionada no pertenece a la empresa.")],
            "document_type" => ["required", "in:order,invoice"],
            "document_number" => ["nullable", "string", "max:50"],
            "issue_date" => ["required", "date"],
            "expected_date" => ["nullable", "date", "after_or_equal:issue_date"],
            "tax" => ["nullable", "numeric"],
            "taxes" => ["nullable", "array", "max:20"],
            "taxes.*.tax_id" => ["required_with:taxes", "integer"],
            "taxes.*.rate" => ["nullable", "numeric", "min:0"],
            "taxes.*.calculation_type" => ["nullable", "in:percentage,fixed"],
            "taxes.*.operation_type" => ["nullable", "in:addition,subtraction"],
            "taxes.*.is_required" => ["nullable", "boolean"],
            "taxes.*.quantity" => ["nullable", "integer", "min:1"],
            "taxes.*.amount" => ["nullable", "numeric"],
            "payments" => ["nullable", "array", "max:20"],
            "payments.*.payment_method_id" => ["required_with:payments", "integer"],
            "payments.*.amount" => ["required_with:payments", "numeric", "gt:0"],
            "payments.*.reference" => ["nullable", "string", "max:100"],
            "payments.*.note" => ["nullable", "string", "max:300"],
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
