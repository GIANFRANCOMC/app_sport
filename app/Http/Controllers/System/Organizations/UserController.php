<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Organizations;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Http\Requests\System\Organizations\Users\{StoreUserRequest, UpdateUserRequest};
use App\Services\System\Organizations\Users\{UserConfigService, UserService};
use App\Models\System\Organizations\{User};

class UserController extends Controller {

    use HandlesApiResponses;

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

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return UserConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of users with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $userAuth = Auth::user();
        $filters  = ["filter_by" => $request->input("filter_by"), "word" => $request->input("word")];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_default);

        return UserService::getPaginatedList($userAuth->company_id, $filters, $perPage);

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

            $userAuth = Auth::user();
            $data     = $this->prepareUserData($request, $userAuth);
            $user     = UserService::create($data, $userAuth->id);

            if(!Utilities::isDefined($user)) {

                return $this->errorResponse("create_failed");

            }

            UserConfigService::clearAllCache($userAuth->company_id);

            return $this->createdResponse($user, "created", "user");

        }catch(Exception $e) {

            return $this->errorResponse("exception_create", ["message" => $e->getMessage()]);

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

            $userAuth = Auth::user();
            $user     = UserService::findByIdAndCompany($id, $userAuth->company_id);

            if(!Utilities::isDefined($user)) {

                return $this->notFoundResponse();

            }

            $data = $this->prepareUserData($request, $userAuth);
            $user = UserService::update($user, $data, $userAuth->id);

            if(!Utilities::isDefined($user)) {

                return $this->errorResponse("update_failed");

            }

            UserConfigService::clearAllCache($userAuth->company_id);

            return $this->updatedResponse($user, "updated", "user");

        }catch(Exception $e) {

            return $this->errorResponse("exception_update", ["message" => $e->getMessage()]);

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
    private function prepareUserData($request, ?object $userAuth = null): array {

        $data = [
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

        if($userAuth) {

            $data["company_id"] = $userAuth->company_id;

        }

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
