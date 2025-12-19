<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Customers\Customers;

use App\Http\Requests\System\Base\BaseFormRequest;

class UpdateCustomerRequest extends BaseFormRequest {

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        return [
            "identity_document_type_id" => "required|integer",
            "document_number"           => "required|string|max:25",
            "status"                    => "required|string",
            "name"                      => "required|string|max:200",
            "email"                     => "nullable|email|max:200",
            "phone_number"              => "nullable|integer",
            "gender"                    => "nullable|string",
            "birthdate"                 => "nullable|date"
        ];

    }

}
