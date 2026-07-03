<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Assets;

use App\Http\Controllers\System\Base\BaseController;
use App\Models\System\Assets\AssetCategory;
use App\Rules\System\Defaults\UniqueInCompany;
use App\Services\System\Base\InitParamsCacheInvalidationService;
use Illuminate\Http\{JsonResponse, Request};

final class AssetCategoryController extends BaseController {

    public function list() {
        return AssetCategory::query()
            ->where("company_id", $this->getCompanyId())
            ->withCount("assets")
            ->orderBy("name")
            ->get();
    }

    public function store(Request $request): JsonResponse {
        $data = $request->validate([
            "name" => ["required", "string", "max:150", new UniqueInCompany("asset_categories", "name")],
            "description" => ["nullable", "string", "max:500"],
            "status" => ["nullable", "in:active,inactive"]
        ]);
        $category = AssetCategory::create([
            ...$data,
            "company_id" => $this->getCompanyId(),
            "status" => $data["status"] ?? "active",
            "created_by" => $this->getUserId()
        ]);
        InitParamsCacheInvalidationService::invalidate(InitParamsCacheInvalidationService::ASSETS, $this->getCompanyId());

        return response()->json(["bool" => true, "msg" => "Categoría de activo agregada.", "data" => $category], 201);
    }

    public function update(Request $request, int $id): JsonResponse {
        $category = AssetCategory::query()->where("company_id", $this->getCompanyId())->findOrFail($id);
        $data = $request->validate([
            "name" => ["required", "string", "max:150", new UniqueInCompany("asset_categories", "name", $id)],
            "description" => ["nullable", "string", "max:500"],
            "status" => ["required", "in:active,inactive"]
        ]);
        $category->fill([...$data, "updated_by" => $this->getUserId()])->save();
        InitParamsCacheInvalidationService::invalidate(InitParamsCacheInvalidationService::ASSETS, $this->getCompanyId());

        return response()->json(["bool" => true, "msg" => "Categoría de activo actualizada.", "data" => $category]);
    }

    protected function getTranslationNamespace(): string {
        return "System.Assets.asset";
    }
}
