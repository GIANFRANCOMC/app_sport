<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Assets\Assets;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetRequest extends FormRequest {

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
            "internal_code" => "required|string|max:100",
            "name"          => "required|string|max:200",
            "description"   => "nullable|string|max:500",
            "status"        => "required|string"
        ];

    }

}
