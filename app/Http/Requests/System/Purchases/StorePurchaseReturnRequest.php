<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Purchases;

use App\Rules\System\Defaults\BelongsToCompany;
use Illuminate\Foundation\Http\FormRequest;

final class StorePurchaseReturnRequest extends FormRequest {

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            "purchase_receipt_id" => ["nullable", "integer", new BelongsToCompany("purchase_receipts")],
            "warehouse_id" => ["required", "integer", new BelongsToCompany("warehouses", ["status" => "active"])],
            "returned_at" => ["nullable", "date"],
            "reason" => ["required", "string", "max:500"],
            "items" => ["required", "array", "min:1", "max:100"],
            "items.*.purchase_item_id" => ["required", "integer", "distinct"],
            "items.*.quantity" => ["required", "numeric", "gt:0"]
        ];
    }

}
