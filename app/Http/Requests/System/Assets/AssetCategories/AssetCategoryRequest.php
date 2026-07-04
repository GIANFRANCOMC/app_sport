<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Assets\AssetCategories;

use App\Http\Requests\System\Base\CompanyFormRequest;
use App\Rules\System\Defaults\UniqueInCompany;

abstract class AssetCategoryRequest extends CompanyFormRequest {

    public function rules(): array {

        $categoryId = $this->route("id") ? (int) $this->route("id") : null;

        return [
            "name" => ["required", "string", "max:150", new UniqueInCompany("asset_categories", "name", $categoryId)],
            "description" => ["nullable", "string", "max:500"],
            "status" => [$this->isMethod("PATCH") ? "required" : "nullable", "in:active,inactive"]
        ];

    }

    protected function normalizedStringFields(): array {

        return ["name", "description", "status"];

    }

}
