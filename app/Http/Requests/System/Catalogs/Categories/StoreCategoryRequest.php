<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Categories;

use App\Http\Requests\System\Base\BaseFormRequest;

class StoreCategoryRequest extends BaseFormRequest {

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        return [
            "internal_code" => "required|string|max:100",
            "name"          => "required|string|max:100",
            "description"   => "nullable|string|max:300",
            "status"        => "required|string"
        ];

    }

}
