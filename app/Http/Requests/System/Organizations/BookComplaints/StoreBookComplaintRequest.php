<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\BookComplaints;

use App\Http\Requests\System\Base\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreBookComplaintRequest extends BaseFormRequest {

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        return [
            "admin_response" => "required|string|max:600",
            "status"         => ["required", "string", Rule::in(["pending", "in_progress", "resolved"])]
        ];

    }


}
