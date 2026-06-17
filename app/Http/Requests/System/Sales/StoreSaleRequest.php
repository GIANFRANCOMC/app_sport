<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Sales;

use App\Helpers\System\{ApiResponse, Utilities};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSaleRequest extends FormRequest {

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

        $round    = Utilities::$inputs["round"];
        $maxValue = Utilities::$inputs["maxValue"];

        return [
            // Header
            "branch_id"   => "required|integer",
            "serie_id"    => "required|integer",
            "holder_id"   => "required|integer",
            "currency_id" => "required|integer",
            "warehouse_id" => "nullable|integer",
            "cash_session_id" => "nullable|integer",
            "issue_date"  => "required|date",
            "observation" => "nullable|string|max:300",
            "taxes" => "nullable|array|max:20",
            "taxes.*.tax_id" => "required_with:taxes|integer",
            "taxes.*.rate" => "nullable|numeric|min:0|max:100",
            "taxes.*.amount" => "nullable|numeric|min:0|max:$maxValue|decimal:0,$round",
            "payments" => "nullable|array|max:20",
            "payments.*.payment_method_id" => "required_with:payments|integer",
            "payments.*.amount" => "required_with:payments|numeric|min:0.01|max:$maxValue|decimal:0,$round",
            "payments.*.reference" => "nullable|string|max:100",
            "payments.*.note" => "nullable|string|max:300",
            // Details
            "details" => "required|array",
            "details.*.item_id" => "required|integer",
            "details.*.type" => "required|string|max:255",
            "details.*.currency_id" => "required|integer",
            "details.*.name" => "required|string|max:255",
            "details.*.quantity" => "required|numeric|min:0.1|max:$maxValue|decimal:0,$round",
            "details.*.price" => "required|numeric|min:0.1|max:$maxValue|decimal:0,$round",
            "details.*.price_includes_tax" => "nullable|boolean",
            "details.*.observation" => "nullable|string|max:300"
        ];

    }

    protected function failedValidation(Validator $validator): void {

        $errors = $validator->errors()->toArray();

        // Props rename for frontend compatibility
        $fieldMappings = [
            "branch_id"   => "branch",
            "serie_id"    => "serie",
            "warehouse_id" => "warehouse",
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
