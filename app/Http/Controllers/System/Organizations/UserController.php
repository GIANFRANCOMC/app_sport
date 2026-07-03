<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Organizations;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\System\Organizations\Users\{StoreUserRequest, UpdateUserRequest};
use App\Services\System\Base\{InitParamsCacheInvalidationService};
use App\Services\System\Organizations\Users\{UserConfigService, UserService};
use App\Services\System\Devices\BiometricDevices\BiometricDeviceService;
use App\Models\System\Organizations\{User};

class UserController extends BaseController {

    /**
     * Translation namespace for module
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
     * Get paginated list with filters
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
     * Display the module index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Organizations/users/main");

    }

    /**
     * Show the form for creating a new record
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created record
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

            InitParamsCacheInvalidationService::invalidate(
                InitParamsCacheInvalidationService::USERS,
                $this->getCompanyId()
            );

            return $this->createdResponse($user, "created", "user");

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    /**
     * Display the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param User $record
     * @return JsonResponse
     */
    public function show(User $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified record
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param User $record
     * @return void
     */
    public function edit(User $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified record
     *
     * @param UpdateUserRequest $request
     * @param int $id User ID
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse {

        try {

            $user = UserService::findByIdAndCompany($id, $this->getCompanyId(), null);

            if(!Utilities::isDefined($user)) {

                return $this->notFoundResponse();

            }

            $data = $this->prepareUserData($request);
            $user = UserService::update($user, $data, $this->getUserId());

            if(!Utilities::isDefined($user)) {

                return $this->errorResponse("update_failed");

            }

            InitParamsCacheInvalidationService::invalidate(
                InitParamsCacheInvalidationService::USERS,
                $this->getCompanyId()
            );

            return $this->updatedResponse($user, "updated", "user");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Remove the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param User $record
     * @return JsonResponse
     */
    public function destroy(User $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    public function registerBiometricFingerprint(Request $request, int $id): JsonResponse {

        $request->validate([
            "biometric_device_id" => ["required", "integer"],
            "device_user_id" => ["nullable", "integer", "min:1"],
            "finger_index" => ["nullable", "integer", "min:0", "max:9"]
        ], [
            "required" => "Campo obligatorio.",
            "integer" => "Debe ser un número entero.",
            "min" => "El valor es menor al permitido.",
            "max" => "El valor supera el límite permitido."
        ]);

        try {

            $user = UserService::findByIdAndCompany($id, $this->getCompanyId(), ["active"]);
            if(!$user) return $this->notFoundResponse();

            $deviceId = (int) $request->input("biometric_device_id");
            $device = BiometricDeviceService::findByIdAndCompany($deviceId, $this->getCompanyId(), ["active"]);
            if(!$device) {
                return $this->errorResponse("not_found", ["msg" => "El dispositivo biométrico no está disponible."], 404);
            }

            $deviceUserId = $request->filled("device_user_id")
                ? (int) $request->input("device_user_id")
                : BiometricDeviceService::getNextDeviceUserId($deviceId);

            $fingerprint = BiometricDeviceService::registerUserFingerprint(
                (int) $user->id,
                $deviceId,
                $deviceUserId,
                (int) $request->input("finger_index", 0),
                $this->getUserId(),
                $this->getCompanyId()
            );

            return $this->createdResponse($fingerprint, "fingerprint_registered", "biometric_fingerprint");

        }catch(\DomainException $exception) {

            return response()->json(["bool" => false, "msg" => $exception->getMessage()], 422);

        }catch(\Throwable $exception) {

            return $this->handleException($exception, "register_fingerprint");

        }

    }

    /**
     * Prepare record data from request
     *
     * @param StoreUserRequest|UpdateUserRequest $request
     * @return array
     */
    private function prepareUserData($request): array {

        $data = [
            "company_id"                => $this->getCompanyId(),
            "role_id"                   => $request->input("role_id"),
            "identity_document_type_id" => $request->input("identity_document_type_id"),
            "document_number"           => $request->input("document_number"),
            "name"                      => $request->input("name"),
            "email"                     => $request->input("email"),
            "phone_number"              => $request->input("phone_number"),
            "gender"                    => $request->input("gender"),
            "birthdate"                 => $request->input("birthdate"),
            "status"                    => $request->input("status"),
            "branch_ids"                => collect($request->input("branch_ids", []))
                                            ->filter()
                                            ->map(fn($branchId) => (int) $branchId)
                                            ->values()
                                            ->all(),
            "cash_register_ids"         => collect($request->input("cash_register_ids", []))
                                            ->filter()
                                            ->map(fn($cashRegisterId) => (int) $cashRegisterId)
                                            ->values()
                                            ->all(),
            "warehouse_ids"             => collect($request->input("warehouse_ids", []))
                                            ->filter()
                                            ->map(fn($warehouseId) => (int) $warehouseId)
                                            ->values()
                                            ->all()
        ];

        $data["branch_scope_mode"] = empty($data["branch_ids"]) ? "inherit" : "restricted";
        $data["cash_register_scope_mode"] = empty($data["cash_register_ids"]) ? "inherit" : "restricted";
        $data["warehouse_scope_mode"] = empty($data["warehouse_ids"]) ? "inherit" : "restricted";

        // Only include password if provided
        if(Utilities::isDefined($request->input("password"))) {

            $data["password"] = $request->input("password");

        }

        return $data;

    }

    /**
     * Get translation namespace for module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
