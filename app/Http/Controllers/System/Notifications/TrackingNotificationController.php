<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Notifications;

use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Services\System\Notifications\{TrackingNotificationConfigService, TrackingNotificationService};
use App\Models\System\Customers\{SubscriptionEmail};

class TrackingNotificationController extends Controller {

    use HandlesApiResponses;

    /**
     * Translation namespace for tracking notification module
     */
    private const TRANSLATION_NAMESPACE = "System.Notifications.tracking_notification";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return TrackingNotificationConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of tracking notifications with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $userAuth = Auth::user();
        $filters  = ["status" => $request->input("status")];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_default);

        return TrackingNotificationService::getPaginatedList($userAuth->company_id, $filters, $perPage);

    }

    public function index() {

        return view("System/general/Notifications/tracking_notifications/main");

    }

    public function create() {

        //

    }

    public function store(Request $request) { // StoreTrackingNotificationRequest

        //

    }

    public function show(SubscriptionEmail $email): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    public function edit(SubscriptionEmail $email): void {

        // Form is handled by frontend SPA

    }

    public function update(Request $request, $id): JsonResponse { // UpdateTrackingNotificationRequest

        return $this->errorResponse("not_implemented", [], 501);

    }

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
