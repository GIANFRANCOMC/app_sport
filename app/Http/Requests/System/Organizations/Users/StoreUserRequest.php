<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\Users;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\System\Defaults\{BelongsToCompany, UniqueInCompany};
use App\Rules\System\Organizations\{UniqueEmailGlobal};

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

        $validations = [
            "role_id"                   => ["required", "integer", new BelongsToCompany("roles", [], null)],
            "identity_document_type_id" => "required|integer",
            "document_number"           => ["required", "string", "max:20", new UniqueInCompany("users", "document_number", null, [], "número de documento")],
            "name"                      => "required|string|max:100",
            "email"                     => ["required", "email", "max:100", new UniqueEmailGlobal()],
            "phone_number"              => "nullable|string|max:15",
            "gender"                    => "nullable|in:male,female,other",
            "birthdate"                 => "nullable|date",
            "password"                  => "required|string|max:100",
            "status"                    => "required|in:active,inactive"
        ];

        return $validations;

    }

}
