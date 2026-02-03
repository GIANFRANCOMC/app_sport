<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Categories;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\System\Defaults\{UniqueInCompany};

class UpdateCategoryRequest extends FormRequest {

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

        $categoryId = (int) $this->route("id");

        $validations = [
            "internal_code" => ["required", "string", "max:50", new UniqueInCompany("categories", "internal_code", $categoryId, [], "código interno")],
            "name"          => "required|string|max:50",
            "description"   => "nullable|string|max:100",
            "status"        => "required|in:active,inactive"
        ];

        return $validations;

    }

}
