<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Base;

use App\Helpers\System\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Shared request contract for company-scoped System modules.
 */
abstract class CompanyFormRequest extends FormRequest {

    public function authorize(): bool {

        return (int) ($this->user()?->company_id ?? 0) > 0;

    }

    protected function prepareForValidation(): void {

        $normalized = [];

        foreach($this->normalizedStringFields() as $field) {

            if(!$this->exists($field)) {

                continue;

            }

            $value = $this->input($field);

            if(!is_string($value)) {

                continue;

            }

            $value = trim($value);
            $normalized[$field] = $value === "" ? null : $value;

        }

        if($normalized !== []) {

            $this->merge($normalized);

        }

    }

    /**
     * @return array<int, string>
     */
    protected function normalizedStringFields(): array {

        return [];

    }

    public function messages(): array {

        return [
            "required" => "Campo obligatorio.",
            "string" => "Ingresa un texto válido.",
            "numeric" => "Ingresa un número válido.",
            "integer" => "Ingresa un número entero válido.",
            "boolean" => "Selecciona una opción válida.",
            "array" => "Selecciona una opción válida.",
            "in" => "Selecciona una opción válida.",
            "date" => "Ingresa una fecha válida.",
            "ip" => "Ingresa una IP válida.",
            "url" => "Ingresa una URL válida.",
            "email" => "Ingresa un correo válido.",
            "distinct" => "No repitas la misma opción.",
            "different" => "Selecciona una opción diferente.",
            "required_with" => "Campo obligatorio.",
            "required_without" => "Campo obligatorio.",
            "required_without_all" => "Campo obligatorio.",
            "gt.numeric" => "Debe ser mayor que :value.",
            "gte.numeric" => "Debe ser mayor o igual a :value.",
            "min.numeric" => "El valor mínimo permitido es :min.",
            "max.numeric" => "El valor máximo permitido es :max.",
            "min.array" => "Selecciona al menos :min opción.",
            "max.array" => "Selecciona como máximo :max opciones.",
            "max.string" => "Debe tener como máximo :max caracteres.",
            "decimal" => "Usa hasta :decimal decimales."
        ];

    }

    protected function failedValidation(Validator $validator): void {

        throw new HttpResponseException(
            ApiResponse::validationError(
                $validator->errors()->toArray(),
                "Revisa los campos marcados para continuar."
            )
        );

    }

}
