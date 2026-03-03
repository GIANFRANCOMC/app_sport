<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Customers\Customers;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\System\Defaults\{DocumentNumberLength, UniqueInCompany};

class StoreCustomerRequest extends FormRequest {

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
            "identity_document_type_id" => "required|integer",
            "document_number"           => ["required", "string", new DocumentNumberLength((int) $this->identity_document_type_id), new UniqueInCompany("customers", "document_number", null, [], "número de documento")],
            "name"                      => "required|string|max:100",
            "email"                     => "nullable|email|max:100",
            "phone_number"              => "nullable|string|max:15",
            "gender"                    => "nullable|in:male,female,other",
            "birthdate"                 => "nullable|date",
            "status"                    => "required|in:active,inactive"
        ];

    }

}
