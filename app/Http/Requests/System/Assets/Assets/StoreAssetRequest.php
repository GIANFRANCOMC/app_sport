<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Assets\Assets;

use App\Http\Requests\System\Base\CompanyFormRequest;
use App\Http\Requests\System\Concerns\AppliesInternalCodePrefix;
use App\Rules\System\Defaults\BelongsToCompany;
use App\Rules\System\Defaults\UniqueInCompany;

final class StoreAssetRequest extends CompanyFormRequest {
    use AppliesInternalCodePrefix;

    protected function internalCodeEntity(): string {
        return "asset";
    }

    protected function normalizedStringFields(): array {
        return ["internal_code", "patrimonial_code", "serial_number", "name", "description"];
    }

    public function rules(): array {
        return [
            "internal_code" => ["required", "string", "max:50", new UniqueInCompany("assets", "internal_code", null, [], "código interno")],
            "asset_category_id" => ["nullable", "integer", new BelongsToCompany("asset_categories", ["status" => "active"])],
            "patrimonial_code" => ["nullable", "string", "max:100", new UniqueInCompany("assets", "patrimonial_code")],
            "serial_number" => ["nullable", "string", "max:150", new UniqueInCompany("assets", "serial_number")],
            "name" => ["required", "string", "max:50"],
            "description" => ["nullable", "string", "max:500"],
            "status" => ["required", "in:active,inactive"],
        ];
    }
}
