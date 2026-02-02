<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Subscriptions;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\System\Defaults\UniqueInCompany;

class StoreSubscriptionRequest extends FormRequest {

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

        $round    = Utilities::$inputs["round"];
        $minValue = Utilities::isDefined($this->min_price) && floatval($this->min_price) > 0 ? floatval($this->min_price) : "0.1";
        $maxValue = Utilities::isDefined($this->max_price) && floatval($this->max_price) > 0 ? floatval($this->max_price) : Utilities::$inputs["maxValue"];

        $validations = [
            "internal_code" => [
                "required",
                "string",
                "max:100",
                new UniqueInCompany("items", "internal_code", null, ["type" => "subscription"], "código interno")
            ],
            "name"           => "required|string|max:100",
            "description"    => "nullable|string|max:300",
            "price"          => "required|numeric|min:$minValue|max:$maxValue|decimal:0,$round",
            "currency_id"    => "required|integer",
            "duration_type"  => "required|string",
            "duration_value" => "required|integer|min:1|max:$maxValue|decimal:0",
            "status"         => "required|string"
        ];

        if(Utilities::isDefined($this->min_price) && floatval($this->min_price) > 0) {

            $validations["max_price"] = "nullable|numeric|min:$minValue|decimal:0,$round";

        }

        return $validations;

    }

}
