<?php

declare(strict_types=1);

namespace App\Models\System\Catalogs;

use Illuminate\Database\Eloquent\Model;

class RecipeDishOptionComponent extends Model {

    protected $table = "recipe_dish_option_components";

    protected $fillable = [
        "recipe_dish_option_id",
        "item_id",
        "quantity",
        "waste_percentage",
        "note",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    protected $casts = [
        "quantity" => "decimal:4",
        "waste_percentage" => "decimal:4"
    ];

    public function option() {

        return $this->belongsTo(RecipeDishOption::class, "recipe_dish_option_id", "id");

    }

    public function item() {

        return $this->belongsTo(Item::class, "item_id", "id");

    }

}
