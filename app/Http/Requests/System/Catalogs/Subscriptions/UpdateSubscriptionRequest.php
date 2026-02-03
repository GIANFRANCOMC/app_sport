<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Subscriptions;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\System\Defaults\{UniqueInCompany};

class UpdateSubscriptionRequest extends FormRequest {

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
            "currency_id"    => "required|integer",
            "status"         => "required|in:active,inactive"
        ];

        if(Utilities::isDefined($this->min_price) && floatval($this->min_price) > 0) {

            $validations["max_price"] = "nullable|numeric|min:$minValue|decimal:0,$round";

        }

        return $validations;

    }

}
