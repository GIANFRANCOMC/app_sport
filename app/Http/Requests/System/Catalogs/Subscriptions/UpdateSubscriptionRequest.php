<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Subscriptions;

use App\Helpers\System\Utilities;
use App\Http\Requests\System\Base\CompanyFormRequest;
use App\Http\Requests\System\Concerns\AppliesInternalCodePrefix;
use App\Rules\System\Defaults\{BelongsToCompany, UniqueInCompany};

class UpdateSubscriptionRequest extends CompanyFormRequest {

    use AppliesInternalCodePrefix;

    protected function internalCodeEntity(): string {

        return "subscription";

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

        $itemId   = (int) $this->route("id");
        $round    = Utilities::$inputs["round"];
        $minValue = Utilities::isDefined($this->min_price) && floatval($this->min_price) > 0 ? floatval($this->min_price) : "0.1";
        $maxValue = Utilities::isDefined($this->max_price) && floatval($this->max_price) > 0 ? floatval($this->max_price) : Utilities::$inputs["maxValue"];

        $validations = [
            "internal_code" => ["required", "string", "max:50", new UniqueInCompany("items", "internal_code", $itemId, ["type" => "subscription"], "código interno")],
            "name"           => "required|string|max:50",
            "description"    => "nullable|string|max:100",
            "duration_value" => "required|integer|min:1|max:$maxValue|decimal:0",
            "duration_type"  => "required|in:hour,day,today,month,year",
            "price"          => "required|numeric|min:$minValue|max:$maxValue|decimal:0,$round",
            "price_includes_tax" => "nullable|boolean",
            "currency_id"    => ["required", "integer", new BelongsToCompany("currencies", ["status" => "active"], "La moneda seleccionada no pertenece a la empresa.")],
            "attendance_limit_per_day" => "nullable|integer|min:1|max:1000",
            "benefits" => "nullable|array|max:50",
            "benefits.*" => "string|max:255",
            "restrictions" => "nullable|array|max:50",
            "restrictions.*" => "string|max:255",
            "status"         => "required|in:active,inactive"
        ];

        if(Utilities::isDefined($this->min_price) && floatval($this->min_price) > 0) {

            $validations["max_price"] = "nullable|numeric|min:$minValue|decimal:0,$round";

        }

        return $validations;

    }

}
