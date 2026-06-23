<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Customers\Customers;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\System\Defaults\{BelongsToCompany, DocumentNumberLength, UniqueInCompany};

class UpdateCustomerRequest extends FormRequest {

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

        $customerId = (int) $this->route("id");

        return [
            "identity_document_type_id" => ["required", "integer", new BelongsToCompany("identity_document_types", ["status" => "active"], "El tipo de documento no pertenece a la empresa.")],
            "document_number"           => ["required", "string", new DocumentNumberLength((int) $this->identity_document_type_id), new UniqueInCompany("customers", "document_number", $customerId, [], "número de documento")],
            "name"                      => "required|string|max:100",
            "email"                     => "nullable|email|max:100",
            "phone_number"              => "nullable|string|max:15",
            "gender"                    => "nullable|in:male,female,other",
            "birthdate"                 => "nullable|date",
            "status"                    => "required|in:active,inactive"
        ];

    }

}
