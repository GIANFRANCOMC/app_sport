<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Products;

use App\Helpers\System\Utilities;
use App\Models\System\Catalogs\Category;
use App\Models\System\Warehouses\Warehouse;
use App\Rules\System\Catalogs\ValidEan13;
use App\Rules\System\Defaults\UniqueInCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class ProductRequest extends FormRequest {

    public function authorize(): bool {

        return true;

    }

    public function rules(): array {

        $itemId   = $this->route("id") ? (int) $this->route("id") : null;
        $round    = Utilities::$inputs["round"];
        $minValue = Utilities::isDefined($this->min_price) && floatval($this->min_price) > 0 ? floatval($this->min_price) : "0.1";
        $maxValue = Utilities::isDefined($this->max_price) && floatval($this->max_price) > 0 ? floatval($this->max_price) : Utilities::$inputs["maxValue"];

        $validations = [
            "internal_code"              => ["required", "string", "max:50", new UniqueInCompany("items", "internal_code", $itemId, ["type" => "product"], "código interno")],
            "barcode"                    => ["required", "string", new ValidEan13(), new UniqueInCompany("items", "barcode", $itemId, [], "código de barras")],
            "name"                       => "required|string|max:50",
            "description"                => "nullable|string|max:100",
            "price"                      => "required|numeric|min:$minValue|max:$maxValue|decimal:0,$round",
            "currency_id"                => "required|integer",
            "categories"                 => "nullable|array",
            "categories.*.category_id"   => "required|integer|distinct",
            "see_my_web"                 => "required|boolean",
            "see_my_web_price"           => "required|boolean",
            "inventory"                  => "required|array",
            "inventory.*.warehouse_id"   => "required|integer|distinct",
            "inventory.*.initial_stock"  => "required|numeric|min:0|decimal:0,$round",
            "inventory.*.minimum_stock"  => "required|numeric|min:0|decimal:0,$round",
            "status"                     => "required|in:active,inactive"
        ];

        if(Utilities::isDefined($this->min_price) && floatval($this->min_price) > 0) {

            $validations["max_price"] = "nullable|numeric|min:$minValue|decimal:0,$round";

        }

        return $validations;

    }

    public function attributes(): array {

        return [
            "description" => "descripción comercial adicional"
        ];

    }

    public function after(): array {

        return [
            function(Validator $validator) {

                $warehouseIds = collect($this->input("inventory", []))
                    ->pluck("warehouse_id")
                    ->filter()
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->values();

                if($warehouseIds->isNotEmpty()) {

                    $validWarehouseIds = Warehouse::whereIn("id", $warehouseIds)
                        ->where("status", "active")
                        ->whereHas("branch", function($query) {

                            $query->where("company_id", $this->user()?->company_id)
                                  ->where("status", "active");

                        })
                        ->pluck("id");

                    if($validWarehouseIds->count() !== $warehouseIds->count()) {

                        $validator->errors()->add("inventory", "Uno o más almacenes no pertenecen a la empresa o no están activos.");

                    }

                }

                $categoryIds = collect($this->input("categories", []))
                    ->pluck("category_id")
                    ->filter()
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->values();

                if($categoryIds->isEmpty()) {

                    return;

                }

                $validCategoryIds = Category::whereIn("id", $categoryIds)
                    ->where("company_id", $this->user()?->company_id)
                    ->where("status", "active")
                    ->pluck("id");

                if($validCategoryIds->count() !== $categoryIds->count()) {

                    $validator->errors()->add("categories", "Una o más categorías no pertenecen a la empresa o no están activas.");

                }

            }
        ];

    }

}
