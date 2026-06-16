<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\Roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest {

    public function authorize(): bool {

        return true;

    }

    public function rules(): array {

        $companyId = (int) $this->user()?->company_id;
        $roleId = (int) $this->route("id");

        return [
            "name" => [
                "required",
                "string",
                "max:80",
                Rule::unique("roles", "name")
                    ->where("company_id", $companyId)
                    ->ignore($roleId)
            ],
            "is_full_access" => ["required", "boolean"],
            "sub_section_ids" => ["array"],
            "sub_section_ids.*" => ["integer"],
            "status" => ["required", "in:active,inactive"]
        ];

    }

    public function messages(): array {

        return [
            "required" => "Campo obligatorio.",
            "unique" => "Ya existe un perfil con este nombre.",
            "boolean" => "Selecciona una opción válida.",
            "array" => "Selecciona módulos válidos.",
            "integer" => "Selecciona módulos válidos.",
            "in" => "Selecciona una opción válida.",
            "max" => "Supera la longitud permitida."
        ];

    }

}
