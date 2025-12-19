<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Essentials;

use App\Helpers\System\{Utilities};
use App\Http\Controllers\System\Base\BaseController;
use Illuminate\Http\{JsonResponse, Request};
use stdClass;

use App\Models\System\Organizations\{UserPreference};

class HomeController extends BaseController {

    /**
     * Translation namespace for home module
     */
    private const TRANSLATION_NAMESPACE = "System.Essentials.home";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $initParams = new stdClass();
        $config     = new stdClass();
        $page       = $this->getPage($request);

        if(in_array($page, ["main"])) {

            //

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    /**
     * Display the home index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Essentials/home/main");

    }

    /**
     * Update user preferences
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse {

        try {

            $user = $this->getAuthUser();

            $data = [
                "show_actions"      => $request["show_actions"],
                "show_only_favorites" => $request["show_only_favorites"],
                "records"           => [
                    [
                        "sub_section_id" => $id,
                        "visible_in_menu" => $request["visible_in_menu"],
                        "is_favorite"     => $request["is_favorite"]
                    ]
                ]
            ];

            $updateItems = UserPreference::updateItems($user->id, "config_companies_sub_sections", $data);

            if($updateItems["bool"]) {

                return $this->successResponse(["preferences" => $user->formatted_preferences], "updated");

            }

            return $this->errorResponse("update_failed");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

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
