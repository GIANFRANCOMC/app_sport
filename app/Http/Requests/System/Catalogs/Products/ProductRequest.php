<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Products;

use App\Helpers\System\Utilities;
use App\Http\Requests\System\Base\CompanyFormRequest;
use App\Models\System\Catalogs\{Brand, Item};
use App\Rules\System\Catalogs\ValidEan13;
use App\Rules\System\Defaults\{BelongsToCompany, UniqueInCompany};
use App\Services\System\Base\InternalCodeService;
use Illuminate\Validation\Validator;

abstract class ProductRequest extends CompanyFormRequest {

    public function rules(): array {

        $itemId = $this->route("id") ? (int) $this->route("id") : null;
        $round = Utilities::$inputs["round"];
        $maxValue = Utilities::$inputs["maxValue"];

        return [
            "internal_code" => ["bail", "required", "string", "max:50", "regex:/^[A-Za-z0-9._-]+$/", new UniqueInCompany("items", "internal_code", $itemId, ["type" => "product"], "código interno")],
            "barcode" => ["bail", "required", "string", new ValidEan13(), new UniqueInCompany("items", "barcode", $itemId, [], "código de barras")],
            "name" => ["bail", "required", "string", "max:50"],
            "description" => ["nullable", "string", "max:100"],
            "brand_id" => ["nullable", "integer", new BelongsToCompany("brands", [], "La marca seleccionada no pertenece a la empresa.")],
            "price" => ["bail", "required", "numeric", "min:0.01", "max:{$maxValue}", "decimal:0,{$round}"],
            "price_includes_tax" => ["nullable", "boolean"],
            "min_price" => ["nullable", "numeric", "min:0", "max:{$maxValue}", "decimal:0,{$round}"],
            "max_price" => ["nullable", "numeric", "min:0", "max:{$maxValue}", "decimal:0,{$round}"],
            "commission_type" => ["required", "in:none,percentage,fixed"],
            "commission_value" => ["nullable", "numeric", "min:0", "max:{$maxValue}", "decimal:0,{$round}"],
            "currency_id" => ["bail", "required", "integer", new BelongsToCompany("currencies", ["status" => "active"], "La moneda seleccionada no pertenece a la empresa.")],
            "capacity_control_enabled" => ["nullable", "boolean"],
            "capacity_limit" => ["nullable", "integer", "min:1", "max:1000000"],
            "expires_at" => ["nullable", "date"],
            "categories" => ["nullable", "array", "max:50"],
            "categories.*.category_id" => ["bail", "required", "integer", "distinct", new BelongsToCompany("categories", ["status" => "active"], "Una o más categorías no pertenecen a la empresa o no están activas.")],
            "see_my_web" => ["required", "boolean"],
            "see_my_web_price" => ["required", "boolean"],
            "inventory" => ["required", "array", "min:1", "max:200"],
            "inventory.*.warehouse_id" => ["bail", "required", "integer", "distinct",
                new BelongsToCompany(
                    "warehouses",
                    ["warehouses.status" => "active", "branches.status" => "active"],
                    "Uno o más almacenes no pertenecen a la empresa o no están activos.",
                    [["branches", "warehouses.branch_id", "=", "branches.id"]],
                    "branches.company_id",
                    "warehouses.id"
                )
            ],
            "inventory.*.initial_stock" => $this->isMethod("PATCH")
                ? ["exclude"]
                : ["required", "numeric", "min:0", "max:{$maxValue}", "decimal:0,{$round}"],
            "inventory.*.minimum_stock" => ["required", "numeric", "min:0", "max:{$maxValue}", "decimal:0,{$round}"],
            "status" => ["required", "in:active,inactive"]
        ];

    }

    public function attributes(): array {

        return [
            "internal_code" => "código interno",
            "barcode" => "código de barras",
            "name" => "nombre",
            "description" => "descripción comercial adicional",
            "brand_id" => "marca",
            "price" => "precio de venta",
            "price_includes_tax" => "precio incluye IGV",
            "min_price" => "precio mínimo",
            "max_price" => "precio máximo",
            "currency_id" => "moneda",
            "expires_at" => "fecha de vencimiento",
            "commission_type" => "tipo de comision",
            "commission_value" => "valor de comision",
            "capacity_control_enabled" => "control de cupos",
            "capacity_limit" => "cupos disponibles",
            "categories" => "categorías",
            "inventory" => "inventario por almacén",
            "status" => "estado"
        ];

    }

    public function messages(): array {

        return array_merge(parent::messages(), [
            "internal_code.regex" => "El código interno solo puede contener letras, números, puntos, guiones y guiones bajos.",
            "inventory.min" => "Debe existir al menos un almacén activo para registrar el producto."
        ]);

    }

