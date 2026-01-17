<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\Branches;

use App\Http\Requests\System\Base\BaseFormRequest;
use App\Rules\System\Defaults\{UniqueInCompany};

class StoreBranchRequest extends BaseFormRequest {

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
            "internal_code" => ["required", "string", "max:50", new UniqueInCompany("branches", "internal_code", null, "Código interno")],
            "name"          => "required|string|max:100",
            "address"       => "nullable|string|max:100",
            "reference"     => "nullable|string|max:100",
            "telephone"     => "nullable|string|max:15",
            "email"         => "nullable|email|max:100",
            "capacity"      => "nullable|integer|min:0",
            "map_url"       => "nullable|url|max:500",
            "status"        => "required|in:active,inactive"
        ];

    }

}
