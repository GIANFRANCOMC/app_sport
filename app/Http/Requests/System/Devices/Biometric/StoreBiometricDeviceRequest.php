<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Devices\Biometric;

use Illuminate\Foundation\Http\FormRequest;

class StoreBiometricDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "branch_id" => "required|exists:branches,id",
            "name" => "required|string|max:50",
            "description" => "nullable|string|max:100",
            "brand" => "required|in:ZKTeco",
            "model" => "required|in:K20 Pro",
            "serial_number" => "nullable|string|max:100",
            "ip_address" => "required|ip",
            "port" => "nullable|integer|min:1|max:65535",
            "device_id" => "nullable|integer",
            "status" => "required|in:active,inactive"
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            "branch_id.required" => "La sucursal es requerida.",
            "branch_id.exists" => "La sucursal seleccionada no es válida.",
            "name.required" => "El nombre del dispositivo es requerido.",
            "model.required" => "El modelo del dispositivo es requerido.",
            "ip_address.required" => "La dirección IP es requerida.",
            "ip_address.ip" => "La dirección IP no es válida.",
            "port.integer" => "El puerto debe ser un número entero.",
            "port.min" => "El puerto debe ser mayor a 0.",
            "port.max" => "El puerto debe ser menor a 65536.",
            "status.required" => "El estado es requerido.",
            "status.in" => "El estado debe ser 'active' o 'inactive'."
        ];
    }
}

