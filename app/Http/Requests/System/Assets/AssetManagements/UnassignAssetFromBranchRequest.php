<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Assets\AssetManagements;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;

class UnassignAssetFromBranchRequest extends FormRequest {

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
            "branch_id" => "required|integer",
        ];

    }

}
