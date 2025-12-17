<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Essentials;

use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Services\System\Essentials\{DashboardConfigService, DashboardService};

class DashboardController extends Controller {

    use HandlesApiResponses;

    /**
     * Translation namespace for dashboard module
     */
    private const TRANSLATION_NAMESPACE = "System.Essentials.dashboard";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return DashboardConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Display the dashboard index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Essentials/dashboard/main");

    }

    /**
     * Get dashboard data for a specific date
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function initData(Request $request): JsonResponse {

        $userAuth = Auth::user();
        $date     = Utilities::isDefined($request->date) && Utilities::isValidDateFormat($request->date) 
                    ? $request->date 
                    : date("Y-m-d");

        $data = DashboardService::getDashboardData($userAuth->company_id, $date);

        return $this->successResponse($data, "data_obtained");

    }

    /**
     * Get translation namespace for dashboard module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
