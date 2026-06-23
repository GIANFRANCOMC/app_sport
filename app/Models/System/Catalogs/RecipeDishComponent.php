<?php

declare(strict_types=1);

namespace App\Models\System\Catalogs;

use Illuminate\Database\Eloquent\Model;

class RecipeDishComponent extends Model {

    protected $table = "recipe_dish_components";

    protected $fillable = [
        "recipe_dish_id",
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

    public function recipeDish() {

        return $this->belongsTo(RecipeDish::class, "recipe_dish_id", "id");

    }

    public function item() {

        return $this->belongsTo(Item::class, "item_id", "id");

    }

}
