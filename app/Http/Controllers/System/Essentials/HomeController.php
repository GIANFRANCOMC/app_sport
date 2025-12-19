<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Essentials;

use App\Helpers\System\{ApiResponse, Utilities};
use App\Http\Controllers\System\Base\BaseController;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{DB};
use stdClass;

use App\Models\System\Organizations\{UserPreference};

class HomeController extends BaseController {

    /**
     * Translation namespace for home module
     */
    private const TRANSLATION_NAMESPACE = "System.Essentials.home";

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            //

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function index() {

        return view("System/general/Essentials/home/main");

    }

    public function update(Request $request, $id) {

        $user = $this->getAuthUser();

        $data = [
            "show_actions" => $request["show_actions"],
            "show_only_favorites" => $request["show_only_favorites"],
            "records" => [
                [
                    "sub_section_id" => $id,
                    "visible_in_menu" => $request["visible_in_menu"],
                    "is_favorite" => $request["is_favorite"]
                ]
            ]
        ];

        $updateItems = UserPreference::updateItems($user->id, "config_companies_sub_sections", $data);

        if($updateItems["bool"]) {

            return ApiResponse::success(["preferences" => $user->formatted_preferences], "Cambio realizado.");

        }

        return ApiResponse::error("No se pudo actualizar las preferencias.", 500);

    }

    /**
     * Get translation namespace for home module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
