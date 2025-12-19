<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Assets\AssetManagements;

use App\Http\Requests\System\Base\BaseFormRequest;

class AssignAssetToBranchRequest extends BaseFormRequest {

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
