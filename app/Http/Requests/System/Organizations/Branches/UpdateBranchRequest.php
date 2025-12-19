<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\Branches;

use App\Http\Requests\System\Base\BaseFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends BaseFormRequest {

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        $userAuth = Auth::user();
        $branchId = $this->route("id");

        return [
            "internal_code" => ["required", "string", "max:50", Rule::unique("branches", "internal_code")->where("company_id", $userAuth->company_id)->ignore($branchId)],
            "name"          => "required|string|max:100",
            "address"       => "nullable|string|max:100",
            "reference"     => "nullable|string|max:150",
            "telephone"     => "nullable|string|max:25",
            "email"         => "nullable|email|max:120",
            "capacity"      => "nullable|integer|min:0",
            "map_url"       => "nullable|url|max:255",
            "status"        => "required|string|in:active,inactive"
        ];

    }

}
