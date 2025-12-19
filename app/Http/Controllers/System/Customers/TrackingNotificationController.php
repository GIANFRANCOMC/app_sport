<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{Request, JsonResponse};

use App\Services\System\Customers\Tracking\{TrackingNotificationConfigService, TrackingNotificationService};
use App\Models\System\Customers\{SubscriptionEmail};

class TrackingNotificationController extends BaseController {

    /**
     * Translation namespace for tracking notification module
     */
    private const TRANSLATION_NAMESPACE = "System.Customers.tracking_notification";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);
        return TrackingNotificationConfigService::getInitParams($this->getCompanyId(), $page);

    }

    /**
     * Get paginated list of tracking notifications with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $filters = ["status" => $request->input("status")];
        $perPage = $this->getPerPage($request, Utilities::$per_page_default);

        return TrackingNotificationService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Display the tracking notifications index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Customers/tracking_notifications/main");

    }

    /**
     * Show the form for creating a new tracking notification
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created tracking notification
     * (Not implemented, but kept for REST compliance)
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request): void {

        // Not implemented

    }

    /**
     * Display the specified tracking notification
     * (Not used, but kept for REST compliance)
     *
     * @param SubscriptionEmail $email
     * @return JsonResponse
     */
    public function show(SubscriptionEmail $email): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified tracking notification
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param SubscriptionEmail $email
     * @return void
     */
    public function edit(SubscriptionEmail $email): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified tracking notification
     * (Not implemented, but kept for REST compliance)
     *
     * @param Request $request
     * @param mixed $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Remove the specified tracking notification
     * (Not used, but kept for REST compliance)
     *
     * @param SubscriptionEmail $email
     * @return JsonResponse
     */
    public function destroy(SubscriptionEmail $email): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Get translation namespace for tracking notification module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}

