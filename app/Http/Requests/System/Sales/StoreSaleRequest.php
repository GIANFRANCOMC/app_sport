<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Sales;

use App\Helpers\System\{ApiResponse, Utilities};
use App\Http\Requests\System\Base\BaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSaleRequest extends BaseFormRequest {

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        $round    = Utilities::$inputs["round"];
        $maxValue = Utilities::$inputs["maxValue"];

        return [
            // Header
            "branch_id"   => "required|integer",
            "serie_id"    => "required|integer",
            "holder_id"   => "required|integer",
            "currency_id" => "required|integer",
            "issue_date"  => "required|date",
            "observation" => "nullable|string|max:300",
            // Details
            "details" => "required|array",
            "details.*.item_id" => "required|integer",
            "details.*.type" => "required|string|max:255",
            "details.*.currency_id" => "required|integer",
            "details.*.name" => "required|string|max:255",
            "details.*.quantity" => "required|numeric|min:0.1|max:$maxValue|decimal:0,$round",
            "details.*.price" => "required|numeric|min:0.1|max:$maxValue|decimal:0,$round",
            "details.*.observation" => "nullable|string|max:300"
        ];

    }

    protected function failedValidation(Validator $validator): void {

        $errors = $validator->errors()->toArray();

        // Props rename for frontend compatibility
        $fieldMappings = [
            "branch_id"   => "branch",
            "serie_id"    => "serie",
            "holder_id"   => "holder",
            "currency_id" => "currency"
        ];

        $renamedErrors = [];

        foreach($errors as $key => $value) {

            $newKey = $fieldMappings[$key] ?? $key;
            $renamedErrors[$newKey] = $value;

        }

        // Throw exception with renamed errors
        throw new HttpResponseException(
            ApiResponse::validationError($renamedErrors, Utilities::$messages["422"] ?? "Validation failed")
        );

    }

}
