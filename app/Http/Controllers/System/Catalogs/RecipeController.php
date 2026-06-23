<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Catalogs;

use Illuminate\Http\{JsonResponse, Request};

use App\Helpers\System\Utilities;
use App\Http\Controllers\System\Base\BaseController;
use App\Http\Requests\System\Catalogs\Recipes\{StoreRecipeRequest, UpdateRecipeRequest};
use App\Models\System\Catalogs\RecipeDish;
use App\Services\System\Base\InitParamsCacheInvalidationService;
use App\Services\System\Catalogs\Recipes\{RecipeConfigService, RecipeService};

class RecipeController extends BaseController {

    private const TRANSLATION_NAMESPACE = "System.Catalogs.recipe";

    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

    public function index() {

        return view("System/general/Catalogs/recipes/main");

    }

    public function initParams(Request $request) {

        return RecipeConfigService::getInitParams($this->getCompanyId(), $this->getPage($request));

    }

    public function list(Request $request) {

        return RecipeService::getPaginatedList(
            $this->getCompanyId(),
            $this->getFilters($request),
            $this->getPerPage($request, Utilities::$per_page_default)
        );

    }

    public function store(StoreRecipeRequest $request): JsonResponse {

        try {

            $recipe = RecipeService::create($request->validated(), $this->getCompanyId(), $this->getUserId());
            InitParamsCacheInvalidationService::invalidate(InitParamsCacheInvalidationService::ITEMS, $this->getCompanyId());

            return response()->json([
                "bool" => true,
                "msg" => "Receta o platillo agregado correctamente.",
                "data" => $recipe
            ], 201);

        }catch(\Throwable $e) {

            return response()->json([
                "bool" => false,
                "msg" => $e->getMessage()
            ], 422);

        }

    }

    public function update(UpdateRecipeRequest $request, int $id): JsonResponse {

        try {

            $recipe = RecipeDish::with(["item"])->findOrFail($id);
            $recipe = RecipeService::update($recipe, $request->validated(), $this->getCompanyId(), $this->getUserId());
            InitParamsCacheInvalidationService::invalidate(InitParamsCacheInvalidationService::ITEMS, $this->getCompanyId());

            return response()->json([
                "bool" => true,
                "msg" => "Receta o platillo actualizado correctamente.",
                "data" => $recipe
            ]);

        }catch(\Throwable $e) {

            return response()->json([
                "bool" => false,
                "msg" => $e->getMessage()
            ], 422);

        }

    }

    public function show(int $id): JsonResponse {

        $recipe = RecipeDish::with([
            "item.brand",
            "item.currency",
            "components.item",
            "dishToppings.topping.components.item",
            "options.components.item"
        ])
            ->where("company_id", $this->getCompanyId())
            ->findOrFail($id);

        return response()->json($recipe);

    }

    public function destroy(int $id): JsonResponse {

        try {

            RecipeService::delete(RecipeDish::findOrFail($id), $this->getCompanyId());
            InitParamsCacheInvalidationService::invalidate(InitParamsCacheInvalidationService::ITEMS, $this->getCompanyId());

            return response()->json([
                "bool" => true,
                "msg" => "Receta o platillo eliminado correctamente."
            ]);

        }catch(\Throwable $e) {

            return response()->json([
                "bool" => false,
                "msg" => $e->getMessage()
            ], 422);

        }

    }

}
