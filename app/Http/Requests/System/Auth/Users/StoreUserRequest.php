<?php

namespace App\Http\Requests\System\Users;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest {

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
            "role_id"                   => "required|integer",
            "identity_document_type_id" => "required|integer",
            "document_number"           => "required|string|max:25",
            "status"                    => "required|string",
            "name"                      => "required|string|max:200",
            "email"                     => "required|email|max:200",
            "phone_number"              => "nullable|integer",
            "gender"                    => "nullable|string",
            "birthdate"                 => "nullable|date",
            "password"                  => "required|string|max:200"
        ];

    }

    protected function failedValidation(Validator $validator) {

        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(response()->json(["errors" => $errors, "message" => Utilities::$messages["422"]], 422));

    }

}
