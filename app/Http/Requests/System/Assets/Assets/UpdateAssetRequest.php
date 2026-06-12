<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Assets\Assets;

use App\Http\Requests\System\Base\CompanyFormRequest;
use App\Http\Requests\System\Concerns\AppliesInternalCodePrefix;
use App\Rules\System\Defaults\{UniqueInCompany};

class UpdateAssetRequest extends CompanyFormRequest {

    use AppliesInternalCodePrefix;

    protected function internalCodeEntity(): string {

        return "asset";

    }

    protected function normalizedStringFields(): array {

        return ["internal_code", "name", "description"];

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        $assetId = (int) $this->route("id");

        $validations = [
            "internal_code" => ["required", "string", "max:50", new UniqueInCompany("assets", "internal_code", $assetId, [], "código interno")],
            "name"          => "required|string|max:50",
            "description"   => "nullable|string|max:100",
            "status"        => "required|in:active,inactive"
        ];

        return $validations;

    }

}
