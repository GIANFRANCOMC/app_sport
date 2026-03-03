<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Devices\BiometricDevices;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\System\Defaults\{BelongsToCompany};

class UpdateBiometricDeviceRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {

        return true;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        return [
            "branch_id"     => ["required", "integer", new BelongsToCompany("branches", [], null)],
            "name"          => "required|string|max:50",
            "description"   => "nullable|string|max:100",
            "brand"         => "required|in:ZKTeco",
            "model"         => "required|in:K20 Pro",
            "serial_number" => "nullable|string|max:50",
            "ip_address"    => "required|ip",
            "port"          => "nullable|integer|min:1|max:65535",
            "device_id"     => "nullable|string|max:50",
            "status"        => "required|in:active,inactive"
        ];

    }

}

