<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Purchases;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSupplierRequest extends FormRequest {

    public function authorize(): bool {

        return true;

    }

    public function rules(): array {

        $companyId = (int) $this->user()?->company_id;

        return [
            "document_type" => ["nullable", "string", "max:20"],
            "document_number" => [
                "nullable",
                "string",
                "max:30",
                Rule::unique("suppliers", "document_number")
                    ->where("company_id", $companyId)
                    ->ignore((int) $this->route("id"))
            ],
            "name" => ["required", "string", "max:255"],
            "contact_name" => ["nullable", "string", "max:255"],
            "telephone" => ["nullable", "string", "max:30"],
            "email" => ["nullable", "email", "max:255"],
            "address" => ["nullable", "string", "max:255"],
            "status" => ["required", "in:active,inactive"]
        ];

    }

    public function messages(): array {

        return [
            "required" => "Campo obligatorio.",
            "unique" => "Ya existe un proveedor con este número de documento.",
            "email" => "Ingresa un correo válido.",
            "in" => "Selecciona una opción válida.",
            "max" => "Supera la longitud permitida."
        ];

    }

}
