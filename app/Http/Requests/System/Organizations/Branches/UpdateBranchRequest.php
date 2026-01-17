<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\Branches;

use App\Http\Requests\System\Base\BaseFormRequest;
use App\Rules\System\Defaults\{UniqueInCompany};

class UpdateBranchRequest extends BaseFormRequest {

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

        $branchId = $this->route("id");

        return [
            "internal_code" => ["required", "string", "max:50", new UniqueInCompany("branches", "internal_code", (int) $branchId, "Código interno")],
            "name"          => "required|string|max:100",
            "address"       => "nullable|string|max:100",
            "reference"     => "nullable|string|max:150",
            "telephone"     => "nullable|string|max:25",
            "email"         => "nullable|email|max:120",
            "capacity"      => "nullable|integer|min:0",
            "map_url"       => "nullable|url|max:255",
            "status"        => "required|in:active,inactive"
        ];

    }

}
