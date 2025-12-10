<?php

namespace App\Http\Requests\System\Assets\AssetManagements;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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

    protected function failedValidation(Validator $validator) {

        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(response()->json(["errors" => $errors, "message" => Utilities::$messages["422"]], 422));

    }

}
