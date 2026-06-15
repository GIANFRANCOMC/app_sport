<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Purchases;

use Illuminate\Foundation\Http\FormRequest;

final class ReceivePurchaseRequest extends FormRequest {

    public function authorize(): bool {

        return true;

    }

    public function rules(): array {

        return [
            "received_at" => ["required", "date"],
            "observation" => ["nullable", "string", "max:1000"],
            "items" => ["required", "array", "min:1"],
            "items.*.purchase_item_id" => ["required", "integer", "distinct"],
            "items.*.quantity" => ["required", "numeric", "gt:0"]
        ];

    }

    public function messages(): array {

        return [
            "required" => "Campo obligatorio.",
            "items.min" => "Agrega al menos un producto.",
            "distinct" => "No repitas un producto.",
            "numeric" => "Ingresa un número válido.",
            "gt" => "Debe ser mayor que cero.",
            "max" => "Supera la longitud permitida."
        ];

    }

}
