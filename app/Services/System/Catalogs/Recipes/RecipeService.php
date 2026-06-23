<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Recipes;

use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

use App\Helpers\System\Utilities;
use App\Models\System\Catalogs\{
    Item,
    RecipeDish,
    RecipeDishComponent,
    RecipeDishOption,
    RecipeDishOptionComponent,
    RecipeDishTopping,
    RecipeTopping,
    RecipeToppingComponent
};

final class RecipeService {

    private const RELATIONS = [
        "item.brand",
        "item.currency",
        "components.item",
        "dishToppings.topping.currency",
        "dishToppings.topping.components.item",
        "options.components.item"
    ];

    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator {

        return self::getFilteredListQuery($companyId, $filters)->paginate($perPage);

    }

    public static function getFilteredListQuery(int $companyId, array $filters = []): Builder {

        $query = RecipeDish::query()
            ->where("company_id", $companyId)
            ->with(self::RELATIONS);

        $filterBy = $filters["filter_by"] ?? null;
        $word = $filters["word"] ?? null;

        if(Utilities::isDefined($word) && Utilities::isDefined($filterBy)) {

            $searchTerm = Utilities::getWordSearch($word);

            $query->where(function(Builder $builder) use($filterBy, $searchTerm) {

                if($filterBy === "all") {

                    $builder->whereHas("item", function(Builder $itemQuery) use($searchTerm) {
                        $itemQuery->where("name", "like", $searchTerm)
                            ->orWhere("internal_code", "like", $searchTerm)
                            ->orWhere("barcode", "like", $searchTerm)
                            ->orWhere("description", "like", $searchTerm);
                    })->orWhere("preparation_notes", "like", $searchTerm);

                    return;

                }

                if(in_array($filterBy, ["name", "internal_code", "barcode"], true)) {

                    $builder->whereHas("item", fn(Builder $itemQuery) => $itemQuery->where($filterBy, "like", $searchTerm));

                    return;

                }

                if($filterBy === "preparation_notes") {

                    $builder->where("preparation_notes", "like", $searchTerm);

                }

            });

        }

        return $query->orderByDesc("id");

    }

    public static function create(array $data, int $companyId, int $userId): RecipeDish {

        return DB::transaction(function() use($data, $companyId, $userId) {

            self::assertItemBelongsToCompany((int) $data["item_id"], $companyId);

            $recipe = RecipeDish::create([
                "company_id" => $companyId,
                "item_id" => (int) $data["item_id"],
                "yield_quantity" => (float) ($data["yield_quantity"] ?? 1),
                "waste_percentage" => (float) ($data["waste_percentage"] ?? 0),
                "preparation_notes" => $data["preparation_notes"] ?? null,
                "status" => $data["status"] ?? "active",
                "created_at" => now(),
                "created_by" => $userId
            ]);

            self::syncChildren($recipe, $data, $companyId, $userId);

            return $recipe->fresh(self::RELATIONS);

        });

    }

    public static function update(RecipeDish $recipe, array $data, int $companyId, int $userId): RecipeDish {

        if((int) $recipe->company_id !== $companyId) {

            throw new DomainException("La receta seleccionada no pertenece a la empresa.");

        }

        return DB::transaction(function() use($recipe, $data, $companyId, $userId) {

            self::assertItemBelongsToCompany((int) $data["item_id"], $companyId);

            $recipe->update([
                "item_id" => (int) $data["item_id"],
                "yield_quantity" => (float) ($data["yield_quantity"] ?? 1),
                "waste_percentage" => (float) ($data["waste_percentage"] ?? 0),
                "preparation_notes" => $data["preparation_notes"] ?? null,
                "status" => $data["status"] ?? "active",
                "updated_at" => now(),
                "updated_by" => $userId
            ]);

            self::syncChildren($recipe, $data, $companyId, $userId);

            return $recipe->fresh(self::RELATIONS);

        });

    }

    public static function delete(RecipeDish $recipe, int $companyId): void {

        if((int) $recipe->company_id !== $companyId) {

            throw new DomainException("La receta seleccionada no pertenece a la empresa.");

        }

        DB::transaction(function() use($recipe) {

            $toppingIds = RecipeDishTopping::where("recipe_dish_id", $recipe->id)
                ->pluck("recipe_topping_id")
                ->all();

            $recipe->delete();

            if(!empty($toppingIds)) {

                RecipeTopping::whereIn("id", $toppingIds)->delete();

            }

        });

    }

    private static function syncChildren(RecipeDish $recipe, array $data, int $companyId, int $userId): void {

        self::syncComponents($recipe, $data["components"] ?? [], $companyId, $userId);
        self::syncToppings($recipe, $data["toppings"] ?? [], $companyId, $userId);
        self::syncOptions($recipe, $data["options"] ?? [], $companyId, $userId);

    }

