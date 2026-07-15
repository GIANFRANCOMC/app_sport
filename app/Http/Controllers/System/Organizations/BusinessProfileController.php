<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Organizations;

use Illuminate\Http\{JsonResponse, Request};

use App\Http\Controllers\System\Base\BaseController;
use App\Services\System\Organizations\BusinessProfileService;

final class BusinessProfileController extends BaseController {

    private const TRANSLATION_NAMESPACE = "System.Organizations.business_profile";

    public function index() {

        return view("System/general/Organizations/business_profile/main");

    }

    public function initParams(): JsonResponse {

        return response()->json([
            "industries" => BusinessProfileService::industries($this->getCompanyId())
        ]);

    }

    public function apply(Request $request): JsonResponse {

        $request->validate([
            "business_industry_id" => ["required", "integer"]
        ]);

        BusinessProfileService::applyIndustry(
            $this->getCompanyId(),
            (int) $request->input("business_industry_id"),
            $this->getUserId()
        );

        return response()->json([
            "bool" => true,
            "msg" => "Rubro aplicado. Los módulos disponibles fueron actualizados para esta empresa."
        ]);

    }

    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
