<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Organizations;

use Exception;
use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};

use App\Http\Requests\System\Organizations\Users\{StoreUserRequest, UpdateUserRequest};
use App\Services\System\Organizations\Users\{UserConfigService, UserService};
use App\Models\System\Organizations\{User};

class UserController extends BaseController {

    /**
     * Translation namespace for user module
     */
    private const TRANSLATION_NAMESPACE = "System.Organizations.user";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);
        return UserConfigService::getInitParams($this->getCompanyId(), $page);

    }

    /**
     * Get paginated list of users with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $filters = $this->getFilters($request);
        $perPage = $this->getPerPage($request, Utilities::$per_page_default);

        return UserService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Display the users index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Organizations/users/main");

    }

    /**
     * Show the form for creating a new user
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created user
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse {

        try {

            $data = $this->prepareUserData($request);
            $user = UserService::create($data, $this->getUserId());

            if(!Utilities::isDefined($user)) {

                return $this->errorResponse("create_failed");

            }

            UserConfigService::clearAllCache($this->getCompanyId());

            return $this->createdResponse($user, "created", "user");

        }catch(Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    /**
     * Display the specified user
     * (Not used, but kept for REST compliance)
     *
     * @param User $record
     * @return JsonResponse
     */
    public function show(User $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified user
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param User $record
     * @return void
     */
    public function edit(User $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified user
     *
     * @param UpdateUserRequest $request
     * @param int $id User ID
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse {

        try {

            $user = UserService::findByIdAndCompany($id, $this->getCompanyId());

            if(!Utilities::isDefined($user)) {

                return $this->notFoundResponse();

            }

            $data = $this->prepareUserData($request);
            $user = UserService::update($user, $data, $this->getUserId());

            if(!Utilities::isDefined($user)) {

                return $this->errorResponse("update_failed");

            }

            UserConfigService::clearAllCache($this->getCompanyId());

            return $this->updatedResponse($user, "updated", "user");

        }catch(Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Remove the specified user
     * (Not used, but kept for REST compliance)
     *
     * @param User $record
     * @return JsonResponse
     */
    public function destroy(User $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare user data from request
     *
     * @param StoreUserRequest|UpdateUserRequest $request
     * @param object|null $userAuth
     * @return array
     */
    private function prepareUserData($request): array {

        $data = [
            "company_id"                => $this->getCompanyId(),
            "role_id"                   => $request->role_id,
            "identity_document_type_id" => $request->identity_document_type_id,
            "document_number"           => $request->document_number,
            "name"                      => $request->name,
            "email"                     => $request->email,
            "phone_number"              => $request->phone_number,
            "gender"                    => $request->gender ?? "other",
            "birthdate"                 => $request->birthdate,
            "status"                    => $request->status
        ];

        // Only include password if provided
        if(Utilities::isDefined($request->password)) {

            $data["password"] = $request->password;

        }

        return $data;

    }

    /**
     * Get translation namespace for user module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
