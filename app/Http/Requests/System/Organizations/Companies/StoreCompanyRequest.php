<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\Companies;

use App\Helpers\System\Utilities;
use App\Http\Requests\System\Base\BaseFormRequest;

class StoreCompanyRequest extends BaseFormRequest {

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


}