    public function after(): array {

        return [
            function(Validator $validator) {

                $this->validatePriceRange($validator);
                $this->validateCommission($validator);
                $this->validateCapacity($validator);
                $this->validateBrandStatus($validator);

            }
        ];

    }

    protected function normalizedStringFields(): array {

        return [
            "internal_code",
            "barcode",
            "name",
            "description"
        ];

    }

    protected function prepareForValidation(): void {

        parent::prepareForValidation();

        $this->merge([
            "internal_code" => InternalCodeService::applyPrefix(
                (int) $this->user()?->company_id,
                "product",
                $this->input("internal_code")
            ),
            "brand_id" => $this->filled("brand_id") ? (int) $this->input("brand_id") : null,
            "min_price" => $this->normalizeOptionalNumber($this->input("min_price")),
            "max_price" => $this->normalizeOptionalNumber($this->input("max_price")),
            "commission_type" => $this->input("commission_type") ?: "none",
            "commission_value" => $this->input("commission_type") === "none"
                ? 0
                : ($this->normalizeOptionalNumber($this->input("commission_value")) ?? 0),
            "capacity_control_enabled" => $this->boolean("capacity_control_enabled"),
            "capacity_limit" => $this->boolean("capacity_control_enabled") ? $this->input("capacity_limit") : null,
            "expires_at" => $this->filled("expires_at") ? $this->input("expires_at") : null
        ]);

    }

    private function validateCapacity(Validator $validator): void {

        if($validator->errors()->has("capacity_limit") || !$this->boolean("capacity_control_enabled")) {

            return;

        }

        if(!$this->filled("capacity_limit")) {

            $validator->errors()->add("capacity_limit", "Indica cuántos cupos estarán disponibles.");
            return;

        }

        $currentUsed = Item::query()
                           ->whereKey((int) $this->route("id"))
                           ->where("company_id", $this->user()?->company_id)
                           ->where("type", "product")
                           ->value("capacity_used");

        if($currentUsed !== null && (int) $this->input("capacity_limit") < (int) $currentUsed) {

            $validator->errors()->add("capacity_limit", "No puede ser menor que los cupos ya consumidos.");

        }

    }

    private function validateCommission(Validator $validator): void {

        if($validator->errors()->hasAny(["commission_type", "commission_value"])) {

            return;

        }

        $type = (string) $this->input("commission_type", "none");
        $value = (float) ($this->input("commission_value") ?? 0);

        if($type !== "none" && $value <= 0) {

            $validator->errors()->add("commission_value", "Debe ser mayor que 0 cuando el producto tiene comision.");

        }

        if($type === "percentage" && $value > 100) {

            $validator->errors()->add("commission_value", "No puede superar el 100%.");

        }

    }

    private function validatePriceRange(Validator $validator): void {

        if($validator->errors()->hasAny(["price", "min_price", "max_price"])) {

            return;

        }

        $price = (float) $this->input("price");
        $minimum = $this->positiveNumberOrNull($this->input("min_price"));
        $maximum = $this->positiveNumberOrNull($this->input("max_price"));

        if($minimum !== null && $minimum > $price) {

            $validator->errors()->add("min_price", "No puede ser mayor que el precio de venta.");

        }

        if($maximum !== null && $maximum < $price) {

            $validator->errors()->add("max_price", "No puede ser menor que el precio de venta.");

        }

        if($minimum !== null && $maximum !== null && $minimum > $maximum) {

            $validator->errors()->add("max_price", "No puede ser menor que el precio mínimo.");

        }

    }

    private function validateBrandStatus(Validator $validator): void {

        if($validator->errors()->has("brand_id") || !$this->filled("brand_id")) {

            return;

        }

        $brand = Brand::query()
                      ->whereKey((int) $this->input("brand_id"))
                      ->where("company_id", $this->user()?->company_id)
                      ->first();

        if(!$brand || $brand->status === "active") {

            return;

        }

        $currentBrandId = Item::query()
                              ->whereKey((int) $this->route("id"))
                              ->where("company_id", $this->user()?->company_id)
                              ->where("type", "product")
                              ->value("brand_id");

        if((int) $currentBrandId !== (int) $brand->id) {

            $validator->errors()->add("brand_id", "La marca seleccionada está inactiva.");

        }

    }

    private function normalizeOptionalNumber(mixed $value): mixed {

        if($value === null || $value === "") {

            return null;

        }

        return $value;

    }

    private function positiveNumberOrNull(mixed $value): ?float {

        return is_numeric($value) && (float) $value > 0 ? (float) $value : null;

    }

}