    private static function syncComponents(RecipeDish $recipe, array $components, int $companyId, int $userId): void {

        RecipeDishComponent::where("recipe_dish_id", $recipe->id)->delete();

        foreach($components as $component) {

            $itemId = (int) ($component["item_id"] ?? 0);
            $quantity = (float) ($component["quantity"] ?? 0);

            if($itemId <= 0 || $quantity <= 0) {
                continue;
            }

            self::assertItemBelongsToCompany($itemId, $companyId);

            RecipeDishComponent::create([
                "recipe_dish_id" => $recipe->id,
                "item_id" => $itemId,
                "quantity" => $quantity,
                "waste_percentage" => (float) ($component["waste_percentage"] ?? 0),
                "note" => $component["note"] ?? null,
                "status" => "active",
                "created_at" => now(),
                "created_by" => $userId
            ]);

        }

    }

    private static function syncToppings(RecipeDish $recipe, array $toppings, int $companyId, int $userId): void {

        $oldToppingIds = RecipeDishTopping::where("recipe_dish_id", $recipe->id)
            ->pluck("recipe_topping_id")
            ->all();

        RecipeDishTopping::where("recipe_dish_id", $recipe->id)->delete();

        if(!empty($oldToppingIds)) {

            RecipeTopping::whereIn("id", $oldToppingIds)->delete();

        }

        foreach($toppings as $toppingData) {

            $name = trim((string) ($toppingData["name"] ?? ""));

            if($name === "") {
                continue;
            }

            $topping = RecipeTopping::create([
                "company_id" => $companyId,
                "currency_id" => (int) ($toppingData["currency_id"] ?? 1),
                "item_id" => $toppingData["item_id"] ?? null,
                "name" => $name,
                "description" => $toppingData["description"] ?? null,
                "price" => (float) ($toppingData["price"] ?? 0),
                "max_quantity" => $toppingData["max_quantity"] ?? null,
                "status" => $toppingData["status"] ?? "active",
                "created_at" => now(),
                "created_by" => $userId
            ]);

            RecipeDishTopping::create([
                "recipe_dish_id" => $recipe->id,
                "recipe_topping_id" => $topping->id,
                "is_default" => (bool) ($toppingData["is_default"] ?? false),
                "min_quantity" => (int) ($toppingData["min_quantity"] ?? 0),
                "max_quantity" => $toppingData["max_quantity"] ?? null,
                "status" => "active",
                "created_at" => now(),
                "created_by" => $userId
            ]);

            self::syncToppingComponents($topping, $toppingData["components"] ?? [], $companyId, $userId);

        }

    }

    private static function syncToppingComponents(RecipeTopping $topping, array $components, int $companyId, int $userId): void {

        foreach($components as $component) {

            $itemId = (int) ($component["item_id"] ?? 0);
            $quantity = (float) ($component["quantity"] ?? 0);

            if($itemId <= 0 || $quantity <= 0) {
                continue;
            }

            self::assertItemBelongsToCompany($itemId, $companyId);

            RecipeToppingComponent::create([
                "recipe_topping_id" => $topping->id,
                "item_id" => $itemId,
                "quantity" => $quantity,
                "waste_percentage" => (float) ($component["waste_percentage"] ?? 0),
                "note" => $component["note"] ?? null,
                "status" => "active",
                "created_at" => now(),
                "created_by" => $userId
            ]);

        }

    }

    private static function syncOptions(RecipeDish $recipe, array $options, int $companyId, int $userId): void {

        RecipeDishOption::where("recipe_dish_id", $recipe->id)->delete();

        foreach($options as $optionData) {

            $name = trim((string) ($optionData["name"] ?? ""));

            if($name === "") {
                continue;
            }

            $option = RecipeDishOption::create([
                "recipe_dish_id" => $recipe->id,
                "name" => $name,
                "description" => $optionData["description"] ?? null,
                "max_portions" => $optionData["max_portions"] ?? null,
                "status" => $optionData["status"] ?? "active",
                "created_at" => now(),
                "created_by" => $userId
            ]);

            foreach(($optionData["components"] ?? []) as $component) {

                $itemId = (int) ($component["item_id"] ?? 0);
                $quantity = (float) ($component["quantity"] ?? 0);

                if($itemId <= 0 || $quantity <= 0) {
                    continue;
                }

                self::assertItemBelongsToCompany($itemId, $companyId);

                RecipeDishOptionComponent::create([
                    "recipe_dish_option_id" => $option->id,
                    "item_id" => $itemId,
                    "quantity" => $quantity,
                    "waste_percentage" => (float) ($component["waste_percentage"] ?? 0),
                    "note" => $component["note"] ?? null,
                    "status" => "active",
                    "created_at" => now(),
                    "created_by" => $userId
                ]);

            }

        }

    }

    private static function assertItemBelongsToCompany(int $itemId, int $companyId): void {

        if($itemId <= 0 || !Item::whereKey($itemId)->where("company_id", $companyId)->exists()) {

            throw new DomainException("El producto, servicio o insumo seleccionado no pertenece a la empresa.");

        }

    }

}
