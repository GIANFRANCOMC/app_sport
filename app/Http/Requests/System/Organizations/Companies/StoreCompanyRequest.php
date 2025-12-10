<?php

namespace App\Http\Requests\System\Companies;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCompanyRequest extends FormRequest {

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

        $maxSize = Utilities::$inputs["maxSize"];

        return [
            "identity_document_type_id" => "required|integer",
            "document_number"           => "required|string|max:25",
            "legal_name"                => "required|string|max:200",
            "commercial_name"           => "required|string|max:200",
            "tagline"                   => "nullable|string|max:500",
            "description"               => "nullable|string|max:500",
            "address"                   => "nullable|string|max:200",
            "telephone"                 => "nullable|string|max:50",
            "email"                     => "nullable|email|max:200",
            "status"                    => "required|string",
            "logotype"                  => "nullable|file|image|mimes:jpeg,png,jpg|max:$maxSize",
            "combinationmark"           => "nullable|file|image|mimes:jpeg,png,jpg|max:$maxSize",
            "logomark"                  => "nullable|file|image|mimes:jpeg,png,jpg|max:$maxSize",
            "login_image"               => "nullable|file|image|mimes:jpeg,png,jpg|max:$maxSize"
        ];

    }

    protected function failedValidation(Validator $validator) {

        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(response()->json(["errors" => $errors, "message" => Utilities::$messages["422"]], 422));

    }

}
