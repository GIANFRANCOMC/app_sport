<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Services;

use App\Helpers\System\Utilities;
use App\Http\Requests\System\Base\BaseFormRequest;

class StoreServiceRequest extends BaseFormRequest {

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
            "internal_code" => "required|string|max:100",
            "name"          => "required|string|max:100",
            "description"   => "nullable|string|max:300",
            "price"         => "required|numeric|min:$minValue|max:$maxValue|decimal:0,$round",
            "currency_id"   => "required|integer",
            "status"        => "required|string"
        ];

        if(Utilities::isDefined($this->min_price) && floatval($this->min_price) > 0) {

            $validations["max_price"] = "nullable|numeric|min:$minValue|decimal:0,$round";

        }

        return $validations;

    }


}
